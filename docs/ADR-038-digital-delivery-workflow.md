# ADR-038 — Digital delivery workflow

## Decision
Aurora separates proof/gallery access from final digital delivery. Customer portal access can exist while image selection and editing are in progress. Final high-resolution delivery requires an explicit photographer release.

## Rules
- Bildevalg owns favorites, customer selection, comments and edit requests.
- Digital levering owns the final delivery set and release state.
- The photographer chooses `all` or `selected` delivery mode per project.
- Any image with an edit request must have a finished edited replacement before final release.
- A finished edited replacement supersedes the proof/original for final delivery.
- Final release requires the existing mandatory project gates plus zero open edit requests.
- Release generates downloadable delivery archives and exposes final files in the authenticated customer portal.

## Security boundary
This release uses the existing Fotoportal media storage model. Private/encrypted media storage and signed delivery URLs are a separate planned Security & Private Media Architecture initiative.
