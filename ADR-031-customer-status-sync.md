# ADR-031 – Customer status sync

Customer-facing project status must be derived from account-scoped public project data, not Photographer Workspace tenant context. The customer portal therefore uses an account-aware delivery-state resolver keyed by project_id and account_id. This prevents false “Venter” states for signed, paid and gallery-ready projects.

The permanent customer portal URL is presented as part of the Customer Portal/Hero Designer configuration because both settings belong to the same customer-facing surface.
