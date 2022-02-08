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

$str = 'tl_c4g_reservation_settings';

$GLOBALS['TL_LANG'][$str]['id'] = array("#", "");
$GLOBALS['TL_LANG'][$str]['caption'] = array("Designation", "For selection on the front end module.");

$GLOBALS['TL_LANG'][$str]['reservation_types'] = array('Reservation types', 'Select the reservation types (rooms, tables, ...) to be taken into account when making reservations in the frontend. If no selection is made, all reservation objects will be loaded. You should not assign too many types (max. 10).');
$GLOBALS['TL_LANG'][$str]['typeHide'] = array('Hide reservation type', 'if only one reservation type is available for selection, then the field can be hidden via this.');
$GLOBALS['TL_LANG'][$str]['reservationButtonCaption'] = array('Button label','Allows you to change the button text. For example, "Reserve with payment".');
$GLOBALS['TL_LANG'][$str]['redirect_site'] = array('Forwarding page', 'After booking will be forwarded here.');
$GLOBALS['TL_LANG'][$str]['speaker_redirect_site'] = array('Speaker forwarding', 'If you want to link the speakers, you can set the forwarding here.');
$GLOBALS['TL_LANG'][$str]['login_redirect_site'] = array('Redirect to login page', 'If the list module is not public.');
$GLOBALS['TL_LANG'][$str]['privacy_policy_site'] = array('Link to privacy policy', 'This page is linked for privacy policy confirmation.');
$GLOBALS['TL_LANG'][$str]['privacy_policy_text'] = array('Privacy comment', 'This privacy comment appears before the consent checkbox.');
$GLOBALS['TL_LANG'][$str]['notification_type'] = array('Notification', 'Select the notification. The setting can be overwritten in the reservation type.');
$GLOBALS['TL_LANG'][$str]['additionaldatas'] = array("Data fields", "The order here corresponds to the display in the frontend. Except for the heading, each field can be used only once.");
$GLOBALS['TL_LANG'][$str]['mandatory'] = array("Mandatory field?","");
$GLOBALS['TL_LANG'][$str]['binding'] = array("Mandatory field?", "Should this data field be displayed as a mandatory field in the frontend?");
$GLOBALS['TL_LANG'][$str]['initialValue'] = array("Initial value", "Here you can enter an initial value. Important for the heading - otherwise optional.");
$GLOBALS['TL_LANG'][$str]['fieldSelection'] = array("Add data fields", "Attention! First name, last name and e-mail are appended if they are missing. The three fields are always required.");
$GLOBALS['TL_LANG'][$str]['additionalDuration'] = array("Individual length of stay in minutes (maximum)", "If 0, no individual entry of the length of stay in minutes is possible (default).");
$GLOBALS['TL_LANG'][$str]['withCapacity'] = array("Customer can specify number of persons", "The customer can specify the number of persons. This is required, for example, for a table reservation in the restaurant.");
$GLOBALS['TL_LANG'][$str]['showFreeSeats'] = array("Show open seats", "Open seats are displayed.");
$GLOBALS['TL_LANG'][$str]['showEndTime'] = array("The end times are displayed with", "The end times are displayed with (depending on the configuration).");
$GLOBALS['TL_LANG'][$str]['showPrices'] = array("Show prices", "Prices are displayed.");
$GLOBALS['TL_LANG'][$str]['showDateTime'] = array("Show appointment at object", "Shows the selected appointment directly at the object.");
$GLOBALS['TL_LANG'][$str]['showMemberData'] = array("Apply member data", "Existing member data is automatically pre-populated in the form fields.");
$GLOBALS['TL_LANG'][$str]['showDetails'] = array("Show object details", "Additional information such as image and description are displayed.");
$GLOBALS['TL_LANG'][$str]['removeBookedDays'] = array("Lock fully booked days in calendar", "The calendar day is fully booked cannot be selected. Attention. The check is partly lengthy.");
$GLOBALS['TL_LANG'][$str]['emptyOptionLabel'] = array("Text empty object list (optional)", "The text of the selectbox can optionally be overwritten.");
$GLOBALS['TL_LANG'][$str]['specialParticipantMechanism'] = array("Participant mechanism (optional)", "If this mechanism is enabled, the participant fields are generated based on the number of people.");

$GLOBALS['TL_LANG'][$str]['referencesObjectType'][1] = 'Time selection';
$GLOBALS['TL_LANG'][$str]['referencesObjectType'][2] = 'Events';
$GLOBALS['TL_LANG'][$str]['referencesObjectType'][3] = 'Object selection';

/** LEGENDS **/
$GLOBALS['TL_LANG'][$str]['settings_legend'] = "General settings";
$GLOBALS['TL_LANG'][$str]['type_legend'] = "Reservation type settings";
$GLOBALS['TL_LANG'][$str]['object_legend'] = "Reservation object settings";
$GLOBALS['TL_LANG'][$str]['form_legend'] = "Form settings";
$GLOBALS['TL_LANG'][$str]['notification_legend'] = 'Notification Center';
$GLOBALS['TL_LANG'][$str]['redirect_legend'] = 'Redirection';
$GLOBALS['TL_LANG'][$str]['expert_legend'] = 'Expert settings';

/** OPERATIONS **/
$GLOBALS['TL_LANG'][$str]['new'] = array("Add settings","Add settings");
$GLOBALS['TL_LANG'][$str]['edit'] = array("Edit settings","Edit settings ID %s");
$GLOBALS['TL_LANG'][$str]['copy'] = array("Copy settings","Copy settings ID %s");
$GLOBALS['TL_LANG'][$str]['delete'] = array("Delete settings","Delete settings ID %s");
$GLOBALS['TL_LANG'][$str]['show'] = array("Show settings","Show settings ID %s");
$GLOBALS['TL_LANG'][$str]['TOGGLE'] = array("Activate settings","Activate settings ID %s");
