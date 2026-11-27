# Layer-by-layer diagnosis

- **Frontend/page load:** network waterfall, request count and size, render-blocking resources, image formats and sizing, font loading, third-party scripts, JS execution (long tasks), hydration cost, layout shifts; tools: browser DevTools Performance/Network, Lighthouse, WebPageTest, bundle analyzers. Core Web Vitals specifics: `seo-web-vitals`.
- **Backend (request path):** time per route, slow spans, N+1 queries, serialization cost, synchronous calls to third parties, lock contention, blocking I/O in async runtimes, missing HTTP/cache headers; tools: APM/traces, framework profilers (Laravel Debugbar/Telescope, Django Debug Toolbar, py-spy, pprof, async-profiler, clinic.js, dotnet-trace, Xdebug/Blackfire/Pyroscope-class tools), query logs.
- **Database:** slow query log, query plans, index usage, lock waits, connection pool saturation, table/index bloat; see `database-engineering`, `mysql-engineering`, `postgresql-engineering`.
- **Memory/CPU:** heap/allocation profilers, GC logs, leak detection by growth over time, flame graphs, CPU vs I/O wait distinction, container limits and throttling.
- **Caching:** what is cached, hit ratio, key design, TTL and invalidation correctness, stampede protection, memory cost; HTTP caching headers and CDN behavior.
- **Background work:** queue depth and lag, job duration distribution, concurrency limits, retries storms, batch sizes.
- **Infrastructure:** CPU/memory/IO saturation, network latency between services, DNS, TLS handshakes, autoscaling lag, cold starts (serverless).
- **Mobile:** startup, jank, memory, battery, size; see `mobile-quality` and the framework skills.
- **Build/CI performance:** dependency caching, parallelism, incremental builds, test sharding.
- **Evidence in the report:** flame graph or trace excerpt, query plan, benchmark output, not only conclusions.
