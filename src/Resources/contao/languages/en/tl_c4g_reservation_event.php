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

$str = 'tl_c4g_reservation_event';

$GLOBALS['TL_LANG'][$str]['id'] = array("#", "");
$GLOBALS['TL_LANG'][$str]['pid'] = array("Contao Event", "Select a Contao event.");
$GLOBALS['TL_LANG'][$str]['number'] = array("Number", "Here you can enter an identifier for your event. For example a seminar number.");
$GLOBALS['TL_LANG'][$str]['reservationType'] = array("Reservation type", "What type of reservation is it?");
$GLOBALS['TL_LANG'][$str]['location'] = array("Event location", "Where will the event take place?");
$GLOBALS['TL_LANG'][$str]['organizer'] = array("Organizer", "Who is the organizer?");
$GLOBALS['TL_LANG'][$str]['speaker'] = array("Speaker", "Who leads the event?");
$GLOBALS['TL_LANG'][$str]['topic'] = array("Topics", "Here you can link your event topics.");
$GLOBALS['TL_LANG'][$str]['targetAudience'] = array("Target audience", "Select your target audience.");
$GLOBALS['TL_LANG'][$str]['minParticipants'] = array("Minimum number of participants", "Minimum number of participants for the event to take place. Default: 1.");
$GLOBALS['TL_LANG'][$str]['maxParticipants'] = array("Maximum number of participants", "Maximum number of participants for the event. Default: 0 (unlimited). If you want to use the participant mechanism in the form, you should limit the number of participants (max. 10 people).");
$GLOBALS['TL_LANG'][$str]['maxParticipantsPerEventBooking'] = array("Maximum number of participants per booking", "Here you can limit the number of participants in the entry per booking.");
$GLOBALS['TL_LANG'][$str]['min_reservation_day'] = array("Earliest reservation date (in days)", "Today + how many days?");
$GLOBALS['TL_LANG'][$str]['state'] = array("Current status", "green, orange, red");

$GLOBALS['TL_LANG'][$str]['participant_params'] = array("Participant options inc. tax rate", "Select participants options");
$GLOBALS['TL_LANG'][$str]['participantParamsFieldType'] = array("Field type", "Select the participant params field type in the reservation form.");
$GLOBALS['TL_LANG'][$str]['price'] = array("Price", "Specify the price for the booking (for example: 50.00).");
$GLOBALS['TL_LANG'][$str]['taxOptions'] = ["Select Tax Rate option", "Select a tax option for all prices."];
$GLOBALS['TL_LANG'][$str]['priceoption'] = array("Price setting", "What should the price be calculated by.");

$GLOBALS['TL_LANG'][$str]['reservationForwarding'] = ["Redirect to module", "Select the page where the reservation module is located. Can also be set globally on the calendar and in the dashboard/settings."];
$GLOBALS['TL_LANG'][$str]['reservationForwardingButtonCaption'] = ["Button label forwarding", "Labeling of the forwarding button to the reservation. Default: empty. The entries from the language files apply."];


$GLOBALS['TL_LANG'][$str]['multi'] = 'Multiple selection (multi-checkbox)';
$GLOBALS['TL_LANG'][$str]['radio'] = 'Single selection (radio group)';
$GLOBALS['TL_LANG'][$str]['references']['tNone'] = "Without Tax";
$GLOBALS['TL_LANG'][$str]['references']['tStandard'] = "Standard";
$GLOBALS['TL_LANG'][$str]['references']['tReduced'] = "Reduced";

$GLOBALS['TL_LANG'][$str]['references']['pMin'] = array("Price per minute");
$GLOBALS['TL_LANG'][$str]['references']['pHour'] = array("Price per hour");
$GLOBALS['TL_LANG'][$str]['references']['pDay'] = array("Price per day");
$GLOBALS['TL_LANG'][$str]['references']['pNight'] = array("Price per night");
$GLOBALS['TL_LANG'][$str]['references']['pNightPerson'] = array("Price per night and person");
$GLOBALS['TL_LANG'][$str]['references']['pWeek'] = array("Price per week");
$GLOBALS['TL_LANG'][$str]['references']['pReservation'] = array("Price per reservation");
$GLOBALS['TL_LANG'][$str]['references']['pPerson'] = array("Price per person");
$GLOBALS['TL_LANG'][$str]['references']['pAmount'] = array("Security amount (pledge)");

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