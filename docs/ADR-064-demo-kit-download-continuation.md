# ADR-064 – Demo-kit download continuation

**Release:** 0.7.1-dev.51-demo-download-continuation  
**Dato:** 11. september 2026

## Beslutning
Nedlasting av Demo-kit skal ikke etterlate fotografen i velkomstmodalen. Når nedlastingen startes, lukkes modalvinduet, Dashboardets Demo Guide bringes frem, Demo-kit-steget markeres ferdig i klientgrensesnittet og neste handling – registrering av demo-kunde – presenteres eksplisitt.

## Begrunnelse
En filnedlasting gir normalt ikke en full side-navigasjon. Uten eksplisitt UI-progresjon oppleves derfor flyten som stoppet selv om ZIP-filen er levert og serverstatus er oppdatert.

## Konsekvens
Serveren fortsetter å registrere `kit_downloaded_at`. Klientsiden gir umiddelbar progresjon, mens neste sidevisning bruker den persistente serverstatusen.
