# ADR-023 – Gallery Interaction Foundation

## Decision
Customer gallery interactions are attached to existing gallery image records. Favorites use the existing favorites table, comments use image_comments, and Approved/Selected uses the existing images.is_selected state. Public writes require a valid gallery token and validate image ownership against gallery and account.

## UX
Image actions are revealed on hover/focus (always accessible on small touch screens). Once an image has activity, a persistent visual status remains on the image. Photographer Workspace and customer portal counters read the same stored interaction data.

## Profile menu
The Photographer Workspace top bar uses the tenant photographer profile image when configured and exposes Min profil and Rediger profil from the profile control.
