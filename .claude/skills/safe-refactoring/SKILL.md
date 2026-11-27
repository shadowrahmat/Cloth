---
name: safe-refactoring
description: "Use when asked to refactor, clean up, restructure, rename, or split code without changing behavior. Establishes tests first and works in small verified steps."
---

# Safe refactoring

1. State the scope and the behavior that must not change. Ask before touching public APIs, database schemas, or files outside the scope.
2. Run the existing tests first. If the code is untested, add characterization tests of current behavior before changing it (ask first when that is large).
3. Work in small steps (rename, extract, move, inline) and run the narrowest relevant tests after each. When a step breaks, revert that step; do not pile on fixes.
4. Never mix refactoring with feature work, bug fixes, or formatting churn on untouched lines.
5. Use language-server or codemod tooling for renames and moves when available; afterwards search for strings, dynamic references, config, routes, and docs.
6. Delete code as dead only after searching every reference (including dynamic and config use); otherwise report it.
7. Do not create an abstraction for a single use. Duplication is cheaper than a wrong abstraction until the third use.
8. Report what changed, what stayed identical, tests run, and follow-ups. Commit only when asked, one concern per commit.
