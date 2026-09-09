# ADR-027 — Project Workflow and Secure Customer Access

## Decision
Aurora Fotoportal uses explicit project delivery gates: project exists, contract registered, contract signed, optional documents, gallery exists and invoice paid. Documents are informative and never block delivery. Customer gallery access requires both a valid portal/gallery token and an authenticated customer account. Final original-file download will build on the same gate checks in dev.32.

Contracts use Aurora Digital Signering (ADS) as the single contract workflow. A contract may contain an optional uploaded attachment, but uploaded contracts are not a separate signing mode.

## Rationale
Secret URLs are useful routing identifiers but should not be the sole security boundary for private photography. Payment and signing also belong to the project, not to individual images or galleries. Centralizing these checks gives the later original-file download handler one consistent authorization model.
