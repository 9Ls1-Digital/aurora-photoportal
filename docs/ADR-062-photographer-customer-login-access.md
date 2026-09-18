# ADR-062 – Photographer customer-login creation access

**Status:** Accepted  
**Release:** 0.7.1-dev.49-customer-login-access-fix  
**Date:** 2026-09-11

## Context
The photographer workspace customer profile includes **Opprett innlogging** for provisioning a customer's Fotoportal login. The form posts through WordPress `admin-post.php`. The handler still required `manage_options`, even though photographer users intentionally do not receive WordPress administrator capability. The result was a `Mangler tilgang.` response after an otherwise valid photographer workflow.

## Decision
The customer-login provisioning handler accepts photographer users only when the request is explicitly marked as originating from the Aurora photographer workspace (`aurora_workspace=1`) and the current user has `aurora_fotoportal_photographer` capability.

The customer lookup remains tenant-scoped through `get_client()`, the existing nonce is still required, and ordinary legacy/admin calls without the workspace marker remain restricted to `manage_options`.

## Consequences
- Photographers can provision a customer login from their own Aurora customer profile.
- No general WordPress admin access is granted.
- Cross-tenant customer IDs continue to fail because the client lookup is constrained by the active tenant account.
- The public customer login and customer portal routes are unchanged.
