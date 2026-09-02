# Changelog

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
