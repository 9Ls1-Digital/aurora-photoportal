# ADR-060 – Guided Demo Project Journey

**Status:** Accepted  
**Release:** 0.7.1-dev.47-guided-project-journey  
**Date:** 2026-09-11

## Decision
The photographer project detail is the primary operational workspace. Its canonical sequence is: Project → Contract registered → Contract signed → Gallery → Documents → Delivery.

A contextual “Next step” card is rendered from real project state and links directly to the action the photographer should perform. Existing gallery content is previewed directly on the project page with a direct Gallery action.

Trial demo projects additionally show an Aurora Demo Guide. The guide explains the customer journey and contains an email-flow preview, while actual contract/customer state remains authoritative. The demo guide does not fake production state changes.

Gallery lock status is shown in the project header on the Gallery view. The former large standalone lock card is replaced by a compact reminder, avoiding duplicate status UI.

## Rationale
New photographers should not need to infer workflow from status labels. The workspace must explain what is complete, what is blocked, and what action comes next while preserving the same underlying contract and gallery security rules.
