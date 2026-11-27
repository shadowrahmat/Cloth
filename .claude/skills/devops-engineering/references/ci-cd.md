# CI/CD

Follow the existing pipeline and provider; do not migrate providers unprompted. Validate pipeline changes locally or in a branch; production deploys need explicit approval naming the environment.
- **Pipeline shape:** fast checks first (format, lint, typecheck), unit tests, build once, integration tests, security scans, publish immutable artifact (container image tagged by commit SHA), deploy to staging, smoke tests, approval gate, production deploy, post-deploy verification.
- **Principles:** build once, promote the same artifact; reproducible builds (pinned toolchains, lockfiles, cached with lockfile keys); fail fast and parallelize; keep pipeline definitions in the repo; small PRs merge behind required checks and reviews.
- **Branch and release flow:** protected branches, required status checks, tags or release branches for production, changelog generation, semantic versions where useful.
- **Secrets and permissions:** CI secret store, least-privilege tokens per job (read-only by default), short-lived OIDC credentials to cloud providers where supported, no secrets for fork PRs, mask in logs, pin third-party actions/plugins to reviewed versions.
- **Deployment strategies:** rolling, blue/green, canary; choose by risk and platform capability; health checks and readiness gates; automatic rollback on failed checks where the platform supports it; database migrations decoupled and backward compatible (see `database-engineering`).
- **Environments:** parity between staging and production in config shape; per-environment config via environment variables or config service; ephemeral preview environments if the project uses them; cost limits.
- **Quality gates:** test coverage trend, dependency/vulnerability scan, license check, container scan, IaC scan; avoid gates nobody acts on.
- **Flaky pipelines:** quarantine with owner and deadline, rerun policy documented, collect timing metrics.
- **Rollback:** previous artifact redeploy path tested, feature flags, documented who decides and how.
- **Cost:** cancel superseded runs, cache wisely, right-size runners; ask before enabling paid services.
