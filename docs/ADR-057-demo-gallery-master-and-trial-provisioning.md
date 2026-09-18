# ADR-057 – Demo gallery master and Trial provisioning

**Status:** Accepted  
**Date:** 2026-09-10  
**Version:** 0.7.1-dev.44-demo-gallery-distribution

## Context
The central Demo Content Manager must be maintainable by Aurora Admin, while Trial photographers must experience demo images as real Fotoportal gallery content rather than external resource links. Demo images also need the same derivative folders and processing behavior as normal gallery uploads.

## Decision
Aurora maintains a central Demo Gallery Master below `wp-content/uploads/9ls1-fotoportal/demo-content/gallery/` with `original/`, `preview/`, `thumbnails/`, `zip/` and `export/` subfolders. Built-in demo images are copied into this master automatically and newly uploaded demo images are stored there directly.

When a Demo Content Pack is assigned to a Trial account, Aurora provisions an account-scoped test customer, test project and test gallery (`is_test=1`). Active demo originals are copied into the account's normal Fotoportal project/gallery filesystem tree. Fotoportal then generates the normal preview and thumbnail derivatives, including the photographer's configured preview watermark behavior.

Each central demo item has a stable ID. Per-account state stores the demo customer/project/gallery IDs and the mapping between demo item IDs and generated gallery image rows. This makes repeated push operations idempotent and enables explicit restoration after removal.

## Consequences
- Aurora Admin can replace and expand Demo Gallery content without a new photographer account.
- Trial users see a genuine project/gallery experience and can inspect the same folder/derivative model used in production.
- Account deletion removes the tenant copy but does not remove Aurora's central Demo Gallery Master.
- Normal push does not intentionally resurrect mapped gallery items that were removed from the Trial copy; explicit restore is the recovery action.
- All provisioned customer/project/gallery/image rows are account-scoped and marked as test data, preserving the later Phase 2 keep/remove migration path.
