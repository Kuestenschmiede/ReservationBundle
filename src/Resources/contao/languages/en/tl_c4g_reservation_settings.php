<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 10
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2025, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

$str = 'tl_c4g_reservation_settings';

$GLOBALS['TL_LANG'][$str]['id'] = array("#", "");
$GLOBALS['TL_LANG'][$str]['caption'] = array("Designation", "For selection on the front end module.");

$GLOBALS['TL_LANG'][$str]['reservation_types'] = array('Reservation types', 'Select the reservation types (rooms, tables, ...) to be taken into account when making reservations in the frontend. If no selection is made, all reservation objects will be loaded. You should not assign too many types (max. 10).');
$GLOBALS['TL_LANG'][$str]['typeDefault'] = array('Preselect reservation type', 'Here you can specify which type is to be preselected as in the selection box. If no type is selected here, then the default takes effect.');
$GLOBALS['TL_LANG'][$str]['typeHide'] = array('Hide reservation type', 'if only one reservation type is available for selection, then the field can be hidden via this.');
$GLOBALS['TL_LANG'][$str]['objectHide'] = array('Hide reservation object', 'Important!! Only useful when using insert tags. For example, "{{c4gevent::title}}" . If this option is enabled, the reservation name will not be displayed in the frontend.');
$GLOBALS['TL_LANG'][$str]['reservationButtonCaption'] = array('Button label','Allows you to change the button text. For example, "Reserve with payment".');
$GLOBALS['TL_LANG'][$str]['redirect_site'] = array('Forwarding page', 'After booking will be forwarded here.');
$GLOBALS['TL_LANG'][$str]['speaker_redirect_site'] = array('Speaker forwarding', 'If you want to link the speakers, you can set the forwarding here.');
$GLOBALS['TL_LANG'][$str]['location_redirect_site'] = array('Location forwarding', 'If you want to link the locations, you can set the forwarding here.');
$GLOBALS['TL_LANG'][$str]['login_redirect_site'] = array('Redirect to login page', 'If the list module is not public.');
$GLOBALS['TL_LANG'][$str]['privacy_policy_site'] = array('Link to privacy policy', 'This page is linked for privacy policy confirmation.');
$GLOBALS['TL_LANG'][$str]['privacy_policy_text'] = array('Privacy comment', 'This privacy comment appears before the consent checkbox.');
$GLOBALS['TL_LANG'][$str]['notification_type'] = array('Notification', 'Select the notification. The setting can be overwritten in the reservation type.');
$GLOBALS['TL_LANG'][$str]['additionaldatas'] = array("Data fields", "The order here corresponds to the display in the frontend. Except for the heading, each field can be used only once.");
$GLOBALS['TL_LANG'][$str]['mandatory'] = array("Mandatory field?","");
$GLOBALS['TL_LANG'][$str]['binding'] = array("Mandatory field?", "Should this data field be displayed as a mandatory field in the frontend?");
$GLOBALS['TL_LANG'][$str]['printing'] = array("Into PDF?", "Should this data field be included in the PDF? (Invoice, ticket, etc.)");
$GLOBALS['TL_LANG'][$str]['initialValue'] = array("Initial value", "Here you can enter an initial value. Important for the heading - otherwise optional.");
$GLOBALS['TL_LANG'][$str]['individualLabel'] = array("Individual Label", "Here you can set an individual label. If it is empty, the default from the language files is used.");
$GLOBALS['TL_LANG'][$str]['additionalClass'] = array("Additional class", "You can extend the field by one class.");
$GLOBALS['TL_LANG'][$str]['fieldSelection'] = array("Add data fields", "Attention! First name, last name and e-mail are appended if they are missing. The three fields are always required.");
$GLOBALS['TL_LANG'][$str]['additionalDuration'] = array("Individual length of use (initial)", "If 0, no individual entry of the length of use is possible (default). Min and max ");
$GLOBALS['TL_LANG'][$str]['withCapacity'] = array("Customer can specify number of persons", "The customer can specify the number of persons. This is required, for example, for a table reservation in the restaurant.");
$GLOBALS['TL_LANG'][$str]['showFreeSeats'] = array("Show open seats", "Open seats are displayed.");
$GLOBALS['TL_LANG'][$str]['showEndTime'] = array("The end times are displayed with", "The end times are displayed with (depending on the configuration).");
$GLOBALS['TL_LANG'][$str]['showPrices'] = array("Show prices", "Prices are displayed.");
$GLOBALS['TL_LANG'][$str]['showPricesWithTaxes'] = array("Calculate and display prices incl. taxes.", "Prices are shown including sales tax.");
$GLOBALS['TL_LANG'][$str]['showDateTime'] = array("Show appointment at object", "Shows the selected appointment directly at the object. Attention. This function currently only works in the standard mode (time selection).");
$GLOBALS['TL_LANG'][$str]['showMemberData'] = array("Apply member data", "Existing member data is automatically pre-populated in the form fields.");
$GLOBALS['TL_LANG'][$str]['showDetails'] = array("Show details", "Additional information such as image and description are displayed.");
$GLOBALS['TL_LANG'][$str]['removeBookedDays'] = array("Lock fully booked days in calendar", "The calendar day is fully booked cannot be selected. Attention. The check is partly lengthy.");
$GLOBALS['TL_LANG'][$str]['showArrivalAndDeparture'] = array('Show Arrival and Departure times', 'Attention! Only valid for the reservation type "Overnight".');
$GLOBALS['TL_LANG'][$str]['showInlineDatepicker'] = array("Display date picker open", "The date picker is displayed as calendar open.");
$GLOBALS['TL_LANG'][$str]['emptyOptionLabel'] = array("Text empty object list (optional)", "The text of the selectbox can optionally be overwritten.");
$GLOBALS['TL_LANG'][$str]['specialParticipantMechanism'] = array("Participant mechanism (optional)", "If this mechanism is enabled, the participant fields are generated based on the number of people.");
$GLOBALS['TL_LANG'][$str]['showMinMaxWithCapacity'] = array("Display Minimum and Maximum","The minimum to maximum permissible entries for specifying the number of persons are displayed");
$GLOBALS['TL_LANG'][$str]['hideParticipantsEmail'] = array("Hide Participants E-Mail", "Additional participant fields do not include the e-mail field.");
$GLOBALS['TL_LANG'][$str]['onlyParticipants'] = array("Only take participants into account", "If this mechanism is enabled, only participants will be considered in the price calculation.");
$GLOBALS['TL_LANG'][$str]['hideReservationKey'] = array("Hide Reservationkey", "If enabled, the reservation key will not be visible in the frontend.");
$GLOBALS['TL_LANG'][$str]['hideOrganizer'] = array("Hide organizer", "The organizer will not be displayed.");
$GLOBALS['TL_LANG'][$str]['hideLocation'] = array("Hide location", "The location will not be displayed.");
$GLOBALS['TL_LANG'][$str]['postals'] = array("Restrict zip codes", "Here you can enable that reservations are only possible via certain zip codes (comma separated list).");
$GLOBALS['TL_LANG'][$str]['documentTemplate'] = array("Document Template", "Here you can assign the template for document generation.");
$GLOBALS['TL_LANG'][$str]['documentStyle'] = array("EExternal CSS file","Here you can link an external CSS file to your template. Important! The file must work with DOM-PDF. Ideally, simple CSS2 instructions.");
$GLOBALS['TL_LANG'][$str]['documentIdPrefix'] = array("ID Prefix","Specify a prefix for your document ID (e.g., invoice number).");
$GLOBALS['TL_LANG'][$str]['documentIdSuffix'] = array("ID Suffix","Specify a suffix for your document ID (e.g., invoice number).");
$GLOBALS['TL_LANG'][$str]['documentIdLength'] = array("ID Length","Specify a length for your document ID (e.g., invoice number).");
$GLOBALS['TL_LANG'][$str]['documentIdNext'] = array("Next ID","Enter the next document ID (e.g., invoice number).");
$GLOBALS['TL_LANG'][$str]['checkInPage'] = array("Link to the check-in page", "The link will be generated in the QR code.");
$GLOBALS['TL_LANG'][$str]['paricipantCheckInWithSameCode'] = array("Check in participants via QR code","When this feature is enabled, multiple participants can be checked in using the same QR code. Please note: It is not yet possible to generate different QR codes for participants, i.e., multiple tickets.");

