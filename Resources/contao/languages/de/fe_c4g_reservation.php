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

/** CONFIGURATION */
$GLOBALS['TL_LANG']['fe_c4g_reservation']['brick_caption']    = 'Reservierung';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['brick_caption_plural'] = 'Reservierungen';

/** FIELDS */
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_type'] = 'Was möchten Sie reservieren?';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_time'] = 'Uhrzeit';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desiredCapacity'] = 'Für wie viele Personen reservieren Sie?';
$GLOBALS['TL_LANG']['tl_c4g_reservation']['periodType'] = "Zeitspannenart";
$GLOBALS['TL_LANG']['fe_c4g_reservation']['duration'] = "Verweildauer in Stunden";
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDate'] = 'An welchem Tag möchten Sie reservieren?'; //ToDo with endDate
$GLOBALS['TL_LANG']['fe_c4g_reservation']['endDate'] = 'Enddatum';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime'] = 'Bitte wählen Sie eine Uhrzeit aus';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['endTime'] = 'Endet um';

$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDateEvent'] = 'Die Veranstaltung findet statt am'; //ToDo with endDate
$GLOBALS['TL_LANG']['fe_c4g_reservation']['endDateEvent'] = 'Enddatum';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeEvent'] = 'zu folgender Uhrzeit';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['endTimeEvent'] = 'Endet um';

$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object'] = 'Reservierungsmöglichkeit';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object_event'] = 'Veranstaltung';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object_none'] = 'Bitte Zeitpunkt wählen';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['additional_params'] = 'Zusatzoptionen';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_id'] = 'Reservierungsschlüssel: ';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['organisation'] = 'Firma / Organisation / Schule';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['salutation'] = 'Anrede: ';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname'] = 'Nachname';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname'] = 'Vorname';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['email'] = 'E-Mail-Adresse';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['phone'] = 'Telefonnummer';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['address'] = 'Straße';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['postal'] = 'Postleitzahl';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['city'] = 'Ort';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['comment'] = 'Ihre Nachricht an uns';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['man'] = 'Herr';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['woman'] = 'Frau';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['various'] = ' - ';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['agreed'] = 'Einwilligung Datenschutz: ';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['periodType'] = 'Zeitspannenart';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['minute'] = 'Minuten';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['hour'] = 'Stunden';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['openingHours'] = 'Öffnungszeiten';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['md'] = 'Mehrtägig';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['event'] = 'Event';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_none'] = 'Derzeit sind keine Reservierungen möglich. Bitte nehmen Sie mit uns Kontakt auf.';

$GLOBALS['TL_LANG']['fe_c4g_reservation']['participants'] = 'Teilnehmer';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['addParticipant'] = 'Teilnehmer hinzufügen';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['removeParticipant'] = 'Teilnehmer entfernen';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['removeParticipantMessage'] = 'Teilnehmer wirklich entfernen';

$GLOBALS['TL_LANG']['fe_c4g_reservation']['pleaseSelect'] = 'Bitte auswählen';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText'] = 'Keine Zeiten verfügbar.';

/** Legends */
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_data'] = 'Reservierungsformular';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['person_data'] = 'Ihre Daten';

/** MESSAGES */
$GLOBALS['TL_LANG']['fe_c4g_reservation']['min_max_failed'] = 'Wir können Ihre Reservierung nicht durchführen. Der Termin ist außerhalb des buchbaren Bereichs.';

/** DESCRIPTIONS */
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_reservation_type'] = 'Wählen Sie die Reservierungsart aus.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_beginDate'] = 'Wählen Sie das Datum, am welchen Tag die Veranstaltung beginnt.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_endDate'] = 'Wählen Sie das Datum, am welchen Tag die Veranstaltung endet.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_beginTime'] = 'Wählen Sie die Uhrzeit aus, wann die Veranstaltung beginnt (9:00-12:00 & 14:00-18:00).';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_endTime'] = 'Wählen Sie die Uhrzeit aus, wann die Veranstaltung endet (9:00-12:00 & 14:00-18:00).';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_reservation_object'] = 'Bei \"Bitte Zeitpunkt wählen\" wählen Sie bitte einen anderen Zeitraum.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_reservation_id'] = 'Diesen Schlüssel senden wir Ihnen per E-Mail zu. Sie benötigen ihn für Änderungen oder Stornierungen.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_lastname'] = 'Geben Sie Ihren Nachnachmen ein.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_firstname'] = 'Geben Sie Ihren Vornamen ein.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_email'] = 'Geben Sie hier Ihre E-Mail-Adresse an.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_phone'] = 'Geben Sie hier Ihre Telefonnummer an.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_address'] = 'Geben Sie hier Ihre Anschrift an (Straße und Hausnummer).';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_postal'] = 'Geben Sie hier eine Postleitzahl an.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_city'] = 'Geben Sie hier Ort oder Stadt ein.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_comment'] = 'Hier können Sie optional eine Nachricht an uns hinterlassen.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed'] = 'Detaillierte Informationen zum Umgang mit Nutzerdaten finden Sie in unserer ';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed_link_text'] = 'Datenschutzerklärung.';

/** BUTTONS */
$GLOBALS['TL_LANG']['fe_c4g_reservation']['button_reservation'] = 'Jetzt reservieren';
