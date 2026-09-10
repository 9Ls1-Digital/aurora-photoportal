# ADR-045 — Aurora Auth controlled route takeover

## Status
Accepted for Fotoportal auth-takeover1.

## Decision
Aurora Fotoportal now registers its Auth adapter with `takeover_routes = true`.

When Aurora Auth is active, it owns the public login entry routes:
- `/fotograf/` for photographer authentication
- `/fotograf/kunde/` for customer authentication

Fotoportal continues to provide product-specific identity resolution, authorization, account/client mapping, branding data and post-login destinations.

## Rollback rule
The existing Fotoportal public auth handlers remain in the plugin. They are not deleted or disabled globally. Because Aurora Auth dispatches earlier when active, they act as a fallback path if the Auth Capsule is deactivated or unavailable.

## Acceptance criteria
- Both public URLs return HTTP 200 login experiences under Aurora Auth ownership.
- Photographer login resolves the correct `account_id` and redirects to Workspace.
- Customer login resolves `account_id` + `client_id` and redirects to the customer portal.
- Invalid context/account mappings are denied.
- Deactivating Aurora Auth restores the legacy Fotoportal route path without code changes.
