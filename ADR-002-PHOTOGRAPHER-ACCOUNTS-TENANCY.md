# ADR-002 — Photographer accounts and tenancy

Status: Proposed

## Context
Aurora Fotoportal has two distinct customer concepts:

1. The photographer/studio that buys and operates Fotoportal.
2. The photographer's own end customers (wedding couples, families, companies, artists, etc.).

These must not share the same data scope.

## Decision direction
Introduce an Aurora-level `Photographer Account` (also called Studio/Tenant internally).

Each Photographer Account will have:
- account/studio name
- owner/contact
- login users
- subscription/status
- branding
- enabled Fotoportal capabilities (entitlements)
- isolated Fotoportal data scope

Example entitlements:
- Customers
- Projects
- Contracts
- Documents
- Galleries
- Premium Proof/PDF
- Customer Portal
- Favorites/comments
- HQ delivery/download
- Shop/orders
- PWA/Customer App

## Data isolation requirement
A feature toggle alone is not sufficient. Before multiple photographers share one installation, Fotoportal data must be scoped by a tenant/account identifier.

Existing 9Ls1 Foto data will be migrated to a default Photographer Account, preserving all current IDs and records.

## Aurora relationship
Photographer Accounts belong at Aurora platform level, while the photographer's end customers remain owned by Aurora Fotoportal.

This work should be implemented on a dedicated feature branch after the current admin UX cleanup is complete.
