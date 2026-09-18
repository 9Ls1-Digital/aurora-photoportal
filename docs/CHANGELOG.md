
## 0.7.1-dev.68 – Download tracking & project status
- HQ downloads are now served through an authenticated tracking endpoint.
- First HQ download updates the project to Levert and sends the photographer an e-mail.
- Delivery view shows real download state, count and last download timestamp instead of legacy `download_enabled`.
- Contract, Documents, Gallery and Digital Delivery use one authoritative gallery open/locked status banner.
- Fixes the Documents view showing “Galleri låst” after the gallery was already open.
- ADR-081 added.

## 0.7.1-dev.67 – Photographer customer preview + login toggle
- Added photographer-only **Åpne som kunde** preview without customer password or identity switching.
- Added short-lived signed preview context for customer portal and galleries.
- Added per-customer **Aktiver/Deaktiver innlogging** control.
- Disabled customers are rejected by Aurora Access/Fotoportal authorization while photographer preview still works.
- Replaced photographer-facing customer-login buttons with preview links; canonical customer login remains `/aurora/kunde/`.
- Added ADR-080.
## 0.7.1-dev.66 — Unified login routing — 2026-09-14
- Canonical shared photographer login: `/aurora/login/` → Mine apper.
- Canonical shared customer login: `/aurora/kunde/` → correct Fotoportal customer automatically.
- Canonical authenticated customer portal: `/aurora/kunde/portal/`.
- Legacy Fotoportal login routes redirect to the canonical Aurora routes.
- Tokenized customer portal URLs are no longer shown as the normal customer login address.


## dev.65 — Aurora Access customer-auth unification
- Customer authentication is now treated as owned by Aurora Access whenever the capsule is active.
- New customer password setup links use the Aurora Access reset route instead of the legacy query-string reset path.
- Fotoportal opts into Access auto-login after a successful customer password reset.
- Customer identity resolution can repair older users with missing Fotoportal account/client meta when one unique exact e-mail match exists.
- Eliminates the split reset/login ownership that caused consumed reset links and repeated login screens.
- Documented in ADR-078.
## 0.7.1-dev.64 – Watermark profile routing – 2026-09-14

- Vannmerke er nå tydelig plassert som en del av fotografens profil/branding.
- Profilmenyen har egen **Rediger vannmerke**-lenke.
- Profilkortet under Innstillinger har egen knapp til vannmerke-editoren.
- Dashboard- og Galleri-lenker for vannmerke går direkte til `#vannmerke`.
- Vannmerke-editoren er merket **Profil · Vannmerke**.

## 0.7.1-dev.63 – Customer reset/login stability – 2026-09-14
- Fixed the post-reset loop where “Gå til innlogging” could reload the already-consumed password-reset URL and show “Lenken er ugyldig eller utløpt”.
- Customer portal URL generation now accepts authoritative client/account context and no longer depends on the ambient photographer tenant during public authentication flows.
- Successful customer password reset now repairs authorization, signs the customer in and opens the customer portal directly; no second login is required.
- The legacy/fallback customer login form posts to the canonical `/fotograf/kunde/` route.
- Added ADR-076 and updated Blueprint.

## 0.7.1-dev.62 – Customer login context repair

- Fixed a public customer-portal context bug where the photographer workspace correctly showed a customer login as active, while the public “Glemt passord” route could report that no customer login existed.
- Public password recovery now resolves the customer e-mail and WordPress user using the customer’s explicit account_id instead of the active photographer/admin tenant context.
- Customer portal user self-healing now accepts the explicit account context on public routes, so legacy mappings can be repaired without crossing tenant boundaries.
- Existing photographer-workspace behavior remains unchanged: tenant-scoped calls still use the active tenant context by default.
- Added ADR-075.

## 0.7.1-dev.61 – Contract mail diagnostics + gate autosave
- Contract requirement now saves automatically when the checkbox changes; no separate save step can be missed.
- ADS contract mail now uses the same minimal mail header profile as the verified photographer invitation flow.
- Captures `wp_mail_failed` details and stores every ADS send attempt in project logs.
- Added visible E-posthistorikk on the photographer Contracts screen with recipient, time and success/failure information.
- Sent contracts can be sent again without creating a duplicate contract.
- Added explicit success/failure feedback after ADS send attempts.


