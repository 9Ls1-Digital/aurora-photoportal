# ADR-059 – Photographer Project Flow UX

**Status:** Accepted  
**Date:** 2026-09-11  
**Release:** 0.7.1-dev.46-project-flow-ux

## Decision
Fotografens prosjektflyt presenteres som den primære steg-navigasjonen øverst på prosjektet. Rekkefølgen er Prosjekt, Galleri, Kontrakt registrert, Kontrakt signert, Dokumenter og Leveranse.

Galleri ligger tidlig fordi bildearbeidet er fotografens naturlige produksjonssteg. Tilgang for kunden er fortsatt sikkerhetsstyrt: galleriet kan klargjøres før signering, men kundevisningen forblir låst til gyldig signert kontrakt.

Galleri-visningen bruker prosjektets toppbanner til å vise både prosjektstatus og galleri-lås. Dermed er den viktigste sperren synlig uten en separat stor statusboks lenger ned på siden.

## UX rules
- Prosjektflyt skal være synlig før prosjektets sekundære metadata.
- Fullførte steg markeres visuelt.
- Galleri før signering skal kommunisere «kan klargjøres, men er låst» og ikke fremstå som en feil.
- Prosjektets Galleri-visning skal ikke ha en duplisert steg-boks nederst.
- Låsen skal fortsatt håndheves i backend/opplastings- og tilgangslogikk, ikke bare visuelt.
