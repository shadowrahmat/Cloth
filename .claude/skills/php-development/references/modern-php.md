# Modern PHP

Applies to: the PHP version the project requires. Features below arrived in specific releases; check the version before using them (for example enums and readonly properties need 8.1+, readonly classes 8.2+, typed class constants 8.3+; confirm in the official PHP manual).
- **Types:** scalar/union/intersection types, nullable types, `never`, `static` returns, property types. Prefer small value objects over arrays with magic keys.
- **Enums:** backed enums for closed sets with `from`/`tryFrom`; keep behavior (labels) as methods.
- **Immutability:** `readonly` properties, constructor property promotion, `with*` methods.
- **Null handling:** `?->` nullsafe, `??`, explicit checks; do not return `false`-or-value mixes in new code.
- **Match vs switch:** `match` is strict and exhaustive (throws `UnhandledMatchError`), prefer it for mappings.
- **Standards:** PSR-1/PSR-12 style, PSR-4 autoloading, PSR-3 logging interface, PSR-7/17/18 HTTP interfaces where HTTP clients/servers are abstracted, PSR-11 container interface. Follow the project's chosen subset.
- **Errors:** exceptions over return codes; custom exception hierarchy per module; `finally` for cleanup; convert warnings to exceptions through the error handler in application bootstrap.
- **Dates:** `DateTimeImmutable` with explicit timezones, store UTC.
- **Money/decimals:** never floats; use integers in minor units or a decimal library.
- **Strings/encoding:** `mb_*` functions for multibyte text; `str_contains`/`str_starts_with` (8.0+).
- **Performance:** OPcache enabled in production with preloading only if understood; avoid N+1 queries; generators for large iterations; measure with Xdebug/Blackfire/profilers before optimizing.
- **Deprecations:** run the test suite with `E_ALL` and deprecation reporting before PHP upgrades; follow `framework-upgrade`.
