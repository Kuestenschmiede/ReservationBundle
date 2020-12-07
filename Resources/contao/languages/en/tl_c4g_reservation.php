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

/** FIELDS */ /**DKo 13.02.19*/
$GLOBALS['TL_LANG']['tl_c4g_reservation']['id'] = array("#", "");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['pid'] = array("Booked", "Set automatically.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_type'] = array("Reservation type", "What type of reservation is it");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_additional_option'] = array("Additional booking option", "Should an additional option be booked?");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['desiredCapacity'] = array("Number of people", "What is the maximum number of people that will appear?");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['periodType'] = array("time span type");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['minute'] = array("minutes");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['hour'] = array("hours");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['openingHours'] = array("opening hours");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['md'] = array("several days");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['beginDate'] = array("begin");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['endDate'] = array("end");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['beginTime'] = array("start time", "when does the event start");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['endTime'] = array("time end", "when does the event end?");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservationObjectType'] = array("Object type", "Reservation objects and Contao events can be reserved.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['referencesObjectType'][1] = 'Default';
$GLOBALS['TL_LANG']['tl_c4g_reservation']['referencesObjectType'][2] = 'Event';
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_object'] = array("reservation object", "Which object should be reserved?");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_id'] = array("reservation key", "Please enter a reservation key for identification");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['duration'] = 'Duration';
$GLOBALS['TL_LANG']['tl_c4g_reservation']['internal_comment'] = "Space for Internal Comments";
$GLOBALS['TL_LANG']['tl_c4g_reservation']['man'] = 'Mr.';
$GLOBALS['TL_LANG']['tl_c4g_reservation']['woman'] = 'Mrs.';
$GLOBALS['TL_LANG']['tl_c4g_reservation']['various'] = ' - ';
$GLOBALS['TL_LANG']['tl_c4g_reservation']['organisation'] = 'Company / Organization / School';
$GLOBALS['TL_LANG']['tl_c4g_reservation']['salutation'] = 'Salutation';
$GLOBALS['TL_LANG']['tl_c4g_reservation']['title'] = ['Title', 'Person title'];
$GLOBALS['TL_LANG']['tl_c4g_reservation']['lastname'] = array("last name", "last name");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['firstname'] = array("first name", "first name");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['email'] = array("email address", "first name");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['phone'] = array("phone number", "phone number");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['address'] = array("street", "street");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['postal'] = array("postal code", "postcode");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['city'] = array("city", "town");

$GLOBALS['TL_LANG']['tl_c4g_reservation']['organisation2'] = ['Company / Organisation / School (2)', 'Please specify company name, organisation or school name.'];
$GLOBALS['TL_LANG']['tl_c4g_reservation']['salutation2'] = ['Salutation (2)', 'Please select Mr, Mrs or no details'];
$GLOBALS['TL_LANG']['tl_c4g_reservation']['title2'] = ['Title (2)', 'Person title'];
$GLOBALS['TL_LANG']['tl_c4g_reservation']['lastname2'] = array("last name (2)", "last name");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['firstname2'] = array("first name (2)", "first name");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['email2'] = array("email address (2)", "first name");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['phone2'] = array("phone number (2)", "phone number");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['address2'] = array("street (2)", "street");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['postal2'] = array("postal code (2)", "postcode");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['city2'] = array("city (2)", "town");

$GLOBALS['TL_LANG']['tl_c4g_reservation']['comment'] = array("comment", "comment from creator");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['agreed'] = array("Privacy Policy", "Selected by user in frontend.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['confirmed'] = array("Confirmed", "Has the appointment been confirmed?If applicable, select this field.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['cancellation'] = array("Canceled", "If the appointment is cancelled, select this field");

$GLOBALS['TL_LANG']['tl_c4g_reservation']['billingAddress'] = array("Billing address", "Billing address");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['additionalHeadline'] = array("Headline", "Headline");

$GLOBALS['TL_LANG']['tl_c4g_reservation']['yes'] = 'yes';

$GLOBALS['TL_LANG']['tl_c4g_reservation']['participants'] = 'Participants';

/** LEGENDS **/
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_legend'] = "reservation data";
$GLOBALS['TL_LANG']['tl_c4g_reservation']['person_legend']      = "personal data";
$GLOBALS['TL_LANG']['tl_c4g_reservation']['person2_legend']     = "Billing addresse / Additional person data";
$GLOBALS['TL_LANG']['tl_c4g_reservation']['comment_legend']     = "Comments";

/** OPERATIONS **/
$GLOBALS['TL_LANG']['tl_c4g_reservation']['new'] = array("add reservation", "add reservation");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['edit'] = array("edit reservation", "edit reservation ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['copy'] = array("Copy reservation", "Copy reservation ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['delete'] = array("delete reservation", "delete reservation ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['show'] = array("Show reservation", "Show reservation ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['TOGGLE'] = array("Activate reservation", "Activate reservation ID %s");
