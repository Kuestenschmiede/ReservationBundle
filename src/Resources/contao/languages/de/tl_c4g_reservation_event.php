<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 10
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2025, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

$str = 'tl_c4g_reservation_event';

$GLOBALS['TL_LANG'][$str]['id'] = array("#", "");
$GLOBALS['TL_LANG'][$str]['pid'] = array("Contao Event", "Wählen Sie ein Contao Event.");
$GLOBALS['TL_LANG'][$str]['number'] = array("Nummer", "Hier können Sie eine Kennung für die Veranstaltung eingeben, bspw. eine Seminarnummer.");
$GLOBALS['TL_LANG'][$str]['reservationType'] = array("Reservierungsart", "Um welche Reservierungsart handelt es sich?");
$GLOBALS['TL_LANG'][$str]['location'] = array("Veranstaltungsort", "Wo findet die Veranstaltung statt? Überschreibt die Einstellung an der Reservierungsart.");
$GLOBALS['TL_LANG'][$str]['organizer'] = array("Veranstalter", "Wer ist Veranstalter?");
$GLOBALS['TL_LANG'][$str]['speaker'] = array("Referent*innen", "Wer leitet die Veranstaltung?");
$GLOBALS['TL_LANG'][$str]['topic'] = array("Thema/Themen", "Hier können Sie Veranstaltungsthemen verknüpfen.");
$GLOBALS['TL_LANG'][$str]['targetAudience'] = array("Zielgruppe(n)", "Wählen Sie das Zielpublikum.");
$GLOBALS['TL_LANG'][$str]['minParticipants'] = array("Minimale Teilnehmerzahl", "Minimale Teilnehmerzahl, damit das Event stattfindet. Standard: 1.");
$GLOBALS['TL_LANG'][$str]['maxParticipants'] = array("Maximale Teilnehmerzahl", "Maximale Teilnehmerzahl für die Veranstaltung. Standard: 0 (unbeschränkt).");
$GLOBALS['TL_LANG'][$str]['maxParticipantsPerEventBooking'] = array("Maximale Teilnehmerzahl pro Buchung", "Hierüber können Sie die Teilnehmerzahl in der Eingabe pro Buchung begrenzen. Sollten Sie den Teilnehmermechanismus im Formular nutzen wollen, dann sollten Sie hier die Anzahl stark eingrenzen (max. 10 Personen).");
$GLOBALS['TL_LANG'][$str]['min_reservation_day'] = array("Frühester Reservierungstermin (in Tagen)", "Heute + wie viele Tage?");
$GLOBALS['TL_LANG'][$str]['state'] = array("Aktueller Status", "grün, orange, rot");

$GLOBALS['TL_LANG'][$str]['price'] = array("Preis", "Bestimmen sie den Preis für die Buchung (bspw.: 50.00)");
$GLOBALS['TL_LANG'][$str]['participant_params'] = array("Teilnehmeroptionen", "Stellen sie mögliche Optionen zur Auswahl.");
$GLOBALS['TL_LANG'][$str]['participantParamsFieldType'] = array("Feldtyp", "Wählen sie den Feldtyp für die Teilnehmeroptionen im Reservierungsformular.");
$GLOBALS['TL_LANG'][$str]['participantParamsMandatory'] = array("Pflichtfeld", "Sollen die Teilnehmeroptionen ein Pflichtfeld sein? (keine Vorauswahl)");
$GLOBALS['TL_LANG'][$str]['taxOptions'] = ["Steuersatz auswählen", "Wählen Sie eine Steueroption für alle Preise."];
$GLOBALS['TL_LANG'][$str]['priceoption'] = array("Preiseinstellung", "Wonach soll der Preis berechnet werden?");
$GLOBALS['TL_LANG'][$str]['discountCode'] = array("Rabattcode", "Ist hier ein Rabattcode eingeben, dann wird der Rabatt automatisch verrechnet.");
$GLOBALS['TL_LANG'][$str]['discountPercent'] = array("Rabatt", "Prozentsatz für den Rabatt.");
$GLOBALS['TL_LANG'][$str]['conferenceLink'] = array("Konferenz Link", "Hier kann zum Beispiel ein Teamslink angebgeben werden, der dann mit in der Reservierungsbetätigung aufgelistet werden kann.");

$GLOBALS['TL_LANG'][$str]['reservationForwarding'] = ["Weiterleitung zum Modul", "Wählen Sie die Seite aus, auf der sich das Reservierungsmodul befindet. Kann auch am Kalender und in den Dashboard/Einstellungen global gesetzt werden."];
$GLOBALS['TL_LANG'][$str]['reservationForwardingButtonCaption'] = ["Buttonbeschriftung Weiterleitung", "Beschriftung des Weiterleitungsbutton zur Reservierung. Standard: leer. Es greifen die Einträge aus den Sprachdateien."];

