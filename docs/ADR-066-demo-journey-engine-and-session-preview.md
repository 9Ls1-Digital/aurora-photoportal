# ADR-066 – Demo Journey Engine and session-safe customer preview

**Status:** Accepted  
**Release:** 0.7.1-dev.53-demo-journey-engine  
**Date:** 11 September 2026

## Context
The earlier Trial guide mixed pre-provisioned demo content with the photographer's own training progress and sent the user out to ordinary workspace pages. The intended experience is a controlled exercise where the photographer can complete the full customer journey without manually inventing test data or logging out to view the customer side.

## Decision
Aurora Fotoportal gets a dedicated `NLS1_Aurora_Demo_Journey` component. It stores one isolated journey state per photographer account and drives a modal step-by-step exercise from Dashboard.

The journey covers Demo-kit download, prefilled customer creation, prefilled project creation, upload of a prefilled DEMO agreement, simulated contract e-mail, simulated customer signing, photo-shoot transition, real ZIP gallery upload through the production gallery pipeline, simulated gallery e-mail, simulated customer image selection, payment and final delivery.

Dashboard exposes **Continue demo**, **Restart demo** and **View DEMO customer portal**. Restart removes only objects created by the guided journey and never touches real customers/projects or the central Demo Content Pack.

## Customer preview context
Photographer preview no longer requires a second browser or incognito window. A signed WordPress nonce tied to the logged-in photographer and demo client grants a short-lived preview context. The photographer remains authenticated as photographer; no WordPress user switch occurs. Preview is only valid for `is_test=1` customers owned by the photographer's account. A persistent banner identifies the customer perspective and links back to Demo Guide.

## Gallery and delivery semantics
A signed contract is sufficient for customer gallery viewing. Payment continues to gate HQ/original download and final delivery. This separates proofing/selection from paid delivery, matching the guided customer journey.

## Security invariants
- Demo preview cannot target a real customer.
- Account ownership and photographer capability are checked before preview.
- No WordPress role or user identity is switched.
- Journey objects are tenant scoped and marked `is_test=1`.
- Existing nonce checks remain on gallery upload and all journey POST actions.
