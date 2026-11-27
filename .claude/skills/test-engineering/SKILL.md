---
name: test-engineering
description: "Use when planning or writing automated tests for new behavior, regressions, or edge cases, using the project's installed test framework. To run existing checks use test; for rules on test quality see testing-best-practices."
---


# Test Engineering

Detect the installed test framework and conventions. Cover observable behavior, critical decisions, edge cases, authorization, validation, and failure paths. Prefer deterministic tests with framework-supported fakes. Keep fixtures minimal and tests independent. Run focused tests first, then relevant broader suites. Do not add test frameworks, browser drivers, or services without approval. Never delete or weaken a failing test to obtain a green run.
