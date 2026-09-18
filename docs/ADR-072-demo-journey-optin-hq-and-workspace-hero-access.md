# ADR-072 – Demo Journey opt-in, HQ image pipeline and workspace HERO access

**Status:** Accepted  
**Release:** 0.7.1-dev.59-demo-optin-hq-hero-access  
**Date:** 2026-09-14

## Context
Guided Demo Journey had become tightly coupled to Trial accounts and central Demo Content Pack provisioning. This caused duplicate demo customers/galleries and made it difficult to pause the training flow while normal Fotoportal functionality was being stabilized. Photographer workspace actions for HERO/gallery settings also still contained admin-only capability checks.

## Decision
Demo Journey is an explicit per-account opt-in, independent of Trial. New photographer creation has an unchecked “Ta med guidet DEMO Journey” option and the account card can later enable or pause the Journey. Central Demo Content Pack provisioning supplies files/resources only; only the Journey may create Journey demo customer/project/gallery entities. A migration removes only legacy materialized pack entities marked as test and recorded in the old Demo Pack state.

The gallery upload contract is clarified: photographers upload final HIGH QUALITY originals. Aurora stores those originals, creates preview and thumbnail derivatives, applies watermark to customer-facing preview views only, and releases the HQ originals without watermark when delivery conditions are met.

Photographer workspace forms that save gallery HERO, gallery details, customer HERO or send the customer portal use an explicit `aurora_workspace=1` marker. The matching handlers permit `aurora_fotoportal_photographer` only for this workspace request and still enforce nonce and tenant-scoped object lookup.

## Consequences
- Demo training can be paused without affecting the photographer Trial/license.
- Normal photographers no longer receive duplicate seeded demo customers/galleries.
- Existing legacy seed entities are cleaned up without deleting separate Journey data.
- The source/derivative image model is clearer to photographers and supports future external storage providers.
- Workspace HERO and related gallery/customer actions no longer fail with “Mangler tilgang” when used by the authorized photographer.
