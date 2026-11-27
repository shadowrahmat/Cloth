---
name: push
description: Pushes the current branch to origin with upstream tracking and protects the production branch. Triggered by /push or requests to push code.
disable-model-invocation: true
---

# Git Push Skill

1. Verify `git remote -v`, the current branch, upstream and working tree.
2. If the current branch is `main`, halt and ask for explicit confirmation: it is protected.
3. Otherwise run `git push -u origin <current-branch>`. Never force-push.
4. Do not commit automatically, open a PR, or merge. Report the push status and tracking branch.
