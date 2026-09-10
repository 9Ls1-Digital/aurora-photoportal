# ADR-048 — Photographer frontend workspace

## Decision
Photographer users receive an authenticated frontend workspace at `/fotograf/portal/`.
Aurora Auth owns route authentication/authorization; Fotoportal owns rendering and domain behavior.

## Compatibility boundary
The established `/fotograf/` login flow is not modified. The legacy hidden wp-admin
workspace remains available to authorized administrators/support and as a hard fallback
when the workspace API is unavailable.

## Identity
Photographer-facing URLs do not carry `account_id`. Account identity is resolved from
the authenticated Aurora/WordPress user and validated by the existing Fotoportal Auth adapter.

## Rollback
Deactivating the workspace registration or running an older Aurora Auth causes the
post-login callback to fall back to the proven hidden admin workspace.
