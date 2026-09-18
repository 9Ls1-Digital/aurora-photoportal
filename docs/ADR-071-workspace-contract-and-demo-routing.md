# ADR-071 – Workspace contract access and Test-fotograf Demo routing

Date: 2026-09-14
Status: Accepted for dev.58

## Context
Testing dev.57 exposed four connected UX/routing defects: Test-fotograf completed onboarding into the ordinary dashboard rather than the guided Demo Journey; photographer workspace contract creation/sending still required `manage_options`; delivery Demo status circles looked interactive even though the real controls were farther down the page; and centrally seeded Demo Content galleries could link to a contract page even though those reference projects intentionally had no contract.

## Decision
1. A completed six-step onboarding for an account flagged `is_test_account=1` redirects to Dashboard with Demo Journey step `kit` active.
2. Contract create/send handlers accept the photographer capability only when the request explicitly carries `aurora_workspace=1`; nonce and tenant-scoped project/contract lookup remain mandatory.
3. Contract creation pre-fills signer name/e-mail from the project customer/contact.
4. Demo Delivery explains that its circles are status indicators and provides anchor links to the actual Bruksrett and Faktura controls.
5. Seeded Demo Content galleries with no contract are labelled as reference/example content and link back to Demo Guide instead of pretending a missing contract exists.
6. The bottom five-step bar is explicitly labelled Project flow so it cannot be confused with Demo Journey.

## Consequences
The permanent Test-fotograf now exercises the same onboarding UI but continues directly into the intended guided training. Normal photographer contract actions no longer fail with “Mangler tilgang”. Demo/reference content is separated visually from authoritative Journey state.
