<?php
/*
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

Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('c4g_reservation_legend', 'con4gisIoLegend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER, true)
    ->addField(['reservationForwarding'], 'c4g_reservation_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_c4g_settings');


/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_c4g_settings']['fields']['reservationForwarding'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['tl_c4g_settings']['reservationForwarding'],
    'exclude'                 => true,
    'inputType'               => 'pageTree',
    'foreignKey'              => 'tl_page.title',
    'eval'                    => array('tl_class'=>'w50 wizard','mandatory'=>false, 'fieldType'=>'radio'),
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
    'relation'                => array('type'=>'hasOne', 'load'=>'eager')
);

