# ADR-012 — Native Galleries in Photographer Workspace

Status: Accepted for development

dev.18 moves gallery administration into the native Photographer Workspace.

The existing tenant-scoped gallery/image data model and ZIP processing pipeline
remain authoritative. Gallery creation is project-scoped and requires a signed
contract. This rule is enforced both in the Workspace presentation layer and in
the backend upload handler.

Photographer-wide gallery browsing is allowed within the active tenant only.
Project-scoped actions return to the project's Gallery step.

Delivery remains the final workflow area still using the legacy bridge.
