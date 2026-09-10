# ADR-018 — Gallery Upload Workflow

Status: Accepted for development

Existing galleries can receive additional batches as ZIP files, multiple individual images, or both. Existing originals are preserved. New image records are tenant-scoped, filename collisions are resolved, sort order continues from the existing gallery, and Aurora reuses the established preview/thumbnail derivative pipeline. Signed-contract gating remains backend-enforced.
