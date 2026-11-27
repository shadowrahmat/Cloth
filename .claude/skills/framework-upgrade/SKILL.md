---
name: framework-upgrade
description: "Use when upgrading a framework or major dependency (React, Next.js, Vue, Laravel, Python, Node, Expo, etc.). Reads the official upgrade guide for the exact versions, upgrades one major at a time, and verifies after each."
---

# Framework and major-version upgrades

1. State the current and target versions from the manifest and lockfile. Read the official upgrade guide and release notes for every major in between (search or docs, not memory) and upgrade one major at a time.
2. Confirm a clean working tree and a passing baseline (tests, build). Ask before starting a major upgrade, and keep it on its own branch or commit.
3. Check compatibility first: runtime (Node, PHP, Python), related packages (router, UI libraries, test tools, TypeScript, lint plugins), and the hosting runtime.
4. Run the official codemods or migration commands when they exist (for example `npx @next/codemod`, `npx react-codemod`); review their diff instead of hand-rewriting what they handle.
5. Update dependencies with the project's package manager, keep the lockfile consistent, and change only what the upgrade needs.
6. Fix in order: build errors, type errors, failing tests, runtime warnings. Keep behavior identical; adopt new features in a later change.
7. Verify with a clean install, build, typecheck, lint, tests, and a smoke test of key flows. Report remaining warnings and known risks.
8. Report breaking changes handled, packages bumped, and the rollback path (revert the branch and lockfile). Never deploy.
