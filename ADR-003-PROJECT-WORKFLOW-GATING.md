# ADR-003 — Project workflow and contract gating

Status: Accepted

## Workflow
Aurora Fotoportal project workflow is:

1. Project
2. Contract
3. Documents
4. Gallery
5. Delivery

The broader business flow begins with the end customer:

Customer → Project → Contract → Documents → Gallery → Delivery

## Contract gate
Gallery production is locked until at least one contract for the project has status `signed`.

The gate is enforced:
- visually in the project workflow navigation
- in the project gallery view
- in the gallery ZIP upload handler

Documents remain available before contract signing.

## Navigation
Each project step provides contextual Back/Next navigation in addition to the step navigation.

## Future configuration
A later Photographer Account entitlement/workflow setting may allow a studio administrator to disable the "signed contract required before gallery" rule. The current implementation intentionally does not add this override yet.
