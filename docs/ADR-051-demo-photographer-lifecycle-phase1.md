# ADR-051 – DEMO Photographer Lifecycle Phase 1

## Status
Accepted for dev.37 test build.

## Decision
Aurora Fotoportal owns the photographer Trial/onboarding lifecycle after a platform administrator creates the account. The activation email exposes one primary CTA. Password activation creates the authenticated photographer session and redirects directly into the existing six-step onboarding flow.

Aurora Fotoportal Admin also provides permanent deletion for non-primary photographer accounts. Deletion is strictly account-scoped, deletes tenant data and account-owned upload/media artifacts, and protects the platform's primary/default account. Legacy gallery directories are removed only when they are not referenced by another tenant.

The linked WordPress user is treated as a platform identity rather than disposable tenant data. It is disconnected by default. Explicit deletion is allowed only when the user is not privileged and no other Aurora identity is detected.

## Demo-content invariant
Future generated DEMO content must be stamped with the account_id and existing is_test=1 marker. This enables phase 2 to offer "keep demo content" versus "remove demo content" without touching photographer-owned production content.

## Rationale
The DEMO should feel like a real customer journey while remaining safe to reset repeatedly during testing. Account-scoped deletion and explicit identity preservation reduce the risk of deleting another photographer's data or a shared Aurora identity.
