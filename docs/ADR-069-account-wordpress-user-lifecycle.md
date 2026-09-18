# ADR-069 – WordPress-user lifecycle follows Fotoportal account lifecycle

**Release:** 0.7.1-dev.56-account-user-cleanup  
**Date:** 2026-09-12

## Context
Permanent deletion of a photographer account removed tenant data, but deletion of the photographer WordPress identity was optional and customer portal identities could remain behind. This could leave orphan users whose e-mail address blocked a later test-account setup.

## Decision
Fotoportal account deletion now automatically removes WordPress identities that are exclusively mapped to the deleted Fotoportal account. Customer portal users are removed as part of account cleanup. The photographer owner is also removed automatically when it is a dedicated Fotoportal identity.

Administrators, WooCommerce managers and identities with another Aurora binding are preserved. Mapping is verified before deletion. The permanent Test-fotograf is never deleted; Test Harness reset removes generated customer identities but preserves the Test-fotograf owner.

The Test Harness can also adopt an existing safe test identity found by the fixed test e-mail address, which makes recovery from an orphan test user possible.

## Consequences
- Deleting a photographer account no longer requires a separate checkbox for its dedicated WordPress user.
- Test resets no longer accumulate `aurora-demo-kunde-*` WordPress users.
- Shared/admin identities remain protected.
- Existing orphan users from releases before dev.56 are not bulk-deleted automatically; they can be removed manually or reclaimed by the Test Harness where applicable.
