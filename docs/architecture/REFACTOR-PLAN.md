# Incremental refactor plan

## Phase 0 — Baseline
- Create Git repository.
- Tag current tested release.
- No functional changes.

## Phase 1 — Admin UX
- Redesign admin presentation.
- Keep underlying handlers and DB unchanged.
- Introduce reusable UI tokens/components where practical.

## Phase 2 — Customer App foundation
Add:
- `/includes/class-customer-app.php`
- `/includes/class-app-api.php`
- `/assets/app/`
- app route/shortcode
- read-only album endpoint

## Phase 3 — Customer Portal
- authentication/access link model
- project/album overview
- gallery frontend
- expiry messaging

## Phase 4 — Interaction
- favorites
- image comments
- photographer review workflow

## Phase 5 — Delivery
- controlled HQ downloads
- delivery events/audit
- PDF and selection exports

## Phase 6 — Aurora adapters
Only after shared Aurora services are stable:
- auth adapter
- branding adapter
- notifications adapter
- activity log adapter
- media/storage adapter if useful
