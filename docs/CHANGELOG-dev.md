# Aurora Fotoportal development changelog

## 0.7.1-dev.10
- Add Aurora Account Platform foundation.
- Replace normal Aurora owner dashboard with platform-only administration.
- Add Photographer Accounts with a default `9Ls1 Foto` account.
- Add per-account module entitlements.
- Add licenses with status, validity, user limits and storage quota.
- Add Aurora platform branding settings.
- Hide the photographer/customer workspace from normal Aurora owner navigation.
- Keep the existing Fotoportal workspace available explicitly as development/support mode.
- Add additive account/module/license database tables.
- Document the platform/photographer boundary and current tenant-isolation limitation in ADR-004.

## 0.7.1-dev.9
- Move project date, location, contract state and editable status into the fixed project header.
- Remove duplicate Project Details and Status panels from step 1.
- Change project workflow order to Project → Contract → Documents → Gallery → Delivery.
- Add contextual Back / Next buttons.
- Lock Gallery until at least one project contract is signed.
- Enforce the contract gate in the gallery ZIP upload handler.
- Add ADR-003 documenting project workflow gating.

## 0.7.1-dev.8
- Convert project profile from one long page into a five-step workspace:
  Prosjekt → Galleri → Dokumenter → Kontrakt → Leveranse.
- Keep all existing forms, handlers and database records.
- Add a delivery preparation view without introducing new delivery data.
- Document Photographer Accounts / multi-tenant entitlement architecture in ADR-002.

## 0.7.1-dev.7
- Replace the combined customer/project page with a real 3-step wizard.
- Separate customer data from project data visually and conceptually.
- Correct customer-name guidance and placeholders.
- Add validation per step and a confirmation summary before creation.
- Keep the existing save handler and database schema unchanged.

## 0.7.1-dev.6
- Add visual Aurora Workflow: Kunde → Prosjekt → Galleri → Leveranse.
- Reuse existing Fotoportal routes; no database or handler changes.
- Prepare admin information architecture for later Customer App and Portal/API modules.
- Keep stable in-place WordPress update packaging established in dev.4/dev.5.

# Development Changelog

## 0.7.1-dev.4
- Keep the installed plugin directory identity from dev.2 so WordPress can replace the existing plugin during ZIP upload.
- No database, gallery, PDF or workflow changes.

# Development changelog

## 0.7.1-dev.3
- Fix release packaging: ZIP now contains the stable `9ls1-fotoportal/` plugin root.
- Keeps the visible product name **Aurora Fotoportal**.
- No functional/database changes.

## 0.7.1-dev.1 — Aurora admin baseline
- Renamed visible WordPress admin hub from **9Ls1 Plugins** to **Aurora**.
- Renamed visible Fotoportal product name to **Aurora Fotoportal**.
- Added Aurora visual skin to existing admin UI.
- Kept all existing WordPress slugs, database structures and `NLS1` technical identifiers unchanged.
- Added Aurora-ready architecture, ADR and Git workflow documentation.
- No gallery, PDF, upload or data-model behavior intentionally changed.

## 0.7.1-dev.2
- Added dedicated **Fotoportal** submenu under the Aurora WordPress admin hub.
- Added a new Aurora module header and grouped navigation (Work / Production / System).
- Redesigned Fotoportal dashboard with clickable KPI cards and quick actions.
- Added Aurora-ready roadmap/status panel.
- Kept existing database schema, handlers, gallery and PDF logic unchanged.
- Legacy Aurora router remains available for compatibility; new internal links use the dedicated Fotoportal admin page.

## 0.7.1-dev.5
- Move third-party WordPress admin notices out of the Aurora Fotoportal hero/header.
- Preserve Fotoportal's own workflow/status notices in their existing locations.
- Keep the dev.2 technical plugin folder for in-place WordPress replacement updates.
