<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @author con4gis contributors (see "authors.md")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

$exportExists = class_exists('con4gis\ExportBundle\con4gisExportBundle');
if ($exportExists) {
    Contao\CoreBundle\DataContainer\PaletteManipulator::create()
        ->addLegend('c4g_reservation_legend', 'con4gisIoLegend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER, true)
        ->addField(['reservationForwarding','reservationForwardingButtonCaption','exportSelection', 'taxRateStandard', 'taxRateReduced'], 'c4g_reservation_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
        ->applyToPalette('default', 'tl_c4g_settings');
} else {
    Contao\CoreBundle\DataContainer\PaletteManipulator::create()
        ->addLegend('c4g_reservation_legend', 'con4gisIoLegend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER, true)
        ->addField(['reservationForwarding','reservationForwardingButtonCaption'], 'c4g_reservation_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
        ->applyToPalette('default', 'tl_c4g_settings');
}

/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_c4g_settings']['fields']['reservationForwarding'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_settings']['reservationForwarding'],
    'exclude'                 => true,
    'inputType'               => 'pageTree',
    'foreignKey'              => 'tl_page.title',
    'eval'                    => array('tl_class'=>'w50 wizard','mandatory'=>false, 'fieldType'=>'radio'),
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
    'relation'                => array('type'=>'hasOne', 'load'=>'eager')
);

$GLOBALS['TL_DCA']['tl_c4g_settings']['fields']['reservationForwardingButtonCaption'] = array (
    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_settings']['reservationForwardingButtonCaption'],
    'exclude'                 => true,
    'filter'                  => false,
    'search'                  => false,
    'sorting'                 => false,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>false, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'clr long'),
    // 'sql'                     => "varchar(254) NOT NULL default ''"
    'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
);

if ($exportExists) {
    $GLOBALS['TL_DCA']['tl_c4g_settings']['fields']['exportSelection'] = array (
        'label'             => &$GLOBALS['TL_LANG']['tl_c4g_settings']['exportSelection'],
        'inputType'         => 'select',
        'filter'            => true,
        'exclude'           => true,
        'foreignKey'        => 'tl_c4g_export.title',
        'eval'              => array('mandatory' => false, 'tl_class' => 'long', 'chosen' => true, 'includeBlankOption' => true),
        'sql'               => "int(10) unsigned NOT NULL default 0",
        'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),
    );
$GLOBALS['TL_DCA']['tl_c4g_settings']['fields']['taxRateStandard'] = array(
        'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_settings']['fields']['taxRateStandard'],
        'exclude'                 => true,
        'inputType'               => 'text',
        'default'                 => 19.0,
        'eval'                    => array('rgxp'=>'digit','mandatory'=>false, 'maxlength'=>10, 'default'=> 19.0, 'feEditable'=>true, 'feViewable'=>true, 'tl_class' => 'w50'),
        'sql'                     => "double(7,2) unsigned NOT NULL default 19.0"
);
$GLOBALS['TL_DCA']['tl_c4g_settings']['fields']['taxRateReduced'] = array(
        'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_settings']['fields']['taxRateReduced'],
        'exclude'                 => true,
        'inputType'               => 'text',
        'default'                 => 7.0,
        'eval'                    => array('rgxp'=>'digit','mandatory'=>false, 'maxlength'=>10, 'default'=> 7.0, 'feEditable'=>true, 'feViewable'=>true, 'tl_class' => 'w50'),
        'sql'                     => "double(7,2) unsigned NOT NULL default 7.0"
);
}