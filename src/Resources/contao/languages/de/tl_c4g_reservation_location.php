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

$str = 'tl_c4g_reservation_location';

$GLOBALS['TL_LANG'][$str]['id'] = array("#", "");
$GLOBALS['TL_LANG'][$str]['name'] = array("Name des Veranstaltungsorts", "Hierüber wird der Veranstaltungsort an verschiedenen Stellen dargestellt.");
$GLOBALS['TL_LANG'][$str]['locgeox'] = array("X-Koordinate","Geben Sie die X-Koordinate an.");
$GLOBALS['TL_LANG'][$str]['locgeoy'] = array("Y-Koordinate","Geben Sie die Y-Koordinate an.");

$GLOBALS['TL_LANG'][$str]['contact_name'] = array("Kontaktname","");
$GLOBALS['TL_LANG'][$str]['contact_phone'] = array("Telefonnummer","");
$GLOBALS['TL_LANG'][$str]['contact_email'] = array("Emailadresse","");
$GLOBALS['TL_LANG'][$str]['contact_street'] = array("Straße","");
$GLOBALS['TL_LANG'][$str]['contact_postal'] = array("Postleitzahl","");
$GLOBALS['TL_LANG'][$str]['contact_city'] = array("Ort","");
$GLOBALS['TL_LANG'][$str]['ics'] = array("Termindaten als ics file generieren","Wenn die Checkbox ausgewählt ist, können die Daten als ICS Datei in die Reservierungsbestätigung gelegt werden.");
$GLOBALS['TL_LANG'][$str]['icsAlert'] = array("Automatische Kalendererinnerung","Anzahl der Stunden bevor Termin beginnt");
$GLOBALS['TL_LANG'][$str]['icsPath'] = array("Pfad zu den ICS Dateien", "Wo können die ICS Dateien abgelegt werden?");

/** LEGENDS **/
$GLOBALS['TL_LANG'][$str]['location_legend'] = "Veranstaltungsort bearbeiten";
$GLOBALS['TL_LANG'][$str]['contact_legend'] = "Kontaktdaten bearbeiten";

/** OPERATIONS **/
$GLOBALS['TL_LANG'][$str]['new'] = array("Veranstaltungsort hinzufügen","Veranstaltungsort hinzufügen");
$GLOBALS['TL_LANG'][$str]['edit'] = array("Veranstaltungsort bearbeiten","Bearbeiten des Veranstaltungsorts ID %s");
$GLOBALS['TL_LANG'][$str]['copy'] = array("Veranstaltungsort kopieren","Kopieren des Veranstaltungsorts ID %s");
$GLOBALS['TL_LANG'][$str]['delete'] = array("Veranstaltungsort löschen","Löschen des Veranstaltungsorts ID %s");
$GLOBALS['TL_LANG'][$str]['show'] = array("Veranstaltungsort anzeigen","Anzeigen des Veranstaltungsorts ID %s");
$GLOBALS['TL_LANG'][$str]['TOGGLE'] = array("Veranstaltungsort aktivieren","Aktivieren des Veranstaltungsorts ID %s");
