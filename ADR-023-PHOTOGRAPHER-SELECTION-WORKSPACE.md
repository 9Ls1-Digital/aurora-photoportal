# ADR-023 — Photographer Selection Workspace

## Decision
Customer gallery interactions are surfaced in a permanent tenant-scoped Photographer Workspace view named **Bildevalg**. The notification bell remains the transient "what is new" surface; Bildevalg is the durable operational overview.

## Data model
The workspace reads the existing favorites, image selection and image comments data. No duplicate interaction state is introduced. Tenant isolation is enforced by anchoring the query to images.account_id and joining galleries, projects and clients on the same account.

## UX
Photographers can filter across interaction type, customer, project and gallery, inspect the latest image comment, open the image in a lightbox and navigate directly to the source gallery.
