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

use con4gis\ReservationBundle\Controller\C4gReservationCancellationController;
use con4gis\ReservationBundle\Controller\C4gReservationController;
use con4gis\ReservationBundle\Controller\C4gReservationListController;
use con4gis\ReservationBundle\Controller\C4gReservationLocationListController;
use con4gis\ReservationBundle\Controller\C4gReservationObjectsController;
use con4gis\ReservationBundle\Controller\C4gReservationSpeakerListController;
use con4gis\ReservationBundle\Controller\C4gReservationAddressListController;

$GLOBALS['TL_LANG']['MOD']['Reservation'] = array('con4gis Reservation', 'www.con4gis.org');
$GLOBALS['TL_LANG']['MOD']['C4gReservation'] = array('Reservations', 'What times have already been reserved?');
$GLOBALS['TL_LANG']['MOD']['C4gReservationObject'] = array('Reservable objects', 'Define bookable objects (e.g. rooms, tables).');
$GLOBALS['TL_LANG']['MOD']['C4gReservationLocation'] = array('Event locations', 'Here you can maintain the event locations.');
$GLOBALS['TL_LANG']['MOD']['C4gReservationLocation'] = array('Event organizers/locations', 'Here you can maintain the event organizers and locations.');
$GLOBALS['TL_LANG']['MOD']['C4gReservationType'] = array('Reservation types', 'Define the selection of reservation types.');
$GLOBALS['TL_LANG']['MOD']['C4gReservationParams'] = array('Services & Options', 'Entry of various booking options (optional).');
$GLOBALS['TL_LANG']['MOD']['C4gReservationEvent']  = array('Event <=> Reservation', 'Events and reservations can be linked here.');
$GLOBALS['TL_LANG']['MOD']['C4gReservationEventAudience'] = array('Target audience', 'Here you can store target groups.');
$GLOBALS['TL_LANG']['MOD']['C4gReservationEventSpeaker'] = array('Speaker', 'Here you can maintain the speakers.');
$GLOBALS['TL_LANG']['MOD']['C4gReservationEventTopic'] = array('Topics', 'Here you can, for example, store the seminar topics.');
$GLOBALS['TL_LANG']['MOD']['C4gReservationSettings'] = array('Form settings', 'Here you can configure the form and link it in the module.');

$GLOBALS['TL_LANG']['FMD'][C4gReservationController::TYPE]  = array('con4gis-Reservation: Form', 'Reservation form');
$GLOBALS['TL_LANG']['FMD'][C4gReservationListController::TYPE] = array('con4gis-Reservation: List', 'Reservation list');
$GLOBALS['TL_LANG']['FMD'][C4gReservationCancellationController::TYPE] = array('con4gis-Reservation: Cancellation', 'Reservation');
$GLOBALS['TL_LANG']['FMD'][C4gReservationSpeakerListController::TYPE]  = array('con4gis-Reservation: Speaker', 'Speaker');
$GLOBALS['TL_LANG']['FMD'][C4gReservationLocationListController::TYPE]  = array('con4gis-Reservation: Location', 'Location');
$GLOBALS['TL_LANG']['FMD'][C4gReservationObjectsController::TYPE]  = array('con4gis-Reservation: Object maintenance', 'Members can maintain their own objects. E.g. for a rental system.');
$GLOBALS['TL_LANG']['FMD'][C4gReservationAddressListController::TYPE] = array('con4gis-Reservation: address list', 'Address list e.g. for label printing (special notifications and reservation types).');