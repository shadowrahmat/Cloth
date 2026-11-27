# Secrets, dependencies and supply chain

- **Secrets in repo:** search the tree and history for keys/tokens/passwords (`.env` files, config, CI files, notebooks, mobile bundles); use a scanner if the project has one; report location and type, never the value; treat any committed live secret as compromised: rotate, then remove from history if needed (ask first).
- **Handling:** environment/secret manager, least-privilege credentials per environment, no secrets in logs, URLs, error messages, client bundles, Docker images or build args.
- **Dependencies:** lockfiles committed; run the ecosystem's audit (`npm audit`, `composer audit`, `pip-audit`, `cargo audit`, etc.) when available and offline-safe; evaluate reachability before ranking severity; prefer upgrades through `dependency-management`; check abandoned packages and typosquats on new additions.
- **CI/CD:** pinned actions/images by digest or trusted versions, minimal token permissions, no secrets exposed to forked PRs, protected branches and required reviews, artifact provenance where available, separate build and deploy credentials.
- **Containers/hosting:** non-root user, minimal base images, no secrets baked in, patched base images, network exposure minimized, security headers at the edge.
- **Third-party scripts/SDKs:** inventory, integrity attributes where practical, CSP allow-lists, privacy impact.
- **Backups and data stores:** access controlled and encrypted; test restore; no public buckets/databases (check storage ACLs).
- **Incident readiness:** who rotates what, how fast, where logs are; link to `engineering:incident-response`-style process if the team has one.
