<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @author con4gis contributors (see "authors.md")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

$str = 'tl_c4g_reservation_object';

/** FIELDS */
$GLOBALS['TL_LANG'][$str]['caption'] = array("Name", "Name your reservation object. Examples: Room1, table7, specialist3");
$GLOBALS['TL_LANG'][$str]['alias'] = array("Alias", "The alias is used in the frontend instead of the ID for detail views and parameters.");
$GLOBALS['TL_LANG'][$str]['options'] = array("Frontend name", "Are displayed in the frontend depending on the language.");
$GLOBALS['TL_LANG'][$str]['quantity'] = array("Available number", "How many objects of this type are available? (default 1)");
$GLOBALS['TL_LANG'][$str]['allTypesQuantity'] = array("Applies across all reservation types (same object)", "The available number of objects is considered across all reservation types.");
$GLOBALS['TL_LANG'][$str]['allTypesValidity'] = array("Applies across all reservation types (all objects)", "If more than one reservation type is active, then the booking will also block other objects.");
$GLOBALS['TL_LANG'][$str]['allTypesEvents'] = array("Contao events block objects", "Select the calendars to be considered when checking free events.");
$GLOBALS['TL_LANG'][$str]['switchAllTypes'] = array("Applies only to the following reservation types", "Allows you to reduce the validity of the previous checkboxes to specific reservation types (optional).");
$GLOBALS['TL_LANG'][$str]['priority'] = array("Should be offered with priority", "If this switch is active, then the object will be preselected if several objects fit into the time.");
$GLOBALS['TL_LANG'][$str]['viewableTypes'] = array("Reservation types", "Assign the reservation object to the reservation types.");
$GLOBALS['TL_LANG'][$str]['typeOfObject'] = array("Type of Object", 'Assign the reservation object to an object type. Caution! Currently only applies to "Object selection" ( reservation type settings).');
$GLOBALS['TL_LANG'][$str]['dateTimeBegin'] = array("Date and Time", "Date and time of the 'fixed date' object type.");
$GLOBALS['TL_LANG'][$str]['typeOfObjectDuration'] = array("Duration", "Duration of the 'fixed date' object type.");
$GLOBALS['TL_LANG'][$str]['option'] = array("Name","");
$GLOBALS['TL_LANG'][$str]['location'] = array("Event location", "Where will the event take place?");
$GLOBALS['TL_LANG'][$str]['speaker'] = array("Speaker", "Who leads the event?");
$GLOBALS['TL_LANG'][$str]['topic'] = array("Topics", "Here you can link your event topics.");
$GLOBALS['TL_LANG'][$str]['targetAudience'] = array("Target audience", "Select your target audience.");
$GLOBALS['TL_LANG'][$str]['language'] = array("language","");
$GLOBALS['TL_LANG'][$str]['minute_interval'] = array("minute interval", "Every how many minutes the reservation object can be booked during opening hours.");
$GLOBALS['TL_LANG'][$str]['hour_interval'] = array("hour interval", "Every how many hours the reservation object can be booked during opening hours");
$GLOBALS['TL_LANG'][$str]['day_interval'] = array("day interval", "Every how many days the reservation object can be booked during opening hours");
$GLOBALS['TL_LANG'][$str]['week_interval'] = array("week interval", "Every how many weeks the reservation object can be booked during opening hours");
$GLOBALS['TL_LANG'][$str]['oh_monday'] = array("Monday", "");
$GLOBALS['TL_LANG'][$str]['oh_tuesday'] = array("Tuesday", "");
$GLOBALS['TL_LANG'][$str]['oh_wednesday'] = array("Wednesday", "");
$GLOBALS['TL_LANG'][$str]['oh_thursday'] = array("Thursday", "");
$GLOBALS['TL_LANG'][$str]['oh_friday'] = array("Friday", "");
$GLOBALS['TL_LANG'][$str]['oh_saturday'] = array("Saturday", "");
$GLOBALS['TL_LANG'][$str]['oh_sunday'] = array("Sunday", "Important! Two periods must be defined for the interval type 'Overnight' (CheckIn and CheckOut)");
$GLOBALS['TL_LANG'][$str]['time_begin'] = array("begin", "");
$GLOBALS['TL_LANG'][$str]['time_end'] = array("end", "");
$GLOBALS['TL_LANG'][$str]['date_from'] = array("valid from (optional)", "");
$GLOBALS['TL_LANG'][$str]['date_to'] = array("valid to (optional)", "");
$GLOBALS['TL_LANG'][$str]['days_exclusion'] = array("Exclude Period", "This allows you to exclude periods from the reservation (e.g. holidays).");
$GLOBALS['TL_LANG'][$str]['date_exclusion'] = array("", "");
$GLOBALS['TL_LANG'][$str]['min_reservation_day'] = array("Earliest reservation date (in days)", "Today + how many days?");
$GLOBALS['TL_LANG'][$str]['max_reservation_day'] = array("Latest reservation date (in days)", "Today + how many days?");
$GLOBALS['TL_LANG'][$str]['maxBeginTime'] = array("Latest reservation start (time)", "Until what time can be booked. Ex. venue: Events can be booked until 2 am. However, the events should start at 9 pm at the latest.");
$GLOBALS['TL_LANG'][$str]['periodType'] = array("Time type", "Select what kind of time interval should be used.");
$GLOBALS['TL_LANG'][$str]['event_id'] = array("Event selection","");
$GLOBALS['TL_LANG'][$str]['event_dayBegin'] = array("Event start", "On which day the event starts.");
$GLOBALS['TL_LANG'][$str]['event_dayEnd'] = array("Event end", "On which day the event ends.");
$GLOBALS['TL_LANG'][$str]['event_timeBegin'] = array("Event start", "At what time does the event start.");
$GLOBALS['TL_LANG'][$str]['event_timeEnd'] = array("Event end", "At what time does the event end");
$GLOBALS['TL_LANG'][$str]['description'] = array("Description", "Description of the reservation object.");
$GLOBALS['TL_LANG'][$str]['image'] = array("Image", "Matching image to reservation option.");
$GLOBALS['TL_LANG'][$str]['minute'] = array("minutes");
$GLOBALS['TL_LANG'][$str]['hour'] = array("hours");
$GLOBALS['TL_LANG'][$str]['day'] = array("days");
$GLOBALS['TL_LANG'][$str]['overnight'] = array("overnights");
$GLOBALS['TL_LANG'][$str]['week'] = array("weeks");
$GLOBALS['TL_LANG'][$str]['openingHours'] = array("booking hours");
$GLOBALS['TL_LANG'][$str]['time_interval'] = array("time interval", "Specify how many X minutes/ X hours the object can be booked (depends on which reservation type the object is assigned to");
$GLOBALS['TL_LANG'][$str]['duration'] = array("Duration per booking (optional)", "Normally [0] the duration is according to the time interval. However, if the actual appointment should be longer than the offered interval. For example, if a conversion or cleaning break is to be scheduled, then a longer interval can be entered here.");
$GLOBALS['TL_LANG'][$str]['date_exclusion_end'] = array("Exclusion end", "Put the last Date of exlusion in");
$GLOBALS['TL_LANG'][$str]['min_residence_time'] = array("Minimum usage duration (optional)", "Minimum usage duration that the customer may choose in the form. Important for multi-day bookings (days, weeks). Attention. Applies only to the reservation type Object selection. If 0, the time interval applies.");
$GLOBALS['TL_LANG'][$str]['max_residence_time'] = array("Maximum usage duration (optional)", "Maximum usage duration that the customer may choose in the form. Important for multi-day bookings (days, weeks). Attention. Applies only to the reservation type Object selection. If 0, the time interval applies.");
$GLOBALS['TL_LANG'][$str]['md'] = array("Several days");
$GLOBALS['TL_LANG'][$str]['event_selection'] = array("event type");
$GLOBALS['TL_LANG'][$str]['event_object'] = array("event object");
$GLOBALS['TL_LANG'][$str]['contao_event'] = array("Contao Event");
$GLOBALS['TL_LANG'][$str]['published'] = array("Publish.", "Should this object be displayed in the frontend?");
$GLOBALS['TL_LANG'][$str]['desiredCapacityMin'] = array("Minimum number of persons", "How many persons may appear at least? With standard 0 the number is not evaluated.");
$GLOBALS['TL_LANG'][$str]['desiredCapacityMax'] = array("Maximum number of persons", "How many persons may appear at least? With standard 0 the number is not evaluated.");
$GLOBALS['TL_LANG'][$str]['location_legend'] = array("Settings for the location");
$GLOBALS['TL_LANG'][$str]['memberId'] = array("Who owns the object?", "Only important if objects are created via the frontend module.");
$GLOBALS['TL_LANG'][$str]['notification_type'] = array('Automatic confirmation message (optional)', 'Select notification. This setting overrides the module settings and also the setting at the reservation type.');

