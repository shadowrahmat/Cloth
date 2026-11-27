# Database scaling and operations

- **Order of operations:** measure, fix queries and indexes, add caching where appropriate, then scale vertically, then read replicas, then partitioning/sharding. Premature sharding is costly; need evidence (size, write throughput, latency).
- **Connection management:** pooling (application pool or a pooler like PgBouncer/ProxySQL-class tools), pool sizes matched to database limits and worker counts, timeouts, avoid connection-per-request and long idle transactions.
- **Read replicas:** replication lag means stale reads; route only lag-tolerant reads, keep read-your-writes paths on the primary, monitor lag, handle failover.
- **Partitioning/sharding:** partition large tables by time or key to prune scans and ease retention; shard by tenant or entity key only when single-node limits are reached; cross-shard queries and transactions become hard; plan resharding.
- **Caching layer:** cache query results or computed views with explicit invalidation; cache stampede protection; never the source of truth.
- **Backups and recovery:** automated backups plus point-in-time recovery where supported, offsite copies, encryption, **tested restores**, defined RPO/RTO, backup of schema migrations and config; do not treat replicas as backups.
- **High availability:** managed failover, health checks, tested failover runbook, clock and replication settings reviewed.
- **Monitoring:** slow query log, query statistics, connections, locks/deadlocks, replication lag, disk and IOPS, cache hit ratios, bloat/vacuum (PostgreSQL), buffer pool (MySQL), table growth; alert on trends.
- **Maintenance:** statistics updates, vacuum/analyze or optimize as engine requires, index rebuilds when justified, retention and archiving jobs, upgrade planning (see `framework-upgrade` for process).
- **Multi-tenant data:** row-level tenant column with enforced scoping, schema-per-tenant, or database-per-tenant trade-offs (isolation vs operational cost); see `saas-multitenancy` when available.
- **Security:** least-privilege database roles (app vs migration vs read-only), network restrictions, TLS, secrets rotation, audit logging, no production access from developer machines without approval.
- **Search/analytics workloads:** move heavy analytical or full-text workloads to purpose-built stores when evidence shows contention; keep the pipeline idempotent.
