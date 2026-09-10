# ADR-024 – Gallery Interaction UX Refinement

## Decision
Customer image actions remain contextual and visually secondary to the photograph. On pointer devices the favorite, selected and comment actions appear only on hover/focus. Persisted interaction state is represented by the active control when the image is hovered, rather than by text badges over the image.

The gallery summary controls are also filters. The customer can switch between all images, favorites, selected images and images with comments without leaving the gallery. Existing comments are rendered in the comment panel for the relevant image.

Favorite state is treated as gallery/customer state rather than browser-session state; counts use distinct image IDs to prevent duplicate rows from inflating the visible total.
