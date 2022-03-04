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

/** Fields **/
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['caption'] = array("Backend Bezeichnung","Bezeichnung zur Auswahl im Backend.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['options'] = array("Frontend Bezeichnung","Wird alternativ im Frontend dargestellt.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['option'] = array("Name","");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['reservationObjectType'] = array("Objekttyp", "Es können Reserverierungsobjekte und Contao Events reserviert werden.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['referencesObjectType'][1] = 'Standard (Zeitauswahl)';
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['referencesObjectType'][2] = 'Contao Events';
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['referencesObjectType'][3] = 'Objektauswahl';
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['notification_type'] = array('Automatische Bestätigungsnachricht (optional)', 'Wählen Sie die Benachrichtigung aus. Diese Einstellung überschreibt die Reservierungsformular Einstellungen. Standard und Objektauswahl sind kompatibel.');
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['notification_confirmation_type'] = array('Bestätigungsnachricht (Backendversand)', 'Sie können aus der Reservierungsliste heraus eine Bestätigungsnachricht verschicken.');
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['notification_special_type'] = array('Spezialnachricht (Backendversand)', 'Diese Nachricht wird aus der Reservierungsliste heraus anstelle der Bestätigungsnachricht versenden, wenn die Checkbox "Spezialnachricht senden" ausgewählt wurde.');
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['location'] = array("Veranstaltungsort", "Wo findet der Termin statt? Diese Einstellung kann am Objekt und an den Eventeinstellungen überschrieben werden.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['periodType'] = array("Zeitspannenart","Art der Zeitspanne deklarieren. Achtung! Mehrtägige Zeitspannen können aktuell nur mit der Reservierungsart Objektauswahl genutzt werden.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['min_residence_time'] = array("Minimale Nutzungsdauer (optional)", "Minimale Nutzungsdauer, die der Kunde im Formular wählen darf. Bei 0 gilt das Zeitintervall.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['max_residence_time'] = array("Maximale Nutzungsdauer (optional)", "Maximale Nutzungsdauer, die der Kunde im Formular wählen darf. Bei 0 gilt das Zeitintervall.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['objectCount'] = array("Maximale Anzahl Objekte pro Zeitspanne","Maximale Anzahl der Gleichzeitig buchbaren Objekte. Das ist sinnvoll um bspw. in der Gastronomie gleichzeitige Tischreservierungen zu minimieren.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['severalBookings'] = array("Objekte mehrfach buchbar","Alle Objekte des Typs können mehrfach gebucht werden. Nur sinnvoll mit Personenangabe. Eine bereits gebuchte Personenzahl wird subtrahiert.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['directBooking'] = array("Datum und Uhrzeit werden automatisch gesetzt","Diese Funktion wird benötigt wenn man ein Formular zum direkten Einbuchen nutzen möchte. Da wo es schnell gehen muss (z.B. Drive Through). Achtung! Funktioniert nur mit einer einzelnen Reservierungsart und nur zu den Öffnungszeiten.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['language'] = array("Sprache","");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['auto_send'] = array("Zusätzliche Bestätigung automatisch versenden","Die E-Mails werden versendet, sobald die Reservierung bestätigt ist.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['auto_del'] = array("Intervall für Automatisches Löschen","In welchem Intervall sollen erledigte Termine gelöscht werden ");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['del_time'] = array("Wert für Intervall angeben","Anzahl der Tage nach denen ein erledigter Termin gelöscht wird");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['no_delete'] = array("Nur Manuelles löschen");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['no_sending'] = array("Nicht automatisch senden");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['minutely'] = array("Minütlich prüfen und versenden");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['hour'] = array("Stunden");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['minute'] = array("Minuten");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['day'] = array("Tage");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['week'] = array("Wochen");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['daily'] = array("Intervall löschen");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['openingHours'] = array("Öffnungszeiten");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['md'] = array("Mehrtägig");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['published'] = array("Veröffentlichen","Soll die Reservierungsart im Frontend angeboten werden?");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['beginDate'] = array("Startdatum", "Am welchen Tag beginnt der Zeitraum?");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['endDate'] = array("Enddatum", "Am welchen Tag endet der Zeitraum");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event_id'] = array("Contao-Event","Wählen Sie das passende Contao-Event aus.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event_dayBegin'] = array("Eventbeginn","Am welchen Tag startet das Event.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event_dayEnd'] = array("Eventende","Am welchen Tag endet das Event.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event_timeBegin'] = array("Eventstart","Um wieviel Uhr startet das Event.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event_timeEnd'] = array("Eventende","Um wieviel Uhr endet das Event.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event'] = array("Selbstdefiniertes Event");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['contao_event'] = array("Contao-Event");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['fixed'] = array("Feste Uhrzeit");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['timeBegin'] = array("Uhrzeitbeginn");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['timeEnd'] = array("Uhrzeitende");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['dayBegin'] = array("Starttag");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['dayEnd'] = array("Endtag");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['included_params'] = array("Enthaltende Leistungen");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['additional_params'] = array("Buchbare Leistungen");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['participant_params'] = array("Teilnehmeroptionen");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['description'] = array("Beschreibung");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['minParticipantsPerBooking'] = array("Minimale Teilnehmerzahl pro Buchung", "Hierüber können Sie die Mindestteilnehmerzahl in der Eingabe pro Buchung erhöhen. 1 = Standard.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['maxParticipantsPerBooking'] = array("Maximale Teilnehmerzahl pro Buchung", "Hierüber können Sie die Teilnehmerzahl in der Eingabe pro Buchung begrenzen. 0 = unbegrenzt. Sollten Sie den Teilnehmermechanismus im Formular nutzen wollen, dann sollten Sie hier die Anzahl stark eingrenzen (max. 8 Personen).");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['almostFullyBookedAt'] = array("Fast ausgebucht bei wieviel %", "Hiermit kann der Warnstatus (Orange) festgelegt werden. 0 = kein Orange-Status");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['bookRunning'] = array("Laufende Termine buchbar", "Auch laufende Termine sind noch buchbar.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['memberId'] = array("Mitglied verknüpfen", "Alle Reservierungen dieser Art werden für dieses Mitglied erstellt.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['emptyMemberId'] = 'Standard (kein spezielles Mitglied)';
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['groupId'] = array("Gruppe verknüpfen", "Alle Reservierungen dieser Art werden für diese Mitgliedergruppe erstellt.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['emptyGroupId'] = 'Standard (keine spezielle Gruppe)';
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['cloneObject'] = array("Klonvorlage", "Das Objekt wird nur für die Frontenderstellung benötigt. Komplexere Einstellungen werden bei der Erstellung neuer Objekte der selben Art übernommen.");

/** Legends **/
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['type_legend'] = "Angaben zur Reservierungsart";
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['notification_legend'] = "Benachrichtigungseinstellungen (optional)";
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['object_legend'] = "Grundsätzliche Einstellungen zu den Objekten";
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['expert_legend'] = "Experteneinstellungen";

/** Info */
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['infotext'] = 'Die Reservierungsart beschreibt den Typ der Reservierungsobjekte. Beispielsweise: "Tischreservierung" für die Objekte "4er Tisch" oder "Tisch 23. "'.
    'Mehr auf <a href="https://docs.con4gis.org/con4gis-reservation" title="con4gis Docs Reservation" target="_blank" rel="noopener"><b>docs.con4gis.org</b></a>.<br>'.
    '<b>Wichtig! Achten Sie darauf, dass die Formularfelder nicht gecached werden. So ersparen Sie Ihren Nutzern nervige Meldungen.</b>';

/** OPERATIONS **/
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['new'] = array("Reservierungsart hinzufügen","Reservierungsart hinzufügen");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['edit'] = array("Reservierungsart bearbeiten","Bearbeiten der Reservierungsart ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['copy'] = array("Reservierungsart kopieren","Kopieren der Reservierungsart ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['delete'] = array("Reservierungsart löschen","Löschen der Reservierungsart ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['show'] = array("Reservierungsart anzeigen","Anzeigen der Reservierungsart ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['TOGGLE'] = array("Reservierungsart aktivieren","Aktivieren der Reservierungsart ID %s");
