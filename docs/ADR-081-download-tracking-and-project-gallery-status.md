# ADR-081 – HQ download tracking and canonical project gallery status

## Decision
Aurora Fotoportal records authenticated HQ downloads through a controlled download endpoint instead of exposing final delivery files only as direct static URLs. The first customer download updates the project to `delivered`, writes a project log entry and notifies the photographer by e-mail. Subsequent downloads remain in the download history and contribute to the visible counter/last-download timestamp.

Project subviews must derive the gallery lock/open banner from the same authoritative `gallery_access_allowed()` and `project_requires_contract()` rules. Contract, Documents, Gallery and Digital Delivery therefore show the same gallery-state banner.

## Rationale
Legacy `gallery.download_enabled` described a gallery setting, not whether the final HQ delivery was released or downloaded. Showing it as the delivery status produced misleading “Nedlasting: Av” text after a customer had successfully downloaded files.

## Security
The tracking endpoint requires an authenticated, authorized customer, a scoped nonce, a released delivery and accepted delivery terms. Files are resolved only inside the WordPress uploads directory. Photographer preview does not create customer download events.
