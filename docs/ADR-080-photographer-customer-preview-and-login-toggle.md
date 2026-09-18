# ADR-080 – Photographer customer preview and customer-login toggle

## Status
Accepted – dev.67

## Context
Photographers need to inspect the exact customer experience without knowing or resetting the customer's password. At the same time, customer login must be administratively controllable per customer without deleting the WordPress user or customer data.

## Decision
Fotoportal adds a photographer-only preview route at `/aurora/kunde/forhandsvisning/`. The route requires an authenticated photographer/admin, a tenant-scoped customer and a WordPress nonce. It establishes a short-lived signed preview cookie and renders the customer portal/galleries without changing the photographer's WordPress/Aurora identity. Customer-mutating authorization continues to require a real customer identity.

Customer login state is stored per tenant/customer and defaults to enabled for backwards compatibility. A photographer can activate/deactivate it from the customer record. Deactivation does not delete the user or customer data; it destroys WordPress sessions for the mapped customer and the Fotoportal Auth adapter denies future customer authentication until re-enabled. Photographer preview remains available while customer login is disabled.

## Consequences
- No customer password is exposed to the photographer.
- Photographer preview is tenant-scoped and auditable.
- Customer access can be suspended and restored without destructive account changes.
- Canonical customer login remains `/aurora/kunde/`; preview is a separate photographer-only capability, not another customer login URL.
