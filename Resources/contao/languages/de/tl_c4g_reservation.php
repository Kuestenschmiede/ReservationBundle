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

/** FIELDS */       /**DKo 13.02.19*/
$GLOBALS['TL_LANG']['tl_c4g_reservation']['id'] = array("#", "");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['pid'] = array("Gebucht wurde", "Wird automatisch gesetzt.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_type'] = array("Reservierungsart", "Um welche Reservierungsart handelt es sich");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_included_option'] = array("Enthaltene Leistungen", "Welche Leistungen sind enthalten?");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_additional_option'] = array("Gebuchte Leistungen", "Folgende Leistungen wurden gebucht?");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['desiredCapacity'] = array("Personen", "Wie viele Personen werden maximal erscheinen?");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['duration'] = array("Verweildauer", "Angabe in Stunden");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['periodType'] = array("Zeitspannenart");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['minute'] = array("Minuten");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['hour'] = array("Stunden");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['openingHours'] = array("Öffnungszeiten");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['md'] = array("Mehrtägig");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['beginDate'] = array("Beginndatum");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['endDate'] = array("Enddatum");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['beginTime'] = array("Beginnzeit", "Wann startet die Veranstaltung");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['endTime'] = array("Endzeit", "Wann endet die Veranstaltung?");

$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservationObjectType'] = array("Objekttyp", "Es können Reserverierungsobjekte und Contao Events reserviert werden.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['referencesObjectType'][1] = 'Standard';
$GLOBALS['TL_LANG']['tl_c4g_reservation']['referencesObjectType'][2] = 'Event';
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_object'] = array("Gebucht wurde", "Welches Objekt soll reserviert werden?");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_id'] = array("Reservierungsschlüssel", "Bitte einen Reservierungsschlüssel zur Identifizierung angeben.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['man'] = ['Herr'];
$GLOBALS['TL_LANG']['tl_c4g_reservation']['woman'] = ['Frau'];
$GLOBALS['TL_LANG']['tl_c4g_reservation']['various'] = [' - '];
$GLOBALS['TL_LANG']['tl_c4g_reservation']['organisation'] = ['Firma / Organisation / Schule','Firmennamen, Organisation oder Namen der Schule angeben.'];
$GLOBALS['TL_LANG']['tl_c4g_reservation']['salutation'] = ['Anrede','Bitte auswählen.'];
$GLOBALS['TL_LANG']['tl_c4g_reservation']['title'] = ['Titel', 'Titel der Person'];
$GLOBALS['TL_LANG']['tl_c4g_reservation']['lastname']  = array("Nachname", "Nachname");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['firstname'] = array("Vorname", "Vorname");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['email'] = array("E-Mail", "E-Mail-Adresse");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['phone'] = array("Telefonnummer", "Telefonnummer");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['address'] = array("Straße", "Straße");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['postal'] = array("Postleitzahl", "Postleitzahl");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['city'] = array("Ort", "Ort");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['dateOfBirth'] = array("Geburtsdatum", "Geburtsdatum");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['organisation2'] = ['Firma / Organisation / Schule (2)','Firmennamen, Organisation oder Namen der Schule angeben'];
$GLOBALS['TL_LANG']['tl_c4g_reservation']['salutation2'] = ['Anrede (2)','Bitte auswählen.'];
$GLOBALS['TL_LANG']['tl_c4g_reservation']['title2'] = ['Titel (2)', 'Titel der Person'];
$GLOBALS['TL_LANG']['tl_c4g_reservation']['lastname2']  = array("Nachname (2)", "Nachname");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['firstname2'] = array("Vorname (2)", "Vorname");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['email2'] = array("E-Mail (2)", "E-Mail-Adresse");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['phone2'] = array("Telefonnummer (2)", "Telefonnummer");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['address2'] = array("Straße (2)", "Straße");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['postal2'] = array("Postleitzahl (2)", "Postleitzahl");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['city2'] = array("Ort (2)", "Ort");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['comment'] = array("Anmerkung vom Kunden", "Kommentarfeld zur Reservierung.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['fileUpload'] = array("Datei Upload", "Hier kann eine Datei angehangen werden.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['internal_comment'] = array("Nachricht an Kunden", "");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['agreed'] = array("Datenschutzerklärung", "Wird vom Nutzer im Frontend ausgewählt.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['confirmed'] = array("Bestätigen", "Der Termin wird bestätigt. Benachrichtigung können an der Reservierungsart verknüpft werden (optional).");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['specialNotification'] = array("Spezialnachricht senden", "Ist dieser Schalter gesetzt wird die an der Reservierungsart verknüpfte Spezialnachricht versendet.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['emailConfirmationSend'] = array("Bestätigung wurde versendet", "Dieser Schalter wird automatisch gesetzt, sobald die E-Mail versendet wurde. Sie können die Checkbox wieder deaktivieren um eine erneute Bestätigung zu versenden.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['cancellation'] = array("Storniert", "Wenn der Termin storniert wird, ist dieses Feld auszuwählen");

$GLOBALS['TL_LANG']['tl_c4g_reservation']['memberId'] = array("Für Mitglied", "Die Reservierung wurde durch oder für dieses Mitglied erstellt.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['groupId'] = array("Für Gruppe", "Die Reservierung wurde durch oder für diese Mitgliedergruppe erstellt.");

$GLOBALS['TL_LANG']['tl_c4g_reservation']['billingAddress'] = array("Rechnungsadresse", "Rechnungsadresse");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['additionalHeadline'] = "Überschrift";

$GLOBALS['TL_LANG']['tl_c4g_reservation']['yes'] = 'ja';

$GLOBALS['TL_LANG']['tl_c4g_reservation']['participants'] = 'Teilnehmer';
$GLOBALS['TL_LANG']['tl_c4g_reservation']['confirmationEmail'] = 'Bestätigung versenden';

/** LEGENDS **/
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_legend'] = "Reservierungsdaten";
$GLOBALS['TL_LANG']['tl_c4g_reservation']['person_legend']      = "Kontaktdaten";
$GLOBALS['TL_LANG']['tl_c4g_reservation']['person2_legend']     = "Rechnungsadresse / Sonstige Personendaten";
$GLOBALS['TL_LANG']['tl_c4g_reservation']['comment_legend']     = "Anlagen";
$GLOBALS['TL_LANG']['tl_c4g_reservation']['notification_legend'] = "Bestätigung an den Buchenden";
$GLOBALS['TL_LANG']['tl_c4g_reservation']['state_legend']       = "Status";

/** OPERATIONS **/
$GLOBALS['TL_LANG']['tl_c4g_reservation']['new'] = array("Reservierung hinzufügen","Reservierung hinzufügen");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['edit'] = array("Reservierung bearbeiten","Bearbeiten der Reservierung ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['copy'] = array("Reservierung kopieren","Kopieren der Reservierung ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['delete'] = array("Reservierung löschen","Löschen der Reservierung ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['show'] = array("Reservierung anzeigen","Anzeigen der Reservierung ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['TOGGLE'] = array("Reservierung aktivieren","Aktivieren der Reservierung ID %s");

/** INFOTEXT */
$GLOBALS['TL_LANG']['tl_c4g_reservation']['infoReservation'] = '';
