# Aurora Fotoportal development changelog

## 0.7.1-dev.19-fix1
- Fix Delivery workflow routing so step 5 opens the native Aurora Delivery view instead of falling back to Dashboard when the standalone delivery entitlement is not enabled.
- Fix customer profile project links so they open the native Aurora project profile instead of the legacy Fotoportal admin.

## 0.7.1-dev.19
- Move Delivery into the native Aurora Photographer Workspace.
- Complete the native five-step project workflow: Project, Contract, Documents, Gallery and Delivery.
- Add project delivery readiness summary for galleries, previews, downloads and signed contract state.
- Add native delivery gallery overview and project status control.
- Remove the final workflow legacy bridge while preserving the existing delivery/status data model.
- Keep Customer Portal and automated final delivery as future module work.

## 0.7.1-dev.18
- Move Galleries into the native Aurora Photographer Workspace.
- Add photographer-wide gallery overview and project-scoped gallery workflow.
- Show gallery thumbnail, image counts, availability date, remaining days and status.
- Add compact actions for preview, derivative regeneration, Premium Proof PDF and deletion.
- Add native ZIP gallery upload with watermark, download and retention options.
- Enforce signed-contract gating in both Workspace UI and the existing backend upload handler.
- Preserve Workspace/project context after gallery actions.
- Leave Delivery as the final legacy workflow step.

## 0.7.1-dev.17
- Move project Documents into the native Aurora Photographer Workspace.
- Keep document navigation scoped to the active project.
- Show existing project documents with type, date, open and delete actions.
- Add native document registration form using the existing document data model.
- Preserve Aurora Workspace context after add/delete actions.
- Keep Gallery and Delivery as the remaining legacy workflow steps.

## 0.7.1-dev.16
- Move project Contracts into the native Aurora Photographer Workspace.
- Open the Contract step directly from the native project workflow.
- Show project-scoped contract list with draft/sent/signed states and signed timestamp.
- Add native contract creation form in Aurora Workspace.
- Allow draft contracts to be marked as sent from the workspace.
- Preserve workspace context after contract creation/status actions instead of redirecting to legacy admin.
- Keep Documents, Gallery and Delivery as the remaining legacy workflow steps.

## 0.7.1-dev.15-fix1
- Fix critical error when opening a native Aurora project profile.
- Use the existing tenant-scoped `get_documents()` and `get_galleries()` helpers instead of non-existent project-specific helper names.

## 0.7.1-dev.15
- Move Projects into the native Aurora Photographer Workspace.
- Add tenant-scoped project list with search, project-type filter and status filter.
- Add native project profile with project metadata, linked customer and project notes.
- Add five-step Aurora project workflow: Project, Contract, Documents, Gallery and Delivery.
- Surface contract/document/gallery counts in the project workflow.
- Include test projects with a visible Test badge.
- Harden customer join in the project list with matching tenant ownership.
- Keep remaining production modules on temporary legacy bridges.

## 0.7.1-dev.14-fix2
- Use the configured Aurora branding logo in Photographer Workspace instead of the static `A` mark when a logo is available.
- Apply the same branding logo to the mobile workspace header.
- Increase customer search-field left padding so the search icon no longer overlaps typed text.

## 0.7.1-dev.14-fix1
- Show all tenant-owned customers in the native Photographer Workspace customer register.
- Include customers/projects created as test data instead of silently hiding them.
- Mark test customers with a visible `Test` status badge.

## 0.7.1-dev.14
- Move Customers into the native Aurora Photographer Workspace.
- Add tenant-scoped customer list with search and filters.
- Add native Aurora customer profile with contact details and project overview.
- Move the new customer/project 3-step wizard into Photographer Workspace.
- Redirect workspace-created customers directly to their new Aurora customer profile.
- Repair required-field handling in the customer/project save handler.
- Tenant-scope customer number generation.
- Keep Projects and remaining modules on temporary legacy bridges for now.

