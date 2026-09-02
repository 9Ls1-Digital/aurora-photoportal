# ADR-024 – Customer Access & Edit Requests

## Decision
Customer portals and galleries require authentication with the customer e-mail account. A portal/gallery token is routing information, not sufficient authorization. Photographer/admin sessions are not treated as customer authorization.

The former selection submission is redefined as an edit request. Favorites and selected images remain mutable customer choices; submitting an edit request notifies the photographer and starts the request status workflow.

Photographer Selection Workspace status filtering must include the status selector in client-side filtering.
