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

/** CONFIGURATION */
$GLOBALS['TL_LANG']['fe_c4g_reservation']['brick_caption']    = 'Reservierung';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['brick_caption_plural'] = 'Reservierungen';

/** FIELDS */
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_type'] = 'Reservierungswunsch?';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_type_short'] = 'Art';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_time'] = 'Uhrzeit';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desiredCapacity'] = 'Für wie viele Personen?';
$GLOBALS['TL_LANG']['tl_c4g_reservation']['periodType'] = "Zeitspannenart";
$GLOBALS['TL_LANG']['fe_c4g_reservation']['duration_caption'] = "Individuelle Nutzungsdauer (";
$GLOBALS['TL_LANG']['fe_c4g_reservation']['duration_minutely'] = " Minuten)";
$GLOBALS['TL_LANG']['fe_c4g_reservation']['duration_hourly'] = " Stunden)";
$GLOBALS['TL_LANG']['fe_c4g_reservation']['duration_daily'] = " Tage)";
$GLOBALS['TL_LANG']['fe_c4g_reservation']['duration_weekly'] = " Wochen)";
$GLOBALS['TL_LANG']['fe_c4g_reservation']['tstamp'] = 'Letzte Änderung';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDate'] = 'An welchem Tag?';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDateMultipleDays'] = 'Reservierungsbeginn';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDateShort'] = 'Datum';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDateTime'] = 'Zeitpunkt';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['endDate'] = 'Endet am';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime'] = 'Gewünschte Uhrzeit?';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeMultipleDays'] = 'Reservierungszeitraum';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeShort'] = 'Uhrzeit';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['endTime'] = 'Endet um';

$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDateEvent'] = 'Startet am';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['endDateEvent'] = 'Endet am';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeEvent'] = 'Startet um';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['endTimeEvent'] = 'Endet um';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['clock'] = 'Uhr';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['eventForwardingButtonText'] = 'Reservieren';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['state'] = 'Status';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['state_none'] = 'kein Status';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['state_green'] = 'Reservierung möglich';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['state_orange'] = 'Reservierung möglich. Nur noch wenige Plätze verfügbar.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['state_red'] = 'Ausgebucht. Keine Reservierung möglich.';

$GLOBALS['TL_LANG']['fe_c4g_reservation']['headline_data'] = 'Ihre Daten';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['headline_participant'] = 'Teilnehmer*innen';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['detailsHeaadline'] = 'Übersicht';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['eventaddress'] = 'Adresse';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['eventnumber'] = 'Nr.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['eventlocation'] = 'Veranstaltungsort';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['speaker'] = 'Referent(en)';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['targetAudience'] = 'Zielgruppe(n)';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['topic'] = 'Thema/Themen';

$GLOBALS['TL_LANG']['fe_c4g_reservation']['objectlocation'] = 'Ort und Kontakt';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['objectspeaker'] = 'Personen';

$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object'] = 'Reservierungsmöglichkeit';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object_short'] = 'Reservierung';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object_event'] = 'Veranstaltung';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object_none'] = 'Für diese Kombination ist keine Reservierung möglich.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_objectsfirst_none'] = 'Reservierungswunsch';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_none_date'] = 'An diesem Datum ist keine Reservierung möglich.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['included_params'] = 'Enthaltende Leistungen';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['additional_params'] = 'Buchbare Leistungen';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_id'] = 'Reservierungsschlüssel: ';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['organisation'] = 'Firma / Organisation / Schule';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['salutation'] = 'Anrede';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['title'] = 'Titel';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname'] = 'Nachname';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname'] = 'Vorname';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['email'] = 'E-Mail';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['phone'] = 'Telefonnummer';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['address'] = 'Straße';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['postal'] = 'Postleitzahl';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['city'] = 'Ort';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['dateOfBirth'] = 'Geburtsdatum';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['organisation2'] = 'Firma / Organisation / Schule';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['salutation2'] = 'Anrede';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['title2'] = 'Titel';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname2'] = 'Nachname';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname2'] = 'Vorname';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['email2'] = 'E-Mail';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['phone2'] = 'Telefonnummer';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['address2'] = 'Straße';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['postal2'] = 'Postleitzahl';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['city2'] = 'Ort';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['additional1'] = 'Zusatzfeld 1';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['additional2'] = 'Zusatzfeld 2';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['additional3'] = 'Zusatzfeld 3';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['comment'] = 'Ihre Nachricht an uns';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['comment_short'] = 'Bemerkung';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['man'] = 'Herr';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['woman'] = 'Frau';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['various'] = ' - ';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['agreed'] = 'Einwilligung Datenschutz: ';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['confirmed'] = 'Bestätigt';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['specialNotification'] = 'Spezialnachricht versenden';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['internalComment'] = 'Nachricht an den Kunden';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['cancellation'] = 'Storniert';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['periodType'] = 'Zeitspannenart';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['minute'] = 'Minuten';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['hour'] = 'Stunden';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['day'] = 'Tage';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['week'] = 'Wochen';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['openingHours'] = 'Öffnungszeiten';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['md'] = 'Mehrtägig';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['event'] = 'Event';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_none'] = 'Derzeit sind keine Reservierungen möglich.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['signature'] = 'Unterschrift';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['description'] = 'Beschreibung';

