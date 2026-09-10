# ADR-005 — Photographer Workspace

Status: Accepted for development

## Decision

Photographers work in a dedicated Aurora Fotoportal workspace rather than the
normal WordPress administration interface.

The workspace has:
- Aurora sidebar/navigation
- photographer account identity
- module-entitlement-driven navigation
- dashboard
- account/settings view
- dedicated module surfaces

## dev.11 limitation

Until tenant ownership is added to existing Fotoportal domain records, dev.11
uses the first/default Photographer Account for development. Dashboard business
metrics intentionally show placeholders rather than mixing unscoped legacy data.

Existing Fotoportal screens remain reachable only as temporary development
bridges while each module is rebuilt inside the new workspace.