$GLOBALS['TL_LANG'][$str]['multi'] = 'Mehrfachauswahl (Multi-Checkbox)';
$GLOBALS['TL_LANG'][$str]['radio'] = 'Einfachauswahl (Radiogruppe)';
$GLOBALS['TL_LANG'][$str]['references']['tNone'] = "Keine MwSt";
$GLOBALS['TL_LANG'][$str]['references']['tStandard'] = "Standard";
$GLOBALS['TL_LANG'][$str]['references']['tReduced'] = "Ermäßigt";

$GLOBALS['TL_LANG'][$str]['references']['pMin'] = array("Preis pro Minute");
$GLOBALS['TL_LANG'][$str]['references']['pHour'] = array("Preis pro Stunde");
$GLOBALS['TL_LANG'][$str]['references']['pDay'] = array("Preis pro Tag");
$GLOBALS['TL_LANG'][$str]['references']['pNight'] = array("Preis pro Übernachtung");
$GLOBALS['TL_LANG'][$str]['references']['pNightPerson'] = array("Preis pro Übernachtung und Person");
$GLOBALS['TL_LANG'][$str]['references']['pWeek'] = array("Preis pro Woche");
$GLOBALS['TL_LANG'][$str]['references']['pReservation'] = array("Preis pro Reservierung");
$GLOBALS['TL_LANG'][$str]['references']['pPerson'] = array("Preis pro Person");
$GLOBALS['TL_LANG'][$str]['references']['pAmount'] = array("Sicherheitsbetrag (Pfand)");

/** LEGENDS **/
$GLOBALS['TL_LANG'][$str]['event_legend'] = "Daten zur Veranstaltung";
$GLOBALS['TL_LANG'][$str]['reservation_legend'] = "Daten zur Reservierung";

/** OPERATIONS **/
$GLOBALS['TL_LANG'][$str]['new'] = array("Event hinzufügen","Event hinzufügen");
$GLOBALS['TL_LANG'][$str]['edit'] = array("Event bearbeiten","Bearbeiten der Veranstaltung ID %s");
$GLOBALS['TL_LANG'][$str]['copy'] = array("Event kopieren","Kopieren der Veranstaltung ID %s");
$GLOBALS['TL_LANG'][$str]['delete'] = array("Event löschen","Löschen der Veranstaltung ID %s");
$GLOBALS['TL_LANG'][$str]['show'] = array("Event anzeigen","Anzeigen der Veranstaltung ID %s");
$GLOBALS['TL_LANG'][$str]['TOGGLE'] = array("Event aktivieren","Aktivieren der Veranstaltung ID %s");


$GLOBALS['TL_LANG'][$str]['showParticipantInfoFields'] = array("Felder in Teilnehmerliste anzeigen","Welcher Felder sollen zusätzlich in der Teilnehmerliste angezeigt werden?");
$GLOBALS['TL_LANG'][$str]['references']['additional1'] = array("Zusatzfeld 1");
$GLOBALS['TL_LANG'][$str]['references']['additional2'] = array("Zusatzfeld 2");
$GLOBALS['TL_LANG'][$str]['references']['additional3'] = array("Zusatzfeld 3");
$GLOBALS['TL_LANG'][$str]['references']['booker'] = array("reserviert von");

$GLOBALS['TL_LANG'][$str]['references']['lastname']  = array("Nachname", "Nachname");
$GLOBALS['TL_LANG'][$str]['references']['firstname'] = array("Vorname", "Vorname");
$GLOBALS['TL_LANG'][$str]['references']['email'] = array("E-Mail-Adresse", "E-Mail-Adresse");
$GLOBALS['TL_LANG'][$str]['references']['phone'] = array("Telefonnummer", "Telefonnummer");
$GLOBALS['TL_LANG'][$str]['references']['address'] = array("Straße", "Straße");
$GLOBALS['TL_LANG'][$str]['references']['postal'] = array("Postleitzahl", "Postleitzahl");
$GLOBALS['TL_LANG'][$str]['references']['city'] = array("Ort", "Ort");
$GLOBALS['TL_LANG'][$str]['references']['dateOfBirth'] = array("Geburtsdatum", "Geburtsdatum");
$GLOBALS['TL_LANG'][$str]['references']['comment'] = array("Anmerkung zum Teilnehmer", "Kommentarfeld zur Reservierung.");
$GLOBALS['TL_LANG'][$str]['references']['reservation_participant_option'] = array("Teilnehmeroptionen", "Ausgewählte Teilnehmerleistungen");


/** INFO */
$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['infoEvent'] = "";
