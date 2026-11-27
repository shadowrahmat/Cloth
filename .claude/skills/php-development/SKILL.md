---
name: php-development
description: "Use for plain or framework-agnostic PHP: modern syntax, Composer, PSR standards, types, error handling, PHPUnit/Pest, static analysis. Detects PHP version first. Laravel: laravel-best-practices; WordPress: wordpress-development."
---

# PHP development

Detect first: PHP version (`composer.json` `require.php`, `.php-version`, Docker image), framework (Laravel, Symfony, WordPress, none), Composer lock, autoloading (PSR-4), coding standard (PHP-CS-Fixer, PHP_CodeSniffer, Pint), static analysis (PHPStan, Psalm) level, test runner (PHPUnit, Pest). Follow them; ask before adding dependencies. Use only syntax and functions available on the project's PHP version.

## Rules
- `declare(strict_types=1)` where the codebase uses it; type every parameter, return and property the version allows; avoid `mixed` and loose comparison (`===`).
- Errors: throw typed exceptions, do not suppress with `@`; never display errors in production; log without secrets.
- Database access only through prepared statements (PDO or the framework's builder); never interpolate input into SQL.
- Output encoding at the sink (`htmlspecialchars` with ENT_QUOTES and UTF-8 or the template engine's escaping); validate input at boundaries.
- Passwords with `password_hash` / `password_verify`; randomness from `random_bytes`/`random_int`; no `md5`/`sha1` for security.
- `unserialize` and `include` of user-influenced data are forbidden; file uploads validated by content and stored outside the web root.
- Composer: commit the lock file for applications, never edit `vendor/`, run `composer audit` when available, avoid wildcard versions.
- No global state, superglobals only at the edge (request object), dependency injection via constructors.

## Read when
| Concern | File |
|---|---|
| Version features, types, enums, readonly, null handling, PSR, error handling | [references/modern-php.md](references/modern-php.md) |
| Security, Composer hygiene, tests and static analysis, production config | [references/security-tooling.md](references/security-tooling.md) |

## Verify
Run the project's formatter, static analysis and the narrowest test first (`vendor/bin/phpunit --filter Name` or `vendor/bin/pest path`), inside the project's container if it runs in Docker.
