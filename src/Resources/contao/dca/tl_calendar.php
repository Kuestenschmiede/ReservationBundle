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

use con4gis\ReservationBundle\Classes\Callbacks\ReservationCalendarEvents;

$str = 'tl_calendar';

Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('c4g_reservation_legend', 'comments_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER, true)
    ->addField(['activateEventReservation','reservationForwarding','reservationForwardingButtonCaption,reservationLocation,reservationOrganizer,reservationType,reservationMinParticipants,reservationMaxParticipants,reservationSpeaker,reservationTopic,reservationtargetAudience,reservationPrice,reservationPriceOption'], 'c4g_reservation_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
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

$GLOBALS['TL_DCA'][$str]['fields']['reservationLocation'] = [
    'label'             => &$GLOBALS['TL_LANG'][$str]['location'],
    'exclude'           => true,
    'default'           => 0,
    'inputType'         => 'select',
    'foreignKey'        => 'tl_c4g_reservation_location.name',
    'eval'              => array('chosen' => true, 'mandatory' => false, 'tl_class' => 'long clr','includeBlankOption'=>true, 'doNotCopy' => true),
    'sql'               => "int(10) unsigned NOT NULL default 0",
    'relation'          => array('type' => 'hasOne', 'load' => 'eager'),
];

$GLOBALS['TL_DCA'][$str]['fields']['reservationOrganizer'] = [
    'label'             => &$GLOBALS['TL_LANG'][$str]['organizer'],
    'exclude'           => true,
    'default'           => 0,
    'inputType'         => 'select',
    'foreignKey'        => 'tl_c4g_reservation_location.name',
    'eval'              => array('chosen' => true, 'mandatory' => false, 'tl_class' => 'long clr','includeBlankOption'=>true, 'doNotCopy' => true),
    'sql'               => "int(10) unsigned NOT NULL default 0",
    'relation'          => array('type' => 'hasOne', 'load' => 'eager'),
];

$GLOBALS['TL_DCA'][$str]['fields']['reservationType'] = [
    'label'             => &$GLOBALS['TL_LANG'][$str]['reservationType'],
    'exclude'           => true,
    'inputType'         => 'select',
    'options_callback'  => [ReservationCalendarEvents::class, 'getReservationTypes'],
    'eval'              => ['mandatory' => false, 'tl_class' => 'long clr', 'doNotCopy' => true],
    'sql'               => "int(10) unsigned NOT NULL default 0"
];

$GLOBALS['TL_DCA'][$str]['fields']['reservationMinParticipants'] = [
    'label'             => &$GLOBALS['TL_LANG'][$str]['minParticipants'],
    'exclude'           => true,
    'sorting'           => false,
    'flag'              => 1,
    'search'            => false,
    'inputType'         => 'text',
    'eval'              => array('mandatory'=>false, 'rgxp'=>'digit', 'tl_class'=>'w50', 'doNotCopy' => true),
    'sql'               => "smallint(3) NOT NULL default 1"
];

$GLOBALS['TL_DCA'][$str]['fields']['reservationMaxParticipants'] = [
    'label'             => &$GLOBALS['TL_LANG'][$str]['maxParticipants'],
    'exclude'           => true,
    'sorting'           => false,
    'flag'              => 1,
    'search'            => false,
    'inputType'         => 'text',
    'eval'              => array('mandatory'=>false, 'rgxp'=>'digit', 'tl_class'=>'w50', 'doNotCopy' => true),
    'sql'               => "smallint(3) NOT NULL default 0"
];

$GLOBALS['TL_DCA'][$str]['fields']['reservationSpeaker'] = [
    'label'             => &$GLOBALS['TL_LANG'][$str]['speaker'],
    'exclude'           => true,
    'inputType'         => 'checkbox',
    'options_callback'  => [ReservationCalendarEvents::class, 'getSpeakerName'],
    'eval'              => array('mandatory' => false, 'tl_class' => 'long clr', 'multiple' => true, 'chosen' => true,'includeBlankOption'=>true, 'doNotCopy' => true),
    'sql'               => "blob NULL"
];

$GLOBALS['TL_DCA'][$str]['fields']['reservationTopic'] = [
    'label'             => &$GLOBALS['TL_LANG'][$str]['topic'],
    'exclude'           => true,
    'inputType'         => 'checkbox',
    'foreignKey'        => 'tl_c4g_reservation_event_topic.topic',
    'eval'              => array('mandatory' => false, 'tl_class' => 'long clr', 'multiple' => true, 'chosen' => true,'includeBlankOption'=>true, 'doNotCopy' => true),
    'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),
    'sql'               => "blob NULL"
];

$GLOBALS['TL_DCA'][$str]['fields']['reservationtargetAudience'] = [
    'label'             => &$GLOBALS['TL_LANG'][$str]['targetAudience'],
    'exclude'           => true,
    'inputType'         => 'checkbox',
    'foreignKey'        => 'tl_c4g_reservation_event_audience.targetAudience',
    'eval'              => array('mandatory' => false, 'tl_class' => 'long clr', 'multiple' => true, 'chosen' => true,'includeBlankOption'=>true, 'doNotCopy' => true),
    'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),
    'sql'               => "blob NULL"
];

$GLOBALS['TL_DCA'][$str]['fields']['reservationPrice'] = [
    'label'                   => &$GLOBALS['TL_LANG'][$str]['price'],
    'exclude'                 => true,
    'search'                  => false,
    'inputType'               => 'text',
    'default'                 => '0.00',
    'eval'                    => array('rgxp'=>'digit','mandatory'=>false, 'maxlength'=>10, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'w50 clr'),
    'sql'                     => "double(7,2) unsigned default '0'"
];

$GLOBALS['TL_DCA'][$str]['fields']['reservationPriceOption'] = [
    'label'                   => &$GLOBALS['TL_LANG'][$str]['priceoption'],
    'exclude'                 => true,
    'inputType'               => 'radio',
    'options'                 => array('pReservation','pPerson','pDay','pNight','pNightPerson','pHour','pMin','pAmount'),
    'default'                 => '',
    'reference'               => &$GLOBALS['TL_LANG'][$str]['references'],
    'eval'                    => array('mandatory'=>false, 'tl_class' => 'long clr'),
    'sql'                     => "varchar(50) NOT NULL default ''"
];