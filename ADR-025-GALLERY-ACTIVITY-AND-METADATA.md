# ADR-025 – Gallery activity and editable metadata

## Decision
Customer gallery interactions are persisted immediately, while the customer UI uses optimistic feedback for favorite and approved actions. Photographer notifications are aggregated per gallery rather than emitted per image action.

Each gallery has editable `gallery_title` and `gallery_description`. The description is customer-facing and replaces the client name as gallery descriptive copy.

## Notification model
The latest favorite/approval/comment activity updates one unread activity item per gallery. The bell shows unread galleries and current aggregate counts. Opening an activity item marks that gallery item read and navigates to the gallery.

## Security
Gallery writes continue to resolve through the public gallery token and validate the image against gallery and account. Photographer metadata edits use authenticated admin-post actions, nonce checks, and tenant-scoped gallery lookup.
