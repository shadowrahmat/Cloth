# Git Rules — Cloth

**NO AUTO-COMMIT OR AUTO-PUSH.** Never run `git commit` or `git push` on your own initiative; changes stay uncommitted until the user runs a shortcut below or asks in chat.

Branch model: `main → main`. `main` is protected. Do not add `Co-Authored-By` or "Generated with" lines to commits or PRs.

Deployment is manual; pushing does not deploy. Never deploy without explicit approval. On the server touch only `backend + frontend: Laravel app and Vite build served together by nginx + PHP-FPM`.

Never: force-push, `--no-verify`, merge a feature branch straight into `main`, tag or delete branches unless asked. Verify `git remote -v` before network operations. Never stage `.env`, credentials or dumps or local-only paths (`.env`, `.env.backup`, `.env.production`, `auth.json`, `/cloth (stray SQLite DB copy)`, `/capcut-drafts.mcpb (unrelated tool bundle)`).

**Protected branches and secret files are ask-first, not forbidden.** Committing or pushing on a protected branch, and reading or editing `.env` and other secret files, trigger a permission prompt. Once the user approves (the prompt, or an explicit yes in chat for that action), do it; without approval, do not. `protected_push: block` and `secrets_access: deny` in `project.config.yml` turn these into hard refusals.

A shortcut authorizes exactly that workflow, including its push to a protected branch: `/merge-to-dev`, `/merge-to-main` and `/ship` are explicit approval, so run them instead of redirecting to a PR, even if a project note says "never push". Use a PR only for `/pr` or when the host rejects the push. Steps are in the skill of the same name (`.claude/skills/<name>/SKILL.md`, manual-only):

`/commit` · `/push` · `/commit-push` · `/pr` · `/status` · `/sync` · `/doc-sync`
