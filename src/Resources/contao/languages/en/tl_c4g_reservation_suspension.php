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

$GLOBALS['TL_LANG'][$str]['caption'] = array("Description", "Enter a description for the suspension list.");
$GLOBALS['TL_LANG'][$str]['showCaption'] = array("Show description in occupancy plan", "Shows the description in the occupancy plan.");
$GLOBALS['TL_LANG'][$str]['showComment'] = array("Show comments in occupancy plan", "Shows the comments for the suspension dates in the occupancy plan.");
$GLOBALS['TL_LANG'][$str]['showCompany'] = array("Show company name in occupancy plan", "Shows the company name in the occupancy plan.");
$GLOBALS['TL_LANG'][$str]['suspension_dates'] = array("Suspension dates", "Add the days on which no bookings should be possible.");
$GLOBALS['TL_LANG'][$str]['date'] = array("Date", "");
$GLOBALS['TL_LANG'][$str]['comment'] = array("Comment", "Optional comment (e.g. reason for suspension).");
$GLOBALS['TL_LANG'][$str]['company'] = array("Company name", "Enter the company name here.");

/** LEGENDS **/
$GLOBALS['TL_LANG'][$str]['suspension_legend'] = "General settings";
$GLOBALS['TL_LANG'][$str]['suspension_dates_legend'] = "Configure suspension dates";

/** OPERATIONS **/
$GLOBALS['TL_LANG'][$str]['new'] = array("Create suspension list", "Creates a new suspension list.");
$GLOBALS['TL_LANG'][$str]['edit'] = array("Edit suspension list", "Edit suspension list ID %s");
$GLOBALS['TL_LANG'][$str]['copy'] = array("Copy suspension list", "Copy suspension list ID %s");
$GLOBALS['TL_LANG'][$str]['delete'] = array("Delete suspension list", "Delete suspension list ID %s");
$GLOBALS['TL_LANG'][$str]['show'] = array("Show suspension list", "Show suspension list ID %s");

$GLOBALS['TL_LANG'][$str]['date_range_wizard'] = array("Add period", "Opens a dialog to automatically enter a date range into the list.");
$GLOBALS['TL_LANG'][$str]['date_range_start'] = "Start date";
$GLOBALS['TL_LANG'][$str]['date_range_end'] = "End date";
