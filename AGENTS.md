# Agents Guide & Workspace Rules

**Cloth**

ATELIER ÉTOILE: luxury fashion e-commerce SPA (Vue 3) with a Laravel 13 API and admin CMS

Type: `fullstack` · Stack: laravel13, php8.3, vue3, vite, tailwind4, pinia, sanctum, mysql, sqlite · Package manager: `composer`

> Generated from `project.config.yml` by claude-template `init.mjs`. Change the config and re-run init instead of editing this file by hand.

---

## 1. Fast Reference
- `app/` — Laravel application code (Http, Models, Services, Support, Console, Providers)
- `resources/` — Vue 3 frontend (js), css, Blade views
- `routes/` — web.php, api.php, console.php
- `database/` — migrations, seeders, factories
- `tests/` — Feature and Unit tests
- `deploy/` — server provisioning, update and data import scripts, DEPLOY.md
- `config/, bootstrap/, public/, storage/` — standard Laravel

Local-only / git-ignored (never commit):

- `.env`
- `.env.backup`
- `.env.production`
- `auth.json`
- `/cloth (stray SQLite DB copy)`
- `/capcut-drafts.mcpb (unrelated tool bundle)`

---

## 2. Agent Execution Rules
1. **Commands:** Run project commands directly on the host with `composer`.
2. **Verification:** Run `php artisan test` before marking work complete.
3. **Deployment:** Deployment is manual; pushing does not deploy. Never deploy without explicit approval. On the server touch only `backend + frontend: Laravel app and Vite build served together by nginx + PHP-FPM`.
4. **Explicit Commit & Push Only (Strict Rule):** never commit or push on your own initiative. Changes stay in the working tree until the user issues `/commit`, `/push`, `/commit-push` (or asks in chat).
5. **Attribution:** Do not add `Co-Authored-By` or "Generated with" lines to commits or PRs.
6. **Branch model:** `main → main`. `main` is protected: do not commit or push to it on your own. The user's `/merge-to-dev`, `/merge-to-main` and `/ship` shortcuts are explicit authorization to push it.
7. **Never** force-push, use `--no-verify`, or stage secrets (`.env`, credentials, dumps).
8. **Delegation (cost):** do small, single-file or well-understood work directly; no planner, architect or reviewer for it. Use `planner` only for multi-file or unclear work, `architect` only for a real design decision, `security-reviewer` only when auth, input, secrets, permissions or dependencies change, `code-reviewer` only on request or for a large diff. One agent per question; never repeat an analysis another agent already did.

### Project-specific rules

- Frontend is a Vue 3 SPA inside Laravel (resources/js, built by Vite); the Laravel API is in app/Http/Controllers/Api.
- Admin CRUD for standard modules is driven by app/Support/ResourceRegistry.php and resources/js/admin-resources.js; extend the registry before writing a new controller.
- Local dev uses SQLite (.env.example); production uses MySQL 8, PHP 8.4, Node 22, with a queue worker (systemd cloth-queue) and database queue/cache/session drivers.
- Production deploy runs migrate --force: write safe, reversible migrations and never edit old ones.
- deploy/upload-data.ps1 overwrites the live database; never run it once real orders exist.
- deploy/DEPLOY.md is written in Bengali; keep it in Bengali when editing.
- Security hardening is covered by tests/Feature/SecurityHardeningTest.php; keep it passing when touching auth, admin or upload code.
- Format PHP with Laravel Pint (vendor/bin/pint).

Detailed rules live in `.claude/rules/` (architecture, commands & testing, docs, git shortcuts). Skills are in `.claude/skills/`; the skill routing for this project is in `.claude/CLAUDE.md`.

---

## 3. Shorthand Commands
- **`/commit [message]`**: inspect working tree, stage safely, create a Conventional Commit locally.
- **`/push`**: push the current branch to `origin` with tracking (protects `main`).
- **`/commit-push [message]`**: commit and push in one step.

- **`/pr`**: open or update a pull request without merging.
- **`/status`**, **`/sync`**: show or safely update branch state.
- **`/doc-sync [notes]`**: update `README.md`, `deploy/DEPLOY.md` to match the changes.
