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

/**
 * Backend Modules
 */
$GLOBALS['BE_MOD']['con4gis'] = array_merge($GLOBALS['BE_MOD']['con4gis'], [
    'C4gReservation' => array
    (
        'brick' => 'reservation',
        'tables'    => array('tl_c4g_reservation','tl_c4g_reservation_participants'),
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

    'C4gReservationSettings' => array
    (
        'brick' => 'reservation',
        'tables'    => array('tl_c4g_reservation_settings'),
        'icon'      => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation_settings.svg',
    ),

    'C4gReservationLocation' => array
    (
        'brick' => 'reservation',
        'tables'    => array('tl_c4g_reservation_location'),
        'icon'      => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation_location.svg',
    ),

    'C4gReservationParams' => array
    (
        'brick' => 'reservation',
        'tables'    => array('tl_c4g_reservation_params'),
        'icon'      => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation_params.svg'
    ),

    'C4gReservationEventAudience' => array
    (
        'brick' => 'reservation',
        'tables'    => array('tl_c4g_reservation_event_audience'),
        'icon'      => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation_audience.svg',
    ),

    'C4gReservationEventSpeaker' => array
    (
        'brick' => 'reservation',
        'tables'    => array('tl_c4g_reservation_event_speaker'),
        'icon'      => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation_speaker.svg',
    ),

    'C4gReservationEventTopic' => array
    (
        'brick' => 'reservation',
        'tables'    => array('tl_c4g_reservation_event_topic'),
        'icon'      => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation_topic.svg',
    )
]);

$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['con4gis_reservation_bundle'] = array
(
    // Type
    'con4gis_reservation' => array
    (
        'recipients' => array('admin_email','email','email2','contact_email','member_email'),
        'email_subject'        => array('admin_email','reservation_type','desiredCapacity', 'beginDate', 'beginTime', 'endDate', 'endTime',
            'reservation_object', 'reservation_title', 'included_params', 'additional_params', 'admin_email', 'participantList',
            'salutation', 'title', 'organisation', 'firstname', 'lastname', 'email', 'phone', 'address', 'postal', 'city', 'dateOfBirth',
            'salutation2', 'title2', 'organisation2', 'firstname2', 'lastname2', 'email2', 'phone2', 'address2', 'postal2', 'city2',
            'comment', 'internal_comment', 'contact_name','contact_email','contact_phone','contact_street','contact_postal','contact_city','reservation_id',
            'location','speaker','topic','audience','agreed','description','additional1','additional2','additional3',
            'price','priceTax','priceSum','priceSumTax','priceNet','priceSumNet', 'priceOptionSum', 'priceOptionSumTax', 'reservationTaxRate', 'dbkey'),
        'email_text' => array('reservation_type','desiredCapacity', 'beginDate', 'beginTime', 'endDate', 'endTime',
            'reservation_object', 'reservation_title', 'included_params', 'additional_params', 'admin_email', 'participantList',
            'salutation', 'title', 'organisation', 'firstname', 'lastname', 'email', 'phone', 'address', 'postal', 'city', 'dateOfBirth',
            'salutation2', 'title2', 'organisation2', 'firstname2', 'lastname2', 'email2', 'phone2', 'address2', 'postal2', 'city2',
            'comment', 'internal_comment','contact_name','contact_email','contact_phone','contact_street','contact_postal','contact_city','reservation_id',
            'location','speaker','topic','audience','agreed', 'icsFilename','raw_data','description','additional1','additional2','additional3',
            'price','priceTax','priceSum','priceSumTax','priceNet','priceSumNet', 'priceOptionSum', 'priceOptionSumTax', 'reservationTaxRate', 'dbkey'),
        'email_html' => array('reservation_type','desiredCapacity', 'beginDate', 'beginTime', 'endDate', 'endTime',
            'reservation_object', 'reservation_title', 'included_params', 'additional_params', 'admin_email', 'participantList',
            'salutation', 'title', 'organisation', 'firstname', 'lastname', 'email', 'phone', 'address', 'postal', 'city', 'dateOfBirth',
            'salutation2', 'title2', 'organisation2', 'firstname2', 'lastname2', 'email2', 'phone2', 'address2', 'postal2', 'city2',
            'comment', 'internal_comment', 'contact_name','contact_email','contact_phone','contact_street','contact_postal','contact_city','reservation_id',
            'location','speaker','topic','audience','agreed', 'icsFilename','raw_data','description','additional1','additional2','additional3',
            'price','priceTax','priceSum','priceSumTax','priceNet','priceSumNet', 'priceOptionSum', 'priceOptionSumTax', 'reservationTaxRate', 'dbkey'),
        'email_sender_name' => array('admin_email','email','contact_email','location','contact_name'),
        'email_sender_address' => array('admin_email','email','contact_email','member_email'),
        'email_recipient_cc' => array('admin_email','email','email2','contact_email','member_email'),
        'email_recipient_bcc' => array('admin_email','email','contact_email','member_email'),
        'email_replyTo' => array('admin_email','email','contact_email','member_email'),
        'file_content' => array('reservation_type','desiredCapacity', 'beginDate', 'beginTime', 'endDate', 'endTime',
            'reservation_object', 'reservation_title', 'included_params', 'additional_params', 'admin_email', 'participantList',
            'salutation', 'title', 'organisation', 'firstname', 'lastname', 'email', 'phone', 'address', 'postal', 'city', 'dateOfBirth',
            'salutation2', 'title2', 'organisation2', 'firstname2', 'lastname2', 'email2', 'phone2', 'address2', 'postal2', 'city2',
            'comment', 'internal_comment','contact_name','contact_email','contact_phone','contact_street','contact_postal','contact_city','reservation_id',
            'location','speaker','topic','audience','agreed', 'icsFilename','raw_data','description','additional1','additional2','additional3',
            'price','priceTax','priceSum','priceSumTax','priceNet','priceSumNet', 'priceOptionSum', 'priceOptionSumTax', 'reservationTaxRate', 'dbkey'),
        'attachment_tokens' => array('icsFilename')
    ),
    'con4gis_cancellation'   => array
    (
        'recipients'           => array('admin_email','email','contact_email'),
        'email_subject'        => array('firstname','lastname','reservation_id','beginDate', 'beginTime', 'endDate', 'endTime'),
        'email_text'           => array('firstname','lastname','reservation_id','beginDate', 'beginTime', 'endDate', 'endTime'),
        'email_html'           => array('firstname','lastname','reservation_id','beginDate', 'beginTime', 'endDate', 'endTime'),
        'email_sender_name'    => array('admin_email','email','contact_email'),
        'email_sender_address' => array('admin_email','email','contact_email'),
        'email_recipient_cc'   => array('admin_email','email','contact_email'),
        'email_recipient_bcc'  => array('admin_email','email','contact_email'),
        'email_replyTo'        => array('admin_email','email','contact_email'),
        'file_content'         => array('firstname','lastname','reservation_id','beginDate', 'beginTime', 'endDate', 'endTime')
    ),
    'con4gis_reservation_confirmation' => array
    (
        'recipients' => array('admin_email','email','email2','contact_email','member_email'),
        'email_subject' => array('admin_email','reservation_type','desiredCapacity', 'beginDate', 'beginTime', 'endDate', 'endTime',
            'reservation_object', 'reservation_title', 'included_params', 'additional_params', 'admin_email', 'participantList',
            'salutation', 'title', 'organisation', 'firstname', 'lastname', 'email', 'phone', 'address', 'postal', 'city', 'dateOfBirth',
            'salutation2', 'title2', 'organisation2', 'firstname2', 'lastname2', 'email2', 'phone2', 'address2', 'postal2', 'city2',
            'comment', 'internal_comment', 'contact_name','contact_email','contact_phone','contact_street','contact_postal','contact_city','reservation_id',
            'location','speaker','topic','audience','agreed','description','additional1','additional2','additional3',
            'price','priceTax','priceSum','priceSumTax','priceNet','priceSumNet', 'priceOptionSum', 'priceOptionSumTax', 'reservationTaxRate', 'dbkey'),
        'email_text' => array('reservation_type','desiredCapacity', 'beginDate', 'beginTime', 'endDate', 'endTime',
            'reservation_object', 'reservation_title', 'included_params', 'additional_params', 'admin_email', 'participantList',
            'salutation', 'title', 'organisation', 'firstname', 'lastname', 'email', 'phone', 'address', 'postal', 'city', 'dateOfBirth',
            'salutation2', 'title2', 'organisation2', 'firstname2', 'lastname2', 'email2', 'phone2', 'address2', 'postal2', 'city2',
            'comment', 'internal_comment','contact_name','contact_email','contact_phone','contact_street','contact_postal','contact_city','reservation_id',
            'location','speaker','topic','audience','agreed','description','additional1','additional2','additional3',
            'price','priceSum','priceSum','priceSumTax','priceNet','priceSumNet', 'priceOptionSum', 'priceOptionSumTax', 'reservationTaxRate', 'dbkey'),
        'email_html' => array('reservation_type','desiredCapacity', 'beginDate', 'beginTime', 'endDate', 'endTime',
            'reservation_object', 'reservation_title', 'included_params', 'additional_params', 'admin_email', 'participantList',
            'salutation', 'title', 'organisation', 'firstname', 'lastname', 'email', 'phone', 'address', 'postal', 'city', 'dateOfBirth',
            'salutation2', 'title2', 'organisation2', 'firstname2', 'lastname2', 'email2', 'phone2', 'address2', 'postal2', 'city2',
            'comment', 'internal_comment', 'contact_name','contact_email','contact_phone','contact_street','contact_postal','contact_city','reservation_id',
            'location','speaker','topic','audience','agreed','description','additional1','additional2','additional3',
            'price','priceTax','priceSum','priceSumTax','priceNet','priceSumNet', 'priceOptionSum', 'priceOptionSumTax', 'reservationTaxRate', 'dbkey'),
        'email_sender_name' => array('admin_email','email','contact_email','location','contact_name'),
        'email_sender_address' => array('admin_email','email','contact_email','member_email'),
        'email_recipient_cc' => array('admin_email','email','email2','contact_email','member_email'),
        'email_recipient_bcc' => array('admin_email','email','contact_email','member_email'),
        'email_replyTo' => array('admin_email','email','contact_email','member_email'),
        'file_content' => array('reservation_type','desiredCapacity', 'beginDate', 'beginTime', 'endDate', 'endTime',
            'reservation_object', 'reservation_title', 'included_params', 'additional_params', 'admin_email', 'participantList',
            'salutation', 'title', 'organisation', 'firstname', 'lastname', 'email', 'phone', 'address', 'postal', 'city', 'dateOfBirth',
            'salutation2', 'title2', 'organisation2', 'firstname2', 'lastname2', 'email2', 'phone2', 'address2', 'postal2', 'city2',
            'comment', 'internal_comment','contact_name','contact_email','contact_phone','contact_street','contact_postal','contact_city','reservation_id',
            'location','speaker','topic','audience','agreed','description','additional1','additional2','additional3',
            'price','priceTax','priceSum','priceSumTax','priceNet','priceSumNet', 'priceOptionSum', 'priceOptionSumTax', 'reservationTaxRate', 'dbkey'),
        'attachment_tokens' => array('uploadFile')
    )
);

$GLOBALS['TL_MODELS']['tl_c4g_reservation'] = \con4gis\ReservationBundle\Classes\Models\C4gReservationModel::class;
$GLOBALS['TL_MODELS']['tl_c4g_reservation_object'] = \con4gis\ReservationBundle\Classes\Models\C4gReservationObjectModel::class;
$GLOBALS['TL_MODELS']['tl_c4g_reservation_type'] = \con4gis\ReservationBundle\Classes\Models\C4gReservationTypeModel::class;
$GLOBALS['TL_MODELS']['tl_c4g_reservation_settings'] = \con4gis\ReservationBundle\Classes\Models\C4gReservationSettingsModel::class;
$GLOBALS['TL_MODELS']['tl_c4g_reservation_params'] = \con4gis\ReservationBundle\Classes\Models\C4gReservationParamsModel::class;
$GLOBALS['TL_MODELS']['tl_c4g_reservation_event'] = \con4gis\ReservationBundle\Classes\Models\C4gReservationEventModel::class;
$GLOBALS['TL_MODELS']['tl_c4g_reservation_location'] = \con4gis\ReservationBundle\Classes\Models\C4gReservationLocationModel::class;
$GLOBALS['TL_MODELS']['tl_c4g_reservation_event_speaker'] = \con4gis\ReservationBundle\Classes\Models\C4gReservationEventSpeakerModel::class;
$GLOBALS['TL_MODELS']['tl_c4g_reservation_event_audience'] = \con4gis\ReservationBundle\Classes\Models\C4gReservationEventAudienceModel::class;
$GLOBALS['TL_MODELS']['tl_c4g_reservation_event_topic'] = \con4gis\ReservationBundle\Classes\Models\C4gReservationEventTopicModel::class;

$GLOBALS['TL_CRON']['daily']['reservationOnDaily'] = [\con4gis\ReservationBundle\Classes\Cron\Cron::class, 'onDaily'];

//Can be overridden in the plugin bundle
$GLOBALS['TL_CRON']['minutely']['reservationOnMinutely'] = [\con4gis\ReservationBundle\Classes\Cron\Cron::class, 'onMinutely'];

$GLOBALS['BE_MOD']['content']['calendar']['tables'][] = 'tl_c4g_reservation_event';
$GLOBALS['BE_MOD']['content']['calendar']['tables'][] = 'tl_c4g_reservation';
$GLOBALS['BE_MOD']['content']['calendar']['tables'][] = 'tl_c4g_reservation_participants';

$exportExists = class_exists('con4gis\ExportBundle\con4gisExportBundle');
if ($exportExists) {
    $GLOBALS['BE_MOD']['content']['calendar']['tables'][] = 'tl_c4g_export';
}

$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array(\con4gis\ReservationBundle\Classes\InsertTags\C4gReservationInsertTags::class, 'replaceTag');