# ADR-068 – Permanent Test Photographer Harness

**Status:** Accepted  
**Release:** 0.7.1-dev.55-test-photographer-harness  
**Date:** 2026-09-12

## Context
Repeated end-to-end testing required deleting and recreating a Trial photographer, re-entering onboarding data, uploading branding and then rebuilding Demo Journey state. This made regression testing slow and introduced unnecessary variation between test runs.

## Decision
Aurora Fotoportal provides one permanent platform-owned **Test-fotograf** account. The account is explicitly marked `is_test_account=1`; destructive test-reset actions refuse to run for any account without that flag.

The Test-fotograf has reusable fixture data for all six onboarding steps: studio/contact/address details, about text, logo, profile image, hero image, watermark, accent colour and portal e-mail defaults. Fixture images live inside the plugin and are restored by reset rather than depending on prior uploads.

Aurora Admin exposes three test actions:

1. **Test onboarding fra start** – clears test tenant content, restores fixtures, resets onboarding to step 1 and opens the onboarding wizard.
2. **Test Demo Journey fra start** – clears test tenant content, restores fixtures, keeps onboarding completed and opens the Dashboard ready for a fresh Demo Journey.
3. **Nullstill alt** – clears account-scoped Fotoportal test content and resets onboarding to step 1 while preserving the Test-fotograf account and owner identity.

The normal photographer-account deletion handler refuses to delete a Test-fotograf. This prevents the permanent test identity from being removed accidentally.

## Data safety
- Reset is allowed only when `is_test_account=1`.
- Cleanup remains account scoped through `account_id`.
- Gallery upload trees are removed only below the WordPress uploads root.
- Platform account identity, owner mapping, module/license configuration and bundled fixtures are preserved.
- No production photographer account can be selected as a reset target.

## Consequences
Regression tests can repeatedly start at onboarding or at Demo Journey without manual account recreation. The permanent test account is development infrastructure and must not be presented as a normal customer/Trial account.
