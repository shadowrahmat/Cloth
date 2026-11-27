---
name: pr
description: "Use when the user says /pr or asks to open or update a pull request for the current branch. Never merges."
disable-model-invocation: true
---
Inspect repository hosting provider, current branch, remote, target-branch convention, PR template, diff, and checks. Reuse an existing open PR where appropriate; otherwise create one with an accurate title and summary. Include issue references only when verified. Do not merge, release, deploy, or delete branches.