## 0.7.1-dev.60 - Contract access, ADS mail and password eye
- Added show/hide password controls on photographer password activation.
- ADS "Marker sendt" now emails the signer and only marks the contract sent after successful mail dispatch.
- Added per-project contract requirement for gallery access, defaulting to required.
- Added photographer workspace control to change the requirement with audit logging.
- Gallery creation/customer visibility now follows the project gate; payment and HQ delivery remain separate.
- Clarified HQ upload/preview/watermark/original-delivery behavior.
- ADR-073 added.
## 0.7.1-dev.43 – Demo Trial distribution
- Automatisk Demo Content Pack ved opprettelse av ny Trial-konto.
- Manuell distribusjon til valgte eller alle eksisterende Trial-kontoer.
- Idempotent oppdatering med stabil demo-ID, uten duplikater.
- Fotograf kan fjerne demo-ressurser; vanlig push respekterer sletting.
- Aurora Admin kan eksplisitt gjenopprette slettet demo-innhold.
- ADR-056 dokumenterer distribusjonsmodellen og fase-2-kompatibilitet.



## 0.7.1-dev.56-account-user-cleanup – 2026-09-12

- Fotografkonto-sletting rydder nå automatisk dedikerte WordPress-brukere for både fotograf og tilknyttede kunder.
- Admin-/WooCommerce-brukere og brukere med annen Aurora-identitet bevares.
- Test-fotograf-nullstilling sletter genererte kundeinnlogginger, men bevarer den permanente Test-fotografen.
- Test Harness kan gjenbruke en eksisterende testbruker funnet på test-e-post dersom en eldre kjøring har etterlatt brukeren.
- Slettegrensesnittet er forenklet; separat avkryssing for WordPress-bruker er fjernet.
- ADR-069 dokumenterer bruker-livssyklusen.

## 0.7.1-dev.42 – Demo Content route fix
- Registrerer den skjulte WordPress-adminruten `aurora-platform-demo` både med og uten Aurora Core.
- Mapper ruten korrekt til seksjonen `demo`, slik at Aurora Admin kan åpne Demo-innhold uten «du har ikke tilgang»-feil.
- Ingen endring i rettighetsnivå: siden krever fortsatt `manage_options`.

## 0.7.1-dev.40 — Onboarding fullscreen fix
- Fixed WordPress top-level page shell so photographer onboarding fills the complete browser viewport.
- Applies the zero-margin/hidden-admin-shell rules to both `toplevel_page_aurora-photographer-workspace` and `admin_page_aurora-photographer-workspace`.
- Removes the white strip at the right edge without changing onboarding content or flow.

## 0.7.1-dev.39-onboarding-admin-ux — 10 September 2026
- Matched first-run onboarding to the same Aurora login background source used by Aurora Access/Fotograf login.
- Changed the onboarding panel to the same dark translucent glass treatment as the Aurora login card.
- Increased contrast for onboarding labels, help text, fields, stepper and success state on the dark glass surface.
- Promoted “Rediger profil, branding og e-post” to a clear primary CTA in Photographer Settings.
- Photographer/studio names in Aurora Admin now deep-link directly to the selected customer card instead of leaving the detail below the fold.
- Added selected customer-card target highlight and scroll margin for clearer navigation.


## 0.7.1-dev.38-demo-onboarding-polish — 10 September 2026

- Refined photographer first-run DEMO onboarding based on live acceptance testing.
- Studio address is now split into street address, postal code and city in onboarding and photographer settings.
- Aurora Admin contact phone and website are seeded into the photographer profile when the account is created; existing accounts also fall back to account contact data during onboarding.
- Added recommended image dimensions and formats for logo, profile image and hero/banner image.
- Onboarding now uses the established Aurora northern-lights login background with a glass-style setup card.
- Uploaded photographer logo is shown on the photographer account identity in Workspace.
- Photographer settings now show the currently selected logo/profile/hero filename and preview, while retaining a standard file picker for replacement.
- Preserved the verified activation, password, routing and 6-step onboarding flow from dev.37.

