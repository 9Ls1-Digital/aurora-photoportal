# ADR-008 — Native Customers in Photographer Workspace

Status: Accepted for development

dev.14 establishes the first real Fotoportal business module inside the
Photographer Workspace.

Customers are no longer a placeholder or legacy-admin bridge. Customer lists,
search/filter, customer profiles and the new customer/project wizard are
rendered in the Aurora workspace and use tenant-scoped Fotoportal data.

The existing domain tables remain unchanged apart from the tenant ownership
foundation already introduced. Projects and other modules remain temporary
legacy bridges until their native workspace views are implemented.

New customer/project creation carries an explicit Workspace origin flag so the
existing backend handler can safely redirect back into Aurora rather than the
legacy WordPress admin.
