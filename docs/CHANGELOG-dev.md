# Aurora Fotoportal development changelog

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
