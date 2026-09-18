# ADR-076 – Customer reset auto-login and public portal URL

Status: Accepted – dev.63

## Context
After a successful customer password reset, the reset key is consumed by WordPress. The previous success screen linked to a portal URL produced by a tenant-scoped helper. In public customer-auth context no photographer tenant is guaranteed to be active, so that helper could return an empty URL. Clicking the apparent login action then reloaded the consumed reset URL and produced “link expired”. The shared customer login also needed an explicit stable form target.

## Decision
- `customer_portal_url()` now accepts an optional authoritative `account_id` and can build/repair the portal token without ambient tenant context.
- A successful customer password reset repairs the client-user mapping, creates the WordPress auth session, restores the signed customer context and redirects directly to the customer portal.
- The reset flow no longer asks the customer to log in again after choosing a new password.
- Fotoportal fallback customer login posts to the canonical `/fotograf/kunde/` entry route.
- Public auth remains scoped to the validated client + account pair.

## Consequences
The consumed reset key is never used as the next navigation target, and customer reset/login no longer depends on a photographer tenant being selected in the current browser session.
