# ADR-053 — Onboarding glass and account-detail navigation

**Status:** Accepted  
**Release:** 0.7.1-dev.39-onboarding-admin-ux  
**Date:** 10 September 2026

## Context
The first-run photographer onboarding must feel like a continuous Aurora Access experience. The previous build used a bundled background and a bright translucent card, which visually diverged from the established Aurora login surface. In Aurora Admin, selecting a photographer account also rendered the account card below the registry without moving the viewport, making the result easy to miss.

## Decision
The onboarding surface uses the configured photographer login background from platform branding and the same dark glass language as Aurora Access: translucent dark panel, light typography, blurred backdrop and dark translucent form fields. The existing onboarding workflow and save handlers remain unchanged.

The Photographer Settings profile editor is exposed as a primary call-to-action rather than a secondary text-like control.

Aurora Admin account links append the stable `#aurora-account-detail` fragment. The account detail card owns that ID and uses scroll margin plus a subtle target highlight, so selecting a studio immediately reveals the requested customer card without JavaScript or duplicate UI.

## Consequences
- Login and first-run onboarding now share a coherent Aurora visual identity.
- Configured platform branding remains authoritative; no duplicate onboarding background needs separate maintenance.
- Account-card navigation becomes explicit and keyboard/browser compatible.
- No changes are made to tenant data, onboarding persistence, authentication or routing.
