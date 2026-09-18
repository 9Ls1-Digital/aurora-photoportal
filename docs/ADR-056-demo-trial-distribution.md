# ADR-056 – Demo Content Pack Trial distribution

## Decision
Aurora Fotoportal uses one central Demo Content Pack as the source for all Trial accounts. New Trial accounts receive the active pack automatically. Aurora Admin can distribute the current pack to selected or all existing Trial accounts and can explicitly restore demo items that a photographer previously removed.

## Identity and safety
Every distributed item retains a stable `demo_item_id`, `is_demo=1` and `demo_pack_version`. Distribution is idempotent: known items are updated rather than duplicated. Normal distribution respects the account's deleted-demo list. Restore is explicit and clears that list. Photographer-owned resources are never overwritten.

## Phase 2 compatibility
The per-account demo identity/state is the basis for the later keep/remove choice when Trial ends or converts to a paid plan.
