// UserPromptSubmit: warns the user (never Claude, so it costs no tokens) when the session transcript gets large.
// Threshold: context_nudge_kb in ./config.json (0 disables). Repeats only after the transcript grows another 25%.
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { readInput, loadConfig, run } from './lib.mjs';

run(() => {
  const input = readInput();
  const cfg = loadConfig();
  const limitKb = Number(cfg.contextNudgeKb) || 0;
  const file = input.transcript_path;
  if (limitKb <= 0 || !file) process.exit(0);
  let kb;
  try { kb = Math.round(fs.statSync(file).size / 1024); } catch { process.exit(0); }
  if (kb < limitKb) process.exit(0);

  const state = path.join(os.tmpdir(), `claude-context-nudge-${String(input.session_id || 'x').replace(/[^\w-]/g, '')}.json`);
  let last = 0;
  try { last = JSON.parse(fs.readFileSync(state, 'utf8')).kb || 0; } catch { /* first nudge */ }
  if (last && kb < last * 1.25) process.exit(0);
  try { fs.writeFileSync(state, JSON.stringify({ kb })); } catch { /* nudge anyway */ }

  process.stdout.write(JSON.stringify({ systemMessage: `Context is large (~${Math.round(kb / 4)}k tokens of transcript). Every turn re-sends it: run /compact to summarize, or /clear when starting a new task.` }) + '\n');
});
