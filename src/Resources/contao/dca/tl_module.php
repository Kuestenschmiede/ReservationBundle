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
 * Table tl_module
 */
//ToDo showFreeSeats, additionalDuration
use con4gis\ProjectsBundle\Classes\Lists\C4GBrickRenderMode;
use con4gis\ReservationBundle\Controller\C4gReservationCancellationController;
use con4gis\ReservationBundle\Controller\C4gReservationController;
use con4gis\ReservationBundle\Controller\C4gReservationListController;
use con4gis\ReservationBundle\Controller\C4gReservationSpeakerListController;

$GLOBALS['TL_DCA']['tl_module']['palettes'][C4gReservationController::TYPE]   = '{title_legend},name,headline,type;{reservation_legend},reservation_settings;';

$GLOBALS['TL_DCA']['tl_module']['palettes'][C4gReservationListController::TYPE]  = '{list_legend},name,headline,type;{reservation_legend}, reservationView, showReservationType, showReservationObject, showSignatureField, cancellation_redirect_site, login_redirect_site;';

$GLOBALS['TL_DCA']['tl_module']['palettes'][C4gReservationCancellationController::TYPE] = '{title_legend},name,headline,type; {reservation_notification_center_legend}, notification_type_contact_request; {reservation_redirect_legend}, reservation_redirect_site;';

$GLOBALS['TL_DCA']['tl_module']['palettes'][C4gReservationSpeakerListController::TYPE]  = '{title_legend},name,headline,type;{list_legend},renderMode,event_redirect_site;';

$GLOBALS['TL_DCA']['tl_module']['fields']['reservation_settings'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['reservation_settings'],
    'exclude'                 => true,
    'filter'                  => true,
    'inputType'               => 'select',
    'foreignKey'              => 'tl_c4g_reservation_settings.caption',
    'eval'                    => array('chosen'=>true,'mandatory'=>true,'multiple'=>false, 'disabled' => false, 'tl_class'=>'long clr'),
    'sql'                     => "blob NULL'"
];
$GLOBALS['TL_DCA']['tl_module']['fields']['renderMode'] = array
(
    'label'             => &$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['renderMode'],
    'exclude'           => true,
    'filter'            => true,
    'inputType'         => 'select',
    'default'           => C4GBrickRenderMode::TILEBASED,
    'options'           => [C4GBrickRenderMode::TILEBASED,C4GBrickRenderMode::TABLEBASED,C4GBrickRenderMode::LISTBASED],
    'reference'          => &$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references'],
    'sql'               => "varchar(25) NOT NULL default 'tiles'"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['event_redirect_site'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['event_redirect_site'],
    'exclude'                 => true,
    'inputType'               => 'pageTree',
    'foreignKey'              => 'tl_page.title',
    'eval'                    => array('mandatory'=>false, 'fieldType'=>'radio'),
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
    'relation'                => array('type'=>'hasOne', 'load'=>'eager')
);
$GLOBALS['TL_DCA']['tl_module']['fields']['login_redirect_site'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['login_redirect_site'],
    'exclude'                 => true,
    'inputType'               => 'pageTree',
    'foreignKey'              => 'tl_page.title',
    'eval'                    => array('mandatory'=>false, 'fieldType'=>'radio'),
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
    'relation'                => array('type'=>'hasOne', 'load'=>'eager')
);
$GLOBALS['TL_DCA']['tl_module']['fields']['notification_type_contact_request'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['notification_type_contact_request'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'foreignKey'              => 'tl_nc_notification.title',
    'eval'                    => array('multiple' => true),
    'sql'                     => "varchar(100) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['reservation_redirect_site'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['redirect_site'],
    'exclude'                 => true,
    'inputType'               => 'pageTree',
    'foreignKey'              => 'tl_page.title',
    'eval'                    => array('mandatory'=>true, 'fieldType'=>'radio'),
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
    'relation'                => array('type'=>'hasOne', 'load'=>'eager')
);
$GLOBALS['TL_DCA']['tl_module']['fields']['reservationView'] = array
(
    'label'             => &$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['reservationView'],
    'exclude'           => true,
    'filter'            => true,
    'inputType'         => 'select',
    'default'           => 'publicview',
    'options'           => ['publicview','memberview','member','group'],
    'reference'          => &$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['references'],
    'sql'               => "varchar(25) NOT NULL default 'publicview'"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['showReservationType'] = array
(   'label'             => &$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['showReservationType'],
    'default'           => 0,
    'exclude'           => true,
    'filter'            => true,
    'inputType'         => 'checkbox',
    'sql'               => "int(1) unsigned NULL default 0"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['showReservationObject'] = array
(   'label'             => &$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['showReservationObject'],
    'default'           => 1,
    'exclude'           => true,
    'filter'            => true,
    'inputType'         => 'checkbox',
    'sql'               => "int(1) unsigned NULL default 1"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['showSignatureField'] = array
(   'label'             => &$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['showSignatureField'],
    'default'           => 0,
    'exclude'           => true,
    'filter'            => true,
    'inputType'         => 'checkbox',
    'sql'               => "int(1) unsigned NULL default 0"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['cancellation_redirect_site'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['c4g_reservation']['fields']['redirect_site'],
    'exclude'                 => true,
    'inputType'               => 'pageTree',
    'foreignKey'              => 'tl_page.title',
    'eval'                    => array('mandatory'=>false, 'fieldType'=>'radio'),
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
    'relation'                => array('type'=>'hasOne', 'load'=>'eager')
);
