# ADR-044 – Aurora Auth Adapter Checkpoint

## Status
Accepted for 0.7.1-dev.32-auth-adapter1.

## Decision
Aurora Fotoportal registers itself with Aurora Auth as app `fotoportal` with two contexts: `photographer` and `customer`. The canonical public routes remain `/fotograf/` and `/fotograf/kunde/`.

The adapter supplies Fotoportal-specific identity resolution, tenant/client authorization, post-login redirects and branding to Aurora Auth. Canonical login URL helpers prefer the Auth service whenever the app is registered.

For this checkpoint `takeover_routes` is false. The proven Fotoportal frontend handlers remain route owners and authentication fallback. No existing login route is removed.

## Rationale
Registration and route takeover are intentionally separate checkpoints. This proves that Auth can discover Fotoportal and understand its tenant/customer identity contract without putting the working public login flow at risk.

## Exit criteria
- Aurora Auth status shows exactly one registered app: Aurora Fotoportal.
- Photographer login URL remains `/fotograf/` and works as before.
- Customer login URL remains `/fotograf/kunde/` and works as before.
- Deactivating Aurora Auth does not break either Fotoportal login route.
