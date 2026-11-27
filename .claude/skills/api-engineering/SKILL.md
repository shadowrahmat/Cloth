---
name: api-engineering
description: "Use when designing, implementing, documenting, or reviewing HTTP/REST/GraphQL APIs: contracts, errors, pagination, versioning, idempotency, API auth, rate limits, webhooks, third-party integrations. Not for UI work."
---


# API Engineering

Inspect existing API style, versioning, authentication, authorization, validation, serialization, error format, pagination, and documentation. Preserve backward compatibility unless breaking changes are requested. Validate input at boundaries, enforce authorization server-side, make write operations appropriately idempotent, and set timeouts/retry policies for outbound calls. Test success, validation, authorization, not-found, conflict, and dependency-failure cases. Never expose secrets or internal stack traces.

## Read when
| Concern | File |
|---|---|
| Resources, status codes, error format, pagination, versioning, idempotency | [references/contract-design.md](references/contract-design.md) |
| Auth, rate limits, webhooks, outbound calls, resilience, data exposure | [references/security-reliability.md](references/security-reliability.md) |
| OpenAPI/docs, endpoint test matrix, contract tests, integrations | [references/docs-testing.md](references/docs-testing.md) |

Security review format: `security-audit`. Database effects: `database-engineering`. Do not change a published contract incompatibly without explicit approval.
