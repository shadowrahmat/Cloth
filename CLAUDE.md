# Engineering Environment

Project facts, commands and skill routing: `.claude/CLAUDE.md`. Rules: `.claude/rules/`.

## Operating profile
- Inspect the repository before acting; never assume branch names, tools or commands. Nearby code and repository conventions beat generic preferences.
- Do safe, reversible, in-scope work without asking. Start with `git status --short --branch`; read only the files relevant to the task.

## Principles
- Understand call sites, data flow and tests before editing; make the smallest complete change.
- Reuse existing abstractions and dependencies; no speculative abstractions or new packages. Preserve public APIs unless a change is requested.
- Handle errors explicitly; add or update tests for changed behavior. Never claim a check passed unless it ran and passed; say what was not run.

## Ask first
Deleting or overwriting data, files or branches; production deploys or migrations; publishing; merging PRs; force-push, history rewrite or bypassing hooks; new dependencies; touching secrets; broad refactors or breaking API/schema changes.

## Git and reports
Inspect status and branch before any git write; stage only intended files. Git rules: `.claude/rules/GIT-SHORTCUTS.md`. Final report: `.claude/rules/COMPLETION-REPORT.md`.
