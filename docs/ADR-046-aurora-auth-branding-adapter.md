# ADR-046 — Aurora Auth branding adapter

## Status
Accepted for `0.7.1-dev.32-auth-branding1`.

## Decision
Fotoportal provides its existing login branding to Aurora Auth through `branding_callback`. Aurora Auth owns the login shell and authentication mechanics; Fotoportal remains the source of product-specific branding values.

For photographer login, Fotoportal supplies the configured photographer desktop/mobile backgrounds. For customer login, the configured customer backgrounds are used, with the existing fallback to photographer backgrounds. The configured Aurora logo and accent color are also supplied.

## Consequence
Existing Aurora Admin branding settings continue to be the single source of truth. No background files or Fotoportal-specific option names are copied into Aurora Auth. This establishes the reusable branding contract for future Aurora products and later tenant-level overrides.
