# ADR-042 – Public Fotoportal authentication routes

## Status
Accepted – 2026-09-09

## Decision
Aurora Fotoportal exposes stable public authentication entry points generated from the WordPress installation URL:

- `/fotograf/` for photographer/studio authentication.
- `/fotograf/kunde/` for photo-client authentication.

No public photographer or photo-client login link may depend on `/wp-admin/` or `/wp-login.php`.

Photographer account-specific login links may add `account_id` to `/fotograf/` to preserve tenant context. The customer route is intentionally shared across all photographers on the installation; after successful authentication Aurora resolves the linked customer/account and redirects to the correct private portal.

Aurora Admin may link internally to a photographer customer card, but this is an admin-only management URL and must not be presented as the photographer's login URL. Support access to Photographer Workspace remains consent-based and separate.

## Rationale
The public authentication contract must survive plugin upgrades and must be reusable by a future Aurora Auth Capsule. Reading the base URL from WordPress keeps standalone installations portable and avoids hard-coded Aurora domains.

## Future extraction
The Fotoportal implementation is the reference implementation for a future `Aurora Auth` Capsule. That Capsule should own shared identity, authentication, MFA/2FA, password recovery, role/context resolution, redirect policy and audit/security controls while Fotoportal supplies product-specific account and customer resolution.
