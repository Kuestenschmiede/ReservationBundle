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

$str = 'tl_c4g_reservation_object';

/** FIELDS */
$GLOBALS['TL_LANG'][$str]['caption'] = array("Bezeichnung", "Bezeichnung im Frontend sowie Backend. Benennen Sie das Reservierungsobjekt. Beispiele: Raum1, Tisch7, Fachkraft3");
$GLOBALS['TL_LANG'][$str]['options'] = array("Frontendbezeichnung","Wird alternativ im Frontend dargestellt.");
$GLOBALS['TL_LANG'][$str]['quantity'] = array("Verfügbare Anzahl","Wie viele Objekte dieses Typs stehen zur Verfügung? (Standard 1)");
$GLOBALS['TL_LANG'][$str]['allTypesQuantity'] = array("Gilt über alle Reservierungsarten (selbes Objekt)","Die verfügbare Anzahl der Objekte wird über alle Reservierungsarten berücksichtigt.");
$GLOBALS['TL_LANG'][$str]['allTypesValidity'] = array("Gilt über alle Reservierungsarten (alle Objekte)","Sollten mehrere Reservierungsarten aktiv sein, dann blockiert die Buchung auch andere Objekte.");
$GLOBALS['TL_LANG'][$str]['switchAllTypes'] = array("Gilt nur für folgende Reservierungsarten","Hiermit kann die Gültigkeit der vorherigen Checkboxen auf bestimmte Reservierungsarten reduziert werden (optional).");
$GLOBALS['TL_LANG'][$str]['priority'] = array("Soll vorrangig angeboten werden","Ist dieser Schalter aktiv, dann wir das Objekt vorausgewählt, wenn mehrere Objekte in den Zeitpunkt passen.");
$GLOBALS['TL_LANG'][$str]['location'] = array("Veranstaltungsort", "Wo findet der Termin statt? Überschreibt die Einstellung an der Reservierungsart.");
$GLOBALS['TL_LANG'][$str]['speaker'] = array("Referent(en)", "Wer leitet die Veranstaltung?");
$GLOBALS['TL_LANG'][$str]['topic'] = array("Thema/Themen", "Hier können Sie Veranstaltungsthemen verknüpfen.");
$GLOBALS['TL_LANG'][$str]['targetAudience'] = array("Zielgruppe(n)", "Wählen Sie das Zielpublikum.");
$GLOBALS['TL_LANG'][$str]['viewableTypes'] = array("Reservierungsarten","Ordnen Sie das Reservierungsobjekt zu den Reservierungsarten zu.");
$GLOBALS['TL_LANG'][$str]['option'] = array("Name","");
$GLOBALS['TL_LANG'][$str]['language'] = array("Sprache","");
$GLOBALS['TL_LANG'][$str]['minute_interval'] = array("Minutenintervall", "Alle wie viele Minuten kann das Reservierungsobjekt während der Öffnungszeiten gebucht werden.");
$GLOBALS['TL_LANG'][$str]['hour_interval'] = array("Stundenintervall", "Alle wie viele Stunden kann das Reservierungsobjekt während der Öffnungszeiten gebucht werden.");
$GLOBALS['TL_LANG'][$str]['day_interval'] = array("Tageintervall", "Alle wie viele Tage kann das Reservierungsobjekt während der Öffnungszeiten gebucht werden.");
$GLOBALS['TL_LANG'][$str]['week_interval'] = array("Wochenintervall", "Alle wie viele Wochen kann das Reservierungsobjekt während der Öffnungszeiten gebucht werden.");
$GLOBALS['TL_LANG'][$str]['oh_monday'] = array("Montag", "");
$GLOBALS['TL_LANG'][$str]['oh_tuesday'] = array("Dienstag", "");
$GLOBALS['TL_LANG'][$str]['oh_wednesday'] = array("Mittwoch", "");
$GLOBALS['TL_LANG'][$str]['oh_thursday'] = array("Donnerstag", "");
$GLOBALS['TL_LANG'][$str]['oh_friday'] = array("Freitag", "");
$GLOBALS['TL_LANG'][$str]['oh_saturday'] = array("Samstag", "");
$GLOBALS['TL_LANG'][$str]['oh_sunday'] = array("Sonntag", "");
$GLOBALS['TL_LANG'][$str]['time_begin'] = array("Beginn", "");
$GLOBALS['TL_LANG'][$str]['time_end'] = array("Ende", "");
$GLOBALS['TL_LANG'][$str]['date_from'] = array("Gültig ab (optional)", "");
$GLOBALS['TL_LANG'][$str]['date_to'] = array("Gültig bis (optional)", "");
$GLOBALS['TL_LANG'][$str]['days_exclusion'] = array("Zeitraum ausschließen von - bis", "Hierüber können Sie Zeiträume von der Reservierung ausschließen (z.B. Betriebsurlaub).");
$GLOBALS['TL_LANG'][$str]['date_exclusion'] = array("", "");
$GLOBALS['TL_LANG'][$str]['date_exclusion_end'] = array("", "");
$GLOBALS['TL_LANG'][$str]['min_reservation_day'] = array("Frühester Reservierungstermin (in Tagen)", "Heute + wie viele Tage?");
$GLOBALS['TL_LANG'][$str]['max_reservation_day'] = array("Spätester Reservierungstermin (in Tagen)", "Heute + wie viele Tage?");
$GLOBALS['TL_LANG'][$str]['periodType'] = array("Zeitart","Wählen Sie aus was für eine Art von Zeitintervall genutzt werden soll.");
$GLOBALS['TL_LANG'][$str]['event_id'] = array("Eventauswahl","");
$GLOBALS['TL_LANG'][$str]['event_dayBegin'] = array("Eventbeginn","Am welchen Tag startet das Event.");
$GLOBALS['TL_LANG'][$str]['event_dayEnd'] = array("Eventende","Am welchen Tag endet das Event.");
$GLOBALS['TL_LANG'][$str]['event_timeBegin'] = array("Eventstart","Um wieviel Uhr startet das Event.");
$GLOBALS['TL_LANG'][$str]['event_timeEnd'] = array("Eventende","Um wieviel Uhr endet das EVent.");
$GLOBALS['TL_LANG'][$str]['description'] = array("Beschreibung","Beschreibung der Reservierungsmöglichkeit (wird im Frontend dargestellt).");
$GLOBALS['TL_LANG'][$str]['image'] = array("Bild", "Passendes Bild zur Reservierungsmöglichkeit (wird im Frontend dargestellt).");
$GLOBALS['TL_LANG'][$str]['minute'] = array("Minuten");
$GLOBALS['TL_LANG'][$str]['hour'] = array("Stunden");
$GLOBALS['TL_LANG'][$str]['day'] = array("Tage");
$GLOBALS['TL_LANG'][$str]['week'] = array("Wochen");
$GLOBALS['TL_LANG'][$str]['openingHours'] = array("Buchungszeiten");
$GLOBALS['TL_LANG'][$str]['time_interval'] = array("Zeitintervall","Geben sie an wie viele X Minuten/ X Stunden das Objekt gebucht werden kann (hängt davon ab welcher Reservierungs-und zeitspannenart das Objekt zugeordnet ist");
$GLOBALS['TL_LANG'][$str]['duration'] = array("Dauer je Buchung (optional)","Im Normalfall [0] ist die Dauer entsprechend des Zeitintervalls. Sollte aber der eigentliche Termin länger sein als die angebotenen Intervall. Bspw. wenn eine Umbau- oder Reinigungspause eingeplante werden soll, dann kann hier ein längeres Intervall eingegeben werden.");
$GLOBALS['TL_LANG'][$str]['min_residence_time'] = array("Minimale Nutzungsdauer (optional)", "Minimale Nutzungsdauer, die der Kunde im Formular wählen darf. Wichtig für mehrtägige Buchungen (Tage, Wochen). Achtung! Gilt nur für die Reservierungsart Objektwahl. Bei 0 gilt das Zeitintervall.");
$GLOBALS['TL_LANG'][$str]['max_residence_time'] = array("Maximale Nutzungsdauer (optional)", "Maximale Nutzungsdauer, die der Kunde im Formular wählen darf. Wichtig für mehrtägige Buchungen (Tage, Wochen). Achtung! Gilt nur für die Reservierungsart Objektwahl. Bei 0 gilt das Zeitintervall.");
$GLOBALS['TL_LANG'][$str]['md'] = array("Mehrtägig");
$GLOBALS['TL_LANG'][$str]['event_selection'] = array("Eventtyp");
$GLOBALS['TL_LANG'][$str]['event_object'] = array("Event Objekt");
$GLOBALS['TL_LANG'][$str]['contao_event'] = array("Contao Event");
$GLOBALS['TL_LANG'][$str]['published'] = array("Veröffentlichen.","Soll dieses Objekt im Frontend angezeigt werden?");
$GLOBALS['TL_LANG'][$str]['desiredCapacityMin'] = array("Minimale Anzahl der Personen", "Wie viele Personen dürfen mindestens erscheinen? Beim Standard 0 wird die Anzahl nicht ausgewertet.");
$GLOBALS['TL_LANG'][$str]['desiredCapacityMax'] = array("Maximale Anzahl der Personen", "Wie viele Personen dürfen maximal erscheinen? Beim Standard 0 wird die Anzahl nicht ausgewertet.");
$GLOBALS['TL_LANG'][$str]['notification_type'] = array('Automatische Bestätigungsnachricht (optional)', 'Wählen Sie die Benachrichtigung aus. Diese Einstellung überschreibt die Reservierungsformular Einstellungen und auch die Einstellung an der Reservierungsart.');
$GLOBALS['TL_LANG'][$str]['memberId'] = array("Wem gehört das Objekt?", "Nur wichtig, wenn Objekte über das Frontendmodul erstellt werden.");

