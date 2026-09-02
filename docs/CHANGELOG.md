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
