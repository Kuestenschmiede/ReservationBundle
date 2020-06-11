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

/** Fields **/
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['caption'] = array("backend name", "name for selection in backend.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['options'] = array("Frontend name", "Displayed in the selection field in the frontend depending on the language.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['option'] = array("option","");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['periodType'] = array("period type","Declare the type of time period");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['objectCount'] = array("Maximum number of objects per time span","Maximum number of simultaneously bookable objects");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['language'] = array("language","");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['auto_del'] = array("Interval for automatic deletion","At which interval should completed appointments be deleted");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['del_time'] = array("Specify value for interval","Number of days after which an existing appointment is deleted");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['daily'] = array("Clear interval");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['no_delete'] = array("Delete manually only");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['minute'] = array("minutes");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['hour'] = array("hours");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['openingHours'] = array("opening hours");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['md'] = array("several days");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['beginDate'] = array("start date", "On which day does the period begin?");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['endDate'] = array("end date", "On which day does the period end");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event_id'] = array("Contao event", "Select the appropriate Contao event.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event_dayBegin'] = array("Event start", "On what day does the event start.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event_dayEnd'] = array("Event end", "On which day the event ends.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event_timeBegin'] = array("Event start", "At what time does the event start.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event_timeEnd'] = array("Event end", "At what time does the event end.");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event'] = array("Self defined event");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['contao_event'] = array("Contao event");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['fixed'] = array("fixed time");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['timeBegin'] = array("start time");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['timeEnd'] = array("time end");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['dayBegin'] = array("start day");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['published'] = array("Publish", "Should the reservation type displayed in the frontend?");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['dayEnd'] = array("end day");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['additional_params'] = array("Reservation options");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['description'] = array("Description");

/** Legends **/
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['type_legend'] = "Details of the type of reservation";
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['expert_legend'] = "Expert settings";

/** OPERATIONS **/
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['new'] = array("add reservation type", "add reservation type");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['edit'] = array("Edit reservation type", "Edit reservation type ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['copy'] = array("Copy reservation type", "Copy reservation type ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['delete'] = array("delete reservation type", "delete reservation type ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['show'] = array("Display reservation type", "Display reservation type ID %s");
$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['TOGGLE'] = array("Activate reservation type", "Activate reservation type ID %s");
