# ADR-073 - Project contract gate, ADS mail and password visibility

## Status
Accepted for dev.60.

## Decision
Gallery access is now a per-project policy. Projects default to requiring a signed agreement, but photographers can explicitly disable the requirement. This affects gallery creation/customer gallery visibility only; payment, delivery terms and HQ delivery remain independent delivery gates. The override is logged.

ADS "Marker sendt" now performs the actual customer email delivery. The contract only transitions to `sent` when `wp_mail()` succeeds. A failed send keeps the contract out of sent state and returns a visible error.

Photographer password creation adds show/hide controls to both password fields.

## Data
`projects.contract_required` is a TINYINT(1), default 1.

## Security
Workspace actions retain nonce, photographer capability and tenant-scoped object lookup. Disabling the contract gate does not unlock HQ delivery by itself.
