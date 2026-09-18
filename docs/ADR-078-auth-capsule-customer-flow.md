# ADR-078 — Aurora Access is authoritative for customer authentication

## Context
Fotoportal still contained legacy customer reset/login paths while Aurora Access owned `/fotograf/kunde/`, `/fotograf/kunde/glemt-passord/`, `/fotograf/kunde/nytt-passord/` and the authenticated customer workspace. This caused reset/login behavior to diverge depending on which path generated the URL.

## Decision
When Aurora Access is active, Fotoportal customer account creation generates the reset URL through Aurora Access. Fotoportal opts into Access auto-login after password reset. The Fotoportal customer identity resolver also repairs legacy customer users that exist by exact e-mail but are missing the account/client user meta, provided exactly one customer match exists.

## Security
Automatic mapping repair requires a unique exact customer e-mail match. Ambiguous matches are rejected. Normal tenant/account authorization and customer identity checks remain mandatory before a session is established.
