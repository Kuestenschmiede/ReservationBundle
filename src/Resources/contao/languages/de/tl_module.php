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

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_module']['reservation_legend'] = 'Reservierungsobjekte';
$GLOBALS['TL_LANG']['tl_module']['list_legend'] = 'Listeneinstellungen';
$GLOBALS['TL_LANG']['tl_module']['reservation_notification_center_legend'] = 'Notification Center';
$GLOBALS['TL_LANG']['tl_module']['reservation_jquery_theme_legend'] = 'jQuery UI-Theme';
$GLOBALS['TL_LANG']['tl_module']['reservation_redirect_legend'] = 'Weiterleitung';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['reservation_types'] = array('Reservierungsarten', 'Wählen Sie die bei der Reservierung im Frontend zu berücksichigenden Reservierungsarten (Räume, Tische, ...). Bei keiner Auswahl werden alle Reservierungsobjekte geladen.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['reservationView'] = array('Listenansicht', 'Verschiedene Ansichten der Liste sind möglich. Standard: Öffentlich');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['renderMode'] = array('Listendarstellung', 'Verschiedene Darstellungen der Liste sind möglich. Standard: Kacheln');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['showReservationType'] = array("Reservierungsart darstellen","Die Reservierungsart wird standardmäßig in Liste und Detail nicht dargestellt. Das können Sie hier ändern.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['showReservationObject'] = array("Reservierungsobjekt darstellen","Das Reservierungsobjekt wird standardmäßig dargestellt. Es gibt Szenarien in denen das Ausblenden sinnvoll ist.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['showSignatureField'] = array("Unterschriftenfeld (nur Gruppenansicht)","Möglichkeit eine Unterschrift abzufragen. Zurzeit nur in der Gruppenliste möglich.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['appearance_themeroller_css'] = array('jQuery UI ThemeRoller CSS Datei', 'Optional: wählen Sie eine, mit dem jQuery UI ThemeRoller erstellte, CSS-Datei aus, um den Stil des Moduls entsprechend anzupassen.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['reservationButtonCaption'] = array('Buttonbeschriftung','Hiermit können Sie den Button Text ändern. Bspw. "Zahlungspflichtig reservieren".');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['redirect_site'] = array('Weiterleitungsseite', 'Nach der Buchung wird hierhin weitergeleitet.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['speaker_redirect_site'] = array('Referentenweiterleitung', 'Falls Sie die Referenten verknüpfen wollen, können Sie hier die Weiterleitung einstellen.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['privacy_policy_site'] = array('Link zur Datenschutzerklärung', 'Diese Seite wird für die Bestätigung der Datenschutzerklärung verlinkt.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['privacy_policy_text'] = array('Datenschutzkommentar', 'Dieser Datenschutzkommentar taucht vor der Checkbox zur Einwilligung auf.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['notification_type'] = array('Benachrichtigung', 'Wählen Sie die Benachrichtigung aus. Die Einstellung kann in der Reservierungsart überschrieben werden.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['notification_type_contact_request']  = array('Benachrichtigung der Kontaktanfrage', 'Wählen Sie die Benachrichtigung für das Versenden der Kontaktanfrage aus');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['additionaldatas'] = array("Datenfelder","Reihenfolge hier entspricht der Anzeige im Frontend. Bis auf die Überschrift, kann jedes Feld nur einmal verwendet werden.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['mandatory'] = array("Pflichtfeld?","");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['binding'] = array("Pflichtfeld?","Soll dieses Datenfeld als Pflichtfeld im Frontend angezeigt werden");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['initialValue'] = array("Initialer Wert", "Hier können Sie einen initialen Wert eingeben. Wichtig bei der Überschrift - ansonsten optional.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['hide_selection'] = array("Datenfelder Hinzufügen","Achtung! Vorname, Nachname und E-Mail werden angehangen, falls diese fehlen. Die drei Felder sind immer erforderlich.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['additionalDuration'] = array("Individuelle Verweildauer in Minuten (maximal)","Bei 0 ist keine individuelle Eingabe der Verweildauer in Minuten möglich (Standard).");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['withCapacity'] = array("Kunde kann Personenanzahl angeben","Der Kunde kann die Personenanzahl angeben. Dieses wird bspw. für eine Tischreservierung im Restaurant benötigt.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['showFreeSeats'] = array("Freie Plätze anzeigen","Offene Plätze werden dargestellt.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['showEndTime'] = array("Die Endzeiten werden mit ausgegeben","Die Endzeiten werden mit dargestellt (abhängig von der Konfiguration).");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['showPrices'] = array("Preise anzeigen","Preise werden dargestellt.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['showDateTime'] = array("Termin am Objekt anzeigen","Zeigt den ausgewählten Termin direkt am Objekt an.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['showMemberData'] = array("Mitgliederdaten übernehmen","Vorhandene Mitgliederdaten werden automatisch in den Formularfeldern vorbelegt.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['removeBookedDays'] = array("Ausgebuchte Tage im Kalender sperren","Der Kalendertag ist ausgebucht nicht auswählbar. Achtung! Die Überprüfung ist zum Teil langwirrig.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['emptyOptionLabel'] = array("Text leere Objektliste (optional)","Der Text der Selectbox kann optional überschrieben werden.");
//$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['overAllTypesReservation'] = array("Übergreifende Reservierungen","Diese Einstellung ist wichtig, wenn es um Verfügbarkeit von Personal geht, um parallele Buchungen über mehrere Reservierungsarten hinweg zu vermeiden.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['specialParticipantMechanism'] = array("Teilnehmermechanismus (optional)","Ist dieser Mechanismus aktiviert, werden die Teilnehmerfelder anhand der Personenzahl generiert.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references']['publicview'] = 'Öffentlich (Reservierungen werden sichtbar)';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references']['memberview'] = 'Mitgliedeansicht (Nur Reservierungen für angemeldetes Mitglied, nicht editierbar) [erfordert con4gis/groups]';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references']['member'] = 'Mitgliederbasiert (Nur Reservierungen für angemeldetes Mitglied, editierbar) [erfordert con4gis/groups]';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references']['group'] = 'Gruppenbasiert (Nur Reservierungen für Gruppen des angemeldeten Mitglieds) [erfordert con4gis/groups]';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references']['tiles'] = 'Kachelliste';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references']['table'] = 'Tabelle (Data table)';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references']['list'] = 'Listenelemente';