## 0.7.1-dev.36-auth-session-guard
- Enables Aurora Auth workspace session guard for Fotoportal.
- Uses an 8-hour sliding inactivity timeout for normal sessions.
- Uses a 14-day sliding inactivity timeout when “Husk meg” is selected.
- Enables safe wrong-context recovery between photographer and customer workspaces.
- Leaves the verified dev.35 clean portal routes and login flow unchanged.
- Adds ADR-050.

## 0.7.1-dev.32-account8-fix2 – Auth route hard fallback
- `/fotograf/` and `/fotograf/kunde/` now dispatch directly from the request path and no longer depend on WordPress rewrite rules being refreshed.
- Public auth routes explicitly suppress theme/WordPress 404 handling and render the Aurora auth surfaces immediately.
- Photographer registry no longer exposes a second admin/open action button. The studio name itself opens the internal kundekort; the only action button is the secure `Login URL`.
- Route generation remains dynamic from the current WordPress `home_url()`.

## 0.7.1-dev.32-account6
- Fixed Digital delivery readiness so active customer edit requests block release until a finished edited replacement is uploaded.
- Added clear green/red delivery readiness cards and server-side release enforcement.
- Added per-image customer/photographer comment threads with author labels and timestamps.
- Customers can delete their own comments; photographers can delete their own replies. Deleted comments disappear for both sides.
- Separated normal comments from edit requests with an explicit customer checkbox.
- Renamed the customer submit action to “Send valg til fotograf”.
- Added direct project/Bildevalg/Digital levering navigation from gallery and image workflows.
- Added schema fields for comment author, edit-request flag and soft deletion.

## 0.7.1-dev.32-account4 - Trial modules and photographer subscription overview
- Renamed Favoritter & kommentarer to Bildevalg with the subtitle Favoritter, kommentarer og redigeringsønsker.
- Renamed HQ-levering to Digital levering with a customer-facing description for secure high-resolution delivery.
- Standard Trial includes every production-ready Fotoportal add-on: Premium Proof / PDF, Kundeportal, Bildevalg and Digital levering.
- Nettbutikk and Customer App / PWA remain outside Standard Trial until production-ready.
- Added Photographer Workspace -> Innstillinger -> Abonnement og moduler overview for core, active, inactive and future modules.
- Existing authoritative module gating remains unchanged: Aurora Admin controls actual entitlement per account.
- Account schema upgraded to 0.8.0.

## 0.7.1-dev.32-account3 - Authoritative module gating
- Aurora Admin module settings are now the source of truth for photographer-account feature access.
- Core Fotoportal modules remain permanently enabled.
- Disabled add-ons are removed from Photographer Workspace navigation and blocked at server-side action endpoints.
- Kundeportal access, customer-login creation and portal e-mail actions are blocked when the Kundeportal add-on is disabled.
- Favoritter & kommentarer now controls Bildevalg, gallery interaction controls, selection submission/status and related notifications.
- Premium Proof / PDF generation and PDF actions are blocked when the add-on is disabled.
- HQ-levering controls the Leveranser workspace, delivery dashboard shortcuts and payment/delivery action.
- Direct URL/action attempts against disabled add-ons return a 403 module-not-enabled response instead of bypassing the UI.
- Schema upgraded to 0.7.0. Module migrations now preserve explicit add-on choices and never re-enable an add-on merely because a schema upgrade runs.

## 0.7.1-dev.32-account2 - Consent-based support access
- Added photographer-controlled support consent under Photographer Workspace -> Innstillinger.
- Aurora Admin can open a photographer Workspace only when the photographer has explicitly enabled support access.
- Support access uses a temporary 60-minute administrator session and never requires or exposes the photographer password.
- Added a persistent Supportmodus banner with explicit exit action while Aurora Admin is inside the photographer Workspace.
- Support context is tenant-locked to the approved photographer account and is automatically cleared when leaving the Workspace, when expired or when consent is revoked.
- Added platform support audit logging for consent, session start/end, denied attempts and revocation.
- Added support status, access action and recent support log to Aurora Admin photographer customer cards.
- Account schema upgraded to 0.6.0 with support-consent fields and a dedicated support log table.

