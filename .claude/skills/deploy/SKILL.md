---
name: deploy
description: "Use only when the user says /deploy or asks to release or deploy. Prepares a plan and safe local checks; actually deploying needs explicit approval for the named environment."
disable-model-invocation: true
---
First inspect deployment documentation, CI/CD, environment, migrations, backup, health checks, and rollback strategy. You may prepare a plan and run safe local validation. Before any deployment, production migration, release, or external publication, state target and impact and ask for explicit approval. Never expose secrets.
