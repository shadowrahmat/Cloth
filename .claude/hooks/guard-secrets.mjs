// PreToolUse(Write|Edit|MultiEdit|NotebookEdit): asks before writes to .env / local-only paths (secrets_access: deny blocks them) and blocks obvious secrets in content.
import fs from 'node:fs';
import path from 'node:path';
import { readInput, loadConfig, block, ask, run } from './lib.mjs';

const SECRET_PATTERNS = [
  ['AWS access key', /\bAKIA[0-9A-Z]{16}\b/],
  ['private key block', /-----BEGIN (?:RSA |EC |DSA |OPENSSH |PGP )?PRIVATE KEY-----/],
  ['GitHub token', /\bgh[pousr]_[A-Za-z0-9]{36,}\b/],
  ['Slack token', /\bxox[baprs]-[A-Za-z0-9-]{10,}/],
  ['Google API key', /\bAIza[0-9A-Za-z_-]{35}\b/],
  ['API secret key (sk-...)', /\bsk-[A-Za-z0-9_-]{32,}/],
];

function matchesLocalOnly(rel, raw) {
  const pat = String(raw).trim().replace(/^\.?\//, '').toLowerCase();
  if (!pat || pat === '.env') return false;
  if (pat.endsWith('/')) return rel.startsWith(pat) || rel.includes('/' + pat);
  if (pat.startsWith('*')) return rel.endsWith(pat.slice(1));
  return rel === pat || rel.startsWith(pat + '/');
}

run(() => {
  const input = readInput();
  const ti = input.tool_input || {};
  const file = ti.file_path || ti.notebook_path;
  if (!file) process.exit(0);
  const cfg = loadConfig();
  const root = process.env.CLAUDE_PROJECT_DIR || input.cwd || process.cwd();
  const abs = path.resolve(input.cwd || root, file);
  const real = (p) => { try { return fs.realpathSync(p); } catch { return p; } };
  // Judge both the path as written and where it really points (a symlink notes.txt -> .env), compared case-insensitively.
  const rels = [...new Set([path.relative(root, abs), path.relative(real(root), real(abs))])].map((r) => r.split(path.sep).join('/').toLowerCase());

  // secrets_access "ask" (default): the user is prompted and can approve the edit; "deny": always refused.
  const guard = (message) => (cfg.secretsAccess === 'deny' ? block(message) : ask(message));
  for (const rel of rels.filter((r) => !r.startsWith('..'))) {
    const base = path.posix.basename(rel);
    if (/^\.env(\..+)?$/.test(base) && !/\.(example|sample|template|dist)$/.test(base)) guard(`${rel} holds secrets. Approve only if Claude should edit it.`);
    if ((cfg.localOnly || []).some((p) => matchesLocalOnly(rel, p))) guard(`${rel} is a local-only path (git-ignored, may hold credentials or dumps). Approve only if Claude should edit it.`);
  }

  const texts = [ti.content, ti.new_string, ti.new_source, ...(Array.isArray(ti.edits) ? ti.edits.map((e) => e && e.new_string) : [])].filter((t) => typeof t === 'string');
  for (const text of texts) {
    for (const [name, re] of SECRET_PATTERNS) {
      if (re.test(text)) block(`content looks like it contains a secret (${name}). Use an environment variable or the project's secret store instead.`);
    }
  }
}, { guard: true });
