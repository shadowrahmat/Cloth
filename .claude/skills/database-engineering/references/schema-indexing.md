# Schema design, constraints and indexing

Applies to any engine; engine-specific behavior belongs to `mysql-engineering` / `postgresql-engineering`. Check the engine version first.
- **Model from access patterns and invariants:** normalize to remove update anomalies, denormalize deliberately (read models, counters) with a documented refresh path. Natural vs surrogate keys: surrogate primary keys plus unique constraints on natural keys.
- **Constraints do the safety work:** NOT NULL, UNIQUE, CHECK, foreign keys (with explicit ON DELETE behavior), enumerations via lookup tables or checked columns; the application is not the only guard.
- **Types:** exact types for money (integer minor units or DECIMAL), timestamps with timezone handling decided (store UTC), text length limits, boolean vs status enums, UUID/ULID vs bigint IDs (index locality trade-off).
- **Indexes:** create for foreign keys, frequent filters, sort/order patterns and unique rules; composite index column order follows equality columns first, then range/sort; covering indexes for hot reads; partial/functional indexes where the engine supports them. Every index costs writes and storage: remove unused ones after checking usage statistics.
- **Query tuning method:** capture the slow query and its parameters, read the plan (`EXPLAIN`/`EXPLAIN ANALYZE` on realistic data volume, in a safe environment), find scans/sorts/joins causing cost, change the index or query, re-measure. Avoid `SELECT *` on wide tables, functions on indexed columns in predicates, leading wildcards, OFFSET pagination on large tables (use keyset), N+1 from ORMs (eager loading/batching).
- **Transactions and concurrency:** keep transactions short, consistent lock order, choose isolation level knowingly, optimistic locking with version columns for user edits, `SELECT ... FOR UPDATE` only when needed, handle deadlock retries, idempotent writes.
- **JSON/semi-structured columns:** for flexible attributes only; index extracted fields if queried; do not hide relational data in JSON.
- **Soft deletes/audit:** soft delete complicates uniqueness and queries (scope everywhere, partial unique indexes); audit via history tables or events.
- **Time-series/large tables:** partitioning by time, retention jobs, archiving.
- **Document stores (MongoDB etc.):** model by access pattern, bounded array growth, indexes for queries, schema validation, transactions limits.
- **Data protection:** classify PII, encrypt sensitive columns where required, restrict access, retention policy.
