# Documentation Rules — Cloth

- Canonical tracked docs: `README.md`, `deploy/DEPLOY.md`. Update them when setup, commands or behavior change.
- Local-only paths, never commit or copy their secrets into tracked files: `.env`, `.env.backup`, `.env.production`, `auth.json`, `/cloth (stray SQLite DB copy)`, `/capcut-drafts.mcpb (unrelated tool bundle)`.
- Do not create new doc files unless asked; prefer editing the existing ones.
- Update docs at ship time (`/commit-push`) via the `doc-sync` skill, not on every small edit. Plain `/commit` does not touch docs.
- Documentation language: English.