$GLOBALS['TL_LANG'][$str]['price'] = array("Price", "Specify the price for the booking (for example: 50.00).");
$GLOBALS['TL_LANG'][$str]['taxOptions'] = ["Select Tax Rate option", "Select a tax option for all prices."];
$GLOBALS['TL_LANG'][$str]['priceoption'] = array("Price setting", "What should the price be calculated by.");

$GLOBALS['TL_LANG'][$str]['references']['tNone'] = "Without Tax";
$GLOBALS['TL_LANG'][$str]['references']['tStandard'] = "Standard";
$GLOBALS['TL_LANG'][$str]['references']['tReduced'] = "Reduced";

$GLOBALS['TL_LANG'][$str]['references']['standard'] = "Standard";
$GLOBALS['TL_LANG'][$str]['references']['fixed_date'] = "Fixed Date";

$GLOBALS['TL_LANG'][$str]['references']['pMin'] = array("Price per minute");
$GLOBALS['TL_LANG'][$str]['references']['pHour'] = array("Price per hour");
$GLOBALS['TL_LANG'][$str]['references']['pDay'] = array("Price per day");
$GLOBALS['TL_LANG'][$str]['references']['pNight'] = array("Price per night");
$GLOBALS['TL_LANG'][$str]['references']['pNightPerson'] = array("Price per night and person");
$GLOBALS['TL_LANG'][$str]['references']['pWeek'] = array("Price per week");
$GLOBALS['TL_LANG'][$str]['references']['pReservation'] = array("Price per reservation");
$GLOBALS['TL_LANG'][$str]['references']['pPerson'] = array("Price per person");
$GLOBALS['TL_LANG'][$str]['references']['pAmount'] = array("Security amount (pledge)");

