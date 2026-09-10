# ADR-039 – Image dialogue and delivery readiness

## Decision
Image comments are a shared per-image dialogue between customer and photographer. Comments have an author role and may optionally be marked as an edit request. Only active customer comments explicitly marked as edit requests participate in delivery readiness.

Users may soft-delete only their own comments. Deleted comments disappear from both customer and photographer views while retaining an audit-safe database marker.

Digital delivery is blocked until contract, payment and gallery requirements are satisfied and every active edit request has a finished edited replacement image. The same rule is enforced in UI and server-side release handling.

Context navigation keeps project, gallery, Bildevalg and Digital levering connected so photographers do not have to return through global lists.
