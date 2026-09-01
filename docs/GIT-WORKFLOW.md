# Git workflow — 9Ls1 Fotoportal

## Repository
Recommended private GitHub repository:

`9ls1-fotoportal`

## Branches

### `main`
Tested, installable releases only.

### `develop`
Integration branch for the next release.

### Feature branches
Examples:
- `feature/admin-ux-cleanup`
- `feature/customer-portal`
- `feature/customer-app-pwa`
- `feature/favorites-comments`
- `feature/premium-proof-pdf`
- `feature/aurora-adapters`

### Hotfix
Example:
- `hotfix/branding-save`

## Release tags

Use:
- `v0.7.0`
- `v0.7.1`
- `v0.8.0`

## Recommended first import

1. Create private GitHub repository `9ls1-fotoportal`.
2. Put the exact currently tested plugin baseline in the repository root.
3. Commit:
   `chore: establish 9Ls1 Fotoportal Git baseline`
4. Tag the exact tested version.
5. Create `develop` from `main`.
6. Do all next work from feature branches.

## Commit style

Examples:
- `feat: add customer app route`
- `feat: add read-only album API`
- `fix: preserve PNG watermark alpha`
- `refactor: extract gallery read service`
- `docs: add Aurora compatibility ADR`
- `chore: prepare release 0.7.1`

## ZIP releases
Release ZIPs are build artifacts and should normally not be committed to Git.
Attach them to GitHub Releases instead.