$GLOBALS['TL_LANG'][$str]['noMember'] = 'no member';

/** LEGENDS **/
$GLOBALS['TL_LANG'][$str]['type_legend'] = "Reservation objects";
$GLOBALS['TL_LANG'][$str]['md_legend'] = "Several days";
$GLOBALS['TL_LANG'][$str]['event_legend'] = "event";
$GLOBALS['TL_LANG'][$str]['event_object_legend'] = "Event";
$GLOBALS['TL_LANG'][$str]['contao_event_legend'] = "Contao event";
$GLOBALS['TL_LANG'][$str]['periodType_legend'] = "time";
$GLOBALS['TL_LANG'][$str]['time_interval_legend'] = "Interval settings (depending on the type)";
$GLOBALS['TL_LANG'][$str]['minute_legend'] = "minute settings";
$GLOBALS['TL_LANG'][$str]['hour_legend'] = "hour settings";
$GLOBALS['TL_LANG'][$str]['day_legend'] = "day settings";
$GLOBALS['TL_LANG'][$str]['week_legend'] = "week settings";
$GLOBALS['TL_LANG'][$str]['publish_legend'] = "publishing options";
$GLOBALS['TL_LANG'][$str]['opening_hours_monday_legend'] = "Opening hours Mondays";
$GLOBALS['TL_LANG'][$str]['opening_hours_tuesday_legend'] = "Opening hours Tuesdays";
$GLOBALS['TL_LANG'][$str]['opening_hours_wednesday_legend'] = "Opening hours Wednesdays";
$GLOBALS['TL_LANG'][$str]['opening_hours_thursday_legend'] = "Opening hours Thursdays";
$GLOBALS['TL_LANG'][$str]['opening_hours_friday_legend'] = "Opening hours Fridays";
$GLOBALS['TL_LANG'][$str]['opening_hours_saturday_legend'] = "Opening hours Saturdays";
$GLOBALS['TL_LANG'][$str]['opening_hours_sunday_legend'] = "Opening hours Sundays";
$GLOBALS['TL_LANG'][$str]['exclusion_legend'] = "Exclusion hours";
$GLOBALS['TL_LANG'][$str]['booking_wd_legend'] = "Possible booking periods or start and end times for multi-day bookings";
$GLOBALS['TL_LANG'][$str]['event_legend'] = 'Event object settings';
$GLOBALS['TL_LANG'][$str]['price_legend'] = 'Price settings';
$GLOBALS['TL_LANG'][$str]['expert_legend'] = 'Expert settings';

/** OPERATIONS **/
$GLOBALS['TL_LANG'][$str]['new'] = array("add object", "add object");
$GLOBALS['TL_LANG'][$str]['edit'] = array("edit object", "edit object ID %s");
$GLOBALS['TL_LANG'][$str]['copy'] = array("Copy object", "Copy object ID %s");
$GLOBALS['TL_LANG'][$str]['delete'] = array("Delete object", "Delete object ID %s");
$GLOBALS['TL_LANG'][$str]['show'] = array("Show object", "Show object ID %s");
$GLOBALS['TL_LANG'][$str]['TOGGLE'] = array("Activate object", "Display object ID %s");
