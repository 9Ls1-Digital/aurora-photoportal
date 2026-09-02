# ADR-026 — Selection Workflow

## Decision
Aurora Fotoportal introduces an explicit gallery selection workflow between customer curation and final delivery.

Each gallery stores a selection status: `open`, `submitted`, `processing`, or `ready`, together with timestamps for submission, processing and ready state.

## Customer workflow
- Customer can favorite, select and comment while the gallery is `open`.
- Customer explicitly submits the selection to the photographer.
- At least one selected image is required before submission.
- After submission, customer image interactions are locked to preserve the submitted selection.
- The customer portal and gallery expose the current selection state.

## Photographer workflow
- Submission creates/updates the existing aggregated gallery activity notification.
- The Photographer Selection Workspace exposes status filtering.
- Photographer can move the gallery through `submitted` → `processing` → `ready`.
- The status model is intentionally separated from final-download delivery, which remains a later phase.

## Security
All photographer-side status updates use tenant-scoped gallery lookup and nonce/capability checks. Public submission is accepted only through the existing unguessable gallery token and nonce-protected AJAX endpoint.
