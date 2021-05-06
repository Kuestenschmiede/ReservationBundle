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

$str = 'tl_c4g_reservation_event';

$GLOBALS['TL_LANG'][$str]['id'] = array("#", "");
$GLOBALS['TL_LANG'][$str]['pid'] = array("Contao Event", "Select a Contao event.");
$GLOBALS['TL_LANG'][$str]['number'] = array("Number", "Here you can enter an identifier for your event. For example a seminar number.");
$GLOBALS['TL_LANG'][$str]['reservationType'] = array("Reservation type", "What type of reservation is it?");
$GLOBALS['TL_LANG'][$str]['location'] = array("Event location", "Where will the event take place?");
$GLOBALS['TL_LANG'][$str]['speaker'] = array("Speaker", "Who leads the event?");
$GLOBALS['TL_LANG'][$str]['topic'] = array("Topics", "Here you can link your event topics.");
$GLOBALS['TL_LANG'][$str]['targetAudience'] = array("Target audience", "Select your target audience.");
$GLOBALS['TL_LANG'][$str]['minParticipants'] = array("Minimum number of participants", "Default: 1");
$GLOBALS['TL_LANG'][$str]['maxParticipants'] = array("Maximum number of participants", "Default: 0 (unlimited)");
$GLOBALS['TL_LANG'][$str]['state'] = array("Current status", "green, orange, red");

/** LEGENDS **/
$GLOBALS['TL_LANG'][$str]['event_legend'] = "Event data";
$GLOBALS['TL_LANG'][$str]['reservation_legend'] = "Reservation data";

/** OPERATIONS **/
$GLOBALS['TL_LANG'][$str]['new'] = array("Add Event", "Add Event");
$GLOBALS['TL_LANG'][$str]['edit'] = array("Edit event", "Edit event ID %s");
$GLOBALS['TL_LANG'][$str]['copy'] = array("Copy event", "Copy event ID %s");
$GLOBALS['TL_LANG'][$str]['delete'] = array("Delete event", "Delete event ID %s");
$GLOBALS['TL_LANG'][$str]['show'] = array("Display event", "Display event ID %s");
$GLOBALS['TL_LANG'][$str]['TOGGLE'] = array("Activate event", "Activate event ID %s");

/** INFO */
$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['infoEvent'] = "";