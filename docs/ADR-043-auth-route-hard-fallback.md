# ADR-043 – Public auth route hard fallback

## Status
Accepted – 0.7.1-dev.32-account8-fix2

## Decision
Aurora Fotoportal's public authentication entry points `/fotograf/` and `/fotograf/kunde/` must not rely solely on WordPress rewrite rules. The frontend dispatcher recognizes the request path directly, suppresses 404 handling, sets the correct authentication context and renders the Aurora login surface.

## Rationale
Plugin updates, host caching and delayed rewrite-rule refreshes can otherwise produce a theme 404 even though the plugin is active. Authentication entry points are security- and UX-critical and must work immediately after every installation/update.

## Admin navigation
The photographer registry exposes one public action: `Login URL`. Internal account administration is reached by clicking the studio name. Direct workspace/admin entry links are not exposed. Support Workspace access remains consent-based.
