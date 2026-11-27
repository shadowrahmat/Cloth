---
name: project-intelligence
description: "Use when starting in an unfamiliar repo, or before substantial work on a codebase not yet explored: maps stack, architecture, commands, conventions, and risks."
---


# Project Intelligence

Build an evidence-based understanding of the current repository before substantial implementation. Detect CodeIgniter 3/4, Laravel, Symfony, vanilla PHP, React, Vue, Next.js, Node, Python or other frameworks from actual manifests and files; do not assume a stack from the task alone.

1. Inspect root files, manifests, lockfiles, source layout, tests, CI, Docker/deployment files, and existing instructions.
2. Identify languages, framework versions, runtime, package manager, app boundaries, entry points, data stores, external services, and build/test commands.
3. Trace one representative request or user flow end-to-end when relevant.
4. Identify conventions from nearby code rather than assuming a preferred architecture.
5. Note security boundaries, environment configuration, migrations, and operational dependencies.
6. Summarize confirmed facts separately from assumptions and unknowns.

Create or update `PROJECT-CONTEXT.md` only when the user requests it or it will materially help ongoing work. Never store secrets or copy `.env` values. Do not treat inferred context as verified fact.
