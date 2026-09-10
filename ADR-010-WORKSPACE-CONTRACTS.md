# ADR-010 — Native Contracts in Photographer Workspace

Status: Accepted for development

dev.16 moves the project Contract step into the Photographer Workspace.

Contracts are loaded through the existing tenant-scoped project contract query.
Creation and status actions keep an explicit Workspace origin and redirect back
to the native contract step rather than the legacy WordPress admin.

Documents, Gallery and Delivery remain temporary legacy workflow steps and will
be migrated in subsequent releases.
