# ADR-067 – Demo Journey delivery, signing notification and safe project deletion

## Status
Accepted for `0.7.1-dev.54-demo-journey-polish`.

## Context
Testing of dev.53 showed that the Demo Journey still completed too much automatically, delivery actions did not teach the photographer the real workflow, payment updates from the photographer workspace were blocked by the legacy `manage_options` gate, and project navigation lacked direct access to the active gallery/customer portal. The project also needed a safe, tenant-scoped permanent project deletion path.

## Decision
1. Demo Journey completion is refreshed from the actual tenant-scoped demo customer/project/contract/gallery state rather than trusting only stored progress flags.
2. Delivery is no longer auto-completed by the demo wizard. The photographer must use the normal Delivery workspace, save usage-rights choices, and mark the invoice as paid. These two actions are recorded as demo-training progress.
3. Customer acceptance of delivery terms remains a customer-side gate and is explicitly explained as not required to be green during the photographer delivery exercise.
4. Digital signing uses the standard ADS contract text in the demo. The default text is changed to: “Ved digital signering registreres tidspunkt og signaturinformasjon i fotoportalen.” Existing stored standard text containing the old Aurora wording is migrated on read.
5. Both real ADS signing and simulated Demo Journey signing notify the photographer by e-mail when the customer signs.
6. The photographer payment handler accepts the dedicated photographer capability and remains tenant-scoped.
7. Demo kit v1.5 requires extraction of the outer ZIP only. The inner gallery ZIP is named `DEMO-BILDER_IKKE PAKK UT DENNE.zip` and must be uploaded intact.
8. Project view exposes project-gallery navigation, a direct gallery button and a customer gallery-portal button. Demo projects use the secure demo-preview context.
9. Permanent project deletion requires the exact project name plus a second confirmation. It removes project-scoped database rows, gallery directories, generated delivery ZIPs and project attachments while preserving the customer record.

## Consequences
- Demo Journey now teaches the actual delivery controls instead of bypassing them.
- Progress can recover if demo objects are deleted or changed.
- A photographer can complete payment updates without `manage_options`.
- Project deletion is destructive but deliberately guarded and tenant-scoped.
