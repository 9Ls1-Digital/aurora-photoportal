# ADR-035 — Consent-based photographer support access

## Status
Accepted for `0.7.1-dev.32-account2`.

## Context
Aurora platform administrators need a safe way to troubleshoot a photographer's Workspace without requesting, storing or impersonating the photographer's password. Normal platform administration should remain separated from photographer end-customer data.

## Decision
Support access is explicit, photographer-controlled and temporary.

- The photographer owner enables or disables support access from Photographer Workspace → Settings.
- Aurora Admin cannot start a support session unless the account has explicit support consent.
- An approved support session is bound to the selected `account_id` and the authenticated Aurora/WordPress administrator.
- Sessions expire after 60 minutes and are cleared when the administrator leaves Photographer Workspace, when consent is withdrawn, or when the administrator explicitly exits support mode.
- Photographer credentials are never read, stored, shared or changed.
- Photographer Workspace displays a persistent support-mode banner during the session.
- Consent changes, session start/end, denied attempts and revocation are written to a dedicated platform support log.

## Data model
`wp_9ls1_aurora_accounts` gains additive consent fields:

- `support_access_enabled`
- `support_access_granted_at`
- `support_access_granted_by`

A new `wp_9ls1_aurora_support_logs` table records account, actor, action, optional session expiry and timestamp.

## Consequences
Aurora support can troubleshoot the correct tenant without password sharing while keeping access auditable and revocable. This is support access, not permanent platform-owner browsing rights to photographer customer data.
