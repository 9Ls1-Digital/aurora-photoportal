## 0.7.1-dev.31-fix19
- Korrigert Workspace-branding slik at engelsk `Photo Portal` ikke dobles med norsk `Fotoportal`.
- Utvidet vannmerkeplassering til syv valg: topp venstre/senter/høyre, bunn venstre/senter/høyre og midt.
- Vannmerkevalgene vises i tre rader og støttes i både preview, dashboard og genererte preview-bilder.

## 0.7.1-dev.31-fix18
- Make Photographer Workspace sidebar branding fully dynamic from Aurora Admin platform branding.
- Use the configured Aurora logo in the dark brand card.
- Show `<Platform name> Fotoportal` with `Developed by <Company>` beneath it.
- Keep mobile workspace branding synchronized with the configured platform name/logo.

## 0.7.1-dev.31-fix17
- Repaired watermark preview/upload UX and configurable platform test image.
- Restored photographer settings access.
- Added aggregate delivery overview and unpaid filter.
- Added resource category routing/filtering.

## 0.7.1-dev.31-fix16 – Dashboard & Watermark Repair
- Repaired the critical runtime error introduced in fix15 by restoring gallery activity, selection status, hero and customer-login handlers that were accidentally replaced.
- Rebuilt Photographer Workspace dashboard with live KPI cards, follow-up tasks, resources and customer-experience preview.
- Redesigns watermark settings to match the approved two-column Aurora layout with upload, position, size, transparency and live preview.
- Adds per-photographer resource uploads without removing existing gallery interaction methods.
- Redigeringsønske notifications now open Bildevalg rather than the gallery.
- Photographer watermark is applied to regenerated preview images only; original files remain untouched.
- Added top-left and top-right watermark rendering support.

## 0.7.1-dev.31-fix14 – Customer Status Sync & Portal Layout Cleanup

- Synkroniserer kundens Status-visning med samme prosjektdata som Fotograf Workspace.
- Viser korrekt kontrakt-, betalings-, galleri- og leveransestatus per prosjekt.
- Dokumenter vises som valgfri tilleggsinformasjon når de finnes.
- Oppdaterer statusforklaringen og flytter den inn under «Fra prosjekt til leveranse».
- Flytter «Kundens faste portal» og portal-URL inn i Kundeportal/Hero Designer-kortet.

## 0.7.1-dev.31-fix13 – Customer Account Views
- Added dedicated customer-facing Min profil view.
- Added dedicated per-project Status view for contract, payment, gallery and delivery.
- Customer account menu now routes to real views; project/gallery link returns to portal overview.
- Removed duplicated profile/status card from the main customer portal.

## 0.7.1-dev.31-fix8
- Added customer account/profile menu to private portal header with profile, status, projects/galleries, password and logout shortcuts.
- Added compact customer profile and delivery status overview in the portal.
- Renamed gallery comments to comments for requested editing throughout customer and photographer interaction UI.

# dev.31-fix6
- Added self-healing customer login creation/linking from the customer contact email.
- Added visible customer-login status and manual Create login action on the customer card.
- Existing WordPress users with the same email are linked instead of duplicated.
- Password recovery now repairs legacy customers that have an email but no portal user.

# 0.7.1-dev.31-fix5
- Added targeted password-reset mail diagnostics: customer email, matched WordPress user, subject, timestamp, wp_mail result and wp_mail_failed detail are logged server-side.
- Keeps WP Mail SMTP/Brevo as the mail transport via WordPress wp_mail().


## 0.7.1-dev.31-fix4
- Added an Aurora-branded customer password recovery flow.
- Replaced the WordPress lost-password link in customer login with a portal-specific reset route.
- Password reset messages are sent with photographer/studio branding and a secure WordPress reset key.
- Customers set the new password inside Aurora rather than the default WordPress login UI.
- Failed reset-mail attempts are surfaced to the customer and logged server-side for SMTP diagnostics.


## 0.7.1-dev.31-fix3
- Reordered the customer detail workspace for a clearer information hierarchy.
- Contact and customer information now appear before the customer Hero Designer.
- Customer projects now follow the Hero Designer.
- The permanent customer portal URL card now appears below the project section.
# 0.7.1-dev.29-fix2 – Gallery activity, optimistic UI and metadata

- Immediate optimistic visual feedback for favorite and approved image actions.
- Aggregated unread photographer notifications per gallery under the Workspace bell.
- Notification items show current favorite, selected and comment totals and link to the gallery.
- Editable gallery name and customer-facing gallery description.
- Gallery description replaces customer name as descriptive copy in the public gallery hero.
- Customer portal gallery cards use gallery description when available.

