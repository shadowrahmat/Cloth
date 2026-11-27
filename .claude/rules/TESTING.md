---
description: Testing standards (loaded only when working on test files)
paths:
  - "**/*.{test,spec}.*"
  - "**/*_test.*"
  - "**/test_*.py"
  - "**/{test,tests,__tests__,spec}/**"
---

# Testing Standards

(How to run tests is in `COMMANDS-TESTING.md`; this is what to test.)

- Test observable behavior and contracts, not implementation details.
- Cover changed behavior, boundaries, authorization, validation, failure paths and regressions.
- Follow the existing framework, naming, fixtures and factories; do not add a second test framework.
- Keep tests deterministic: isolate time, randomness, network and external services with the project's fakes.
- Run the focused test first, then the relevant broader suite.
- A failing test means diagnose the cause; never weaken, skip or delete it to get green.
- Report exactly which commands ran and which passed, failed or were skipped.
