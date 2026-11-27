# Universal project convention checklist

Use only sections supported by repository evidence. Include file paths and note conflicts.

1. **Language and versions:** runtime, framework and library versions, module type, min supported versions.
2. **Architecture:** directory boundaries, layered/module/domain patterns, shared code, dependency injection, service/repository patterns.
3. **Routing and API:** route organization, validation, authorization, response envelopes, errors, pagination, versioning.
4. **Persistence:** ORM/query builder, migrations, schemas, caching, transactions, tenant scoping.
5. **UI:** framework/component conventions, CSS strategy, design tokens, accessibility, responsive layout, state and data fetching.
6. **Testing:** installed runner and assertion style, test layout, mocking/fakes, fixtures, database isolation, meaningful coverage.
7. **Quality tooling:** linters/formatters/static checks, build scripts, lockfiles, CI jobs, generated code.
8. **Git collaboration:** conventional commits or local format, branch strategy, PR template, review/check policies; never guess protected refs.
9. **Infrastructure:** Docker/compose, runtime/env requirements, deployment process, monitoring, backups, rollback.
10. **Docs and security:** canonical documentation, secrets-handling, auth boundaries, customer-data safety.

For each category: cite at least 2-3 examples where available, identify competing patterns, distinguish defaults from deliberate choices, avoid writing duplicate rules, and prefer paths scoped to the files where the convention applies.
