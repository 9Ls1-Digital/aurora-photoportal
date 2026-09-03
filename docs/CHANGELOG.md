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
