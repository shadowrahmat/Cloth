# Migration safety

Never run migrations against production or modify production data without explicit approval, a verified backup and a rollback plan. Develop and rehearse on a copy or local database.
- **Principles:** every migration is versioned, reviewed, idempotent where possible, and tested on a realistic data copy; separate schema changes from data backfills; ship in backward-compatible steps so old and new application versions both work during deploy.
- **Expand/contract pattern:** (1) expand: add nullable column/new table/index (concurrently where supported); (2) deploy code writing both old and new; (3) backfill in batches; (4) switch reads; (5) contract: remove old column/table in a later release after confirming no use.
- **Risky operations on large tables:** adding NOT NULL with default, changing column types, adding indexes without online/concurrent options, renames, dropping columns referenced by running code, foreign key additions (validate separately where supported), table rewrites. Check your engine and version for online DDL behavior and lock levels, then choose the safe form; set lock/statement timeouts for migrations.
- **Backfills:** batch by primary key ranges, small transactions, throttle, resumable, idempotent, monitor replication lag and locks, run as a job rather than inside the schema migration.
- **Rollbacks:** down migrations are not safe after data changes; plan forward fixes, keep backups/snapshots, feature flags to disable new behavior; destructive steps last.
- **Zero-downtime deploy ordering:** migrations that add compatible structure run before code; migrations that remove run after code no longer needs them.
- **Testing:** run migrations up on empty database and on a snapshot of realistic data, run application tests against the migrated schema, test rollback path if provided, measure duration and locks.
- **ORM specifics:** generated migrations still need review (they can drop and recreate); keep seed data separate from schema migrations; avoid model code inside migrations (use SQL/schema builder).
- **Release record:** document migration, risk, duration estimate, rollback/forward plan, approval.
