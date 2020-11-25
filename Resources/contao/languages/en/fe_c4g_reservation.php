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

/** CONFIGURATION */
$GLOBALS['TL_LANG']['fe_c4g_reservation']['brick_caption'] = 'reservation';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['brick_caption_plural'] = 'Reservations';

/** FIELDS */ /**DKo 13.02.19*/
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_type'] = 'What do you want to reserve?';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_time'] = 'Time';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desiredCapacity'] = 'How many people are you reserving for?';
$GLOBALS['TL_LANG']['tl_c4g_reservation']['periodType'] = 'Time span type';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDate'] = 'For which date would you like to make a reservation?';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['endDate'] = 'end date';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime'] = 'Please select a time';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['endTime'] = 'Ends at';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['duration'] = 'Duration';

$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object'] = 'Reservation options';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object_none'] = 'Reservation request not available.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_id'] = 'Identification key: ';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['organisation'] = 'Company / Organization / School';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['salutation'] = 'Salutation';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname'] = 'Last name';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname'] = 'first name';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['email'] = 'email address';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['phone'] = 'phone number';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['address'] = 'street';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_none'] = 'No Bookings possible';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['postal'] = 'postal code';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['city'] = 'city';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['comment'] = 'Your message to us';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['agreed'] = 'Privacy policy';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['periodType'] = 'Time span type';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['man'] = 'Mr.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['woman'] = 'Mrs.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['various'] = ' - ';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['minute'] = 'minutes';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['hour'] = 'hours';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['openingHours'] = 'opening hours';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['additional_params'] = 'Additional options';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['md'] = 'several days';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['event'] = 'Event';

$GLOBALS['TL_LANG']['fe_c4g_reservation']['pleaseSelect'] = 'Please select';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText'] = 'No times available';

$GLOBALS['TL_LANG']['fe_c4g_reservation']['participants'] = 'Participants';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['addParticipant'] = 'Add participant';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['removeParticipant'] = 'Remove participant';

/** Legends */
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_data'] = 'reservation form';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['person_data'] = 'your data';

/** MESSAGES */
$GLOBALS['TL_LANG']['fe_c4g_reservation']['min_max_failed'] = 'We cannot process your reservation. The date is outside the bookable range.';

/** DESCRIPTIONS */ /**DKo 13.02.19*/
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_reservation_type'] = 'Select the reservation type';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_beginDate'] = 'Select the date on which the event starts.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_endDate'] = 'Choose the date on which the event ends.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_beginTime'] = 'Select the time when the event starts(9:00-12:00 & 14:00-18:00)';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_endTime'] = 'Select the time when the event ends(9:00-12:00 & 14:00-18:00)';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_reservation_object'] = 'Select what you want to reserve.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_reservation_id'] = 'This key is mandatory for a cancellation You will receive a copy by e-mail.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_lastname'] = 'Enter your descendant.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_firstname'] = 'Enter your first name.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_email'] = 'Enter your e-mail address here.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_phone'] = 'Enter your phone number here';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_address'] = 'Enter your address here (street and number).';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_postal'] = 'Enter a postal code here.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_city'] = 'Enter city or town here.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_comment'] = 'Here you can optionally leave a message for us.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed'] = 'Please agree to the privacy policy.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed_link_text'] = 'Privacy Policy.';

/** BUTTONS */
$GLOBALS['TL_LANG']['fe_c4g_reservation']['button_reservation'] = 'Reserve appointment';



