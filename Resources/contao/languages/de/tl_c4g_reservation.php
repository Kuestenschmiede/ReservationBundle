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

/** FIELDS */       /**DKo 13.02.19*/
$GLOBALS['TL_LANG']['tl_c4g_reservation']['id'] = array("#", "");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_type'] = array("Reservierungsart", "Um welche Reservierungsart handelt es sich");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_additional_option'] = array("Zusätzliche Buchungsoption", "Soll eine zusätzliche Option gebucht werden?");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['desiredCapacity'] = array("Anzahl der Personen", "Wie viele Personen werden maximal erscheinen?");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['duration'] = array("Verweildauer", "Angabe in Stunden");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['periodType'] = array("Zeitspannenart");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['minute'] = array("Minuten");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['hour'] = array("Stunden");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['openingHours'] = array("Öffnungszeiten");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['md'] = array("Mehrtägig");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['beginDate'] = array("Beginn");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['endDate'] = array("Ende");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['beginTime'] = array("Uhrzeit Beginn", "Wann startet die Veranstaltung");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['endTime'] = array("Ende", "Wann endet die Veranstaltung?");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_object'] = array("Reservierungsobjekt", "Welches Objekt soll reserviert werden?");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_id'] = array("Reservierungsschlüssel", "Bitte einen Reservierungsschlüssel zur Identifizierung angeben.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['organisation'] = ['Firma / Organisation / Schule','Bitte Firmenname , Organisations oder Schulenname angeben'];
$GLOBALS['TL_LANG']['tl_c4g_reservation']['salutation'] = ['Anrede: ','Bitte Wählen Herr, Frau oder keine Angabe'];
$GLOBALS['TL_LANG']['tl_c4g_reservation']['man'] = ['Herr'];
$GLOBALS['TL_LANG']['tl_c4g_reservation']['woman'] = ['Frau'];
$GLOBALS['TL_LANG']['tl_c4g_reservation']['various'] = [' - '];
$GLOBALS['TL_LANG']['tl_c4g_reservation']['lastname']  = array("Nachname", "Nachname");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['firstname'] = array("Vorname", "Vorname");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['email'] = array("E-Mail-Adresse", "E-Mail-Adresse");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['phone'] = array("Telefonnummer", "Telefonnummer");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['address'] = array("Straße", "Straße");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['postal'] = array("Postleitzahl", "Postleitzahl");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['city'] = array("Ort", "Ort");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['comment'] = array("Anmerkung vom Kunden", "Kommentarfeld zur Reservierung.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['internal_comment'] = array("Nachricht an Kunde", "");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['agreed'] = array("Datenschutzerklärung", "Wird vom Nutzer im Frontend ausgewählt.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['confirmed'] = array("Bestätigt", "Wurde der Termin bestätigt?(Optional)");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['cancellation'] = array("Storniert", "Wenn der Termin storniert wird, ist dieses Feld anwählen");

$GLOBALS['TL_LANG']['tl_c4g_reservation']['yes'] = 'ja';

/** LEGENDS **/
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_legend'] = "Reservierungsdaten";
$GLOBALS['TL_LANG']['tl_c4g_reservation']['person_legend']      = "Kontaktdaten";

/** OPERATIONS **/
$GLOBALS['TL_LANG']['tl_c4g_reservation']['new'] = array("Reservierung hinzufügen","Reservierung hinzufügen");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['edit'] = array("Reservierung bearbeiten","Bearbeiten der Reservierung ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['copy'] = array("Reservierung kopieren","Kopieren der Reservierung ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['delete'] = array("Reservierung löschen","Löschen der Reservierung ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['show'] = array("Reservierung anzeigen","Anzeigen der Reservierung ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['TOGGLE'] = array("Reservierung aktivieren","Aktivieren der Reservierung ID %s");
