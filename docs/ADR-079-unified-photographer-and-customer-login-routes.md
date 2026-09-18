# ADR-079 — Unified photographer and customer login routes

Date: 2026-09-14
Status: Accepted

## Context
Fotoportal historically exposed photographer/account-specific links and tokenized customer portal URLs in administration, e-mails and workspace UI. Aurora Access is now the authoritative authentication/session capsule, so public login addresses no longer need to identify the tenant in the URL.

## Decision
- All photographers use one permanent login: `/aurora/login/`.
- Every successful photographer login continues to Aurora Mine apper at `/aurora/apps/`; Fotoportal is opened from there.
- All Fotoportal customers use one permanent login: `/aurora/kunde/`.
- Authenticated customers are routed from their WordPress/Aurora identity metadata to the correct photographer account and customer record.
- The authenticated customer workspace uses `/aurora/kunde/portal/`.
- Legacy `/fotograf/`, `/fotograf/kunde/`, customer password subroutes and `/fotograf/kunde/portal/` redirect to the new canonical routes while preserving query arguments needed by transitional flows.
- Photographer/customer e-mails and admin-visible login fields use the shared login addresses instead of exposing customer portal tokens.
- Internal legacy token portal support remains available only where required for compatibility/demo internals; it is no longer the advertised customer login address.

## Consequences
Photographers and customers can bookmark one stable URL each. Aurora Access resolves identity and tenant after authentication, and public-facing UI no longer exposes long `?fotoportal_customer=...&token=...` login links.
