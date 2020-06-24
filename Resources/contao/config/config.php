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
 * Frontend Modules
 */
$GLOBALS['FE_MOD']['con4gis']['C4gReservation'] = \con4gis\ReservationBundle\Resources\contao\modules\C4gReservation::class;
$GLOBALS['FE_MOD']['con4gis']['C4gReservationCancellation'] = \con4gis\ReservationBundle\Resources\contao\modules\C4gReservationCancellation::class;
asort($GLOBALS['FE_MOD']['con4gis']);


/**
 * Backend Modules
 */
$GLOBALS['BE_MOD']['con4gis'] = array_merge($GLOBALS['BE_MOD']['con4gis'], [
    'C4gReservation' => array
    (
        'brick' => 'reservation',
        'tables'    => array('tl_c4g_reservation'),
        'icon'      => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation.svg',
    ),

    'C4gReservationType' => array
    (
        'brick' => 'reservation',
        'tables'    => array('tl_c4g_reservation_type'),
        'icon'      => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation_types.svg',
    ),

    'C4gReservationObject' => array
    (
        'brick' => 'reservation',
        'tables'    => array('tl_c4g_reservation_object'),
        'icon'      => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation_objects.svg',
    ),

    'C4gReservationObjectPrices' => array
    (
        'brick' => 'reservation',
        'tables'    => array('tl_c4g_reservation_object_prices'),
        'icon'      => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation_prices.svg',
    ),

    'C4gReservationParams' => array
    (
        'brick' => 'reservation',
        'tables'    => array('tl_c4g_reservation_params'),
        'icon'      => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation_params.svg'
    )
]);

$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['con4gis_reservation_bundle'] = array
(
    // Type
    'con4gis_reservation'   => array
    (
        'recipients'           => array('admin_email','email'),
        'email_subject'        => array('admin_email', 'reservation_type','desiredCapacity', 'reservation_date','reservation_time', 'beginDate', 'beginTime', 'endDate', 'endTime', 'reservation_object', 'additional_params', 'firstname', 'lastname', 'email', 'phone', 'address', 'postal', 'city',  'comment', 'internal_comment', 'reservation_id'),
        'email_text'           => array('reservation_type','desiredCapacity','reservation_date','reservation_time',  'beginDate', 'beginTime', 'endDate', 'endTime', 'reservation_object', 'additional_params', 'firstname', 'lastname', 'admin_email', 'email', 'phone', 'address', 'postal', 'city', 'comment', 'internal_comment', 'reservation_id'),
        'email_html'           => array('reservation_type','desiredCapacity','reservation_date', 'reservation_time',  'beginDate', 'beginTime', 'endDate', 'endTime', 'reservation_object', 'additional_params', 'firstname', 'lastname', 'admin_email', 'email', 'phone', 'address', 'postal', 'city', 'comment', 'internal_comment', 'reservation_id'),
        'email_sender_name'    => array('admin_email','email'),
        'email_sender_address' => array('admin_email','email'),
        'email_recipient_cc'   => array('admin_email','email'),
        'email_recipient_bcc'  => array('admin_email','email'),
        'email_replyTo'        => array('admin_email','email'),
    ),
    'con4gis_cancellation'   => array
    (
        'recipients'           => array('admin_email','email'),
        'email_subject'        => array('admin_email','lastname','reservation_id'),
        'email_text'           => array('lastname','reservation_id'),
        'email_html'           => array('lastname','reservation_id'),
        'email_sender_name'    => array('admin_email','email'),
        'email_sender_address' => array('admin_email','email'),
        'email_recipient_cc'   => array('admin_email','email'),
        'email_recipient_bcc'  => array('admin_email','email'),
        'email_replyTo'        => array('admin_email','email'),
    )
);

$GLOBALS['TL_MODELS']['tl_c4g_reservation'] = \con4gis\ReservationBundle\Resources\contao\models\C4gReservationModel::class;
$GLOBALS['TL_MODELS']['tl_c4g_reservation_object'] = \con4gis\ReservationBundle\Resources\contao\models\C4gReservationObjectModel::class;
$GLOBALS['TL_MODELS']['tl_c4g_reservation_type'] = \con4gis\ReservationBundle\Resources\contao\models\C4gReservationTypeModel::class;
$GLOBALS['TL_MODELS']['tl_c4g_reservation_params'] = \con4gis\ReservationBundle\Resources\contao\models\C4gReservationParamsModel::class;
$GLOBALS['TL_MODELS']['tl_c4g_reservation_object_prices'] = \con4gis\ReservationBundle\Resources\contao\models\C4gReservationObjectPrices::class;
$GLOBALS['TL_CRON']['daily'][] = [\con4gis\ReservationBundle\Classes\Cron::class, 'onDaily'];



