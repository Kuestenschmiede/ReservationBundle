<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 10
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2025, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

$str = 'tl_c4g_reservation_settings';

$GLOBALS['TL_LANG'][$str]['id'] = array("#", "");
$GLOBALS['TL_LANG'][$str]['caption'] = array("Bezeichnung", "Zur Auswahl am Frontendmodul");

$GLOBALS['TL_LANG'][$str]['reservation_types'] = array('Reservierungsarten', 'Wählen Sie die bei der Reservierung im Frontend zu berücksichigenden Reservierungsarten (Räume, Tische, ...). Bei keiner Auswahl werden alle Reservierungsobjekte geladen. Wenn Teilnehmer ins Formular gebracht werden, darf zurzeit lediglich eine entsprechende Art verknüpft werden.');
$GLOBALS['TL_LANG'][$str]['typeDefault'] = array('Reservierungsart vorauswählen', 'Hier können Sie festlegen, welche Art als in der Auswahlbox vorausgewählt werden soll. Wenn hier keine Art ausgewählt wird, dann greift der Standard.');
$GLOBALS['TL_LANG'][$str]['typeHide'] = array('Reservierungsart verstecken', 'sollte nur eine Reservierungsart zur Auswahl stehen, dann kann das Feld hierüber ausgeblendet werden.');
$GLOBALS['TL_LANG'][$str]['objectHide'] = array('Reservierungsobjekt verstecken', 'Wichtig!! Nur sinnvoll bei Verwendung von Insert-Tags. Bspw. "{{c4gevent::title}}". Wenn diese Option aktiviert ist, wird der Reservierungsname im Frontend nicht angezeigt.');
$GLOBALS['TL_LANG'][$str]['reservationButtonCaption'] = array('Beschriftung Absendebutton','Hiermit können Sie den Button Text ändern. Bspw. "Zahlungspflichtig reservieren".');
$GLOBALS['TL_LANG'][$str]['redirect_site'] = array('Weiterleitung nach der Reservierung', 'Nach der Buchung wird zur ausgewählten Seite weitergeleitet. Bspw. für eine Reservierungsbestätigung.');
$GLOBALS['TL_LANG'][$str]['speaker_redirect_site'] = array('Weiterleitung zu den Referenten', 'Falls Sie die Referenten verknüpfen wollen, können Sie hier die Weiterleitung einstellen.');
$GLOBALS['TL_LANG'][$str]['location_redirect_site'] = array('Weiterleitung zu den Orten', 'Falls Sie die Orte (Veranstaltungsorte) verknüpfen wollen, können Sie hier die Weiterleitung einstellen.');
$GLOBALS['TL_LANG'][$str]['login_redirect_site'] = array('Weiterleitung zur Anmeldeseite', 'Falls das Listenmodul nicht öffentlich ist.');
$GLOBALS['TL_LANG'][$str]['privacy_policy_site'] = array('Link zur Datenschutzerklärung', 'Diese Seite wird für die Bestätigung der Datenschutzerklärung verlinkt.');
$GLOBALS['TL_LANG'][$str]['privacy_policy_text'] = array('Datenschutzkommentar', 'Dieser Datenschutzkommentar taucht an der Checkbox zur Einwilligung auf und kann auch Inserttags enthalten.');
$GLOBALS['TL_LANG'][$str]['notification_type'] = array('Benachrichtigung', 'Wählen Sie die Benachrichtigung aus. Die Einstellung kann in der Reservierungsart überschrieben werden.');
$GLOBALS['TL_LANG'][$str]['additionaldatas'] = array("Formularfelder","Reihenfolge hier entspricht der Anzeige im Frontend. Bis auf die Überschrift, kann jedes Feld nur einmal verwendet werden. Achtung! Vorname, Nachname und E-Mail werden angehangen, falls diese fehlen. Die drei Felder sind immer erforderlich.");
$GLOBALS['TL_LANG'][$str]['mandatory'] = array("Pflichtfeld?","");
$GLOBALS['TL_LANG'][$str]['binding'] = array("Pflichtfeld?","Soll dieses Datenfeld als Pflichtfeld im Frontend angezeigt werden");
$GLOBALS['TL_LANG'][$str]['initialValue'] = array("Initialer Wert", "Hier können Sie einen initialen Wert eingeben. Wichtig bei der Überschrift - ansonsten optional.");
$GLOBALS['TL_LANG'][$str]['individualLabel'] = array("Individuelles Label", "Hier können Sie ein individuelles Label setzen. Ist es Leer greift der Standard aus den Language Dateien.");
$GLOBALS['TL_LANG'][$str]['fieldSelection'] = array("Formularfelder Hinzufügen","Achtung! Vorname, Nachname und E-Mail werden angehangen, falls diese fehlen. Die drei Felder sind immer erforderlich.");
$GLOBALS['TL_LANG'][$str]['additionalDuration'] = array("Individuelle Nutzungsdauer (initial)","Bei 0 ist keine individuelle Eingabe der Nutzungsdauer möglich (Standard). Es greifen die Einstellungen aus der Art für min und max Werte.");
$GLOBALS['TL_LANG'][$str]['withCapacity'] = array("Kunde kann Personenanzahl angeben","Der Kunde kann die Personenanzahl angeben. Dieses wird bspw. für eine Tischreservierung im Restaurant benötigt.");
$GLOBALS['TL_LANG'][$str]['showFreeSeats'] = array("Freie Plätze anzeigen","Offene Plätze werden dargestellt.");
$GLOBALS['TL_LANG'][$str]['showEndTime'] = array("Die Endzeiten werden mit ausgegeben","Die Endzeiten werden mit dargestellt (abhängig von der Konfiguration).");
$GLOBALS['TL_LANG'][$str]['showPrices'] = array("Preise anzeigen","Preise werden dargestellt.");
$GLOBALS['TL_LANG'][$str]['showPricesWithTaxes'] = array("Preise inkl. Steuern berechnen und anzeigen.", "Die Preise werden inklusive Mehrwertsteuer dargestellt.");
$GLOBALS['TL_LANG'][$str]['showDateTime'] = array("Termin am Objekt anzeigen","Zeigt den ausgewählten Termin direkt am Objekt an. Achtung! Diese Funktion greift aktuell nur im Standard (Zeitauswahl).");
$GLOBALS['TL_LANG'][$str]['showMemberData'] = array("Mitgliederdaten übernehmen","Vorhandene Mitgliederdaten werden automatisch in den Formularfeldern vorbelegt.");
$GLOBALS['TL_LANG'][$str]['showDetails'] = array("Details anzeigen","Zusatzinformationen wie Bild und Beschreibung werden dargestellt. Bei den Events Teaser und Bild.");
$GLOBALS['TL_LANG'][$str]['removeBookedDays'] = array("Ausgebuchte Tage im Kalender sperren","Der Kalendertag ist ausgebucht nicht auswählbar. Achtung! Die Überprüfung ist zum Teil langwirrig.");
$GLOBALS['TL_LANG'][$str]['showArrivalAndDeparture'] = array('Anreise- und Abreisezeiten anzeigen', 'Achtung! Gilt nur für die Reservierungsart "Übernachtung".');
$GLOBALS['TL_LANG'][$str]['showInlineDatepicker'] = array("Datepicker geöffnet darstellen","Der Datepicker wird als Kalender geöffnet dargestellt.");
$GLOBALS['TL_LANG'][$str]['emptyOptionLabel'] = array("Text leere Objektliste","Der Text, der in der Objektauswahl angezeigt wird wenn kein passendes Objekt zur Verfügung steht, kann optional überschrieben werden.");
$GLOBALS['TL_LANG'][$str]['specialParticipantMechanism'] = array("Teilnehmermechanismus","Ist dieser Mechanismus aktiviert, dann werden Teilnehmerfelder (Titel, Name, Vorname, E-Mail, ggf. Leistungen) anhand der Personenzahl generiert. Wichtig! Das Teilnehmer-Feld muss dafür ins Formular gebracht werden. Außerdem sollten Sie die maximale Teilnehmerzahl an der Art aufgrund der Ladezeiten stark eingrenzen. Für die Teilnehmerfelder darf aktuell nur eine Reservierungsart im Formular verknüpft sein.");
$GLOBALS['TL_LANG'][$str]['showMinMaxWithCapacity'] = array("Minimum und Maximum anzeigen","Die mini- bis maximal erlaubte Eingabe für die Angabe der Personenzahl wird angezeigt");
$GLOBALS['TL_LANG'][$str]['hideParticipantsEmail'] = array("Teilnehmer E-Mail-Feld ausblenden", "Bei den zusätzlichen Teilnehmerfeldern wird das E-Mail-Feld nicht berücksichtigt.");
$GLOBALS['TL_LANG'][$str]['hideReservationKey'] = array("Reservierungsschlüssel verstecken", "Wenn diese Option aktiviert ist, wird der Reservierungsschlüssel im Frontend nicht angezeigt.");
$GLOBALS['TL_LANG'][$str]['onlyParticipants'] = array("Nur Teilnehmer berücksichtigen", "Wenn dieser Mechanismus aktiviert ist, werden nur die Teilnehmer bei der Preisberechnung / Buchung berücksichtigt und die buchende Person nicht mit gezählt (z.B. nur Rechnungsadresse).");
$GLOBALS['TL_LANG'][$str]['postals'] = array("Postleitzahlen einschränken","Hierüber können Sie ermöglichen, dass Reservierungen nur über bestimmte Postleitzahlen möglich sind (kommagetrennte Liste).");

