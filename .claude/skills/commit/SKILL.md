---
name: commit
description: Inspects working tree changes, stages relevant files safely, writes a Conventional Commit message, and creates a local Git commit. Triggered by /commit or requests to commit changes.
disable-model-invocation: true
---

# Git Commit Skill

Use when the user asks to commit changes or types `/commit`. Local commit only: no push, no branch switch, no merge.

1. **Inspect:** `git status`, `git diff`, `git diff --staged`.
2. **Safety:** never stage `.env`, secrets, private keys, database dumps or runtime logs, or local-only paths (`.env`, `.env.backup`, `.env.production`, `auth.json`, `/cloth (stray SQLite DB copy)`, `/capcut-drafts.mcpb (unrelated tool bundle)`). Stage only changes related to the task.
3. **Message** (Conventional Commits, imperative, concise): `feat(<scope>):`, `fix(<scope>):`, `refactor(<scope>):`, `docs:`, `test:`, `chore:`. Do not add `Co-Authored-By` or "Generated with" lines to commits or PRs.
4. **No doc updates here:** `/commit` is a checkpoint; `README.md`, `deploy/DEPLOY.md` are synced only in `/commit-push`.
5. Stage the targeted files and commit. Report the hash, message and changed files.
