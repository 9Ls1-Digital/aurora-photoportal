# Development changelog

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
