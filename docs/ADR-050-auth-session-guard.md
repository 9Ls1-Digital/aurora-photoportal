# ADR-050 — Aurora Auth session guard

## Status
Accepted for `0.7.1-dev.36-auth-session-guard`.

## Context
Fotoportal now uses Aurora Auth for canonical photographer and customer login/workspace routes. Aurora Auth 0.1.8-dev.3 adds opt-in workspace session timeout and wrong-context recovery.

## Decision
Fotoportal opts into the shared Auth session guard with:

- normal session inactivity timeout: 8 hours
- “Husk meg” inactivity timeout: 14 days
- wrong-context redirect: enabled

The login routes, workspace routes, identity resolver, authorization callbacks and legacy fallbacks are otherwise unchanged from the verified dev.35 checkpoint.

## Safety
- WordPress administrator sessions remain protected by Aurora Auth.
- The Fotoportal adapter does not implement its own cookie or credential handling.
- Existing sessions created before the Auth session guard are migrated safely by Aurora Auth.
- If Aurora Auth is unavailable, existing Fotoportal fallback behavior remains in place.
