# ADR-055 – Demo Content admin route

**Status:** Accepted  
**Version:** 0.7.1-dev.42

## Context
Demo Content Manager generated URLs using the slug `aurora-platform-demo`, but dev.41 did not register that hidden WordPress admin page. WordPress therefore rejected navigation before Fotoportal could render the section.

## Decision
Register `aurora-platform-demo` as a hidden submenu page with `manage_options` in both Aurora Core and standalone menu modes, and map the page slug to the `demo` section in the shared renderer.

## Consequences
Aurora Admin can open Demo-innhold from the Fotoportal navigation while the page remains absent from the native WordPress submenu and retains administrator-only access.