## 0.7.1-dev.32-account1 - Photographer account management foundation
- Expanded Aurora photographer/studio accounts with organization number, phone, website, billing identity/address/email, internal admin notes and last-active timestamp.
- Added searchable and filterable Aurora Admin photographer customer registry.
- Added sorting by name, creation date, update date, last activity and status.
- Added a complete photographer/studio customer card with editable company, contact, billing and internal information.
- Added account status editing in the customer card.
- Photographer login now records last activity on the owning Aurora account.
- Preserved Trial, invitation and module-management controls on the photographer account detail view.
- Schema version advanced to 0.5.0 via dbDelta migration.

# Changelog

## 0.7.1-dev.31-fix38 - Central login branding and customer login backgrounds

- Photographer authentication now uses the Aurora Admin `Logo URL` as the authoritative Aurora logo, with the previous generated wordmark only as fallback.
- Aurora Admin Branding now has separate desktop and mobile background uploads for photo-customer login.
- Customer login backgrounds are platform-owned and fall back safely to photographer-login backgrounds until dedicated customer artwork is uploaded.
- Customer login/password screens now use a dedicated full-screen, responsive glass-style authentication shell.
- Customer-facing authentication prioritizes the photographer/studio logo; Aurora platform logo is used only as fallback.
- Customer authentication remains isolated from photographer and WordPress admin authentication.

## 0.7.1-dev.31-fix37 - Invitation email login link correction
- Removed the direct wp-admin Photographer Workspace URL from photographer invitation emails.
- Invitation emails now point to Aurora's dedicated Photographer Login surface.
- The Aurora Photographer Login URL includes the correct account_id and pre-fills the photographer's account email.
- Prevents logged-out photographers from being sent to the standard WordPress login merely by following the email's “Fotoportal” link.
- Activation confirmation also returns to Aurora Photographer Login using the account email.
- Clarified in Aurora Admin that photographer invitations use Aurora authentication, not wp-admin/wp-login.
- Direct Workspace URLs remain protected and are intended only for already-authenticated photographer sessions.

## 0.7.1-dev.31-fix36 - Login background management and account-email separation
- Added Aurora Admin controls for separate Photographer Login background images on desktop/PC and mobile.
- Desktop login background defaults to the supplied high-resolution Aurora landscape and always fills the viewport with CSS cover.
- Mobile can use its own portrait-oriented image; if unset it automatically falls back to the desktop image.
- Reworked Photographer authentication layout so Aurora branding is a separate block above the glass login card and cannot be covered by the form.
- Photographer account/login email is now read-only in onboarding and Photographer Settings.
- Added an optional separate customer-facing email address for Reply-To/contact use.
- If customer-facing email is empty, outgoing customer portal mail falls back to the account email.
- Account/login email remains controlled by Aurora Admin.
- Retains fix35 photographer permission correction for saving watermark and profile settings.

## 0.7.1-dev.31-fix35 - Onboarding guidance, photographer settings permission and Aurora auth design
- Added contextual help below every first-run onboarding field, explaining where each value is used.
- Every onboarding section now tells the photographer where the setting can be changed later.
- Clarified logo, profile image, hero/banner, profile color, watermark and customer-email usage.
- Fixed “Mangler tilgang” when an Aurora Photographer saves portal/watermark settings after onboarding.
- Portal settings save handler now accepts the Aurora Photographer capability and resolves the bound account_id safely.
- Corrected watermark upload guidance to PNG/WebP/JPEG, matching the current image-processing implementation.
- Added a dedicated high-resolution Aurora login background derived from the existing Aurora platform visual.
- Redesigned Photographer login, set-password and activation screens with the Aurora full-screen background and dark glass panel.
- No Microsoft login is included.

## 0.7.1-dev.31-fix34 - Clean first-run onboarding
- Onboarding now uses a dedicated distraction-free canvas without the Photographer Workspace sidebar or top bar.
- Removed the accidental Dashboard/module placeholder content that rendered below the onboarding wizard.
- Added consistent Aurora form styling for text fields, textarea, select, file upload, color and number controls.
- Improved spacing and responsive layout for all six onboarding steps.
- Photographer enters the full Workspace navigation only after onboarding is completed.

