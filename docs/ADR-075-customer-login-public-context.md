# ADR-075 – Customer login public-context resolution

## Status
Accepted – dev.62

## Context
The photographer workspace could show a customer portal login as active while the public password-recovery route reported that no customer login existed. The admin/workspace view runs inside an explicit photographer tenant, but public token routes do not have the same active tenant context. Customer login helpers were therefore resolving the same client through two different account contexts.

## Decision
Customer login helper functions accept an optional explicit `account_id`. Public token routes must pass the account id from the already validated public client object. Photographer/admin workspace calls may continue to omit it and use the active tenant context.

The explicit account id is used for both the client lookup and primary-contact lookup. Customer WordPress user self-healing uses the same explicit account context.

## Security
The change does not introduce global client lookup by e-mail. Public resolution starts from a valid token-resolved client and then constrains all subsequent lookups by both `client_id` and `account_id`.

## Result
The customer portal, password recovery and customer-login status now agree on the same customer identity and WordPress user mapping.
