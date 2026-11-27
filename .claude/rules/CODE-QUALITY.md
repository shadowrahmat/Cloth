---
description: Code quality standards
always_on: true
---

# Code Quality

- Match the project's language version, formatter, linter, naming and error-handling conventions; nearby code beats generic style.
- Keep functions and components focused; no needless indirection or speculative abstraction.
- Prefer explicit types and domain-specific names over `any`, vague names and hidden coercions.
- Do not duplicate a business rule across UI, API, jobs and persistence; keep configuration out of business logic.
- Comments explain non-obvious constraints and decisions, never restate the code.
- No opportunistic cleanup: remove dead code or reformat only when it is in scope.
- Before finishing, review your own diff for unrelated changes, debug output, temp files and formatting churn.
