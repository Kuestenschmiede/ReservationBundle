<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @author con4gis contributors (see "authors.md")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

$str = 'tl_c4g_reservation_suspension';

$GLOBALS['TL_LANG'][$str]['caption'] = array("Bezeichnung", "Geben Sie eine Bezeichnung für die Sperrtage-Liste ein.");
$GLOBALS['TL_LANG'][$str]['showCaption'] = array("Bezeichnung im Belegungsplan anzeigen", "Zeigt die Bezeichnung im Belegungsplan an.");
$GLOBALS['TL_LANG'][$str]['showComment'] = array("Kommentare im Belegungsplan anzeigen", "Zeigt die Kommentare zu den Sperrtagen im Belegungsplan an.");
$GLOBALS['TL_LANG'][$str]['suspension_dates'] = array("Sperrtage", "Fügen Sie hier die Tage hinzu, an denen keine Buchungen möglich sein sollen.");
$GLOBALS['TL_LANG'][$str]['date'] = array("Datum", "");
$GLOBALS['TL_LANG'][$str]['comment'] = array("Kommentar", "Optionaler Kommentar (z.B. Grund der Sperrung).");

/** LEGENDS **/
$GLOBALS['TL_LANG'][$str]['suspension_legend'] = "Allgemeine Einstellungen";
$GLOBALS['TL_LANG'][$str]['suspension_dates_legend'] = "Sperrtage konfigurieren";

/** OPERATIONS **/
$GLOBALS['TL_LANG'][$str]['new'] = array("Sperrtage-Liste erstellen", "Erstellt eine neue Sperrtage-Liste.");
$GLOBALS['TL_LANG'][$str]['edit'] = array("Sperrtage-Liste bearbeiten", "Bearbeiten der Sperrtage-Liste ID %s");
$GLOBALS['TL_LANG'][$str]['copy'] = array("Sperrtage-Liste kopieren", "Kopieren der Sperrtage-Liste ID %s");
$GLOBALS['TL_LANG'][$str]['delete'] = array("Sperrtage-Liste löschen", "Löschen der Sperrtage-Liste ID %s");
$GLOBALS['TL_LANG'][$str]['show'] = array("Sperrtage-Liste anzeigen", "Anzeigen der Sperrtage-Liste ID %s");

$GLOBALS['TL_LANG'][$str]['date_range_wizard'] = array("Zeitraum hinzufügen", "Öffnet einen Dialog, um einen Datumsbereich automatisch in die Liste einzutragen.");
$GLOBALS['TL_LANG'][$str]['date_range_start'] = "Startdatum";
$GLOBALS['TL_LANG'][$str]['date_range_end'] = "Enddatum";
