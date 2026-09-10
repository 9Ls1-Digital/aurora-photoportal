# Aurora-ready architecture for 9Ls1 Fotoportal

## Decision

Do not rewrite the working Fotoportal.

Use an incremental modularization ("strangler") approach:
- Keep the existing database schema.
- Keep current routes/admin actions working.
- Extract domain services behind stable interfaces when a module is actively changed.
- New customer-facing functionality is built modular from day one.

## Product boundary

### 9Ls1 Fotoportal owns
- Photography customers
- Photography projects
- Contracts for photography assignments
- Galleries
- Proof/preview workflows
- Favorites and image comments
- Photo delivery
- Photo-specific PDF/contact sheets
- Customer album experience

### Aurora candidates / shared platform capabilities
These should be designed as reusable services, but do not need to move to Aurora yet:
- Authentication/session abstraction
- Branding/theme tokens
- Notification service
- File/media storage abstraction
- Audit/activity log
- API response conventions
- Permissions/capabilities
- PWA shell/app infrastructure
- QR/link service
- Publishing/integration services where relevant

## Main technical finding

The current `class-9ls1-fotoportal-admin.php` is a God Class. It currently combines:
- routing
- repositories/database queries
- customer domain
- project domain
- contracts
- galleries
- media processing
- watermarking
- PDF generation
- documents/templates
- logging
- settings
- test data

It works, so it should NOT be rewritten wholesale.

## Target module map

```text
9ls1-fotoportal/
├── 9ls1-fotoportal.php
├── includes/
│   ├── class-9ls1-fotoportal-admin.php       # temporary façade/controller
│   ├── class-customer-app.php                # NEW: PWA/customer app shell
│   ├── class-app-api.php                     # NEW: REST/API boundary
│   ├── class-customer-portal.php             # frontend portal orchestration
│   ├── class-gallery-service.php             # extracted when gallery changes next
│   ├── class-media-service.php               # preview/thumb/watermark
│   ├── class-proof-pdf-service.php            # premium proof PDF
│   ├── class-contract-service.php
│   ├── class-document-service.php
│   ├── class-activity-log.php
│   └── repositories/
│       ├── class-customer-repository.php
│       ├── class-project-repository.php
│       └── class-gallery-repository.php
├── assets/
│   ├── css/
│   └── app/
│       ├── app.css
│       ├── app.js
│       ├── manifest.json
│       └── sw.js
├── templates/
│   ├── customer-portal.php
│   └── customer-app.php
└── docs/
```

## What to refactor NOW

Only these items should be done before customer portal work:

1. Introduce `class-customer-app.php`.
2. Introduce `class-app-api.php`.
3. Create `/assets/app/`.
4. Give PWA/customer app its own route/shortcode.
5. Keep all existing DB and gallery functions untouched unless the new module needs them.
6. Add a small service/facade boundary for future customer album reads.

## What NOT to refactor now

Do not:
- rename database tables
- migrate existing customer/project IDs
- rewrite galleries
- replace working PDF engine only for architecture reasons
- move everything into namespaces
- introduce Composer solely for structure
- split every 20-line function into separate classes
- make Aurora a hard dependency

## Compatibility contract

Fotoportal must run without Aurora installed.

Future integration should be optional:

```php
if (class_exists('Aurora_Core')) {
    // use shared Aurora service
} else {
    // Fotoportal local implementation
}
```

This keeps Fotoportal sellable as an independent product while allowing an Aurora edition later.

## Customer App v3

The already tested standalone PWA should become an internal module:

```text
/includes/class-customer-app.php
/includes/class-app-api.php
/assets/app/
shortcode/app-route
```

First integration should use test/sample album data or read-only gallery access.
Do not connect write operations (favorites/comments) until authentication and permissions are defined.

## API design

Use WordPress REST API as the initial transport:

```text
/wp-json/9ls1-fotoportal/v1/me
/wp-json/9ls1-fotoportal/v1/albums
/wp-json/9ls1-fotoportal/v1/albums/{id}
/wp-json/9ls1-fotoportal/v1/albums/{id}/images
```

Later:
```text
.../favorites
.../comments
.../downloads
```

Internally, API controllers call Fotoportal services/repositories rather than direct SQL.

## Aurora migration path

Later, selected implementations can be replaced by Aurora adapters without changing the customer app:

```text
Customer App
     ↓
Fotoportal API contract
     ↓
Service interface
   ↙       ↘
Local      Aurora adapter
```

This is the key architectural choice.