## 0.7.1-dev.31-fix33 - Photographer Workspace vs WooCommerce My Account
- Fixed successful photographer login being redirected to WooCommerce My Account instead of Aurora Photographer Workspace.
- Aurora photographer owners are now exempt from WooCommerce's prevent-admin-access redirect.
- Existing linked users that previously had a WooCommerce/customer role are normalized to the Aurora Photographer role when an invitation is sent or when they successfully log in.
- Privileged WordPress administrators and WooCommerce managers are never role-rewritten.
- Aurora Photographer capability and account_id binding remain the authorization source.
- Photographer users no longer receive the normal WordPress admin bar on frontend pages.
- Authentication separation between Admin, Photographer and Photo Client remains intact.

## 0.7.1-dev.31-fix32 - Dedicated photographer authentication
- Photographer invitation links no longer use wp-login.php for password creation.
- Added a dedicated Aurora photographer password route with WordPress reset-key validation underneath.
- Added a dedicated Aurora photographer login route, separate from both WordPress Admin and the photo-client customer portal.
- Password reset validates account_id, Aurora Photographer role/capability and the WordPress reset key before allowing a password change.
- After password activation the photographer is sent to Aurora Fotografinnlogging, then to Photographer Workspace.
- Incomplete onboarding is still enforced by Photographer Workspace and opens automatically after successful photographer login.
- Resending an invitation generates a fresh WordPress reset key but always points to the Aurora photographer password UI.
- Locks the permanent auth architecture: Admin auth, Photographer auth and Customer auth are separate routing contexts.

## 0.7.1-dev.31-fix31 - WordPress/admin login isolation
- Fixed Aurora customer authentication context hijacking the normal WordPress login page.
- Generic wp-login.php requests are now always left to WordPress unless explicitly marked as Aurora customer authentication.
- Stale Aurora customer-context cookies are cleared when a normal WordPress/admin/photographer login begins.
- Administrator login can no longer be redirected into the Aurora customer login gate because of a previously visited customer portal.
- Photographer authentication remains separate from customer authentication.
- Aurora customer portal login and password recovery continue to use the dedicated frontend portal routes.

## 0.7.1-dev.31-fix30 - Core functions vs add-on modules
- Split the Fotoportal module catalogue into fixed core functions and optional add-on modules.
- Kunder, Prosjekter, Kontrakter, Dokumenter and Gallerier are now always included with Aurora Fotoportal.
- Core functions can no longer be disabled per photographer account.
- Trial accounts receive the mature add-on set by default: Premium Proof / PDF, Kundeportal, Favoritter & kommentarer and HQ-levering.
- Nettbutikk and Customer App / PWA remain visible as future add-ons but are not enabled by default in Trial.
- Existing Trial accounts are normalized to the new standard Trial module set during schema migration.
- Account Admin now presents “Inkludert i Fotoportal” separately from “Tilleggsmoduler”.
- The module catalogue page now documents the same Core/Add-on distinction.
- Prepared the add-on layer for later package/entitlement control by Aurora License.

## 0.7.1-dev.31-fix29 - Separate photographer and customer authentication
- Fixed photographer invitation/password reset being hijacked by an existing customer-portal authentication context.
- Photographer invitation URLs now carry an explicit Aurora photographer-auth marker and Workspace redirect.
- Customer authentication interceptor now detects photographer-owner reset requests and leaves them in the photographer/WordPress authentication flow.
- A stale customer authentication context cookie is cleared when photographer authentication starts.
- Photographer login remains linked to the correct account_id and redirects to Photographer Workspace/onboarding.
- Customer portal authorization remains unchanged and still requires client-specific authorization.

## 0.7.1-dev.31-fix28 - Invitation delivery fix + resend
- Fixed a regression in fix27 where photographer invitation code was inserted in the install/seed routine instead of the new-account handler.
- New photographer accounts now actually create/link the photographer WordPress user and attempt the welcome invitation immediately.
- Added reusable invitation service with a fresh secure WordPress set-password key on every send.
- Added “Send invitasjon” / “Send invitasjon på nytt” in Aurora Admin on each photographer account.
- Existing trial accounts created by fix27 can now be repaired by resending: the photographer user is created/linked automatically if missing.
- Added clear admin success/error notices and wp_mail_failed diagnostics when WordPress rejects the mail.
- Stores invitation-sent timestamp on the photographer user.
- Email transport remains WordPress wp_mail(), allowing WP Mail SMTP/Brevo or the site's configured mail provider to handle delivery.

