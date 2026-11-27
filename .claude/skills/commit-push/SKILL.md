---
name: commit-push
description: Inspects the working tree, stages changes safely, commits with a Conventional Commit message, and pushes to origin with upstream tracking. Triggered by /commit-push or requests to commit and push together.
disable-model-invocation: true
---

# Git Commit & Push Skill

Use when the user types `/commit-push` or asks to commit and push in one step.

1. **Inspect:** `git branch --show-current`, `git status`, diff. If the tree is clean but there are unpushed commits, skip to step 5.
2. **Doc-sync (ship time):** run the `doc-sync` skill over everything since the last push (`git diff @{upstream}..HEAD` plus the working tree; no upstream → compare with `origin/main`). Skip if only docs/config changed or the user passed `--no-docs`.
3. **Safety:** never stage `.env`, secrets, private keys, dumps or logs, or local-only paths (`.env`, `.env.backup`, `.env.production`, `auth.json`, `/cloth (stray SQLite DB copy)`, `/capcut-drafts.mcpb (unrelated tool bundle)`).
4. **Commit:** use the user's message or write a Conventional Commit. Do not add `Co-Authored-By` or "Generated with" lines to commits or PRs.
5. **Push:** if the branch is `main`, halt and ask for confirmation first (protected). Otherwise `git push -u origin <current-branch>`.
6. **Report:** hash, message, files, push status.
