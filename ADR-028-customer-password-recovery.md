# ADR-028 – Customer Password Recovery

## Decision
Customer password recovery is handled inside the Aurora Fotoportal customer experience instead of linking to WordPress core lost-password screens.

The portal token identifies the relevant customer context. Aurora resolves the customer WordPress user, creates a WordPress password-reset key, sends a branded reset email, validates the reset key on return, and updates the password using WordPress core password APIs.

## Security
- Reset keys are generated and validated by WordPress core.
- The public portal token does not itself reset a password.
- New passwords require a valid reset key and login.
- Reset forms use WordPress nonces.
- Mail failures are not silently reported as success.

## UX
The customer remains within the photographer-branded Aurora experience for request, reset, confirmation, and return to portal login.
