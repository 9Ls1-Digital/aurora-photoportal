# ADR-082 – Customer gallery status and edit-request workflow

## Decision
Customer gallery selections are persisted automatically and no longer require a manual “Send valg til fotograf” action. Gallery cards expose delivery readiness. The customer Status view is a visual four-step journey. Photographer Bildevalg shows the complete comment thread for each image and exposes the edited-image upload directly on images with edit requests. A completed edited upload is shown as an explicit completed checkbox state.

## Rationale
The UI must reflect the automatic interaction model and give both customer and photographer an authoritative, understandable workflow state without duplicate confirmation actions.
