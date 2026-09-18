# ADR-083 – Field-test UX, permissions and gallery upload

Date: 2026-09-15
Status: Accepted for dev.70 test build

## Context
A real photographer tested Aurora Fotoportal with a real customer. The test exposed six friction points: draft/sent contracts could not be deleted, new gallery creation appeared to require ZIP, photographer workspace edits for customer data and gallery expiry could hit `Mangler tilgang`, the customer card exposed a technical photographer-preview URL as if it were the customer login address, and transparency controls used inconsistent semantics.

## Decision
1. Draft/sent/cancelled contracts may be deleted by the owning photographer from Workspace after explicit confirmation. Signed contracts are retained and cannot be deleted through this action.
2. New galleries accept multiple HIGH QUALITY image files directly. ZIP remains an optional bulk-upload path. Aurora still creates originals, previews, thumbnails and delivery derivatives automatically.
3. Photographer Workspace may update its own tenant-scoped customer records and gallery availability/deletion dates without `manage_options`.
4. The customer profile displays only the shared customer login address from Aurora Access. Photographer preview URLs and token URLs are not presented as customer-facing links.
5. `Transparens` has one meaning across Hero and watermark controls: higher percentage means more transparent; 0% means fully visible and 100% means fully transparent. Stored UI values are converted to rendering opacity internally.

## Security / scope
All Workspace write actions still require nonce validation, photographer capability, tenant-scoped lookups and account-scoped database writes. Signed contracts are protected from deletion.
