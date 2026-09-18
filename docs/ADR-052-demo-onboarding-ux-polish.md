# ADR-052 — DEMO onboarding UX polish

**Status:** Accepted  
**Date:** 10 September 2026  
**Release:** 0.7.1-dev.38-demo-onboarding-polish

## Context
Live acceptance testing of the photographer activation and six-step onboarding flow confirmed the core lifecycle while identifying several UX/data-continuity refinements.

## Decision
Keep the verified activation/password/routing flow unchanged. Refine onboarding by splitting the studio postal address into street, postal code and city; seed known Aurora Admin contact phone and website into the photographer profile; provide recommended branding image dimensions; use the established Aurora login background with a glass setup surface; surface the photographer logo in Workspace; and show the currently configured image filename/preview in profile settings before a replacement file is selected.

## Compatibility
The legacy `address` setting remains the street-address field. New `address_postal_code` and `address_city` keys extend the existing option without database migration. Existing accounts continue to work and can populate the new fields when edited.

## Invariants
- Public photographer routes remain `/fotograf/` and `/fotograf/portal/`.
- The onboarding wizard remains six steps.
- Browser file inputs are not programmatically prefilled; current media is presented separately because browsers prohibit pre-populating local file inputs.
