# ADR-016 — Contract and Document overviews

## Decision
Top-level Contracts and Documents in Photographer Workspace are account-wide registers, while project workflow links remain project-scoped management views.

## Rationale
Photographers need operational overview and search across projects without losing the project workflow as the authoritative editing context. All overview queries remain tenant-scoped by `account_id`.

## UX
Both registers support search, filtering and sortable columns, expose project/customer context, and route row actions into the relevant project workflow step.
