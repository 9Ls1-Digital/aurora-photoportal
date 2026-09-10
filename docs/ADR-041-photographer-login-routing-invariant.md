# ADR-041 — Photographer Login Routing Invariant

## Status
Accepted

## Context
Aurora Photographer and WordPress/Aurora Admin are separate authentication contexts. During later Fotoportal development, direct Photographer Workspace links could again fall through WordPress `auth_redirect()` and expose the standard `wp-login.php` screen when the photographer was logged out.

## Decision
1. Photographer Workspace must never use WordPress `wp-login.php` as its user-facing login route.
2. Logged-out requests to Photographer Workspace are intercepted through the WordPress `login_url` filter and routed to Aurora Photographer Login.
3. Account-specific Workspace URLs carry `account_id` when it is known.
4. Aurora also provides a global photographer login route without `account_id`; after successful authentication the photographer user's `aurora_fotoportal_account_id` resolves the correct Workspace.
5. Every photographer/studio account exposes a permanent account-specific Aurora login URL in Aurora Admin.
6. Future releases must preserve this boundary between WordPress/Aurora Admin, Aurora Photographer and Aurora Photo Client authentication.

## Consequences
- Photographers can bookmark or receive a stable Aurora login URL.
- Older Workspace links without tenant context fall back to Aurora's global photographer login rather than WordPress login.
- `account_id` is routing context only and is not treated as an authentication secret.
