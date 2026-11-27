---
name: bug-investigation
description: "Use when something is broken: errors, exceptions, stack traces, regressions, wrong behavior, or 'why does X happen'. Finds the root cause first; fixes with a regression test when asked."
---


# Bug Investigation

Reproduce the issue where safe. Gather logs, stack traces, inputs, environment, recent changes, and related tests. Trace the failing path and identify root cause rather than masking symptoms. Form a minimal hypothesis and verify it. Add a regression test for the defect. Preserve unrelated behavior and user changes. Do not delete data or alter production to reproduce an issue without approval.
