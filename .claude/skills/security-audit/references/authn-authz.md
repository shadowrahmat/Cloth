# Authentication, authorization, RBAC and tenancy

- **Rule:** authorization is decided on the server for every request and object. UI hiding is not a control. Middleware alone is insufficient; check again at the data/resource layer.
- **BOLA/IDOR (object-level):** for every endpoint taking an ID, verify the caller may access that specific object (ownership, tenant, role). Test with two users and two tenants: swap IDs, nested resources (`/orgs/{a}/projects/{b}` where b belongs to another org), bulk endpoints, exports, file downloads, GraphQL fields and mutations, background job inputs.
- **Function-level authorization:** admin or privileged routes protected server-side; HTTP method differences (GET protected but PUT not); mass-assignment (binding request bodies to models: use allow-listed fields, block `role`, `is_admin`, `tenant_id`, `owner_id`).
- **RBAC/ABAC design:** roles map to permissions; permissions checked via policies in one place (framework policy/gate mechanisms), deny by default, least privilege, separation of duties, audit privileged actions; avoid role checks scattered as string comparisons; support revocation and role changes taking effect without stale tokens (short-lived access tokens or server-side checks).
- **Multi-tenancy:** every query scoped by tenant (global scopes/RLS/enforced repository layer), tenant ID derived from the authenticated context, never from client input; caches, queues, search indexes and file storage keys include the tenant; tests that prove isolation (see `saas-multitenancy` when available).
- **Enterprise SSO:** OIDC/SAML via maintained libraries, validate signature/issuer/audience/nonce/state, account linking rules (verified emails), JIT provisioning limits, SCIM/deprovisioning, session lifetime vs IdP sessions.
- **Tokens:** API keys hashed at rest, scoped and revocable, shown once; service-to-service auth separate from user auth; OAuth scopes enforced.
- **Sensitive actions:** re-authentication/step-up for password/email change, payments, key creation; notify user.
- **Testing:** matrix of roles × endpoints × objects; negative tests for each denial; automated authorization tests in CI.
