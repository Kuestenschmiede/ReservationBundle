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

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_module']['reservation_legend'] = 'reservation objects';
$GLOBALS['TL_LANG']['tl_module']['reservation_notification_center_legend'] = 'Notification Center';
$GLOBALS['TL_LANG']['tl_module']['reservation_jquery_theme_legend'] = 'jQuery UI-Theme';
$GLOBALS['TL_LANG']['tl_module']['reservation_redirect_legend'] = 'forwarding';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['reservation_types'] = array('reservation types', 'Select the reservation types (rooms, tables, ...) to be considered in the reservation in the frontend. If no selection is made, all reservation objects are loaded.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['reservationView'] = array('List view', 'Default: public');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['appearance_themeroller_css'] = array('jQuery UI ThemeRoller CSS file', 'Optional: select a CSS file created with the jQuery UI ThemeRoller to customize the style of the module accordingly');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['reservationButtonCaption'] = array('Button label', 'This allows you to change the button text.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['redirect_site'] = array('Forwarding page', 'After booking, you will be forwarded here.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['notification_type'] = array('notification', 'Select notification');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['privacy_policy_site']  = array('Link to privacy policy', 'This page is linked for the confirmation of the privacy policy.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['privacy_policy_text']  = array('Privacy Comment', 'This privacy comment appears before the consent checkbox.');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['notification_type_contact_request'] = array('Contact request notification', 'Select the notification for sending the contact request');
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['additionaldatas'] =array("Data fields","Which additional data should be specified in the front end? Except for the heading, each field can only be used once.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['mandatory'] = array("Mandatory field?","");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['binding'] = array("Mandatory field?", "Should this data field be displayed as mandatory field in the frontend");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['initialValue'] = array("Initial value", "Here you can enter an initial value. Important for the heading - otherwise optional.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['hide_selection'] = array("Add data fields","Attention! First name, last name and e-mail are appended if they are missing. The three fields are always required.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['additionalDuration'] = array("Customer can specify length of stay (BETA)", "If checked, a new field appears in the frontend in which the customer specifies his length of stay");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['withCapacity'] = array("Customer can specify number of persons","The customer can specify the number of persons. This is required, for example, for a table reservation in a restaurant.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['showFreeSeats'] = array("Show free seats", "Open seats are displayed.");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['showEndTime'] = array("The end times are also output", "The end times are also displayed (depending on the configuration).");
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['showPrices'] = array("Show prices","Prices are displayed");

$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references']['publicview'] = 'Public';
$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references']['member'] = 'Member based';