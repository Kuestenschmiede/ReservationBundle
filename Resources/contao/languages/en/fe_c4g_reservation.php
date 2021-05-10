<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 8
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2021, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

/** CONFIGURATION */
$GLOBALS['TL_LANG']['fe_c4g_reservation']['brick_caption'] = 'reservation';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['brick_caption_plural'] = 'Reservations';

/** FIELDS */
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_type'] = 'What do you want to reserve?';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_type_short'] = 'Reservation type';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_time'] = 'Time';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desiredCapacity'] = 'How many people are you reserving for?';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['periodType'] = 'Time span type';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['tstamp'] = 'Last change';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDate'] = 'For which date would you like to make a reservation?';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDateShort'] = 'Date';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDateTime'] = 'Time';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['endDate'] = 'end date';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime'] = 'Please select a time';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeShort'] = 'Time';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['endTime'] = 'Ends at';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['duration'] = 'Duration';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDateEvent'] = 'Begin date';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['endDateEvent'] = 'End date';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeEvent'] = 'Begin time';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['endTimeEvent'] = 'End time';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object_event'] = 'Event';

$GLOBALS['TL_LANG']['fe_c4g_reservation']['clock'] = '';//o\'clock'; use a.m. / p.m. with timeFormat A
$GLOBALS['TL_LANG']['fe_c4g_reservation']['targetAudience'] = 'Target audience';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['topic'] = 'Topic';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['speaker'] = 'Speaker';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['eventForwardingButtonText'] = 'Continue to reservation';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['state'] = 'State';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['state_none'] = 'no state';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['state_green'] = 'Reservation possible';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['state_orange'] = 'Reservation possible. Only a few seats available.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['state_red'] = 'Fully booked. No reservation possible.';

$GLOBALS['TL_LANG']['fe_c4g_reservation']['headline_data'] = 'Your data';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['detailsHeaadline'] = 'Overview';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['eventlocation'] = 'Location';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['eventaddress'] = 'Address';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['eventnumber'] = 'Number';

$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object'] = 'Reservation options';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object_none'] = 'Please select or change time';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_id'] = 'Identification key: ';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['organisation'] = 'Company / Organization / School';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['salutation'] = 'Salutation';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['title'] = 'Title';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname'] = 'Last name';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname'] = 'First name';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['email'] = 'Email address';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['phone'] = 'Phone number';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['address'] = 'Street';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['postal'] = 'Postal code';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['city'] = 'City';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['dateOfBirth'] = 'Date of birth';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['organisation2'] = 'Company / Organization / School';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['salutation2'] = 'Salutation';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['title2'] = 'Title';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname2'] = 'Last name';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname2'] = 'First name';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['email2'] = 'Email address';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['phone2'] = 'Phone number';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['address2'] = 'Street';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['postal2'] = 'Postal code';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['city2'] = 'City';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_none'] = 'No Bookings possible';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['comment'] = 'Your message to us';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['comment_short'] = 'Comment';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['agreed'] = 'Privacy policy';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['confirmed'] = 'Confirmed';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['cancellation'] = 'Cancelled';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['periodType'] = 'Time span type';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['man'] = 'Mr.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['woman'] = 'Mrs.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['various'] = ' - ';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['minute'] = 'minutes';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['hour'] = 'hours';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['openingHours'] = 'opening hours';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['included_params'] = 'Included services';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['additional_params'] = 'Bookable services';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['md'] = 'several days';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['event'] = 'Event';

$GLOBALS['TL_LANG']['fe_c4g_reservation']['pleaseSelect'] = 'Please select';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText'] = 'No times available';

$GLOBALS['TL_LANG']['fe_c4g_reservation']['participants'] = 'Participants';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['addParticipant'] = 'Add participant';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['removeParticipant'] = 'Remove participant';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['removeParticipantMessage'] = 'Really remove participant';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['participant_params'] = 'Participant services';

/** Legends */
$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_data'] = 'reservation form';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['person_data'] = 'your data';

/** MESSAGES */
$GLOBALS['TL_LANG']['fe_c4g_reservation']['min_max_failed'] = 'We cannot process your reservation. The date is outside the bookable range.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['fully_booked'] = 'Reservations are no longer possible. The date is already fully booked.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['too_many_participants'] = 'Reservation is no longer possible. The number of free places is exceeded. Possible number: ';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['too_many_participants_per_booking'] = 'The number of participants per booking is limited. Maximum number: ';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['error'] = 'An error has occurred. The reservation cannot be made. Please contact us.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['duplicate_reservation_id'] = 'Sorry. An error has occurred. The reservation cannot be made.  Please reload the page and try again.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['duplicate_booking'] = 'Sorry. An error has occurred. The reservation cannot be made.  Please reload the page and try again.';

/** DESCRIPTIONS */
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_reservation_type'] = 'Select the reservation type';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_beginDate'] = 'Select the date on which the event starts.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_endDate'] = 'Choose the date on which the event ends.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_beginTime'] = 'Select the time when the event starts(9:00-12:00 & 14:00-18:00)';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_endTime'] = 'Select the time when the event ends(9:00-12:00 & 14:00-18:00)';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_reservation_object'] = '';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_reservation_id'] = 'This key is mandatory for a cancellation You will receive a copy by e-mail.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_lastname'] = 'Enter your descendant.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_firstname'] = 'Enter your first name.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_email'] = 'Enter your e-mail address here.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_phone'] = 'Enter your phone number here';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_address'] = 'Enter your address here (street and number).';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_postal'] = 'Enter a postal code here.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_city'] = 'Enter city or town here.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_comment'] = 'Here you can optionally leave a message for us.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed'] = 'Detailed information on the handling of user data can be found in our ';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed_link_text'] = 'privacy policy.';
$GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed_without_link'] = 'Detailed information on the handling of user data can be found in our privacy policy.';

/** BUTTONS */
$GLOBALS['TL_LANG']['fe_c4g_reservation']['button_reservation'] = 'Reserve appointment';
