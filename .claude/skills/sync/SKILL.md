---
name: sync
description: "Use when the user says /sync or asks to update the current branch from its upstream. Fast-forward only; stops on divergence."
disable-model-invocation: true
---
Inspect branch, status, remote, upstream, and repository branch conventions. Fetch first. If the working tree has changes, preserve them and do not risk overwriting. Prefer fast-forward-only synchronization. If histories diverge, conflicts occur, or the target is ambiguous, stop and explain options. Never force-push or rewrite shared history.
