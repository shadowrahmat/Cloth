---
name: performance-engineering
description: "Use when something is slow or heavy: page load, slow queries, memory/CPU, bundle size, API latency. Measures first, optimizes the measured bottleneck, re-measures. For Core Web Vitals and SEO context: seo-web-vitals."
---


# Performance Engineering

Measure before optimizing. Identify the relevant user journey and baseline. Inspect network waterfalls, rendering, bundle size, memory/CPU, database queries, cache behavior, and background work as applicable. Optimize the measured bottleneck without degrading correctness, accessibility, or maintainability. Re-measure and report the result. Avoid speculative caching and premature complexity.

## Read when
| Concern | File |
|---|---|
| Method, baselines, budgets, load testing, reporting | [references/method-budgets.md](references/method-budgets.md) |
| Diagnosing frontend, backend, database, memory, caching, background work, infra | [references/layers-tools.md](references/layers-tools.md) |
