<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 8
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2022, by Küstenschmiede GmbH Software & Design
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
$GLOBALS['TL_LANG'][$str]['state'] = array("Aktueller Status", "grün, orange, rot");

$GLOBALS['TL_LANG'][$str]['price'] = array("Preis", "Bestimmen sie den Preis für die Buchung (bspw.: 50.00)");
$GLOBALS['TL_LANG'][$str]['priceoption'] = array("Preiseinstellung", "Wonach soll der Preis berechnet werden");

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

/** INFO */
$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['infoEvent'] = "";
