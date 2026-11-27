# Performance method and budgets

1. **Define the goal:** which user journey or job, which metric (latency percentile, throughput, memory, bundle size, Core Web Vital), target and why (budget). No target means no stopping rule.
2. **Baseline:** measure the current behavior in a realistic environment (production-like build, data volume, hardware/network, warm and cold cache). Record the command, environment and numbers.
3. **Find the bottleneck:** profile, do not guess. Use the right tool per layer (below). Check the biggest contributor first (Amdahl): a 5% component cannot give a 50% win.
4. **Change one thing**, re-measure the same way, keep or revert. Prefer removing work over speeding it up, then caching, then parallelizing.
5. **Guard against regressions:** add a benchmark, budget or alert where the metric matters; document the finding.
6. **Report:** before/after table with environment, variance (several runs, median/percentiles), trade-offs (memory, complexity, staleness), and what was not measured.
- **Percentiles over averages:** p50/p95/p99 and tail behavior; throughput vs latency under load; coordinated omission when load testing.
- **Do not:** micro-optimize unmeasured code, add caches without an invalidation story, sacrifice correctness, accessibility or readability for unmeasurable gains, benchmark in debug mode or on an empty database.
- **Budgets (examples of dimensions):** API p95 latency per endpoint, page LCP/INP/CLS, JS bundle size per route, DB query count per request, memory ceiling per worker, queue lag, build time.
- **Load testing:** representative scenarios and data, ramp profiles, separate load generator from system under test, test in staging not production unless approved, monitor the whole stack, stop conditions.
