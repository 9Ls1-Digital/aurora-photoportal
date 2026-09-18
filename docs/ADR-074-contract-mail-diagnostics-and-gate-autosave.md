# ADR-074 – Contract mail diagnostics and contract-gate autosave

## Status
Accepted for dev.61.

## Context
During photographer testing, the project contract gate could be unchecked without an obvious save action, and ADS contract messages were not reaching recipients even though the UI offered "Marker sendt". There was also no visible e-mail history, making it impossible to distinguish a WordPress mail failure from a downstream delivery problem.

## Decision
1. The project contract-gate checkbox saves immediately when changed. A short "Lagrer …" state replaces the previous easy-to-miss save action.
2. ADS contract mail uses the same minimal HTML mail-header profile as the verified photographer invitation flow. The configured SMTP/mail plugin remains responsible for the sender transport.
3. `wp_mail_failed` is captured for each ADS attempt and the detailed failure is written to the project log.
4. Every ADS attempt is written as `contract_email` or `contract_email_error`, including recipient, agreement title and timestamp.
5. The photographer Contracts view shows a dedicated E-posthistorikk panel. A successful `wp_mail()` call is described as accepted by the WordPress mail system, not as proof of final delivery.
6. Contracts in `sent` state can be sent again from the same view; signed contracts cannot.

## Consequences
The photographer can see exactly whether Aurora/WordPress accepted or rejected the send request and can retry without creating another contract. If WordPress reports success but the recipient still receives nothing, the next diagnostic target is the configured SMTP/mail provider rather than Fotoportal state handling.