$GLOBALS['TL_LANG'][$str]['typeWithEmptyOption'] = array('Reservierungsart Auswahl treffen', 'Wenn dieser Schalter gesetzt ist, wird die Reservierungsart nicht vorbelegt und muss ausgewählt werden.');

$GLOBALS['TL_LANG'][$str]['referencesObjectType'][1] = 'Zeitauswahl';
$GLOBALS['TL_LANG'][$str]['referencesObjectType'][2] = 'Contao Events';
$GLOBALS['TL_LANG'][$str]['referencesObjectType'][3] = 'Objektauswahl';

/** LEGENDS **/
$GLOBALS['TL_LANG'][$str]['settings_legend'] = "Allgemeine Einstellungen";
$GLOBALS['TL_LANG'][$str]['type_legend'] = "Einstellungen zur Reservierungsart";
$GLOBALS['TL_LANG'][$str]['object_legend'] = "Einstellungen zur Zeitauswahl";
$GLOBALS['TL_LANG'][$str]['form_legend'] = "Reservierungsformular";
$GLOBALS['TL_LANG'][$str]['notification_legend'] = 'Notification Center';
$GLOBALS['TL_LANG'][$str]['redirect_legend'] = 'Weiterleitung';
$GLOBALS['TL_LANG'][$str]['expert_legend'] = 'Experteneinstellungen';

/** OPERATIONS **/
$GLOBALS['TL_LANG'][$str]['new'] = array("Formular erstellen","Neues Formular erstellen");
$GLOBALS['TL_LANG'][$str]['edit'] = array("Einstellungen bearbeiten","Bearbeiten der Einstellung ID %s");
$GLOBALS['TL_LANG'][$str]['copy'] = array("Einstellungen kopieren","Kopieren der Einstellung ID %s");
$GLOBALS['TL_LANG'][$str]['delete'] = array("Einstellungen löschen","Löschen der Einstellung ID %s");
$GLOBALS['TL_LANG'][$str]['show'] = array("Einstellungen anzeigen","Anzeigen der Einstellung ID %s");
$GLOBALS['TL_LANG'][$str]['TOGGLE'] = array("Einstellungen aktivieren","Aktivieren der Einstellung ID %s");