$GLOBALS['TL_LANG']['fe_c4g_reservation']['participants'] = 'Teilnehmer*innen';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['additionalParticipant'] = 'Teilnehmer*in';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['additionalParticipants'] = 'Weitere Teilnehmer*innen';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['addParticipant'] = 'Teilnehmer*in hinzufügen';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['removeParticipant'] = 'Teilnehmer*in entfernen';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['removeParticipantMessage'] = 'Teilnehmer*in wirklich entfernen';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['participant_params'] = 'Optionen';

$GLOBALS['TL_LANG']['fe_c4g_reservation']['pleaseSelect'] = 'Bitte auswählen';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText'] = 'Keine Zeiten verfügbar.';

/** Legends */
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_data'] = 'Reservierungsformular';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['person_data'] = 'Ihre Daten';

/** MESSAGES */
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_none'] = 'Derzeit sind keine Reservierungen möglich.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['min_max_failed'] = 'Wir können Ihre Reservierung nicht durchführen. Der Termin ist außerhalb des buchbaren Bereichs.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['fully_booked'] = 'Eine Reservierung ist nicht mehr möglich. Der Termin ist bereits ausgebucht.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['too_many_participants'] = 'Eine Reservierung ist nicht mehr möglich. Die Anzahl der freien Plätze wird überschritten. Mögliche Anzahl: ';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['too_many_participants_per_booking'] = 'Die Anzahl der Teilnehmer pro Buchung ist begrenzt. Maximale Anzahl: ';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['error'] = 'Es ist ein Fehler auftreten. Die Reservierung kann nicht durchgeführt werden.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['duplicate_reservation_id'] = 'Entschuldigung. Es ist ein Fehler auftreten. Die Reservierung kann nicht durchgeführt werden. Bitte Seite neu laden.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['duplicate_booking'] = 'Entschuldigung. Es ist ein Fehler auftreten. Die Reservierung kann nicht durchgeführt werden. Bitte die Seite neu laden und erneut versuchen.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['wrong_postal'] = 'Mit Ihrer Postleitzahl kann keine Reservierung durchgeführt werden.';

$GLOBALS['TL_LANG']['fe_c4g_reservation']['pMin'] = " pro Minute";
$GLOBALS['TL_LANG']['fe_c4g_reservation']['pHour'] = " pro Stunde";
$GLOBALS['TL_LANG']['fe_c4g_reservation']['pDay'] = " pro Tag";
$GLOBALS['TL_LANG']['fe_c4g_reservation']['pWeek'] = " pro Woche";
$GLOBALS['TL_LANG']['fe_c4g_reservation']['pEvent'] = " pro Veranstaltung";
$GLOBALS['TL_LANG']['fe_c4g_reservation']['pPerson'] = " pro Person";
$GLOBALS['TL_LANG']['tl_c4g_reservation']['pAmount'] = " als Pfand";

/** DESCRIPTIONS */
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_reservation_type'] = 'Wählen Sie die Reservierungsart aus.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_beginDate'] = 'Wählen Sie das Datum für den Tag, an dem die Veranstaltung beginnt.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_endDate'] = 'Wählen Sie das Datum für den Tag, an dem die Veranstaltung endet.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_beginTime'] = 'Wählen Sie die Uhrzeit aus, zu der die Veranstaltung beginnt (9:00-12:00 & 14:00-18:00).';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_endTime'] = 'Wählen Sie die Uhrzeit aus, zu der die Veranstaltung endet (9:00-12:00 & 14:00-18:00).';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_reservation_object'] = '';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_reservation_id'] = 'Wichtig! Eindeutiger Schlüssel für Änderungen oder Stornierungen.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_lastname'] = 'Geben Sie Ihren Nachnachmen ein.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_firstname'] = 'Geben Sie Ihren Vornamen ein.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_email'] = 'Geben Sie Ihre E-Mail-Adresse an.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_phone'] = 'Geben Sie Ihre Telefonnummer an.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_address'] = 'Geben Sie Ihre Anschrift an (Straße und Hausnummer).';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_postal'] = 'Geben Sie eine Postleitzahl an.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_city'] = 'Geben Sie Ort oder Stadt ein.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_comment'] = 'Optionale Nachricht.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed'] = 'Detaillierte Informationen zum Umgang mit Nutzerdaten in unserer ';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed_link_text'] = 'Datenschutzerklärung.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed_without_link'] = 'Detaillierte Informationen zum Umgang mit Nutzerdaten in unserer Datenschutzerklärung.';

/** BUTTONS */
$GLOBALS['TL_LANG']['fe_c4g_reservation']['button_reservation'] = 'Jetzt reservieren';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['button_cancellation'] = 'Stornieren';

/** SPECIAL */
$GLOBALS['TL_LANG']['fe_c4g_reservation']['decimal_seperator'] = ',';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['thousands_seperator'] = '.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['currency'] = '€';
