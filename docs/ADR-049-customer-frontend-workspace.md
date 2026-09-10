# ADR-049 — Customer frontend workspace

## Decision
Authenticated Fotoportal customers use `/fotograf/kunde/portal/` as their canonical portal URL.

## Login compatibility
The existing `/fotograf/kunde/` authentication entry is preserved unchanged.

## Token handling
Legacy invitation/deep links may still contain the customer portal token. After successful
authentication, the canonical portal route is session/context based and does not expose the
token in the browser address bar.

## Rendering
The established customer portal renderer is reused internally. Aurora Auth resolves and
authorizes the customer identity before rendering.

## Rollback
If the Aurora Auth workspace API is unavailable, Fotoportal retains the previous token-based
portal URL as a compatibility fallback.
