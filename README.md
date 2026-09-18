# Aurora Fotoportal

Aurora Fotoportal is the photography workflow and customer-delivery module developed by 9Ls1 Digital.

## Development baseline
- Base: Fotoportal v0.7.0 Premium Proof development line
- Current build: `0.7.1-dev.1`
- Git branch: `feature/admin-ux-cleanup`

## Compatibility policy
Existing `9ls1` / `NLS1` technical identifiers, WordPress option names and database structures are intentionally retained until a controlled migration is required. Aurora is introduced first as the product/platform layer.

See `/docs` for architecture and Git workflow.


## v0.7.1-dev.3
Admin UX checkpoint: dedicated Fotoportal submenu, grouped Aurora navigation, dashboard KPI cards and quick actions. No database or media workflow changes.


## v0.7.1-dev.3
- Release ZIP is packaged with the stable technical root folder `9ls1-fotoportal/`.
- This allows WordPress to recognize uploads as updates to the existing Aurora Fotoportal plugin and offer replacement.
- No database, gallery, PDF or workflow logic changes in this build.


### dev.5
Third-party admin notices are relocated above the Aurora module header.


## 0.7.1-dev.10 — Account Platform Foundation
Aurora Admin now separates platform-owner administration from the photographer workspace. Photographer Accounts, licenses, module entitlements and platform branding are introduced as additive infrastructure. Existing Fotoportal domain data is not yet tenant-migrated.


## Aurora Auth adapter checkpoint
`0.7.1-dev.32-auth-adapter1` registers Fotoportal with Aurora Auth when available, while retaining the existing public login handlers as a tested fallback.


## 0.7.1-dev.32-auth-branding1
Fotoportal now enables controlled Aurora Auth route takeover for `/fotograf/` and `/fotograf/kunde/`. Legacy Fotoportal route handlers remain in place as rollback/fallback if Aurora Auth is unavailable.

## Aurora Auth branding adapter
`0.7.1-dev.32-auth-branding1` keeps Aurora Auth as owner of `/fotograf/` and `/fotograf/kunde/` and supplies Fotoportal's existing photographer/customer login backgrounds, logo and accent through the reusable Auth branding contract. Legacy Fotoportal handlers remain as rollback fallback.


## 0.7.1-dev.32-auth-context2
Context-specific branding and logout integration for Aurora Auth. Photographer and customer public login entries retain separate background configuration, and logout returns to the matching canonical Aurora login route.

### DEMO phase 1 (dev.37)
Platform admin can create a Trial photographer, send a branded activation email, continue directly from password activation into the existing six-step photographer onboarding, and permanently remove non-primary test/demo photographer accounts with account-scoped database and uploads cleanup. Future generated demo content must be marked `is_test=1` for safe phase-2 retention/removal.


### DEMO onboarding polish (dev.38)
Live-test refinements: split address fields, account phone prefill, image size guidance, Aurora glass onboarding background, photographer logo in Workspace and explicit current-image display in profile settings.
