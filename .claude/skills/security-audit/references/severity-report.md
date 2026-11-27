# Severity rubric and report template

Authorized, non-destructive analysis only. Never exploit live systems, access data you are not authorized to see, print secrets or change production settings. Redact secrets in reports (show a prefix only).
- **Severity = impact × exploitability**, stated with reasoning:
  - **Critical:** unauthenticated or low-privilege remote compromise of data/accounts/systems, or exposure of live secrets.
  - **High:** authenticated privilege escalation, broken object-level authorization across tenants/users, injection reaching data, stored XSS on privileged pages.
  - **Medium:** needs unusual conditions or user interaction, limited data exposure, weak controls with partial compensating controls.
  - **Low:** hardening gaps, information leakage with little use, defense-in-depth.
  - **Info:** observations.
- **Confidence:** *Confirmed* (reproduced safely or proven by code path with evidence), *Likely* (code path suggests it, not run), *Potential* (depends on unseen config). Never present potential as confirmed.
- **Finding template:** ID, title, severity, confidence, affected component (file:line, endpoint), description, evidence (code excerpt, request/response with secrets redacted), impact, remediation (specific change), verification step, references (standard names/versions only after checking them).
- **Report structure:** scope and method (what was and was not reviewed), summary table by severity, findings ordered by severity, positive observations, not-verified list, suggested fix order.
- **Standards mapping:** OWASP Top 10, ASVS, API Security Top 10, CWE are useful labels; check the current edition names before citing and map only where the evidence fits. Do not claim "compliant" with a standard.
- **Fix review:** after remediation, re-check the same evidence and look for sibling occurrences of the same pattern (grep).
