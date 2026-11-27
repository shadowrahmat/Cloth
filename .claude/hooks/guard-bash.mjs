// PreToolUse(Bash): blocks risky git/gh commands. Settings come from ./config.json.
import { spawnSync } from 'node:child_process';
import { readInput, loadConfig, block, ask, run } from './lib.mjs';

// Heredoc bodies (<<EOF ... EOF) are data, not commands: text such as "git push origin main" inside a script or a
// commit message must not be judged as a command. The attribution check still reads the full command.
function stripHeredocs(cmd) {
  return cmd.replace(/<<-?[ \t]*(['"]?)([A-Za-z_]\w*)\1[^\n]*\n[\s\S]*?\n[ \t]*\2[ \t]*(?=\n|$)/g, (m) => m.slice(0, m.indexOf('\n')));
}

// Splits on ; | & and newlines that are outside quotes.
function splitSegments(cmd) {
  const out = []; let cur = ''; let q = '';
  for (let i = 0; i < cmd.length; i++) {
    const ch = cmd[i];
    if (q) {
      cur += ch;
      if (ch === '\\' && q === '"') { cur += cmd[++i] ?? ''; continue; }
      if (ch === q) q = '';
      continue;
    }
    if (ch === '"' || ch === "'") { q = ch; cur += ch; continue; }
    if (ch === '\\') { cur += ch + (cmd[++i] ?? ''); continue; }
    if (ch === ';' || ch === '\n' || ch === '|' || ch === '&') { out.push(cur); cur = ''; while (cmd[i + 1] === '|' || cmd[i + 1] === '&') i++; continue; }
    cur += ch;
  }
  out.push(cur);
  return out.map((s) => s.trim().replace(/^[({]+\s*/, '')).filter(Boolean);
}

function tokenize(seg) {
  const t = []; let cur = ''; let q = ''; let has = false;
  for (const ch of seg) {
    if (q) { if (ch === q) q = ''; else cur += ch; continue; }
    if (ch === '"' || ch === "'") { q = ch; has = true; continue; }
    if (/\s/.test(ch)) { if (cur || has) { t.push(cur); cur = ''; has = false; } continue; }
    cur += ch;
  }
  if (cur || has) t.push(cur);
  return t;
}

const WRAPPERS = new Set(['env', 'command', 'builtin', 'time', 'nohup', 'exec', 'sudo', 'doas', 'nice']);
// Drops VAR=x prefixes and wrapper programs (env, sudo, command ...) so `env git push` is judged like `git push`.
function unwrap(tokens) {
  let i = 0;
  for (;;) {
    while (i < tokens.length && /^\w+=/.test(tokens[i])) i++;
    if (!WRAPPERS.has((tokens[i] || '').replace(/^.*[\\/]/, ''))) break;
    i++;
    while (i < tokens.length && tokens[i].startsWith('-')) i++;
  }
  return tokens.slice(i);
}
// `bash -c "<cmd>"` and `eval <cmd>` -> the inner command string, otherwise ''.
function shellInner(tokens) {
  const name = (tokens[0] || '').replace(/^.*[\\/]/, '');
  if (name === 'eval') return tokens.slice(1).join(' ');
  if (['sh', 'bash', 'zsh', 'dash'].includes(name)) { const i = tokens.findIndex((t) => /^-[a-z]*c[a-z]*$/.test(t)); if (i >= 0) return tokens[i + 1] || ''; }
  return '';
}

// -> { sub, args, dir } for `git [global opts] <sub> ...`, otherwise null.
function parseGit(tokens) {
  let i = 0;
  while (i < tokens.length && /^\w+=/.test(tokens[i])) i++;
  if (!/(^|[\\/])git(\.exe)?$/.test(tokens[i] || '')) return null;
  i++;
  let dir = '';
  while (i < tokens.length && tokens[i].startsWith('-')) {
    if (tokens[i] === '-C') { dir = tokens[i + 1] || ''; i += 2; }
    else if (['-c', '--git-dir', '--work-tree', '--namespace'].includes(tokens[i])) i += 2;
    else i++;
  }
  return tokens[i] ? { sub: tokens[i], args: tokens.slice(i + 1), dir } : null;
}

run(() => {
  const input = readInput();
  const cmd = String((input.tool_input || {}).command || '');
  if (!/\bgit\b|\bgh\b/.test(cmd)) process.exit(0);
  const cfg = loadConfig();
  const protectedBranches = cfg.protectedBranches || [];
  const cwd = input.cwd || process.cwd();
  const branchCache = new Map();
  const currentBranch = (dir) => {
    const key = dir || '';
    if (!branchCache.has(key)) {
      const r = spawnSync('git', [...(dir ? ['-C', dir] : []), 'symbolic-ref', '--short', 'HEAD'], { cwd, encoding: 'utf8' });
      branchCache.set(key, r.status === 0 ? r.stdout.trim() : '');
    }
    return branchCache.get(key);
  };
  const isProtected = (b) => Boolean(b) && protectedBranches.includes(b);
  // protected_push "ask" (default): the user is prompted and can approve; "block": always refused.
  const askOnly = cfg.protectedPush !== 'block';
  const protect = (message) => (askOnly ? asks.push(message) : block(message));

  const asks = [];
  let sawCommit = false;
  // Best effort, not a sandbox: it follows wrappers, `bash -c`, `eval`, $(...) and backticks, but not aliases or scripts.
  // The real protection for main/dev is the host's branch protection.
  const inspect = (text, depth) => {
  if (depth > 3) return;
  for (const m of text.matchAll(/\$\(([^()]*)\)|`([^`]*)`/g)) inspect(m[1] ?? m[2], depth + 1);
  for (const seg of splitSegments(text)) {
    const tokens = unwrap(tokenize(seg));
    const inner = shellInner(tokens);
    if (inner) inspect(inner, depth + 1);
    if (/(^|[\\/])git(\.exe)?$/.test(tokens[0] || '') && tokens.some((t) => /^core\.hookspath=/i.test(t))) asks.push('overriding core.hooksPath can disable the git hooks.');
    if (tokens[0] === 'gh' && tokens[1] === 'pr' && ['create', 'edit'].includes(tokens[2])) sawCommit = true;
    const g = parseGit(tokens);
    if (!g) continue;
    const flags = g.args.filter((a) => a.startsWith('-'));
    const positional = g.args.filter((a) => !a.startsWith('-'));
    const branch = currentBranch(g.dir);

    if (g.sub === 'commit') {
      sawCommit = true;
      if (flags.includes('--no-verify') || flags.some((f) => /^-[a-zA-Z]*n[a-zA-Z]*$/.test(f))) block('git commit --no-verify (skipping hooks) is not allowed.');
      if (isProtected(branch)) protect(`direct commit to protected branch "${branch}". Approve only if you want this on ${branch}.`);
    }
    if (g.sub === 'push') {
      if (flags.includes('--no-verify')) block('git push --no-verify (skipping hooks) is not allowed.');
      if (flags.includes('--mirror') || flags.includes('--all')) block('git push --mirror/--all would also push protected branches; name the branch instead.');
      if (flags.some((f) => f === '--force' || f.startsWith('--force-with-lease') || f === '--force-if-includes' || /^-[a-zA-Z]*f[a-zA-Z]*$/.test(f)) || positional.slice(1).some((r) => r.startsWith('+'))) block('force-push is not allowed.');
      const refspecs = positional.slice(1);
      const targets = refspecs.map((r) => r.replace(/^\+/, '').split(':').pop().replace(/^refs\/heads\//, '')).map((t) => (t === 'HEAD' ? branch : t));
      if (targets.some(isProtected)) protect(`push to a protected branch (${protectedBranches.join(', ')}). Approve only if you want this push.`);
      if (!refspecs.length && isProtected(branch)) protect(`push of the protected branch "${branch}". Approve only if you want this push.`);
    }
    if (g.sub === 'config' && g.args.some((a) => /^core\.hookspath$/i.test(a))) asks.push('changing core.hooksPath can disable the git hooks.');
    if (g.sub === 'reset' && flags.includes('--hard')) asks.push('git reset --hard discards uncommitted work.');
    if (g.sub === 'branch' && (flags.includes('-D') || (flags.includes('-d') && (flags.includes('-f') || flags.includes('--force'))))) asks.push('git branch -D force-deletes a branch.');
  }
  };
  inspect(stripHeredocs(cmd), 0);

  if (sawCommit && cfg.blockAttribution && /co-authored-by:|\bgenerated with\b/i.test(cmd)) {
    block('this project forbids "Co-Authored-By" / "Generated with" attribution lines in commits and PRs. Remove them and retry.');
  }
  if (asks.length) ask(asks.join(' '));
}, { guard: true });
