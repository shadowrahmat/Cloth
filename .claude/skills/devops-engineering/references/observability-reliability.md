# Observability and reliability

- **Three signals:** structured logs (JSON, request/trace IDs, levels, no secrets or PII), metrics (RED for services: rate, errors, duration; USE for resources: utilization, saturation, errors), traces for cross-service latency. Instrument with the project's stack (OpenTelemetry is a common vendor-neutral choice; follow what exists).
- **Health:** liveness (process alive), readiness (can serve traffic), dependency checks kept light; do not expose internals publicly.
- **SLOs and alerting:** define user-centered SLIs (availability, latency percentile, correctness) and SLOs with error budgets; alert on symptoms users feel and on budget burn, not on every resource blip; every alert has an owner, a runbook link and a severity; remove noisy alerts.
- **Dashboards:** golden signals per service, deployment markers, dependency health, business metrics for key flows.
- **Incident readiness:** on-call rotation and escalation, runbooks for known failures, status communication plan, blameless postmortems with tracked actions (see `engineering:incident-response` if available).
- **Reliability patterns:** timeouts, retries with backoff and jitter, circuit breakers, bulkheads, rate limiting, load shedding, graceful shutdown (SIGTERM handling and connection draining), idempotency, queues for smoothing spikes, multi-AZ where required.
- **Capacity and performance:** load tests before launches, autoscaling signals, headroom, cost monitoring; see `performance-engineering`.
- **Backups and disaster recovery:** define RPO/RTO, automated backups of data and configuration, offsite copies, **restore drills**, documented failover, infrastructure reproducible from code.
- **Release safety:** feature flags, staged rollouts, automatic rollback triggers, post-deploy verification checks, change calendar for risky changes.
- **Security operations:** audit logs, alert on suspicious auth/admin activity, patch cadence, vulnerability scanning, secrets rotation.
- **Verification:** show configuration diffs, validation commands and results; for alerts/SLOs, show the query and threshold with reasoning.
