---
name: project-init
description: "Use only on explicit /project-init request to initialize the project source in src/ with the chosen framework and configure Docker services accordingly."
disable-model-invocation: true
---

# /project-init — Initialize Project Source

## Purpose

Set up the `src/` directory with the chosen framework and configure Docker services accordingly.

## Steps

1. **Ask** which framework/stack to initialize:
   - Laravel (PHP)
   - Next.js (React/Node)
   - React + Vite
   - Python (Django/FastAPI/Flask)
   - Custom (specify)

2. **Initialize the framework** inside `src/`:
   - For Laravel: `composer create-project laravel/laravel src`
   - For Next.js: `npx create-next-app@latest src`
   - For React+Vite: `npm create vite@latest src`
   - For Python: set up virtual environment and project structure in `src/`

3. **Configure Docker services** in `docker/`:
   - Update `docker-compose.yml` with the right services for the chosen stack.
   - Set up Nginx config in `docker/nginx/` to serve the app.
   - Set up PHP-FPM config in `docker/php/` if Laravel.
   - Set up Node config in `docker/node/` if JS-based.
   - Set up MySQL config in `docker/mysql/` with data persistence.

4. **Generate `.env.example`** with all required environment variables (names only, no secrets).

5. **Update `README.md`** with stack-specific setup notes.

6. **Update `.gitignore`** for the chosen framework.

7. **Report** what was set up and suggest next steps (update `README.md`).

## Notes

- Never install dependencies that the project doesn't need.
- Use the framework's official installer/scaffolding.
- Keep Docker configs minimal and production-ready.
- Source code lives ONLY in `src/` — Docker, docs, and config stay at root level.