# 0.7.1-dev.29-fix1 – Gallery Interaction UX refinement
- Reduces gallery hover actions to compact, neutral controls that do not dominate the photograph.
- Keeps action controls hidden until hover/focus on desktop; removes persistent text badges from image corners.
- Makes favorite state gallery-wide for the customer gallery and counts unique favorited images.
- Displays existing comments inside the image comment panel.
- Makes the summary pills clickable filters for All images, Favorites, Selected and Comments.
- Keeps filters synchronized immediately after customer interactions.

# 0.7.1-dev.29 – Gallery Interaction Foundation

- Added customer gallery hover actions for Favorite, Approved/Selected and Comment.
- Interaction state persists and remains visible on gallery images.
- Gallery counters now reflect stored favorites, selections and comments in customer and photographer views.
- Added anonymous visitor identity for per-customer favorite state without requiring login.
- Added tenant/gallery/image validation to public interaction writes.
- Fixed Photographer Workspace top-right profile image and profile dropdown with Min profil / Rediger profil.
- Preserved Hero Designer from dev.28.

# Aurora Fotoportal development changelog

## 0.7.1-dev.28
- Add Hero Designer Foundation for customer portals and individual galleries.
- Choose a gallery/customer hero from available customer images.
- Configure Small, Medium or Large hero height.
- Configure focal X/Y point for responsive cropping.
- Configure overlay color and opacity.
- Provide live Hero Designer preview in Photographer Workspace.
- Persist hero configuration per tenant/customer and per tenant/gallery with ownership validation.
- Apply saved hero design to public customer portals and galleries.

## 0.7.1-dev.27-fix1
- Show the saved photographer cover/hero image as a preview in Profile settings.
- Use the photographer cover image as a large photographic hero on the customer main portal.
- Use the first gallery image as the gallery hero, with photographer cover as fallback.
- Center photographer signature/contact information above the Aurora/9Ls1 footer.
- Show photographer profile image in the Photographer Workspace topbar when available.
- Add a profile dropdown with Min profil and Rediger profil shortcuts.

## 0.7.1-dev.27
- Refine photographer profile settings and customer-facing branding.
- Add edit mode, URL normalization, accent preview and stable customer portal links on customer profiles.
- Apply photographer identity to public galleries and add UI-ready favorites, selections and comment counters.

## 0.7.1-dev.26
- Add permanent customer portal URL with all customer projects and galleries.
- Add Send URL to customer with editable tenant email template.
- Add photographer portal branding: studio/name, contact details, logo, profile image, cover image and accent color.
- Keep direct gallery URLs and discreet Aurora/9Ls1 footer attribution.

## 0.7.1-dev.25-fix1
- Add previous/next arrows to the photographer gallery lightbox.
- Add previous/next arrows to the customer gallery lightbox.
- Support keyboard ArrowLeft and ArrowRight navigation in both gallery views.
- Fix customer gallery 404 after plugin replacement by using the registered query-var URL directly instead of depending on rewrite rules being flushed.

## 0.7.1-dev.25
- Add a native Gallery Detail view inside Photographer Workspace.
- Render all gallery images in a masonry layout that preserves each image's natural aspect ratio.
- Open gallery images in a large lightbox from the photographer view.
- Change the gallery row preview action to open the full native gallery instead of only the first image.
- Add a secure random customer-gallery URL for each gallery.
- Show and copy the customer gallery URL from the gallery detail page.
- Add a clean public read-only customer gallery using the same masonry presentation foundation.
- Keep public gallery lookup token-based and fetch images using the gallery's own tenant account id.

## 0.7.1-dev.24
- Add “Legg til flere bilder” to existing galleries.
- Append ZIP archives and/or multiple individual image files without deleting existing originals.
- Resolve filename collisions automatically and continue existing image sort order.
- Reuse Aurora preview/thumbnail processing and refresh gallery counts.
- Enforce tenant ownership and signed-contract gating in the append handler.

## 0.7.1-dev.23-fix1
- Fix mobile navigation controls being hidden by a later CSS rule.
- Ensure hamburger button, menu close button and overlay are visible on mobile widths.

## 0.7.1-dev.23
- Replace the collapsed mobile sidebar with a true off-canvas Aurora navigation.
- Add mobile hamburger button, close button, overlay, Escape handling and automatic close after navigation.
- Keep photographer account identity, module navigation and footer available inside the mobile menu.
- Make the Workspace top bar sticky on mobile and allow content to use the full viewport width.
- Improve mobile title bars, cards, forms, workflow navigation and customer/project actions.
- Make register tables horizontally scrollable instead of compressing their content into an unusable layout.

