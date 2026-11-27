---
name: build
description: "Use only on explicit /build request to take a feature or change from idea to done: plan, get approval, implement, test, review, and report."
disable-model-invocation: true
---

# Build a feature end to end

Input: the user's description of what they want. Keep every step proportionate; skip steps a tiny change does not need.

1. **Plan** (`planner` agent, or inline for small changes): goal, acceptance criteria, ordered tasks, open questions. Ask about ambiguities that change the design.
2. **Approve:** show the plan briefly and **wait for the user's approval**. Never implement before it, unless the change is trivial and the user said to proceed.
3. **Implement** task by task with `backend-engineer` / `frontend-engineer` (or directly when small). Follow repository conventions; smallest complete change; `devops-engineer` for Docker/CI/config.
4. **Verify:** run the project's test command and relevant lint/build. On failures use `debugger`; never weaken tests.
5. **Review:** `code-reviewer` on the diff. Add `security-reviewer` only if auth, user input, uploads, secrets, permissions or dependencies changed.
6. **Report** per `.claude/rules/COMPLETION-REPORT.md`. Do not commit or push unless asked; offer `/commit`.

**Full lifecycle (only when the user starts from an idea rather than a defined change, and only for skills that are installed, e.g. `domains: [product]`):** `product-discovery` → `prd-requirements` → `product-delivery` → steps 1-6 above → `production-readiness`. Each stage ends with a written artifact and waits for the user's approval; never skip an approval, and never deploy or change production without explicit approval.

Use subagents only when they isolate noisy or independent work; do small edits directly to save tokens.
