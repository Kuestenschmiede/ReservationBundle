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

$str = 'tl_calendar';

Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('c4g_reservation_legend', 'comments_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER, true)
    ->addField(['activateEventReservation','reservationForwarding','reservationForwardingButtonCaption'], 'c4g_reservation_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', $str);


/**
 * Add fields
 */
$GLOBALS['TL_DCA'][$str]['fields']['activateEventReservation'] = [
    'label'             => &$GLOBALS['TL_LANG'][$str]['activateEventReservation'],
    'default'           => false,
    'exclude'           => true,
    'filter'            => false,
    'inputType'         => 'checkbox',
    'eval'              => array('tl_class'=>'w50 clr'),
    'sql'               => "char(1) NOT NULL default '1'"
];

$GLOBALS['TL_DCA'][$str]['fields']['reservationForwarding'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_settings']['reservationForwarding'],
    'exclude'                 => true,
    'inputType'               => 'pageTree',
    'foreignKey'              => 'tl_page.title',
    'eval'                    => array('tl_class'=>'w50 clr wizard','mandatory'=>false, 'fieldType'=>'radio'),
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
    'relation'                => array('type'=>'hasOne', 'load'=>'eager')
];

$GLOBALS['TL_DCA'][$str]['fields']['reservationForwardingButtonCaption'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_settings']['reservationForwardingButtonCaption'],
    'exclude'                 => true,
    'filter'                  => false,
    'search'                  => false,
    'sorting'                 => false,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>false, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'w50'),
    'sql'                     => "varchar(254) NOT NULL default ''"
];