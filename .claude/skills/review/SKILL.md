---
name: review
description: "Use when the user says /review: a quick, read-only pass over the current changes. For a PR, a diff or a severity-ranked report use code-review."
disable-model-invocation: true
---
Inspect the diff and relevant surrounding code. Prioritize actionable findings by severity and cite file/line locations. Check tests and project conventions. Do not modify code unless the user asks for fixes. Distinguish confirmed defects from questions or suggestions.
