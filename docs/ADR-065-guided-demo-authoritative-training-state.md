# ADR-065 – Guided Demo uses photographer training state

## Decision
The Trial guide must never treat centrally provisioned Aurora example content as evidence that the photographer has completed a training step.

The guided journey now follows the photographer's own non-demo customer/project created after the first Demo-kit download. Contract, signing, gallery and delivery status are derived from that project only.

## UX consequences
- After customer + project creation, the next CTA becomes **Fortsett: send kontrakt**.
- The e-mail simulation opens at the contract stage and explains the photographer/customer hand-off.
- Bilder/galleri stays incomplete until the photographer actually creates/uploads a gallery for the training project.
- The status panel below the guide changes with the current step instead of continuing to ask for a demo customer that already exists.
- Re-downloading the Demo-kit does not reset the training start timestamp.

## Rationale
The central Demo Content Pack is reference material. Training completion must represent actions performed by the photographer, otherwise seeded images/contracts can create false green states and an unclear next action.
