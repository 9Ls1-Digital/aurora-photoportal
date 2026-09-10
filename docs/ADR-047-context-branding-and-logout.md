# ADR-047 - Context branding and logout

## Status
Accepted for 0.7.1-dev.32-auth-context2.

## Decision
Fotoportal supplies independent photographer and customer branding to Aurora Auth using the existing persisted platform options. Customer branding falls back to photographer branding only when no dedicated customer background is configured.

Photographer Workspace exposes an explicit Logg ut action. Photographer logout returns to `/fotograf/`. Customer logout returns to `/fotograf/kunde/` and never to a legacy `?fotoportal_customer=1&token=...` login gate.

Legacy token URLs remain valid authenticated portal destinations and rollback compatibility paths, but are not canonical public login entries.

## Context redirect precedence fix

In auth-context2, explicit Aurora Auth logout destinations are authoritative for both photographer and customer contexts before any legacy customer-meta fallback is evaluated. This prevents a photographer account carrying historical customer metadata from being redirected to the customer login route.
