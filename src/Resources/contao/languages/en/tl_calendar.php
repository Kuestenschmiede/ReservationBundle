<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @author con4gis contributors (see "authors.md")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

$str = 'tl_calendar';

$GLOBALS['TL_LANG'][$str]['activateEventReservation'] = ['Activate event reservation (con4gis)', 'Activates the event reservation via con4gis Reservation.'];
$GLOBALS['TL_LANG'][$str]['reservationForwarding'] = ["Forwarding to module", "Select the page where the reservation module is located. If you want the forwarding to apply across calendars, then you can also make the setting in the con4gis Dashboard."];
$GLOBALS['TL_LANG'][$str]['reservationForwardingButtonCaption'] = ["Button label forwarding", "Labeling of the forwarding button to the reservation. Default: empty. The entries from the language files or the cross-calendar setting from the con4gis Dashboard apply."];

$GLOBALS['TL_LANG'][$str]['c4g_reservation_legend'] = "Reservation settings";

$GLOBALS['TL_LANG'][$str]['reservationType'] = array("Preset reservation type", "What type of reservation is it?");
$GLOBALS['TL_LANG'][$str]['location'] = array("Preset event location", "Where will the event take place?");
$GLOBALS['TL_LANG'][$str]['organizer'] = array("Preset organizer", "Who is the organizer?");
$GLOBALS['TL_LANG'][$str]['speaker'] = array("Preset speaker", "Who leads the event?");
$GLOBALS['TL_LANG'][$str]['topic'] = array("Preset topics", "Here you can link your event topics.");
$GLOBALS['TL_LANG'][$str]['targetAudience'] = array("Preset target audience", "Select your target audience.");
$GLOBALS['TL_LANG'][$str]['minParticipants'] = array("Preset minimum number of participants", "Minimum number of participants for the event to take place. Default: 1.");
$GLOBALS['TL_LANG'][$str]['maxParticipants'] = array("Preset maximum number of participants", "Maximum number of participants for the event. Default: 0 (unlimited)");
$GLOBALS['TL_LANG'][$str]['price'] = array("Preset price", "Specify the price for the booking (for example: 50.00).");
$GLOBALS['TL_LANG'][$str]['priceoption'] = array("Preset price setting", "What should the price be calculated by.");

$GLOBALS['TL_LANG'][$str]['references']['pMin'] = array("Price per minute");
$GLOBALS['TL_LANG'][$str]['references']['pHour'] = array("Price per hour");
$GLOBALS['TL_LANG'][$str]['references']['pDay'] = array("Price per day");
$GLOBALS['TL_LANG'][$str]['references']['pNight'] = array("Price per night");
$GLOBALS['TL_LANG'][$str]['references']['pNightPerson'] = array("Price per night and person");
$GLOBALS['TL_LANG'][$str]['references']['pWeek'] = array("Price per week");
$GLOBALS['TL_LANG'][$str]['references']['pReservation'] = array("Price per reservation");
$GLOBALS['TL_LANG'][$str]['references']['pPerson'] = array("Price per person");
$GLOBALS['TL_LANG'][$str]['references']['pAmount'] = array("Security amount (pledge)");