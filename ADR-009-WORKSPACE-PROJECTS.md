# ADR-009 — Native Projects in Photographer Workspace

Status: Accepted for development

dev.15 moves project listing and project detail into the Photographer Workspace.

Project data is read through the tenant-scoped Fotoportal admin data layer.
Project-to-customer joins require matching account ownership. The new project
profile is the first native workspace view to expose the intended five-step
workflow:

1. Project
2. Contract
3. Documents
4. Gallery
5. Delivery

Contract, document and gallery modules are still temporary bridges in this
release. Their native workspace implementations will replace those links in
subsequent releases.
