<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @author con4gis contributors (see "authors.md")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

$str = 'tl_calendar';

$GLOBALS['TL_LANG'][$str]['activateEventReservation'] = ['Event-Reservierung aktivieren (con4gis)', 'Aktiviert die Event-Reservierung über con4gis Reservation.'];
$GLOBALS['TL_LANG'][$str]['reservationForwarding'] = ["Weiterleitung zum Modul", "Wählen Sie die Seite aus, auf der sich das Reservierungsmodul befindet. Wenn die Weiterleitung Kalenderübergreifend gelten soll, dann können Sie die Einstellung auch im con4gis Dashboard machen."];
$GLOBALS['TL_LANG'][$str]['reservationForwardingButtonCaption'] = ["Buttonbeschriftung Weiterleitung", "Beschriftung des Weiterleitungsbutton zur Reservierung. Standard: leer. Es greifen die Einträge aus den Sprachdateien bzw. die Kalenderübergreifende Einstellung aus dem con4gis Dashboard."];

$GLOBALS['TL_LANG'][$str]['c4g_reservation_legend'] = "Reservierungseinstellungen";

$GLOBALS['TL_LANG'][$str]['reservationType'] = array("Vorbelegung Reservierungsart", "Um welche Reservierungsart handelt es sich?");
$GLOBALS['TL_LANG'][$str]['location'] = array("Vorbelegung Veranstaltungsort", "Wo findet die Veranstaltung statt? Überschreibt die Einstellung an der Reservierungsart.");
$GLOBALS['TL_LANG'][$str]['organizer'] = array("Vorbelegung Veranstalter", "Wer ist Veranstalter?");
$GLOBALS['TL_LANG'][$str]['speaker'] = array("Vorbelegung Referent*innen", "Wer leitet die Veranstaltung?");
$GLOBALS['TL_LANG'][$str]['topic'] = array("Vorbelegung Thema/Themen", "Hier können Sie Veranstaltungsthemen verknüpfen.");
$GLOBALS['TL_LANG'][$str]['targetAudience'] = array("Vorbelegung Zielgruppe(n)", "Wählen Sie das Zielpublikum.");
$GLOBALS['TL_LANG'][$str]['minParticipants'] = array("Vorbelegung Minimale Teilnehmerzahl", "Minimale Teilnehmerzahl, damit das Event stattfindet. Standard: 1.");
$GLOBALS['TL_LANG'][$str]['maxParticipants'] = array("Vorbelegung Maximale Teilnehmerzahl", "Maximale Teilnehmerzahl für die Veranstaltung. Standard: 0 (unbeschränkt).");
$GLOBALS['TL_LANG'][$str]['price'] = array("Vorbelegung Preis", "Bestimmen sie den Preis für die Buchung (bspw.: 50.00)");
$GLOBALS['TL_LANG'][$str]['priceoption'] = array("Vorbelegung Preiseinstellung", "Wonach soll der Preis berechnet werden");

$GLOBALS['TL_LANG'][$str]['references']['pMin'] = array("Preis pro Minute");
$GLOBALS['TL_LANG'][$str]['references']['pHour'] = array("Preis pro Stunde");
$GLOBALS['TL_LANG'][$str]['references']['pDay'] = array("Preis pro Tag");
$GLOBALS['TL_LANG'][$str]['references']['pNight'] = array("Preis pro Übernachtung");
$GLOBALS['TL_LANG'][$str]['references']['pNightPerson'] = array("Preis pro Übernachtung und Person");
$GLOBALS['TL_LANG'][$str]['references']['pWeek'] = array("Preis pro Woche");
$GLOBALS['TL_LANG'][$str]['references']['pReservation'] = array("Preis pro Reservierung");
$GLOBALS['TL_LANG'][$str]['references']['pPerson'] = array("Preis pro Person");
$GLOBALS['TL_LANG'][$str]['references']['pAmount'] = array("Sicherheitsbetrag (Pfand)");
