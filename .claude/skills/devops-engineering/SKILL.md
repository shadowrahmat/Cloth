---
name: devops-engineering
description: "Use for Docker, CI/CD pipelines, IaC, server or hosting setup, environment configuration, logging/monitoring/alerting, backups and rollback planning. Actual deploys and releases: deploy (needs approval)."
---


# DevOps Engineering

Detect the existing hosting, container, CI/CD, secrets, environment, logging, monitoring, and release setup. Follow current infrastructure-as-code and deployment conventions. Prefer reproducible builds, least privilege, health checks, immutable artifacts, and rollback plans. Validate configuration locally where safe. Ask before production changes, migrations, publishing, or infrastructure modifications. Never expose secrets or run destructive commands without approval.

## Read when
| Concern | File |
|---|---|
| Pipelines, artifacts, secrets in CI, deployment strategies, quality gates | [references/ci-cd.md](references/ci-cd.md) |
| Dockerfiles, Compose, Kubernetes, Terraform/IaC, networking, servers | [references/containers-iac.md](references/containers-iac.md) |
| Logs/metrics/traces, SLOs and alerts, reliability patterns, backups and DR | [references/observability-reliability.md](references/observability-reliability.md) |

Database operations: `database-engineering`. Security review: `security-audit`. Mobile pipelines: `mobile-release`.
