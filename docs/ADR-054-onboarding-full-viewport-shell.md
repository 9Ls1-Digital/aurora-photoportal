# ADR-054 — Onboarding full viewport shell

## Decision
The photographer onboarding surface must occupy the complete browser viewport regardless of whether WordPress assigns the workspace the `toplevel_page_aurora-photographer-workspace` or `admin_page_aurora-photographer-workspace` body class.

## Reason
WordPress keeps its admin-menu offset on top-level pages unless it is explicitly reset. This produced a visible white strip on the right side of the Aurora onboarding background.

## Implementation
Both WordPress page contexts now hide the native admin shell and reset `#wpcontent`, `#wpbody`, and `#wpbody-content` to full width. The onboarding workspace and main canvas are forced to `100vw` / `100vh`.

## Scope
Visual shell only. Authentication, onboarding steps, saving, routing, and tenant data are unchanged.
