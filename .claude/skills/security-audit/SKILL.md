---
name: security-audit
description: "Use for security reviews: auth, authorization/RBAC, injection, XSS/CSRF, uploads, secrets, sessions, supply chain. Non-destructive, evidence-based, with severity. For a general quality review use code-review; broad health check: audit."
---


# Security Audit

Perform only authorized, non-destructive analysis. Inspect authentication, authorization, input validation, output encoding, injection risks, file handling, secrets, session/cookie controls, CORS/CSRF, rate limits, dependencies, logging, and data exposure. Prioritize findings by severity and exploitability. Provide evidence and remediation. Do not exploit live systems, exfiltrate data, print secrets, or change production settings. Distinguish confirmed findings from potential risks.

## Read when
| Concern | File |
|---|---|
| Severity rubric, confidence levels, report template | [references/severity-report.md](references/severity-report.md) |
| Web application checklist (injection, XSS, CSRF, sessions, files, SSRF, headers) | [references/web-checklist.md](references/web-checklist.md) |
| Authentication, object-level authorization, RBAC, multi-tenancy, SSO | [references/authn-authz.md](references/authn-authz.md) |
| Secrets, dependencies, CI/CD and container supply chain | [references/secrets-supply-chain.md](references/secrets-supply-chain.md) |

Mobile apps: `mobile-security`. API design details: `api-engineering`. State clearly what was not verified.
