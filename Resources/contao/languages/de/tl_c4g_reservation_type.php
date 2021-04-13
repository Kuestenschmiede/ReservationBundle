<?php
/**
 * This file is part of con4gis,
 * the gis-kit for Contao CMS.
 *
 * @package    con4gis
 * @version    7
 * @author     con4gis contributors (see "authors.txt")
 * @license    LGPL-3.0-or-later
 * @copyright  Küstenschmiede GmbH Software & Design
 * @link       https://www.con4gis.org
 */

/** Fields **/
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['caption'] = array("Backend Bezeichnung","Bezeichnung zur Auswahl im Backend.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['options'] = array("Frontend Bezeichnung","Werden je nach Sprache im Auswahlfeld im Frontend dargestellt.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['option'] = array("Name","");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['reservationObjectType'] = array("Objekttyp", "Es können Reserverierungsobjekte und Contao Events reserviert werden.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['referencesObjectType'][1] = 'Standard';
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['referencesObjectType'][2] = 'Events';
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['notification_type'] = array('Benachrichtigung', 'Wählen Sie die Benachrichtigung aus. Diese Einstellung überschreibt die Moduleinstellungen.');
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['location'] = array("Veranstaltungsort", "Wo findet der Termin statt?");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['periodType'] = array("Zeitspannenart","Art der Zeitspanne deklarieren");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['objectCount'] = array("Maximale Anzahl Objekte pro Zeitspanne","Maximale Anzahl der Gleichzeitig buchbaren Objekte. Das ist sinnvoll um bspw. in der Gastronomie gleichzeitige Tischreservierungen zu minimieren.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['severalBookings'] = array("Objekte mehrfach buchbar","Alle Objekte des Typs können mehrfach gebucht werden. Nur sinnvoll mit Personenangabe. Eine bereits gebuchte Personenzahl wird subrahiert.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['language'] = array("Sprache","");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['auto_del'] = array("Intervall für Automatisches Löschen","In welchem Intervall sollen erledigte Termine gelöscht werden ");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['del_time'] = array("Wert für Intervall angeben","Anzahl der Tage nach denen ein erledigter Termin gelöscht wird");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['minute'] = array("Minuten");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['no_delete'] = array("Nur Manuelles löschen");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['hour'] = array("Stunden");
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
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['maxParticipantsPerBooking'] = array("Maximale Teilnehmerzahl pro Buchung", "Hierüber können Sie die Teilnehmerzahl in der Eingabe pro Buchung begrenzen. 0 = unbegrenzt.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['almostFullyBookedAt'] = array("Fast ausgebucht bei wieviel %", "Hiermit kann der Warnstatus (Organge) festgelegt werden. 0 = kein Orange-Status");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['memberId'] = array("Mitglied verknüpfen", "Alle Reservierungen dieser Art werden für dieses Mitglied erstellt.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['emptyMemberId'] = 'Standard (kein spezielles Mitglied)';

/** Legends **/
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['type_legend'] = "Angaben zur Reservierungsart";
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['object_legend'] = "Grundsätzliche Einstellungen zu den Objekten";
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['expert_legend'] = "Experteneinstellungen";

/** OPERATIONS **/
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['new'] = array("Reservierungsart hinzufügen","Reservierungsart hinzufügen");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['edit'] = array("Reservierungsart bearbeiten","Bearbeiten der Reservierungsart ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['copy'] = array("Reservierungsart kopieren","Kopieren der Reservierungsart ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['delete'] = array("Reservierungsart löschen","Löschen der Reservierungsart ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['show'] = array("Reservierungsart anzeigen","Anzeigen der Reservierungsart ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['TOGGLE'] = array("Reservierungsart aktivieren","Aktivieren der Reservierungsart ID %s");
