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
    ->addField(['activateEventReservation'], 'c4g_reservation_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
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
    'eval'              => array('tl_class'=>'w50'),
    'sql'               => "char(1) NOT NULL default '1'"
];