$GLOBALS['TL_LANG'][$str]['typeWithEmptyOption'] = array('Make reservation type selection', 'If this switch is set, the reservation type is not preset and must be selected.');

$GLOBALS['TL_LANG'][$str]['referencesObjectType'][1] = 'Time selection';
$GLOBALS['TL_LANG'][$str]['referencesObjectType'][2] = 'Contao Events';
$GLOBALS['TL_LANG'][$str]['referencesObjectType'][3] = 'Object selection';

$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['noDocument'] = 'No document';

/** LEGENDS **/
$GLOBALS['TL_LANG'][$str]['settings_legend'] = "General settings";
$GLOBALS['TL_LANG'][$str]['type_legend'] = "Reservation type settings";
$GLOBALS['TL_LANG'][$str]['object_legend'] = "Time selection settings";
$GLOBALS['TL_LANG'][$str]['form_legend'] = "Form settings";
$GLOBALS['TL_LANG'][$str]['notification_legend'] = 'Notification Center';
$GLOBALS['TL_LANG'][$str]['document_legend'] = "Document generator";
$GLOBALS['TL_LANG'][$str]['ticket_legend'] = "Ticketing";
$GLOBALS['TL_LANG'][$str]['redirect_legend'] = 'Redirection';
$GLOBALS['TL_LANG'][$str]['expert_legend'] = 'Expert settings';

/** OPERATIONS **/
$GLOBALS['TL_LANG'][$str]['new'] = array("Create form", "Create new form");
$GLOBALS['TL_LANG'][$str]['edit'] = array("Edit settings","Edit settings ID %s");
$GLOBALS['TL_LANG'][$str]['copy'] = array("Copy settings","Copy settings ID %s");
$GLOBALS['TL_LANG'][$str]['delete'] = array("Delete settings","Delete settings ID %s");
$GLOBALS['TL_LANG'][$str]['show'] = array("Show settings","Show settings ID %s");
$GLOBALS['TL_LANG'][$str]['TOGGLE'] = array("Activate settings","Activate settings ID %s");