## 0.7.1-dev.31-fix27 - First-login onboarding
- Photographer account creation now creates or links a WordPress photographer user.
- Sends a welcome email with trial end date and secure WordPress set-password link.
- Links photographer user to the correct Aurora Fotoportal account.
- Added photographer-specific tenant context.
- Photographer login redirects to Photographer Workspace.
- Added mandatory six-step first-login onboarding: Studio, Contact, Branding, Watermark, Customer Portal, Finish.
- Each onboarding step saves to the existing tenant portal/settings model.
- Onboarding progress is persisted and resumes at the last unfinished step.
- Completing onboarding opens the normal dashboard with a first-time welcome message.
- Added dedicated Aurora Photographer role/capability.
- Payment and license purchase conversion remain outside this phase.

## 0.7.1-dev.31-fix26 - Photographer Onboarding Trial foundation
- New photographer accounts now start in Trial state instead of Active.
- Added configurable default demo length (30 days by default).
- Added onboarding/trial timestamps to photographer accounts.
- Added effective trial-state calculation and remaining-days display.
- Added admin controls to extend demo by 7, 14 or 30 days.
- Added admin action to expire a demo immediately for testing.
- Synchronized the existing Fotoportal account-license foundation with trial dates/status.
- Prepared the account model for later Aurora License entitlement synchronization.
- No payment flow or public self-registration is included in this phase.

## 0.7.1-dev.31-fix25
- Integrates Fotoportal explicitly with Aurora Core.
- Stops creating a duplicate top-level Aurora menu when Core is active.
- Keeps Fotoportal product routes hidden and product-specific.
- Registers Fotoportal card, admin URL and quick links with Core.

# dev.31-fix24 – Global Aurora Product Navigation
- Changed WordPress Aurora submenu to show Control Center and installed Aurora products only.
- Hid Fotoportal-internal sections from the global WordPress submenu.
- Kept Fotoportal internal navigation inside Aurora Fotoportal Admin.
- Added Aurora License as a peer product menu item when active.
- Added reusable product quick_links metadata.
- Added split Open button with hover/focus dropdown shortcuts on product cards.
- Updated Blueprint documentation.

# dev.31-fix23 – Separate Aurora Product Admin Shells
- Removed Fotoportal admin navigation from Aurora Control Center.
- Removed Fotoportal header/navigation from Aurora License administration.
- Added dedicated Aurora Control Center header.
- Kept Fotoportal-specific navigation only inside Aurora Fotoportal Admin.
- Removed License from the Fotoportal admin navigation.
- Added back-navigation from product admins to Aurora Control Center.
- Updated Blueprint documentation.

# dev.31-fix22 – Aurora Product Admin Hierarchy
- Cleaned the shared Aurora Control Center dashboard.
- Moved Fotoportal-specific account/tenant content out of the platform dashboard.
- Added dedicated Aurora Fotoportal Admin product layer.
- Changed Aurora Fotoportal product card to open Fotoportal Admin, not Photographer Workspace.
- Locked navigation hierarchy: Control Center > Fotoportal Admin > Photographer Account > Workspace.
- Updated Blueprint documentation.

# dev.31-fix21 – Aurora Platform Control Center
- Added installed Aurora product/plugin dashboard.
- Added direct product/workspace navigation.
- Added shared product registry/filter.
- Embedded Aurora License administration under Aurora > Lisenser when available.
- Deprecated the legacy Fotoportal license UI as primary license management.
- Updated Blueprint.

# Changelog

## 0.7.1-dev.31-fix12
- Reparerer autorisasjonskoblingen mellom WordPress-bruker, Aurora-kunde og fotografkonto ved kundeinnlogging.
- Autorisasjon bruker nå lagret kunde/account-metadata først og reparerer eldre/manglende metadata når brukerens e-post matcher kundens primærkontakt.
- Offentlig portaloppslag bruker kundens faktiske `account_id` ved kontaktkontroll, slik at tenant-kontekst ikke gir falsk «ingen tilgang»-feil.
- Setter eksplisitt innlogget bruker etter `wp_signon()` før Aurora utfører portalautorisasjon.

## 0.7.1-dev.31-fix9
- Harden Aurora customer authentication redirects.
- Keep customer login, password recovery and password reset inside the Aurora-branded flow.
- Intercept WordPress login/reset endpoints when an Aurora customer context is active.
- Return Aurora customers to their private portal after logout and authentication.
- Preserve normal WordPress/WooCommerce authentication for unrelated users and administrators.

