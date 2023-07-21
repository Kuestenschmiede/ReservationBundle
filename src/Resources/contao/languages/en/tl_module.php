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

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_module']['reservation_legend'] = 'Reservation objects';
$GLOBALS['TL_LANG']['tl_module']['list_legend'] = 'List settings';
$GLOBALS['TL_LANG']['tl_module']['reservation_notification_center_legend'] = 'Notification Center';
$GLOBALS['TL_LANG']['tl_module']['reservation_redirect_legend'] = 'Forwarding';
$GLOBALS['TL_LANG']['tl_module']['reservation_objects_legend'] = 'Settings for object care';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['reservation_object_types'] = array('Which types may be available for selection?', 'Select the types to be used for object management.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['notification_type'] = array('Notification', 'Select the notification. The preference can be overridden in the reservation type.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['reservation_settings'] = array('Select form settings', 'Select your configuration from the Backend module Reservation form.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['notification_type_contact_request'] = array('Contact request notification', 'Select the notification for sending the contact request');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['reservationView'] = array('List view', 'Default: Public');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['renderMode'] = array('List display', 'Different displays of the list are possible. Default: Public');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['selectReservationTypes'] = array('Reservation types', 'Select the reservation types (rooms, tables, ...) to be taken into account when making reservations in the frontend. If no selection is made, all reservation objects will be loaded.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['showReservationType'] = array("Show reservation type", "The reservation type is not shown in list and detail by default. You can change that here.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['showReservationObject'] = array("Show reservation object", "The reservation object is shown by default. There are scenarios in which hiding it makes sense.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['showSignatureField'] = array("Signature field (group view only)", "Possibility to request a signature. Currently only possible in the group list.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['showPrices'] = array("Show prices", "If possible, defined prices are shown.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['past_day_number'] = array("Load data from the past (days)", "How many days should the list data go back (default 1 day).");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['redirect_site'] = array('Forwarding page', 'After booking, you will be forwarded here.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['login_redirect_site'] = array('Redirect to login page', 'If the list module is not public.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['event_redirect_site'] = array('Event forwarding', 'Here you can set the forwarding to the event detail page. In case you want to display the events at the referent.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['speaker_redirect_site'] = array('Forward speakers', 'Clicking on the tiles will redirect to another page.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['cancellation_redirect_site'] = array('Redirect to cancellation', 'You can offer a cancellation button directly via this');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['cancellationButtonCaption'] = array('Button label','Allows you to change the button text. For example, "Cancel Reservation".');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['reservation_add_member_location'] = array('Automatically set location for member', 'Automatically sets a new location for maintaining members.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['additionaldatas'] = array("Data fields", "The order here corresponds to the appearance shown in the frontend. Except for the heading, each field can be used only once.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['mandatory'] = array("Mandatory?","");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['binding'] = array("Mandatory?","Should this data field be displayed as a mandatory field in the frontend");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['initialValue'] = array("Initial value", "Here you can enter an initial value. Important for the heading - otherwise optional.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references']['publicview'] = 'Public (reservations will be visible)';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references']['memberview'] = 'Member view (Only reservations for logged in member, without editing)';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references']['member'] = 'Member based (Only reservations for logged in member, with editing)';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references']['group'] = 'Group based (Only reservations for groups of the logged in member)';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references']['tiles'] = 'Tiles';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references']['table'] = 'Data table';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references']['list'] = 'List elements';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['printTpl'] = ["Print template", "You can use this to store your own print template."];
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['removeListImage'] = ['Hide image in list','The image will no longer be displayed in the referent list.'];
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['withMap'] = ['Show location map in form', 'Important: con4gis/maps must be installed. A map content element must be linked in the dastboard setting (the content element can be invisible in the article). Also, the frontend geopicker must be active in the map profile.'];
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['postals'] = ["Restrict zip codes", "Here you can enable that the objects may only be merstellt and Publenltlich with certain zip codes (comma separated list)."];