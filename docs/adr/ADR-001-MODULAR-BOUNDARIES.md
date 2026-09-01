# ADR-001 — Modular boundaries and Aurora compatibility

Status: Accepted

## Context
9Ls1 Fotoportal has grown from a WordPress plugin into a broader photography workflow platform. The application is expected to gain a customer portal, installable PWA, favorites, comments, delivery and potentially commercial multi-photographer distribution.

Aurora is being developed as the broader 9Ls1 business platform.

## Decision
9Ls1 Fotoportal remains an independent application. Aurora integration is optional.

New capabilities must be built behind stable boundaries so shared infrastructure can later be supplied by Aurora adapters.

Existing working functionality is not rewritten only to achieve architectural purity.

## Consequences
Positive:
- Lower regression risk.
- Fotoportal stays independently sellable.
- Customer app/API can survive backend changes.
- Aurora integration becomes incremental.

Trade-off:
- For a period, the existing admin class remains a façade containing legacy responsibilities.
- Some duplication may exist until a capability is actually migrated.
