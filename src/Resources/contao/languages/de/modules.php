<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @author con4gis contributors (see "authors.md")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

use con4gis\ReservationBundle\Controller\C4gReservationCancellationController;
use con4gis\ReservationBundle\Controller\C4gReservationController;
use con4gis\ReservationBundle\Controller\C4gReservationListController;
use con4gis\ReservationBundle\Controller\C4gReservationObjectsController;
use con4gis\ReservationBundle\Controller\C4gReservationSpeakerListController;
use con4gis\ReservationBundle\Controller\C4gReservationLocationListController;
use con4gis\ReservationBundle\Controller\C4gReservationAddressListController;
use con4gis\ReservationBundle\Controller\C4gReservationCheckInController;

$GLOBALS['TL_LANG']['MOD']['Reservation'] = array('con4gis Reservation', 'www.con4gis.org');
$GLOBALS['TL_LANG']['MOD']['C4gReservation'] = array('Reservierung', 'Auflistung bereits erfolgter Reservierungen');
$GLOBALS['TL_LANG']['MOD']['C4gReservationObject'] = array('Reservierungsobjekt', 'Definition der buchbaren Objekte (z.B. Tische, Räume, Seminare).');
$GLOBALS['TL_LANG']['MOD']['C4gReservationLocation'] = array('Veranstalter-/Ort', 'Veranstalter und Veranstaltungsorte erfassen.');
$GLOBALS['TL_LANG']['MOD']['C4gReservationType'] = array('Reservierungsart', 'Definition der Reservierungsarten (z.B. Tischreservierung, Raumreservierung)');
$GLOBALS['TL_LANG']['MOD']['C4gReservationParams'] = array('Option/Leistung', 'Erfassung verschiedener Buchungsoptionen (optional).');
$GLOBALS['TL_LANG']['MOD']['C4gReservationEvent']  = array('Event <=> Reservierung', 'Hier können Veranstaltungen und Reservierungen verknüpft werden.');
$GLOBALS['TL_LANG']['MOD']['C4gReservationEventAudience'] = array('Zielpublikum', 'Hier können Sie Zielgruppen hinterlegen.');
$GLOBALS['TL_LANG']['MOD']['C4gReservationEventSpeaker'] = array('Referent*in', 'Hier können Sie die Referenten pflegen.');
$GLOBALS['TL_LANG']['MOD']['C4gReservationEventTopic'] = array('Thema/Schwerpunkt', 'Hier können Sie bspw. die Seminarthemen hinterlegen.');
$GLOBALS['TL_LANG']['MOD']['C4gReservationSettings'] = array('Formulareinstellung', 'Hier können Sie das Formular konfigurieren und im Modul verknüpfen.');
$GLOBALS['TL_LANG']['MOD']['C4gReservationCheckIn'] = array('Ticket CheckIn', 'Einfaches Modul zum einchecken der Gäste.');

$GLOBALS['TL_LANG']['FMD'][C4gReservationController::TYPE]  = array('con4gis-Reservation: Formular', 'Reservierungsformular');
$GLOBALS['TL_LANG']['FMD'][C4gReservationListController::TYPE] = array('con4gis-Reservation: Liste', 'Reservierungsliste');
$GLOBALS['TL_LANG']['FMD'][C4gReservationCancellationController::TYPE] = array('con4gis-Reservation: Stornierung', 'Reservierung');
$GLOBALS['TL_LANG']['FMD'][C4gReservationSpeakerListController::TYPE] = array('con4gis-Reservation: Referent*innen', 'Referent*innen Liste');
$GLOBALS['TL_LANG']['FMD'][C4gReservationLocationListController::TYPE] = array('con4gis-Reservation: Orte', 'Orte Liste (z.B. Veranstaltungsorte)');
$GLOBALS['TL_LANG']['FMD'][C4gReservationObjectsController::TYPE] = array('con4gis-Reservation: Objektpflege', 'Mitglieder können eigenen Objekte pflege. Bspw. für ein Verleihsystem.');
$GLOBALS['TL_LANG']['FMD'][C4gReservationAddressListController::TYPE] = array('con4gis-Reservation: Adressliste', 'Adressliste bspw. für den Labeldruck (Spezialbenachrichtigungen und Reservierungsarten).');
$GLOBALS['TL_LANG']['FMD'][C4gReservationCheckInController::TYPE]  = array('con4gis-Reservation: CheckIn', 'Einfaches Modul zum einchecken der Gäste.');
