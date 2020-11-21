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

$str = 'tl_c4g_reservation_event';

$GLOBALS['TL_LANG'][$str]['id'] = array("#", "");
$GLOBALS['TL_LANG'][$str]['pid'] = array("Contao Event", "Wählen Sie ein Contao Event.");
$GLOBALS['TL_LANG'][$str]['number'] = array("Nummer", "Hier können Sie eine Kennung für Ihre Veranstaltung eingeben. Bspw. eine Seminarnummer.");
$GLOBALS['TL_LANG'][$str]['reservationType'] = array("Reservierungsart", "Um welche Reservierungsart handelt es sich");
$GLOBALS['TL_LANG'][$str]['speaker'] = array("Referent(en)", "Wer leitet die Veranstaltung?");
$GLOBALS['TL_LANG'][$str]['topic'] = array("Thema/Themen", "Hier können Sie Ihre Veranstaltungsthemen verknüpfen.");
$GLOBALS['TL_LANG'][$str]['targetAudience'] = array("Zielgruppe(n)", "Wählen Sie Ihr Zielpublikum.");
$GLOBALS['TL_LANG'][$str]['minParticipants'] = array("Minimale Teilnehmerzahl", "Standard: 1");
$GLOBALS['TL_LANG'][$str]['maxParticipants'] = array("Maximale Teilnehmerzahl", "Standard: 0 (unbegrenzt)");
$GLOBALS['TL_LANG'][$str]['state'] = array("Aktueller Status", "grün, gelb, rot");



/** LEGENDS **/
$GLOBALS['TL_LANG'][$str]['event_legend'] = "Daten zur Veranstaltung";
$GLOBALS['TL_LANG'][$str]['reservation_legend'] = "Daten zur Reservierung";

/** OPERATIONS **/
$GLOBALS['TL_LANG'][$str]['new'] = array("Event hinzufügen","Event hinzufügen");
$GLOBALS['TL_LANG'][$str]['edit'] = array("Event bearbeiten","Bearbeiten der Veranstaltung ID %s");
$GLOBALS['TL_LANG'][$str]['copy'] = array("Event kopieren","Kopieren der Veranstaltung ID %s");
$GLOBALS['TL_LANG'][$str]['delete'] = array("Event löschen","Löschen der Veranstaltung ID %s");
$GLOBALS['TL_LANG'][$str]['show'] = array("Event anzeigen","Anzeigen der Veranstaltung ID %s");
$GLOBALS['TL_LANG'][$str]['TOGGLE'] = array("Event aktivieren","Aktivieren der Veranstaltung ID %s");
