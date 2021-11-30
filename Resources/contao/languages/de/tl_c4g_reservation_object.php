<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 8
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2021, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

/** FIELDS */
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['caption'] = array("Bezeichnung", "Bezeichnung im Frontend sowie Backend. Benennen Sie das Reservierungsobjekt. Beispiele: Raum1, Tisch7, Fachkraft3");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['options'] = array("Frontendbezeichnung","Werden je nach Sprache im Frontend dargestellt.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['quantity'] = array("Verfügbare Anzahl","Wie viele Objekte dieses Typs stehen zur Verfügung? (Standard 1)");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['allTypesQuantity'] = array("Gilt über alle Reservierungsarten (selbes Objekt)","Die verfügbare Anzahl der Objekte wird über alle Reservierungsarten berücksichtigt.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['allTypesValidity'] = array("Gilt über alle Reservierungsarten (alle Objekte)","Sollten mehrere Reservierungsarten aktiv sein, dann blockiert die Buchung auch andere Objekte.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['switchAllTypes'] = array("Gilt nur für folgende Reservierungsarten","Hiermit kann die Gültigkeit der vorherigen Checkboxen auf bestimmte Reservierungsarten reduziert werden (optional).");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['priority'] = array("Soll vorrangig angeboten werden","Ist dieser Schalter aktiv, dann wir das Objekt vorausgewählt, wenn mehrere Objekte in den Zeitpunkt passen.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['location'] = array("Veranstaltungsort", "Wo findet der Termin statt?");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['viewableTypes'] = array("Reservierungsarten","Ordnen Sie das Reservierungsobjekt zu den Reservierungsarten zu.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['option'] = array("Name","");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['language'] = array("Sprache","");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['minute_interval'] = array("Minutenintervall", "Alle wie viele Minuten kann das Reservierungsobjekt während der Öffnungszeiten gebucht werden.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['hour_interval'] = array("Stundenintervall", "Alle wie viele Stunden kann das Reservierungsobjekt während der Öffnungszeiten gebucht werden.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['oh_monday'] = array("Montag", "");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['oh_tuesday'] = array("Dienstag", "");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['oh_wednesday'] = array("Mittwoch", "");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['oh_thursday'] = array("Donnerstag", "");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['oh_friday'] = array("Freitag", "");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['oh_saturday'] = array("Samstag", "");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['oh_sunday'] = array("Sonntag", "");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_begin'] = array("Beginn", "");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_end'] = array("Ende", "");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['date_from'] = array("Gültig ab (optional)", "");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['date_to'] = array("Gültig bis (optional)", "");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['days_exclusion'] = array("Zeitraum ausschließen von - bis", "Hierüber können Sie Zeiträume von der Reservierung ausschließen (z.B. Betriebsurlaub).");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['date_exclusion'] = array("", "");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['date_exclusion_end'] = array("", "");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['min_reservation_day'] = array("Frühester Reservierungstermin (in Tagen)", "Heute + wie viele Tage?");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['max_reservation_day'] = array("Spätester Reservierungstermin (in Tagen)", "Heute + wie viele Tage?");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['periodType'] = array("Zeitart","Wählen Sie aus was für eine Art von Zeitintervall genutzt werden soll.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['event_id'] = array("Eventauswahl","");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['event_dayBegin'] = array("Eventbeginn","Am welchen Tag startet das Event.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['event_dayEnd'] = array("Eventende","Am welchen Tag endet das Event.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['event_timeBegin'] = array("Eventstart","Um wieviel Uhr startet das Event.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['event_timeEnd'] = array("Eventende","Um wieviel Uhr endet das EVent.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['description'] = array("Beschreibung"," Backend Beschreibung des Reservierungsobjektes.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['minute'] = array("Minuten");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['hour'] = array("Stunden");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['openingHours'] = array("Buchungszeiten");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_interval'] = array("Zeitintervall","Geben sie an wie viele X Minuten/ X Stunden das Objekt gebucht werden kann (hängt davon ab welcher Reservierungs-und zeitspannenart das Objekt zugeordnet ist");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['duration'] = array("Dauer je Buchung (optional)","Im Normalfall [0] ist die Dauer entsprechend des Zeitintervalls. Sollte aber der eigentliche Termin länger sein als die angebotenen Intervall. Bspw. wenn eine Umbau- oder Reinigungspause eingeplante werden soll, dann kann hier ein längeres Intervall eingegeben werden.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['min_residence_time'] = array("Minimale Verweildauer (optional)", "Minimale Verweildauer, wenn der Kunde wählen darf.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['max_residence_time'] = array("Maximale Verweildauer (optional)", "Maximale Verweildauer, die der Kunde wählen darf.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['md'] = array("Mehrtägig");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['event_selection'] = array("Eventtyp");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['event_object'] = array("Event Objekt");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['contao_event'] = array("Contao Event");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['published'] = array("Veröffentlichen.","Soll dieses Objekt im Frontend angezeigt werden?");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['desiredCapacityMin'] = array("Minimale Anzahl der Personen", "Wie viele Personen dürfen mindestens erscheinen? Beim Standard 0 wird die Anzahl nicht ausgewertet.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['desiredCapacityMax'] = array("Maximale Anzahl der Personen", "Wie viele Personen dürfen maximal erscheinen? Beim Standard 0 wird die Anzahl nicht ausgewertet.");


/** LEGENDS **/
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['type_legend'] = "Reservierungsobjekte";
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['md_legend'] = "Mehrtägig";
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['event_legend'] = "Event";
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['event_object_legend'] = "Event";
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['contao_event_legend'] = "Contao-Event";
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['periodType_legend'] = "Zeit";
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_interval_legend'] = "Zeiteinstellung";
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['minute_legend'] = "Minuteneinstellung";
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['hour_legend'] = "Stundeneinstellung";
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['publish_legend'] = "Veröffentlichungsoptionen";
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['opening_hours_monday_legend'] = "Öffnungszeiten Montags";
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['opening_hours_tuesday_legend'] = "Öffnungszeiten Dienstags";
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['opening_hours_wednesday_legend'] = "Öffnungszeiten Mittwochs";
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['opening_hours_thursday_legend'] = "Öffnungszeiten Donnerstags";
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['opening_hours_friday_legend'] = "Öffnungszeiten Freitags";
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['opening_hours_saturday_legend'] = "Öffnungszeiten Samstags";
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['opening_hours_sunday_legend'] = "Öffnungszeiten Sonntags";
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['exclusion_legend'] = "Ausschlusszeiten";
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['booking_wd_legend'] = "Buchungszeiträume";
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['location_legend'] = 'Einstellungen zum Ort';
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['expert_legend'] = 'Experteneinstellungen';

/** OPERATIONS **/
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['new'] = array("Objekt hinzufügen","Objekt hinzufügen");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['edit'] = array("Objekt bearbeiten","Bearbeiten der Objekt ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['copy'] = array("Objekt kopieren","Kopieren der Objekt ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['delete'] = array("Objekt löschen","Löschen der Objekt ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['show'] = array("Objekt anzeigen","Anzeigen der Objekt ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['TOGGLE'] = array("Objekt aktivieren","Aktivieren der Objekt ID %s");
