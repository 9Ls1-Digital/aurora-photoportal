# ADR-007 — Tenant Enforcement

Status: Accepted for development

dev.13 moves Aurora from tenant metadata to enforced account scoping in the
core Fotoportal admin data path.

All photographer-owned domain reads must include the current `account_id`.
New domain records are stamped with the current account. Existing legacy rows
are migrated to the seeded default photographer account.

The public contract signing token lookup remains token-based: possession of the
high-entropy signing token is the external access credential. Admin-side
contract access remains account-scoped.

This is a security boundary. New module code must use the centralized Aurora
tenant context and may not perform unscoped domain-table reads.
