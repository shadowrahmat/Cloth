# API documentation and testing

- **Contract first or code first:** follow the project. For contract-first, an OpenAPI document (check the version the tooling supports) is the source of truth; for code-first, generate the spec from code and fail CI when it drifts. Do not hand-write docs that can be generated.
- **Documentation content:** auth, base URLs/environments, every endpoint with parameters, request/response examples including errors, pagination, rate limits, idempotency, webhook payloads and signature verification, versioning/deprecation policy, changelog, SDK snippets validated by tests.
- **Tests (per endpoint):** success path; validation errors per field; authentication missing/expired; authorization (other user/tenant, wrong role); not found; conflict/precondition; idempotent retry; pagination edges (empty, last page, invalid cursor); rate limit; dependency failure (timeouts, 5xx from upstream mocked); large/malformed payloads.
- **Contract tests:** validate responses against the schema; consumer-driven contract tests where several clients exist; breaking-change detection between spec versions (an OpenAPI diff tool if the project has one).
- **Compatibility checks:** old client fixtures against new server; migration of stored payloads; feature flags for behavior changes.
- **Performance checks:** latency under expected load for hot endpoints, N+1 detection in tests (query counting), payload size limits.
- **Test data and environments:** deterministic fixtures, isolated databases, sandbox keys for third parties; never call real payment/email/SMS providers in tests.
- **Third-party integrations:** wrap each provider behind an interface, record/replay or fake servers, handle rate limits, pagination, webhooks and partial outages; keep provider IDs and raw payload references for support.
- **Verification:** run the narrowest endpoint tests first, then the suite; report spec generation/validation commands and results.
