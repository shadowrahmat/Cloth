# Web application review checklist

Walk each area; mark pass / fail / n.a. / not verified, with evidence.
- **Injection:** SQL (parameterized queries; ORM raw fragments; dynamic ORDER BY/column names from allow-lists), NoSQL operators, OS commands (avoid shell; allow-lists), LDAP/XPath, template injection, header/CRLF injection, log injection.
- **XSS:** output encoding per context, framework escaping bypasses (`dangerouslySetInnerHTML`, `v-html`, `{!! !!}`, `innerHTML`), sanitizer allow-lists, CSP presence and quality, DOM sinks, user-generated HTML, SVG/upload content types.
- **CSRF:** tokens or SameSite cookies on state-changing requests, JSON endpoints relying on cookies, CORS configuration (no wildcard with credentials, origin reflection), clickjacking (`frame-ancestors`/X-Frame-Options).
- **Authentication:** password storage (modern adaptive hashing), brute-force/rate limits, MFA availability, reset flows (token entropy, expiry, single use, no user enumeration), session fixation, logout invalidation, cookie flags (`HttpOnly`, `Secure`, `SameSite`).
- **Sessions/tokens:** JWT validation (alg, issuer, audience, expiry), storage choice, revocation, refresh rotation.
- **Authorization:** see authn-authz.md.
- **Files:** upload type/size validation by content, storage outside web root or non-executable, path traversal in downloads, archive extraction (zip slip), image processing limits.
- **SSRF/outbound:** URL fetchers allow-list hosts, block internal ranges/metadata endpoints, limit redirects and timeouts.
- **Deserialization/XXE:** no native deserialization of untrusted data; XML parsers with external entities disabled.
- **Transport/headers:** HTTPS everywhere, HSTS, secure cookies, CSP, `X-Content-Type-Options`, referrer policy, no sensitive data in URLs.
- **Error handling/logging:** no stack traces or secrets to users, security events logged without PII/secrets, log tampering considerations.
- **Rate limiting/abuse:** login, reset, OTP, search, expensive endpoints; business-logic abuse (coupon reuse, race conditions on balances, negative quantities).
- **Configuration:** debug off in production, default credentials, exposed admin/debug endpoints, directory listing, backups in web root, environment separation.
- **Data protection:** encryption in transit/at rest where needed, PII minimization, retention, backups access.
