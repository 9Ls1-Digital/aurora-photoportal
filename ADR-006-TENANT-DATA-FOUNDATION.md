# ADR-006 — Tenant Data Foundation

Status: Accepted for development

## Decision

Every photographer-owned Fotoportal domain record must belong to an Aurora
Photographer Account through `account_id`.

dev.12 adds account ownership additively to existing domain tables and assigns
all legacy rows to the seeded/default `9Ls1 Foto` account.

## Central context

`NLS1_Aurora_Tenant_Context` is the single source for resolving the current
Photographer Account. Future photographer authentication/session logic must
plug into this context rather than adding account-selection logic throughout
individual modules.

## Migration safety

- Existing columns and rows are not removed.
- `account_id` is added only where absent.
- Existing rows with no owner are assigned to the default photographer account.
- An index is added for account-scoped access.
- Migration is versioned and idempotent.

## Security boundary

Adding `account_id` is the foundation, not the final enforcement layer.
All module repositories/queries and write handlers must be converted to require
the current account. Until that query audit is complete, the system must not
claim full cross-tenant isolation.

The next development step is to convert module queries/handlers and then bind
the new Photographer Workspace to real tenant-scoped data.
