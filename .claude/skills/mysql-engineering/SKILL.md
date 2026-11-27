---
name: mysql-engineering
description: "Use for MySQL/MariaDB specifics: InnoDB schema, indexes, EXPLAIN, slow queries, online DDL on large tables, utf8mb4 and collations, locking and deadlocks. Detects the server version first."
---

# MySQL / MariaDB engineering

Detect first: MySQL vs MariaDB and exact version (compose files, hosting docs, `SELECT VERSION()`), ORM and migration tool, charset/collation, storage engine. Do not use features the version lacks. Do not connect to production; propose queries and run them only on local or dev data.

## Rules
- InnoDB and `utf8mb4`. Keep collation consistent across joined columns (mismatches cause errors and lost indexes).
- Every table gets a primary key (short, increasing preferred). Foreign keys with explicit `ON DELETE`; matching type and signedness on both sides.
- Composite indexes: equality columns first, then range/sort (leftmost-prefix rule). Do not wrap indexed columns in functions (`DATE(col)`, `col + 1`) or compare mismatched types; use range conditions.
- Diagnose with `EXPLAIN` (`EXPLAIN ANALYZE` on MySQL 8.0.18+). Watch for `type: ALL`, `Using filesort`, `Using temporary`, huge `rows`. Avoid `SELECT *`, deep `OFFSET` (use keyset pagination), and `LIKE '%x'`.
- Money is `DECIMAL`, never FLOAT. Store UTC; `TIMESTAMP` stops at 2038, so prefer `DATETIME` for far-future dates.
- Large-table DDL: check whether `ALGORITHM=INSTANT/INPLACE, LOCK=NONE` applies; otherwise plan an online schema change (gh-ost, pt-online-schema-change) with backup and rollback.
- Keep transactions short with a consistent lock order; retry on deadlock (error 1213). `REPEATABLE READ` uses gap locks.
- Keep strict mode and `ONLY_FULL_GROUP_BY`; fix the query instead of disabling them.
- `DROP`, `TRUNCATE`, migrations, and bulk `UPDATE`/`DELETE` need explicit approval. Show the affected row count first with `SELECT COUNT(*)` using the same `WHERE`.
