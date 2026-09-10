# ADR-029 – Customer login linking

The customer/primary-contact email is the source of truth for Aurora Fotoportal authentication. Legacy customers may predate customer accounts. Aurora therefore resolves an existing WordPress user by email or creates a subscriber account when needed, and stores client/account metadata on that user. Password recovery performs the same repair before generating a reset key.
