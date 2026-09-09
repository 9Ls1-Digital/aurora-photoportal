# ADR – dev.31-fix16 Dashboard & Watermark Repair

The fix15 change accidentally replaced a block of existing gallery interaction handlers while adding resources and watermark settings. This caused a fatal runtime error because `gallery_activity_notifications()` was no longer defined.

fix16 is rebuilt from the stable fix14 codebase and reapplies the dashboard, resources and watermark features additively. Existing gallery activity, selection workflow, hero design and customer-login handlers remain intact.

The dashboard is an operational surface: live project/signature/gallery/delivery counters, follow-up items, resource shortcuts, customer-experience preview and account status. Watermark configuration is tenant-specific and affects generated preview derivatives only. Files in `original/` are never modified by watermark settings.