## 0.7.1-dev.13-fix3
- Fix the remaining Aurora Admin / System “Fotografvisning” links that still pointed directly to the legacy Fotoportal admin.
- Route both photographer-view entry points to the new Photographer Workspace dashboard.
- Update the support-mode wording so Aurora Admin clearly distinguishes platform administration from the photographer workspace.

## 0.7.1-dev.13-fix2
- Route Aurora System / Fotografvisning to the new Photographer Workspace instead of the legacy Fotoportal admin.
- Fix new customer/project save flow so newly created records keep the active photographer account and redirect with the correct client/project ID.
- Harden `get_client()` with account-scoped lookup.
- Suppress WordPress/third-party notices such as Vipps inside temporary legacy Fotoportal screens.
- Remove the old notice-relocation script that intentionally moved external notices into Aurora.

## 0.7.1-dev.13-fix1
- Fix the Photographer Workspace “Ny kunde / prosjekt” bridge to open the existing `wizard` tab.
- Make every legacy Fotoportal URL automatically include `aurora_legacy=1`.
- Keep navigation inside the temporary legacy Fotoportal view instead of redirecting back to the new Workspace dashboard.
- Fix buttons such as “Ny kunde/prosjekt” inside Kunder and other legacy module navigation.

## 0.7.1-dev.13
- Correct tenant migration to target the real `9ls1_fotoportal_*` domain tables.
- Migrate legacy domain rows to the default photographer account using `account_id`.
- Enforce photographer account scope on core customer, project, contract, document, gallery, image and log reads.
- Stamp new domain records with the active photographer account.
- Add account-aware lookups for project/customer relationships.
- Keep public contract token lookup separate because the signing token itself is the external access credential.
- Tenant isolation is now enforced in the core Fotoportal admin data path; remaining secondary handlers will continue to be audited as modules move into Photographer Workspace.

## 0.7.1-dev.12-fix1
- Fix Photographer Workspace legacy-module links.
- Pass the legacy Fotoportal tab as the correct first argument to `fotoportal_url()`.
- Preserve `aurora_legacy=1` as a separate query argument so Kunder, Prosjekter, Kontrakter, Dokumenter and Gallerier open their intended existing views instead of returning to Dashboard.

## 0.7.1-dev.12
- Add centralized Aurora Tenant Context.
- Add additive `account_id` ownership to existing Fotoportal domain tables.
- Migrate existing unscoped rows to the seeded/default `9Ls1 Foto` photographer account.
- Add indexes for tenant-scoped domain access.
- Add centralized current photographer account resolution.
- Add helpers for tenant-scoped SQL, insert stamping and row ownership checks.
- Begin stamping new domain records with the current photographer account.
- Expose tenant foundation status in Aurora Admin / System.
- Preserve all existing Fotoportal data during migration.

## 0.7.1-dev.11-fix2
- Left-align Photographer Workspace content against the sidebar.
- Remove centered desktop content container behavior on wide screens.
- Keep a controlled maximum content width while unused space remains to the right.

## 0.7.1-dev.11-fix1
- Redirect the legacy Fotoportal admin route to the new Photographer Workspace.
- Require an explicit `aurora_legacy=1` flag to open the old Fotoportal UI.
- Keep temporary legacy module links working only as development bridges.
- Suppress WordPress/plugin admin notices inside the Photographer Workspace.

## 0.7.1-dev.11
- Add the first dedicated Aurora Photographer Workspace shell.
- Remove normal WordPress admin chrome inside the photographer workspace.
- Add permanent Aurora sidebar, top bar, photographer identity and dashboard.
- Drive photographer navigation from account module entitlements.
- Add photographer account/settings and module-access overview.
- Keep existing Fotoportal module screens available as explicit temporary development links.
- Keep dashboard metrics unbound until Fotoportal domain records have tenant ownership.
- Point Aurora Admin development/support link to the new Photographer Workspace.

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
