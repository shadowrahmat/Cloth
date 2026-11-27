# PHP security, Composer and tooling

- **Input/output:** validate (type, length, allow-list) then encode on output for the context (HTML, attribute, URL, JS, SQL param). CSRF tokens for state-changing browser requests; SameSite cookies, `HttpOnly`, `Secure`; regenerate the session ID on login; set session cookie flags in config.
- **SQL:** PDO prepared statements with `ATTR_EMULATE_PREPARES` understood, bound parameters; identifiers (table/column/order) from allow-lists.
- **Files:** validate MIME by content (`finfo`), generate server-side names, store outside web root or deny execution, size limits; never trust `$_FILES['name']`.
- **Deserialization/includes:** no `unserialize` of untrusted data (use JSON); no dynamic `include`/`require` from input; avoid `eval`, `extract` on input, `shell_exec` with input (use `escapeshellarg` and allow-lists, or avoid).
- **SSRF/outbound:** allow-list hosts for server-side fetches; timeouts; disable redirects unless needed.
- **Secrets:** from environment or secret manager, not committed; `.env` outside web root; rotate on leak.
- **Config (php.ini):** `display_errors=0`, `log_errors=1`, `expose_php=0`, sensible `memory_limit`/`upload_max_filesize`, OPcache on, disable dangerous functions only when the app does not need them.
- **Composer:** lock file committed (apps), `composer install` in CI (not update), `composer audit` for known advisories where supported, review new packages (maintenance, downloads, license), no `dev` packages in production install (`--no-dev`).
- **Static analysis:** PHPStan/Psalm at a level the project agrees on, baseline file for legacy; do not lower levels to pass.
- **Tests:** PHPUnit or Pest; unit tests for logic, integration tests with a real test database in a transaction or fresh schema, HTTP tests for endpoints; data providers for edge cases; fake the clock and HTTP clients.
- **Reporting security findings:** use `security-audit` format.
