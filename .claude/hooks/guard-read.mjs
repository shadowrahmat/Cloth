// PreToolUse(Read): stops whole-file reads of huge files and lockfiles (they burn context for little value).
// A read with offset/limit/pages is always allowed. Threshold: big_file_kb in ./config.json (0 disables).
import fs from 'node:fs';
import path from 'node:path';
import { readInput, loadConfig, block, run } from './lib.mjs';

const LOCKFILES = new Set(['package-lock.json', 'yarn.lock', 'pnpm-lock.yaml', 'composer.lock', 'Cargo.lock', 'poetry.lock', 'uv.lock', 'Gemfile.lock', 'go.sum', 'bun.lock', 'Pipfile.lock']);

run(() => {
  const input = readInput();
  const ti = input.tool_input || {};
  const file = ti.file_path;
  if (!file) process.exit(0);
  if (ti.offset !== undefined || ti.limit !== undefined || ti.pages !== undefined) process.exit(0);
  const cfg = loadConfig();
  const abs = path.resolve(input.cwd || process.cwd(), file);
  let size;
  try { const st = fs.statSync(abs); if (!st.isFile()) process.exit(0); size = st.size; } catch { process.exit(0); }
  const base = path.basename(abs);
  const kb = Math.round(size / 1024);
  if (LOCKFILES.has(base) && size > 4096) block(`${base} is a lockfile (${kb} KB). Use Grep for the package you need, or Read with offset/limit.`);
  const limitKb = Number(cfg.bigFileKb) || 0;
  if (limitKb > 0 && size > limitKb * 1024) block(`${base} is ${kb} KB (limit ${limitKb} KB, roughly ${Math.round(size / 4000)}k tokens). Use Grep to find what you need, then Read with offset/limit.`);
}, { guard: true });
