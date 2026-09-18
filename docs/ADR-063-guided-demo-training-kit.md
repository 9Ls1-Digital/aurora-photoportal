# ADR-063 – Guided Demo Training Kit

**Status:** Accepted  
**Release:** 0.7.1-dev.50-guided-demo-training  
**Date:** 2026-09-11

## Context
Trial-fotografer kunne se ferdig demo-innhold, men lærte ikke nødvendigvis rekkefølgen i et reelt fotooppdrag. Demo-guiden lå dessuten inne på demo-prosjektet og kunde-/galleristatus kunne oppleves motstridende.

## Decision
Dashboardet er kontrollsenter for en guidet demoøvelse. Trial-kontoen får en første-gangs velkomst og kan laste ned et Demo-kit som inneholder bilder, kontrakter, dokumenter og instruksjon. Guiden følger progresjon gjennom kunde, prosjekt, kontrakt, kundesignering, galleri og levering.

Demo-e-postsimulatoren peker direkte til kundeportalen og minner om bruk av annen nettleser eller inkognito. Galleri-status skal skille mellom intern teknisk klarstatus og om kunden faktisk har tilgang. Et ferdig behandlet galleri uten signert kontrakt vises som «Galleri klart · låst for kunde».

## Consequences
- Demo-opplevelsen lærer arbeidsflyten, ikke bare funksjonene.
- Demo-kit kan gjenbrukes i senere opplæringssteg og ved ZIP-opplasting.
- Prosjektstatus blir mindre tvetydig.
- Senere faser kan utvide simulatoren med eksplisitte hendelser for e-post, kundegodkjenning og betaling uten å sende reelle meldinger.
