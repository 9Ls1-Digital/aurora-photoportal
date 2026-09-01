# ADR-004 — Aurora Account Platform foundation

Status: Accepted for development

## Product boundary

Aurora has two different administration contexts:

### Platform owner (Aurora Admin)
The 9Ls1/Aurora administrator manages:
- Photographer Accounts
- Licenses
- Module entitlements
- Platform branding
- System/support status

Normal platform-owner views do **not** list the photographer's end customers,
projects, contracts, galleries or images.

### Photographer workspace
The photographer manages:
- own end customers
- projects
- contracts
- documents
- galleries
- delivery

The current Fotoportal admin workspace remains available only as an explicit
development/support view until photographer login/account context is implemented.

## dev.10 database foundation

New additive tables:
- `wp_9ls1_aurora_accounts`
- `wp_9ls1_aurora_account_modules`
- `wp_9ls1_aurora_licenses`

The existing installation is seeded as the default `9Ls1 Foto` Photographer Account.

## Important limitation in dev.10

Existing Fotoportal domain records do not yet contain an `account_id`.
Therefore dev.10 establishes account/licensing/entitlement infrastructure but
does not yet claim tenant isolation for customer/project/gallery data.

The next migration must add account ownership to Fotoportal domain data and
scope every repository/query/API operation by the current Photographer Account.
