---
name: testing-best-practices
description: "Use when writing or reviewing tests, fixing flaky tests, improving coverage, or testing APIs. Detects the installed framework (Pest, PHPUnit, Jest, Vitest, pytest, etc.) first."
---

# Testing best practices: framework aware

1. Inspect test scripts and installed dependencies from manifest/lockfile, plus nearby tests and CI. Identify the actual runner: Pest, PHPUnit, Jest, Vitest, Playwright, Cypress, pytest, Go test, or another supported tool. Do not assume Pest is installed.
2. Match nearby test language, naming, directory layout, assertions, fixtures, database isolation, and mocking conventions. Prefer the smallest meaningful test that observes user-visible behavior.
3. Cover changed behavior, validation, authorization, boundary cases, failures, and regressions. Avoid brittle tests that just duplicate implementation details.
4. Use the built-in assertions/fakes of the **installed** test framework. Avoid real network calls, real production data, flaky clocks, and shared mutable state.
5. Run the narrowest relevant existing test command first, then broader project-defined checks if useful. Do not install a new test framework or browser dependency unless requested/approved.
6. Report exact tests executed, passes/failures, and tests not run. Never weaken a failing test to make it pass.

## Supporting rules (load only when relevant)

Most existing `rules/*.md` references are **Laravel/Pest-specific**. Do not apply their syntax or test-runner choices to other projects; even in Laravel, follow the installed Pest/PHPUnit version and local conventions.

| Concern | Reference |
| --- | --- |
| Test naming/layout (Laravel/Pest examples) | [rules/naming.md](rules/naming.md) |
| Assertions (Pest examples) | [rules/assertions.md](rules/assertions.md) |
| API endpoint tests (Laravel) | [rules/endpoint-tests.md](rules/endpoint-tests.md) |
| Factories and fixtures (Laravel) | [rules/test-data.md](rules/test-data.md) |
| Fakes, mocks, DB isolation (Laravel) | [rules/isolation.md](rules/isolation.md) |
| Security test cases | [rules/security.md](rules/security.md) |
| Suite performance | [rules/performance.md](rules/performance.md) |
| Review checklist | [rules/review.md](rules/review.md) |
| Framework features (Pest) | [rules/finding-features.md](rules/finding-features.md) |

For non-Laravel codebases, use framework-native documentation and equivalent concepts rather than copying PHP examples. In Laravel apps using PHPUnit instead of Pest, use PHPUnit-style assertions and test classes; never introduce `it()`/`expect()` just because the references show them.
