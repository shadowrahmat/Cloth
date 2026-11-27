---
description: Secure-by-default engineering
always_on: true
---

# Security

- Treat all external input as untrusted: URL params, headers, files, webhooks, messages, database content.
- Enforce authentication and authorization on the server boundary, not in the UI.
- Parameterized queries and framework escaping only; never concatenate input into SQL, shell commands or HTML.
- Uploads: validate content, size, type, storage location and ownership; do not trust the extension.
- Secrets live in environment/config stores; never commit, log, print or paste them into examples. Do not read `.env` or other secret files (Read tool or shell) on your own; ask the user first, and read them only after the user approves (a permission prompt or an explicit yes in chat). Never print or paste their values.
- Apply CSRF, CORS, rate limiting, secure cookies/sessions and security headers as the stack supports.
- Do not leak stack traces, tokens or internal ids to clients.
- Check provenance, known vulnerabilities and license before adding a dependency.
- Reporting a finding: exploitability, impact, evidence, practical fix. Never run destructive security tests without authorization.