## 0.7.1-dev.22
- Turn the top-level Contracts menu into a tenant-scoped photographer-wide contract register.
- Add search, status/type filters and sortable Contract, Project, Customer, Type, Created and Status columns.
- Turn the top-level Documents menu into a tenant-scoped photographer-wide document register.
- Add search, document-type filtering and sortable Document, Project, Customer, Type and Created columns.
- Keep project-scoped Contract and Document workflow screens unchanged when opened from a project.
- Add direct actions from overview rows back into the correct project workflow step.

## 0.7.1-dev.21
- Add sortable Project, Customer, Type, Date and Status columns to the project register.
- Add colored project status/readiness treatment and a status legend on the project profile.
- Keep green reserved for delivered or tracked workflow readiness; payment is not yet tracked by Aurora.
- Allow existing contracts to be viewed in Workspace.
- Support Aurora digital-signing contracts and uploaded external contract files as separate contract modes.
- Add real file upload to Documents while retaining URL as an optional alternative.
- Surface generated Premium Proof PDFs directly on each gallery with a View PDF action.
- Hide the New Gallery form until + New Gallery is selected.
- Fix Delivery project-status updates so they return to the native Aurora Delivery view.

## 0.7.1-dev.20-fix2
- Run Fotoportal database upgrades automatically when the plugin version changes, so new customer billing columns are added on plugin replacement as well as fresh activation.
- Fix native customer Edit form placement so Rediger opens the form on the customer profile.
- Add customer registration date to the customer profile and customer register.
- Make registration date sortable.
- Show a sort indicator on every sortable customer-list heading, with active ascending/descending direction.

## 0.7.1-dev.20-fix1
- Fix customer register sorting on the actual table headings.
- Fix the new customer/project wizard so step navigation works again.
- Fix alternate billing fields so they are revealed when same-as-customer is disabled.
- Add native customer editing for contact, address, organization and billing data.
- Keep customer updates inside Photographer Workspace.

## 0.7.1-dev.20
- Sortable customer register columns.
- Structured address and billing information.
- Simplified customer type classification.
- Horizontal Aurora creation stepper.
- Optional project document upload.

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

## 0.7.1-dev.30 — Photographer Selection Workspace
- Added a dedicated **Bildevalg** workspace for photographers.
- Aggregates customer favorites, approved/selected images and comments across all tenant galleries.
- Added quick filters for All, Favorites, Selected and Comments.
- Added customer, project and gallery filters.
- Image cards show interaction state, latest comment and direct link back to the source gallery.
- Added lightbox access from the selection overview.
- Added a Bildevalg navigation entry with unread gallery-activity badge.
- All selection queries are scoped through the active photographer account via image/gallery/project/client joins.
## 0.7.1-dev.31 — Selection Workflow & Delivery Foundation

- Add explicit **Send bildevalg til fotograf** action in public customer galleries.
- Require at least one selected image before submission.
- Add gallery selection states: Pågår, Innsendt, Under behandling and Klar.
- Lock customer image interactions after a selection is submitted.
- Aggregate submitted-selection activity into the photographer notification bell.
- Add selection-status controls and status filtering to Photographer Selection Workspace.
- Show selection status on customer portal gallery cards.
- Add additive gallery schema fields and timestamps for the workflow.
- Add ADR-026 for the selection workflow architecture.


## 0.7.1-dev.31-fix1 — Project Workflow & Secure Access Foundation
- Added explicit project gates for project created, contract registered, contract signed, optional documents, gallery created and invoice paid.
- Added project-level payment state and a dedicated **Mark invoice as paid** delivery action.
- Customer portal delivery is released only when a signed contract, at least one gallery and paid invoice are present.
- Customer portals and gallery URLs now require an authenticated WordPress customer account; token URLs alone are no longer sufficient access.
- Gallery interaction AJAX endpoints now enforce the same authenticated customer/project access checks.
- When a project becomes delivery-ready, Aurora provisions a customer login when needed and sends the customer a link to the permanent portal.
- Simplified contracts to one Aurora Digital Signering (ADS) workflow.
- ADS now uses editable standard contract text and supports an optional uploaded attachment instead of a separate upload-only contract type.
- Digital signing continues to update the contract to Signed automatically after the customer completes the signing page.
