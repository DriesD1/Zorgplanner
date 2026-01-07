# Zorgplanner

Een Laravel + Filament applicatie om als zelfstandige (medisch) verzorger bewoners van woonzorgcentra te plannen, op te volgen en de communicatie met het zorgteam te stroomlijnen.

## Wat doet het?

-   **Beheer locaties**: maak woonzorgcentra/afdelingen aan met contactgegevens, eigen planningslogica (weekmatrix of dagagenda) en optioneel een e-mail voor het automatisch delen van weekrapporten.
-   **Beheer bewoners (klanten)**: registreer naam, kamer, locatie en planinstellingen (frequentie in weken, startdatum). Voeg dynamische medische velden toe via "Fiche Instellingen" zodat elke gebruiker zijn eigen vragenlijst kan samenstellen.
-   **Voetenanalyse-tekening**: teken probleemzones direct op een voetzool-afbeelding; bestanden worden automatisch opgeruimd bij verwijderen van een klant.
-   **Planning**: per huis kies je tussen
    -   **Weekmatrix** (periodieke zorg): 12-weeks raster gebaseerd op `frequency_weeks` en `next_planned_date` van de klant.
    -   **Dagagenda** (precies uurrooster): 30-minuten slots per dag/week, met live conflictcontrole voordat je plant.
-   **Communicatieblad** (enkel huizen met weekplanning): genereer per huis en week een formulier met geplande/due cliënten, vul datum en notities in, archiveer per week en exporteer of bekijk als PDF (DomPDF).

## Belangrijkste onderdelen

-   **Huizen**: naam, adres, contactmail, planningstype (`week` of `day`), schakelaar voor eigen planning, badge met aantal locaties.
-   **Klanten**: gekoppeld aan huis, kamer, frequentie, startdatum, dynamische fichevelden, voetentekening, badge met totaal aantal klanten. Extra navigatie: Planning; Communicatieblad verschijnt enkel als het huis een weekplanning heeft.
-   **Planning-pagina**: toont ofwel 12-weeks matrix of dagrooster met drag/drop-achtige acties: open planmodal, dubbelboekingscheck, afspraken aan/uit, vorige/volgende periode navigatie, en knop naar communicatieblad met actuele weekdatum.
-   **Communicatieblad-pagina**: vult automatisch cliënten die volgens frequentie of geplande visit in de week vallen; archief met weekscope laden/verwijderen; instant opslag van datum/notitie per rij; PDF-preview en download.

## Techniek

-   Laravel met Filament admin (kleuraccent roze) en Livewire-forms.
-   Models: `House`, `Client`, `Visit`, `CommunicationEntry`, `FicheDefinition` met typecasts voor datum/boolean en automatische cleanup van tekeningen.
-   PDF-generatie via Barryvdh DomPDF.
-   Custom Form Component `FeetDrawing` voor canvas-tekeningen.

## Hoe te gebruiken (korte flow)

1. Voeg een **huis** toe en kies planningstype (week of dag).
2. Leg **fiche velden** vast (tekst of checkbox) zodat ze verschijnen op elke klantenfiche.
3. Maak **klanten** aan, koppel ze aan een huis en stel frequentie/startdatum en medische fiche in; teken indien nodig voetenanalyse.
4. Plan via **Planning** (weekmatrix of dagagenda). Conflicten worden geblokkeerd.
5. Maak per week een **Communicatieblad** voor huizen met weekplanning, vul datums/notities in, archiveer of exporteer als PDF voor het zorgteam.
