# API contract design

Follow the project's existing style first; the notes below are defaults when none exists.
- **Resources and verbs:** nouns for resources, HTTP methods with correct semantics (GET safe/idempotent, PUT/DELETE idempotent, POST creation/actions), plural collections, nested only one level, actions as sub-resources when needed. Return the created resource with 201 and `Location`.
- **Status codes:** 200/201/202/204, 400 malformed, 401 unauthenticated, 403 forbidden, 404 not found (also to avoid leaking existence when appropriate), 409 conflict, 412/428 for preconditions, 422 validation (or 400, follow the project), 429 rate limited with `Retry-After`, 5xx server faults.
- **Error format:** one consistent error body; the Problem Details format (RFC 9457, formerly RFC 7807, verify current number) is a reasonable standard: `type`, `title`, `status`, `detail`, `instance` plus field errors extension. Machine-readable error codes, human messages safe to show, never stack traces or internals; correlation/request ID included.
- **Pagination:** cursor-based for large or changing data (stable ordering key, opaque cursor, `limit` with a max), offset only for small stable sets; include next cursor or links; document total counts cost.
- **Filtering/sorting/fields:** allow-listed filter and sort fields, explicit parameter names, sparse fieldsets/includes bounded to prevent N+1 and over-fetch; validate enumerations.
- **Versioning and compatibility:** additive changes are non-breaking (new optional fields, new endpoints); removing/renaming fields, changing types or semantics is breaking. Version via URL prefix or header per project convention; deprecate with `Deprecation`/`Sunset` headers and a documented timeline; clients must ignore unknown fields.
- **Idempotency:** `Idempotency-Key` header for unsafe retries on POST (payments, orders): store key + request fingerprint + response for a window; same key with different payload returns 422/409.
- **Concurrency:** ETags with `If-Match` for updates, or version fields; return 412/409 on conflict.
- **Dates/numbers/money:** ISO 8601 UTC, decimals/strings or integer minor units for money, stable enumerations and IDs (opaque, non-sequential if enumeration risk).
- **Bulk and async:** bulk endpoints with partial-failure semantics; long operations return 202 + status resource; webhooks for completion.
- **GraphQL/RPC:** schema-first, bounded depth/complexity, persisted queries, per-field authorization, error extensions; same security rules apply.
