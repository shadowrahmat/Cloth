// Shared helpers for the generated project hooks (zero dependencies, Node 18+).
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

export function readInput() {
  let raw = '';
  try { raw = fs.readFileSync(0, 'utf8'); } catch { /* no stdin */ }
  try { return JSON.parse(raw || '{}'); } catch { throw new Error('hook input is not valid JSON'); }
}

export function loadConfig() {
  const file = path.join(path.dirname(fileURLToPath(import.meta.url)), 'config.json');
  try { return JSON.parse(fs.readFileSync(file, 'utf8')); } catch { throw new Error(`cannot read ${file}`); }
}

// Exit code 2 blocks the tool call; stderr is shown to Claude.
export function block(message) {
  process.stderr.write(`Blocked by project hook: ${message}\n`);
  process.exit(2);
}

// Defers to the user: Claude Code shows a permission prompt with this reason.
export function ask(reason) {
  process.stdout.write(JSON.stringify({ hookSpecificOutput: { hookEventName: 'PreToolUse', permissionDecision: 'ask', permissionDecisionReason: reason } }) + '\n');
  process.exit(0);
}

// A crashing hook must not silently let the call through. Guards (security checks) fail closed: exit 2 blocks the call.
// Escape hatch if a hook bug blocks your work: set HOOK_FAIL_OPEN=1. Non-guard hooks (format, context) stay non-blocking (exit 1).
export function run(main, { guard = false } = {}) {
  try { main(); } catch (e) {
    const closed = guard && process.env.HOOK_FAIL_OPEN !== '1';
    process.stderr.write(`Project hook error: ${e.message}. The check did not run${closed ? ', so the call is blocked (set HOOK_FAIL_OPEN=1 to bypass)' : ''}.\n`);
    process.exit(closed ? 2 : 1);
  }
}
