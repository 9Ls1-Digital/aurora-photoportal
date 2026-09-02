# ADR – dev.31-fix12 Customer Auth Decision

Customer portal authentication now has one deterministic decision path: validate the current WordPress session against the requested client, repair a legitimate e-mail match, otherwise clear the unrelated session and render the complete Aurora login form. Successful login explicitly sets the current user and auth cookie before redirecting to the canonical customer portal URL. The login gate must never render a partial/blank intermediate state.
