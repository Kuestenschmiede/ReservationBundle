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
$GLOBALS['TL_LANG']['tl_module']['reservation_legend'] = 'Reservierungseinstellungen';
$GLOBALS['TL_LANG']['tl_module']['list_legend'] = 'Listeneinstellungen';
$GLOBALS['TL_LANG']['tl_module']['reservation_notification_center_legend'] = 'Notification Center';
$GLOBALS['TL_LANG']['tl_module']['reservation_redirect_legend'] = 'Weiterleitung';
$GLOBALS['TL_LANG']['tl_module']['reservation_objects_legend'] = 'Einstellungen zur Objektpflege';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['reservation_object_types'] = array('Welche Arten dürfen zur Auswahl stehen?','Auswahl der Arten, die für die Objektpflege verwendet werden sollen.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['reservation_settings'] = array('Auswahl Reservierungsformular', 'Wählen Sie Ihre Konfiguration aus dem Backendmodul Reservierungsformular aus.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['notification_type_contact_request'] = array('Benachrichtigung der Kontaktanfrage', 'Wählen Sie die Benachrichtigung für das Versenden der Kontaktanfrage aus');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['reservation_types'] = array('Reservierungsarten', 'Wählen Sie die bei der Reservierung im Frontend zu berücksichigenden Reservierungsarten (Räume, Tische, ...). Bei keiner Auswahl werden alle Reservierungsobjekte geladen.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['reservationView'] = array('Listenansicht', 'Verschiedene Ansichten der Liste sind möglich. Standard: Öffentlich');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['renderMode'] = array('Listendarstellung', 'Verschiedene Darstellungen der Liste sind möglich. Standard: Kacheln');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['showReservationType'] = array("Reservierungsart darstellen","Die Reservierungsart wird standardmäßig in Liste und Detail nicht dargestellt. Das können Sie hier ändern.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['showReservationObject'] = array("Reservierungsobjekt darstellen","Das Reservierungsobjekt wird standardmäßig dargestellt. Es gibt Szenarien in denen das Ausblenden sinnvoll ist.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['showSignatureField'] = array("Unterschriftenfeld (nur Gruppenansicht)","Möglichkeit eine Unterschrift abzufragen. Zurzeit nur in der Gruppenliste möglich.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['redirect_site'] = array('Weiterleitungsseite', 'Nach der Buchung wird hierhin weitergeleitet.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['login_redirect_site'] = array('Weiterleitung zur Anmeldeseite', 'Falls das Listenmodul nicht öffentlich ist.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['event_redirect_site'] = array('Terminweiterleitung', 'Hier können Sie die Weiterleitung zum Eventleser einstellen, falls Sie die Events am Referenten darstellen wollen.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['cancellation_redirect_site'] = array('Weiterleitung zur Stornierung', 'Hierüber können Sie direkt einen Stornierungsbutton anbieten');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['notification_type'] = array('Benachrichtigung', 'Wählen Sie die Benachrichtigung aus. Die Einstellung kann in der Reservierungsart überschrieben werden.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['reservation_add_member_location'] = array('Automatisch Veranstaltungsort für Mitglied setzen', 'Setzt automatisch einen neuen Veranstaltungsort für pflegende Mitglieder.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['additionaldatas'] = array("Datenfelder","Reihenfolge hier entspricht der Anzeige im Frontend. Bis auf die Überschrift, kann jedes Feld nur einmal verwendet werden.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['mandatory'] = array("Pflichtfeld?","");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['binding'] = array("Pflichtfeld?","Soll dieses Datenfeld als Pflichtfeld im Frontend angezeigt werden");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['initialValue'] = array("Initialer Wert", "Hier können Sie einen initialen Wert eingeben. Wichtig bei der Überschrift - ansonsten optional.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references']['publicview'] = 'Öffentlich (Reservierungen werden sichtbar)';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references']['memberview'] = 'Mitgliedeansicht (Nur Reservierungen für angemeldetes Mitglied, nicht editierbar) [erfordert con4gis/groups]';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references']['member'] = 'Mitgliederbasiert (Nur Reservierungen für angemeldetes Mitglied, editierbar) [erfordert con4gis/groups]';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references']['group'] = 'Gruppenbasiert (Nur Reservierungen für Gruppen des angemeldeten Mitglieds) [erfordert con4gis/groups]';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references']['tiles'] = 'Kachelliste';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references']['table'] = 'Tabelle (Data table)';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references']['list'] = 'Listenelemente';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['printTpl'] = ["Druck-Template", "Hierüber können Sie ein eigenes Drucktemplate hinterlegen (Standard: pdf_c4g_brick)."];
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['removeListImage'] = ['Bild in der Liste ausblenden','Das Bild wird nicht mehr in der Referentenliste dargestellt.'];
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['withMap'] = ['Standortkarte im Formular anzeigen', 'Wichtig: con4gis/maps muss instaliert sein. In den Dastboard Einstellung muss ein Karteninhaltselement verknüpft sein (das Inhaltselement kann im Artikel unsichtbar sein). Außerdem muss im Kartenprofil der Frontend-Geopicker aktiv sein.'];
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['postals'] = ["Postleitzahlen einschränken","Hierüber können Sie ermöglichen, dass die Objekte nur mit bestimmten Postleitzahlen merstellt und veröffenlticht werden dürfen (kommagetrennte Liste)."];
