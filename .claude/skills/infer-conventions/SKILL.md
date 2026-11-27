---
name: infer-conventions
description: "Use only on explicit request to inventory and document a repository's real coding, architecture, testing, and Git conventions on any stack. Never imposes a preferred framework or tool."
disable-model-invocation: true
---

# Infer existing project conventions

Only run a full sweep on explicit user request. Document observed reality, not unrequested refactoring goals.

1. Detect stack, versions, architecture, test framework, package manager, CI, and existing team guidance (`CLAUDE.md`, `AGENTS.md`, README, CONTRIBUTING, lint/format configs). Do not assume Laravel or Boost MCP.
2. Read representative files from the relevant layer and directories: controllers/routes, services, models/database, React/Vue components, tests, build/config, and CI as applicable. Skip absent or irrelevant categories.
3. Use [universal-checklist.md](references/universal-checklist.md). For Laravel, optionally consult [Laravel-focused checklist](references/checklist.md) and [advanced Laravel workflow](references/laravel-advanced.md); those are references only, not mandatory for other frameworks.
4. For each proposed convention, collect at least three independent consistent examples when available, and investigate rivals. For small repositories, label low-confidence observations instead of pretending they're settled policy.
5. Distinguish established project choices from framework defaults, formatter-owned style, and conflicts. Respect meaningful module-specific differences.
6. Summarize candidate conventions with evidence paths and suggest the narrowest scope; ask the user before recording durable rules unless asked explicitly to write them.
7. Record approved rules in the **project**, not in global user config. Prefer documented project conventions or `.claude/rules/` with appropriate `paths:` globs. Never overwrite existing rules. If the project already uses `.ai/rules/` or a `record-rule` MCP action, use it only when available and intended by the user.
8. Report findings, conflicts, unknowns, files written, and how to verify the inferred rules. No speculative claims about installed packages or integrations.

Never write secrets, `.env` values, or invented project metadata into rules.