$GLOBALS['TL_LANG'][$str]['price'] = array("Preis", "Bestimmen sie den Preis für die Buchung (bspw.: 50.00)");
$GLOBALS['TL_LANG'][$str]['priceoption'] = array("Preiseinstellung", "Wonach soll der Preis berechnet werden");

$GLOBALS['TL_LANG'][$str]['references']['pMin'] = array("Preis pro Minute");
$GLOBALS['TL_LANG'][$str]['references']['pHour'] = array("Preis pro Stunde");
$GLOBALS['TL_LANG'][$str]['references']['pDay'] = array("Preis pro Tag");
$GLOBALS['TL_LANG'][$str]['references']['pWeek'] = array("Preis pro Woche");
$GLOBALS['TL_LANG'][$str]['references']['pReservation'] = array("Preis pro Reservierung");
$GLOBALS['TL_LANG'][$str]['references']['pPerson'] = array("Preis pro Person");
$GLOBALS['TL_LANG'][$str]['references']['pAmount'] = array("Sicherheitsbetrag (Pfand)");

/** LEGENDS **/
$GLOBALS['TL_LANG'][$str]['type_legend'] = "Reservierungsobjekte";
$GLOBALS['TL_LANG'][$str]['md_legend'] = "Mehrtägig";
$GLOBALS['TL_LANG'][$str]['event_legend'] = "Event";
$GLOBALS['TL_LANG'][$str]['event_object_legend'] = "Event";
$GLOBALS['TL_LANG'][$str]['contao_event_legend'] = "Contao-Event";
$GLOBALS['TL_LANG'][$str]['periodType_legend'] = "Zeit";
$GLOBALS['TL_LANG'][$str]['time_interval_legend'] = "Intervalleinstellungen (Abhängig von der Art)";
$GLOBALS['TL_LANG'][$str]['minute_legend'] = "Minuteneinstellung";
$GLOBALS['TL_LANG'][$str]['hour_legend'] = "Stundeneinstellung";
$GLOBALS['TL_LANG'][$str]['day_legend'] = "Tageeinstellung";
$GLOBALS['TL_LANG'][$str]['week_legend'] = "Wocheneinstellung";
$GLOBALS['TL_LANG'][$str]['publish_legend'] = "Veröffentlichungsoptionen";
$GLOBALS['TL_LANG'][$str]['opening_hours_monday_legend'] = "Öffnungszeiten Montags";
$GLOBALS['TL_LANG'][$str]['opening_hours_tuesday_legend'] = "Öffnungszeiten Dienstags";
$GLOBALS['TL_LANG'][$str]['opening_hours_wednesday_legend'] = "Öffnungszeiten Mittwochs";
$GLOBALS['TL_LANG'][$str]['opening_hours_thursday_legend'] = "Öffnungszeiten Donnerstags";
$GLOBALS['TL_LANG'][$str]['opening_hours_friday_legend'] = "Öffnungszeiten Freitags";
$GLOBALS['TL_LANG'][$str]['opening_hours_saturday_legend'] = "Öffnungszeiten Samstags";
$GLOBALS['TL_LANG'][$str]['opening_hours_sunday_legend'] = "Öffnungszeiten Sonntags";
$GLOBALS['TL_LANG'][$str]['exclusion_legend'] = "Ausschlusszeiten";
$GLOBALS['TL_LANG'][$str]['booking_wd_legend'] = "Mögliche Buchungszeiträume bzw. Start- und Endzeiten für mehrtägige Buchungen";
$GLOBALS['TL_LANG'][$str]['location_legend'] = 'Einstellungen zum Ort';
$GLOBALS['TL_LANG'][$str]['event_legend'] = 'Einstellungen für Veranstaltungsobjekte';
$GLOBALS['TL_LANG'][$str]['price_legend'] = 'Einstellungen zum Preis';
$GLOBALS['TL_LANG'][$str]['expert_legend'] = 'Experteneinstellungen';

/** OPERATIONS **/
$GLOBALS['TL_LANG'][$str]['new'] = array("Objekt hinzufügen","Objekt hinzufügen");
$GLOBALS['TL_LANG'][$str]['edit'] = array("Objekt bearbeiten","Bearbeiten der Objekt ID %s");
$GLOBALS['TL_LANG'][$str]['copy'] = array("Objekt kopieren","Kopieren der Objekt ID %s");
$GLOBALS['TL_LANG'][$str]['delete'] = array("Objekt löschen","Löschen der Objekt ID %s");
$GLOBALS['TL_LANG'][$str]['show'] = array("Objekt anzeigen","Anzeigen der Objekt ID %s");
$GLOBALS['TL_LANG'][$str]['TOGGLE'] = array("Objekt aktivieren","Aktivieren der Objekt ID %s");
