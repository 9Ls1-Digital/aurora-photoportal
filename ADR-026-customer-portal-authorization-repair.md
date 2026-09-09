# ADR-026 – Customer Portal Authorization Repair

## Decision
Aurora customer portal authorization is anchored to the client ID and photographer account ID stored on the WordPress user. For legacy accounts, Aurora may self-heal this mapping only when the authenticated user's normalized email matches the client email or the primary contact email for the same client and account.

## Rationale
Public customer pages are resolved from portal tokens and can execute outside the Photographer Workspace tenant context. Authorization must therefore use the account ID carried by the resolved public client instead of relying on the current backend tenant context.

## Security
A successful WordPress login alone does not grant portal access. The user must either already be mapped to the exact client/account pair or pass the email-match repair check for that same pair.
