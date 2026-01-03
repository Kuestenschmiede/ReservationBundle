<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @author con4gis contributors (see "authors.md")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design
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
$GLOBALS['BE_MOD']['content']['calendar']['tables'][] = 'tl_c4g_reservation_event_participants';


$exportExists = class_exists('con4gis\ExportBundle\con4gisExportBundle');
if ($exportExists) {
    $GLOBALS['BE_MOD']['content']['calendar']['tables'][] = 'tl_c4g_export';
}

$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array(\con4gis\ReservationBundle\Classes\InsertTags\C4gReservationInsertTags::class, 'replaceTag');

// Hook: Projects-Framework generische Print-Daten-Anreicherung
if (!isset($GLOBALS['TL_HOOKS']['c4gProjectsPreparePrintData']) || !is_array($GLOBALS['TL_HOOKS']['c4gProjectsPreparePrintData'])) {
    $GLOBALS['TL_HOOKS']['c4gProjectsPreparePrintData'] = [];
}
$GLOBALS['TL_HOOKS']['c4gProjectsPreparePrintData'][] = [
    \con4gis\ReservationBundle\Classes\Hooks\ReservationPrintDataEnricher::class,
    'enrich'
];

/**
 * Purge entry for reservation form cache (System > Wartung).
 */
$GLOBALS['TL_PURGE']['folders']['con4gis_reservation_form'] = [
    'callback' => [\con4gis\ReservationBundle\Classes\Caches\C4gReservationCacheAutomator::class, 'purgeReservationFormCache'],
    'affected' => ['var/cache/* (cache.app)']
];