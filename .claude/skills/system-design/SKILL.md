---
name: system-design
description: "Use for architecture decisions: module boundaries, data flow, scalability, trade-offs between options, or 'how should I structure X'. Compares options; does not implement."
---


# System Design

Start from product requirements, constraints, current architecture, and expected scale. Identify actors, core use cases, domain boundaries, data ownership, trust boundaries, integrations, and failure modes. Compare viable options with trade-offs. Consider consistency, idempotency, retries, caching, observability, migrations, backward compatibility, and operational complexity. Prefer the simplest design that meets evidenced needs. Document major decisions as ADRs if the project uses them. Do not introduce microservices or infrastructure without a concrete need.
