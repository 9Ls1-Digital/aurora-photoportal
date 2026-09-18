# ADR-061 – Photographer customer/project creation access

**Status:** Accepted  
**Release:** 0.7.1-dev.48-customer-create-access-fix  
**Date:** 2026-09-11

## Context
Fotograf-workspace bruker WordPress `admin-post.php` som transport for lagring av ny kunde og prosjekt. Fotografrollen har med vilje ikke `manage_options`, men `handle_save_client_project()` krevde fortsatt denne administrator-capabilityen. Resultatet var «Mangler tilgang» på siste steg i den offentlige Aurora-workspacen.

## Decision
Tillat `9ls1_fotoportal_save_client_project` når requesten eksplisitt kommer fra Aurora photographer workspace (`aurora_workspace=1`) og innlogget bruker har `aurora_fotoportal_photographer`. Vanlige/legacy-kall uten workspace-flagget krever fortsatt `manage_options`. Eksisterende nonce-validering og tenant/account-scope beholdes.

## Consequences
- Fotograf kan fullføre Ny kunde / prosjekt uten tilgang til WordPress-admin.
- Administratorgrensen svekkes ikke for legacy/admin-skjemaer.
- Redirect etter lagring går fortsatt til den nye kundens Aurora-profil.
