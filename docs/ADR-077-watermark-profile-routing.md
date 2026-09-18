# ADR-077 – Watermark profile routing

Status: Accepted – dev.64
Date: 2026-09-14

## Context
Photographer watermark settings are part of the photographer's profile/branding experience, but entry points from Dashboard and Galleries previously linked only to the generic Settings page. This made the destination ambiguous and placed the watermark editor too far from the profile context.

## Decision
Watermark editing is treated as a profile/branding function. The Photographer Workspace profile menu and Settings profile card expose a dedicated **Rediger vannmerke** action. All existing watermark edit links now deep-link directly to the watermark editor using the `#vannmerke` anchor.

The watermark editor is explicitly labelled **Profil · Vannmerke** and remains the authoritative editor for watermark file, position, size and opacity.

## Consequences
- Photographer can reach watermark settings directly from the profile menu.
- Dashboard and Gallery watermark edit actions land on the correct editor instead of the top of Settings.
- No storage or image-processing behavior changes in this release.
