# ADR-036: Authoritative module gating

## Status
Accepted for `0.7.1-dev.32-account3`.

## Context
Aurora Admin already stored per-account module flags, but several Fotoportal features remained visible or callable even when the corresponding add-on checkbox was disabled. This made the module catalogue descriptive rather than authoritative.

## Decision
`wp_9ls1_aurora_account_modules` is the runtime source of truth for add-on entitlement inside Aurora Fotoportal. Core modules are always available. Add-ons must pass both presentation gating and server-side enforcement.

The first enforced add-ons are:

- `premium_proof`: Premium Proof / PDF generation and related Workspace actions.
- `customer_portal`: customer main portal, portal-user provisioning and portal e-mail actions.
- `favorites_comments`: favorites, selections, comments, Bildevalg and interaction notifications.
- `hq_delivery`: dedicated delivery Workspace and payment/delivery actions.
- `shop` and `customer_app`: remain catalogue entries and unavailable unless explicitly enabled and implemented.

## Migration rule
Schema migrations must never overwrite an explicit add-on choice. Core rows are repaired to enabled. Missing Trial add-on rows may receive Trial defaults once; existing enabled/disabled rows are preserved.

## Security
Hiding a menu item is insufficient. Direct admin-post/AJAX/public-route attempts against disabled add-ons are rejected server-side with a 403 response.

## Consequences
Aurora Admin can now reliably test packaging by toggling a module off and confirming the photographer no longer has functional access. Aurora License can later feed the same entitlement layer without changing feature-level gates.
