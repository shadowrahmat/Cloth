# API security and reliability

- **Authentication:** follow the project's scheme (OAuth2/OIDC bearer tokens, sessions, API keys); validate token signature, issuer, audience, expiry, scopes on every request; short-lived access tokens; API keys hashed, scoped, rotatable.
- **Authorization:** object- and function-level checks server-side (see `security-audit` authn-authz reference); no trust in client-supplied IDs, roles or tenant; mass-assignment protection with allow-listed fields.
- **Input:** schema validation (type, length, range, enum) at the boundary, reject unknown fields where practical, size limits for bodies and uploads, content-type enforcement, safe parsing limits (JSON depth).
- **Rate limiting and abuse control:** per key/user/IP and per endpoint cost; stricter on auth, reset, search and expensive operations; `429` with `Retry-After`; protect against enumeration and scraping; quotas for tenants.
- **CORS and transport:** explicit allowed origins, no wildcard with credentials, HTTPS only, HSTS, secure headers; no secrets in query strings.
- **Webhooks (outgoing):** signed payloads (HMAC with timestamp), retries with exponential backoff and jitter, idempotent event IDs, delivery logs, dead-letter handling, endpoint verification, SSRF protections on target URLs. **Incoming webhooks:** verify signatures on the raw body, check timestamp tolerance, dedupe by event ID, respond fast (2xx) and process async, handle out-of-order events.
- **Outbound calls:** timeouts (connect + read), bounded retries only for idempotent operations with backoff and jitter, circuit breaker or bulkhead where appropriate, fallback behavior documented, per-dependency metrics.
- **Resilience:** idempotent writes, transactions around multi-step updates, outbox pattern for publishing events with DB writes, graceful degradation, load shedding, health/readiness endpoints that do not leak details.
- **Data exposure:** serializers allow-list fields (no accidental password hashes/internal IDs), pagination caps, error messages that do not reveal existence/state beyond need, PII redaction in logs.
- **Observability:** request ID propagation, structured logs, per-route latency/error metrics, audit logs for privileged actions.
