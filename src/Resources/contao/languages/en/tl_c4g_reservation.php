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

/** FIELDS */
$GLOBALS['TL_LANG']['tl_c4g_reservation']['id'] = array("#", "");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['pid'] = array("Booked", "Set automatically.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_type'] = array("Reservation type", "What type of reservation is it");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_included_option'] = array("Included booking options", "This options are included.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_additional_option'] = array("Additional booking options", "Should an additional option be booked?");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['desiredCapacity'] = array("Number of people", "What is the maximum number of people that will appear?");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['periodType'] = array("time span type");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['minute'] = array("minutes");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['hour'] = array("hours");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['day'] = array("days");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['week'] = array("weeks");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['openingHours'] = array("opening hours");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['md'] = array("several days");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['beginDate'] = array("begin");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['endDate'] = array("end");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['beginTime'] = array("start time", "when does the event start");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['endTime'] = array("time end", "when does the event end?");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservationObjectType'] = array("Object type", "Reservation objects and Contao events can be reserved.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['referencesObjectType'][1] = 'Time selection (default)';
$GLOBALS['TL_LANG']['tl_c4g_reservation']['referencesObjectType'][2] = 'Contao Events';
$GLOBALS['TL_LANG']['tl_c4g_reservation']['referencesObjectType'][3] = 'Object selection (new!)';
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_object'] = array("reservation object", "Which object should be reserved?");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_id'] = array("reservation key", "Please enter a reservation key for identification. The key must be unique and is generated automatically when an empty field is saved.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['duration'] = array('Duration','');
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
$GLOBALS['TL_LANG']['tl_c4g_reservation']['dateOfBirth'] = array("date of birth", "date of birth");

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
$GLOBALS['TL_LANG']['tl_c4g_reservation']['fileUpload'] = array("file Upload", "A file can be attached here.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['agreed'] = array("Privacy Policy", "Selected by user in frontend.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['confirmed'] = array("Confirm", "The appointment is confirmed. Link notification to the reservation type (optional).");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['specialNotification'] = array("Send special information", "Ist dieser Schalter gesetzt wird die an der Reservierungsart verknüpfte Spezialnachricht versendet. You can uncheck the checkbox again to send a new confirmation.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['emailConfirmationSend'] = array("Confirmation has been sent", "This switch will be set automatically once the email has been sent.");

$GLOBALS['TL_LANG']['tl_c4g_reservation']['cancellation'] = array("Canceled", "If the appointment is cancelled, select this field");

$GLOBALS['TL_LANG']['tl_c4g_reservation']['memberId'] = array("For member", "The reservation was created for or by this member.");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['groupId'] = array("For group", "The reservation was created for or by this group.");

$GLOBALS['TL_LANG']['tl_c4g_reservation']['billingAddress'] = array("Billing address", "Billing address");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['additionalHeadline'] = array("Headline", "Headline");

$GLOBALS['TL_LANG']['tl_c4g_reservation']['yes'] = 'yes';

$GLOBALS['TL_LANG']['tl_c4g_reservation']['participants'] = 'Participants';
$GLOBALS['TL_LANG']['tl_c4g_reservation']['confirmationEmail'] = 'Send confirmation';

$GLOBALS['TL_LANG']['tl_c4g_reservation']['tstamp'] = array('Last change','');
$GLOBALS['TL_LANG']['tl_c4g_reservation']['bookedAt'] = array('Booked at','');

/** LEGENDS **/
$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_legend'] = "reservation data";
$GLOBALS['TL_LANG']['tl_c4g_reservation']['person_legend']      = "personal data";
$GLOBALS['TL_LANG']['tl_c4g_reservation']['person2_legend']     = "Billing addresse / Additional person data";
$GLOBALS['TL_LANG']['tl_c4g_reservation']['comment_legend']     = "Attachments";
$GLOBALS['TL_LANG']['tl_c4g_reservation']['notification_legend'] = "Confirmation to the booker";
$GLOBALS['TL_LANG']['tl_c4g_reservation']['state_legend']       = "State";

/** OPERATIONS **/
$GLOBALS['TL_LANG']['tl_c4g_reservation']['new'] = array("add reservation", "add reservation");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['edit'] = array("edit reservation", "edit reservation ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['copy'] = array("Copy reservation", "Copy reservation ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['delete'] = array("delete reservation", "delete reservation ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['show'] = array("Show reservation", "Show reservation ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation']['TOGGLE'] = array("Activate reservation", "Activate reservation ID %s");

/** INFOTEXT */
$GLOBALS['TL_LANG']['tl_c4g_reservation']['infoReservation'] = '';