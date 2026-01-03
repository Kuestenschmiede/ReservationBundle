<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @author con4gis contributors (see "authors.md")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

$str = 'tl_c4g_reservation_location';

$GLOBALS['TL_LANG'][$str]['id'] = array("#", "");
$GLOBALS['TL_LANG'][$str]['name'] = array("Name of the location", "This displays the location at various places");
$GLOBALS['TL_LANG'][$str]['alias'] = array("Alias", "The alias is used in the frontend instead of the ID for detail views and parameters.");
$GLOBALS['TL_LANG'][$str]['locgeox'] = array("X coordinate", "Specify the X coordinate.");
$GLOBALS['TL_LANG'][$str]['locgeoy'] = array("Y coordinate", "Specify the Y coordinate.");

$GLOBALS['TL_LANG'][$str]['contact_name'] = array("contactname","");
$GLOBALS['TL_LANG'][$str]['contact_phone'] = array("Phone","");
$GLOBALS['TL_LANG'][$str]['contact_email'] = array("Email","");
$GLOBALS['TL_LANG'][$str]['contact_website'] = array("Website","");
$GLOBALS['TL_LANG'][$str]['contact_street'] = array("Street","");
$GLOBALS['TL_LANG'][$str]['contact_postal'] = array("Postal","");
$GLOBALS['TL_LANG'][$str]['contact_city'] = array("City","");
$GLOBALS['TL_LANG'][$str]['ics'] = array("Generate address data as ics file", "If the checkbox is selected, the data can be placed as ics file in the reservation confirmation.");
$GLOBALS['TL_LANG'][$str]['icsAlert'] = array("Automatic calendar reminder", "Number of hours before appointment starts");
$GLOBALS['TL_LANG'][$str]['icsPath'] = array("ics path", "Where can the ics files be stored?");

/** LEGENDS **/
$GLOBALS['TL_LANG'][$str]['location_legend'] = "Edit location";
$GLOBALS['TL_LANG'][$str]['contact_legend'] = "Edit contact data";

/** OPERATIONS **/
$GLOBALS['TL_LANG'][$str]['new'] = array("Add location", "Add location");
$GLOBALS['TL_LANG'][$str]['edit'] = array("Edit location", "Edit location ID %s");
$GLOBALS['TL_LANG'][$str]['copy'] = array("Copy location", "Copy the location ID %s");
$GLOBALS['TL_LANG'][$str]['delete'] = array("Delete location", "Delete the location ID %s");
$GLOBALS['TL_LANG'][$str]['show'] = array("Show location", "Showing the location ID %s");
$GLOBALS['TL_LANG'][$str]['TOGGLE'] = array("Enable location", "Enabling the location ID %s");