## 0.7.1-dev.31-fix12
- Reworked customer portal login gate into a deterministic auth decision.
- Clears unrelated WordPress sessions before rendering Aurora customer login.
- Explicitly establishes current user/auth cookie after successful customer login.
- Redirects successful login to the canonical customer portal URL.
- Removes the blank/intermediate customer-login state seen after authentication.

## 0.7.1-dev.32-account8-fix1 – Public Fotoportal auth routes
- Stable `/fotograf/` photographer login route.
- Stable `/fotograf/kunde/` universal customer login route.
- Admin photographer registry separates public login from internal customer-card management.
- Route definitions are installation-relative and ready for future extraction into Aurora Auth Capsule.

## Auth takeover checkpoint
- Aurora Auth can now own Fotoportal's two public authentication entry routes while Fotoportal retains fallback handlers.

## 0.7.1-dev.37-demo-phase1
DEMO lifecycle phase 1: branded photographer activation, direct continuation to six-step onboarding, and safe permanent account/tenant/upload cleanup for test and demo photographers. Blueprint and ADR-051 updated.

### 0.7.1-dev.53-demo-journey-engine
Introduces the guided Aurora Demo Journey Engine, same-session customer preview, prefilled Demo exercise data, real gallery ZIP processing in the training flow, restart/continue controls and a complete simulated customer journey through payment and delivery.

## 0.7.1-dev.54-demo-journey-polish — 2026-09-12
- Demo Journey delivery now trains the real Bruksrett and invoice-paid actions instead of auto-completing them.
- Fixed photographer payment action access that returned “Mangler tilgang”.
- Photographer receives e-mail when a customer signs, for both ADS and Demo Journey signing.
- ADS default wording now says signature metadata is registered “i fotoportalen”.
- Demo-kit v1.5 clearly separates the outer ZIP to unpack from the inner gallery ZIP `DEMO-BILDER_IKKE PAKK UT DENNE.zip` that must remain zipped.
- Added direct project-gallery/customer-portal navigation and secure permanent project deletion with full project-scoped cleanup.
- Aurora-styled delivery controls and Demo helper added.

## 0.7.1-dev.55-test-photographer-harness – 2026-09-12
- Permanent Test-fotograf for repeated end-to-end testing without recreating a photographer account.
- One-click reset for onboarding, Demo Journey or all test content.
- Onboarding fixtures include prefilled studio/contact/address data plus logo, profile, hero and watermark assets.
- Normal account deletion is blocked for the Test-fotograf; only explicit test-reset actions can clear its tenant data.

## 0.7.1-dev.57-demo-journey-reset-flow – 2026-09-14
- Test-fotograf now follows the real invitation → password → six-step onboarding path.
- Demo-kit is only marked complete after an actual download; provisioning no longer advances the Journey.
- Restart now returns to Demo-kit step 1 and clears stale download progress.
- `Lær Fotoportal` is shown first on Dashboard for Trial and Test-fotograf accounts.
- Test Harness cleanup now removes the correct Demo Content Pack state.
## 0.7.1-dev.59-demo-optin-hq-hero-access – 2026-09-14
- Demo Journey is now optional per photographer account. Aurora Admin chooses “Ta med guidet DEMO Journey” when creating a photographer and can enable/pause it later on the photographer account card.
- Trial remains independent from Demo Journey; disabling the Journey hides guided training without changing the Trial period.
- Demo Content Pack is resources-only. It no longer creates a second seeded demo customer/project/gallery; legacy seeded demo entities are removed account-scoped during migration, while Journey-created entities remain separate.
- Gallery guidance now explicitly asks for HIGH QUALITY originals. Aurora retains the HQ originals, automatically creates preview/thumbnail derivatives, applies watermark only to customer-facing previews and delivers approved HQ downloads without watermark.
- Added visible “Faktura / betaling” shortcuts from project/gallery context to Digital levering.
- Fixed photographer workspace access for saving gallery HERO, gallery details, customer HERO and sending customer-portal e-mail by requiring an explicit workspace marker while retaining nonce and tenant scoping.
- Added ADR-072 and updated the Aurora Fotoportal Blueprint.

