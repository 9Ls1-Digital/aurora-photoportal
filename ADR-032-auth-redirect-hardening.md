# ADR-032 – Auth Redirect Hardening

## Decision
Aurora customer authentication must remain inside the branded customer portal flow even when WordPress, WooCommerce, Jetpack or security plugins attempt to route the user through `wp-login.php`.

A signed, HttpOnly customer auth context cookie identifies the relevant Aurora customer portal. Requests to WordPress login, lost-password and reset-password endpoints are intercepted for that customer context and redirected back to the Aurora portal/password flow. Aurora customer logout also returns to the customer's portal login gate.

## Scope
This applies only to WordPress users linked to an Aurora Fotoportal client and to sessions carrying a valid Aurora customer context. Photographer/admin and unrelated WordPress/WooCommerce users keep their normal authentication flow.
