# Project Engineering Environment

> Project-specific configuration generated from `project.config.yml`; do not edit by hand. General rules: root `CLAUDE.md` and `.claude/rules/`.

## Project

**Cloth**. ATELIER ÉTOILE: luxury fashion e-commerce SPA (Vue 3) with a Laravel 13 API and admin CMS

Type `fullstack`; stack: laravel13, php8.3, vue3, vite, tailwind4, pinia, sanctum, mysql, sqlite.

- `app/` — Laravel application code (Http, Models, Services, Support, Console, Providers)
- `resources/` — Vue 3 frontend (js), css, Blade views
- `routes/` — web.php, api.php, console.php
- `database/` — migrations, seeders, factories
- `tests/` — Feature and Unit tests
- `deploy/` — server provisioning, update and data import scripts, DEPLOY.md
- `config/, bootstrap/, public/, storage/` — standard Laravel

## Skill routing

**Primary for this project** (fullstack; laravel13, php8.3, vue3, vite, tailwind4, pinia, sanctum, mysql, sqlite): `frontend-engineering`, `ui-ux-engineering`, `api-engineering`, `database-engineering`, `devops-engineering`, `laravel-best-practices`, `infer-conventions`, `php-development`, `vue-development`, `tailwindcss-development`, `mysql-engineering`

## Token Efficiency

- `Grep` first, then read line ranges; never open lockfiles, dumps or logs whole.
- `Edit`, not `Write`, for existing files; batch independent calls; no re-read after an edit; pipe long output through `head`/`tail`.
- No skills or subagents for simple tasks. `/clear` between tasks; plan first for big changes. Keep responses short.

## Workflow

1. Read `README.md`, `deploy/DEPLOY.md` only when the task needs setup context. Run project commands directly on the host with `composer`.
2. Run `php artisan test` before marking work complete.
3. Commit with conventional messages; push only when explicitly asked. Do not add `Co-Authored-By` or "Generated with" lines to commits or PRs.
4. Deployment is manual; pushing does not deploy. Never deploy without explicit approval. On the server touch only `backend + frontend: Laravel app and Vite build served together by nginx + PHP-FPM`.

## Project-specific rules

- Frontend is a Vue 3 SPA inside Laravel (resources/js, built by Vite); the Laravel API is in app/Http/Controllers/Api.
- Admin CRUD for standard modules is driven by app/Support/ResourceRegistry.php and resources/js/admin-resources.js; extend the registry before writing a new controller.
- Local dev uses SQLite (.env.example); production uses MySQL 8, PHP 8.4, Node 22, with a queue worker (systemd cloth-queue) and database queue/cache/session drivers.
- Production deploy runs migrate --force: write safe, reversible migrations and never edit old ones.
- deploy/upload-data.ps1 overwrites the live database; never run it once real orders exist.
- deploy/DEPLOY.md is written in Bengali; keep it in Bengali when editing.
- Security hardening is covered by tests/Feature/SecurityHardeningTest.php; keep it passing when touching auth, admin or upload code.
- Format PHP with Laravel Pint (vendor/bin/pint).
