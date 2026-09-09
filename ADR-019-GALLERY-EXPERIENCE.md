# ADR-019 — Gallery Experience

Status: Accepted for development

Aurora uses one visual gallery foundation for both the photographer workspace and the customer's gallery experience. Images are rendered in a masonry layout without forcing them into fixed aspect-ratio cards, so portrait, landscape and other image formats retain their natural proportions.

Each gallery receives a cryptographically random public sharing token. The photographer sees a copyable customer-gallery URL from the native gallery detail page. Public gallery access is token-based and image retrieval is scoped using the gallery's stored account_id rather than the current admin tenant context.

The initial customer gallery is intentionally read-only. Favorites, comments, approvals, download controls and other customer-portal functions remain later work on top of this shared gallery foundation.
