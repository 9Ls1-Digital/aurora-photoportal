# ADR-027 – Full Aurora Customer Auth Flow

Aurora customer accounts use an Aurora-owned login and password reset experience. Customer login is handled with `wp_signon()` inside the portal gate, password reset links target the Aurora password route, and linked customer users are redirected away from WooCommerce account pages to their fixed customer portal. New Aurora customer users receive an Aurora password setup link rather than the standard WordPress user notification.
