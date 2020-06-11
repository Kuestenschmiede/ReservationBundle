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

use con4gis\CoreBundle\Classes\C4GVersionProvider;


/**
 * Table tl_module
 */

if (C4GVersionProvider::isInstalled('con4gis/maps')) {
    $default = '{type_legend}, caption, quantity, options, description, desiredCapacityMin, desiredCapacityMax, viewableTypes, additionalBookingParams, min_reservation_day, max_reservation_day;{time_interval_legend},time_interval;{booking_wd_legend}, oh_monday,oh_tuesday, oh_wednesday,oh_thursday, oh_friday,oh_saturday,oh_sunday;{event_legend},event_selection;{exclusion_legend}, days_exclusion;{location_legend},locgeox, locgeoy;{publish_legend}, published';
} else {
    $default = '{type_legend}, caption, quantity, options, description, desiredCapacityMin, desiredCapacityMax, viewableTypes, additionalBookingParams, min_reservation_day, max_reservation_day;{time_interval_legend},time_interval;{booking_wd_legend}, oh_monday,oh_tuesday, oh_wednesday,oh_thursday, oh_friday,oh_saturday,oh_sunday;{event_legend},event_selection;{exclusion_legend}, days_exclusion;{publish_legend}, published';
}

$GLOBALS['TL_DCA']['tl_c4g_reservation_object'] = array
(
    //config
    'config' => array
    (   //'onsubmit_callback' => [['tl_c4g_reservation_object', 'multi_booking']],
        'dataContainer'     => 'Table',
        'enableVersioning'  => 'true',
        'sql'               => array
        (
            'keys' => array
            (
                'id' => 'primary'
            )
        )
    ),

    //List
    'list' => array
    (
        'sorting' => array
        (
            'mode'              => 2,
            'fields'            => array('caption','quantity','desiredCapacityMin','desiredCapacityMax','viewableTypes','time_interval'),
            'panelLayout'       => 'filter;sort,search,limit',
            'headerFields'      => array('caption','quantity','desiredCapacityMin','desiredCapacityMax','viewableTypes','time_interval'),
        ),

        'label' => array
        (
            'fields'            => array('caption','quantity','desiredCapacityMin','desiredCapacityMax','viewableTypes:tl_c4g_reservation_type.caption','time_interval'),
            'label_callback'    => array('tl_c4g_reservation_object', 'listFields'),
            'showColumns'       => true
        ),

        'global_operations' => array
        (
            'all' => array
            (
                'label'         => $GLOBALS['TL_LANG']['MSC']['all'],
                'href'          => 'act=select',
                'class'         => 'header_edit_all',
                'attributes'    => 'onclick="Backend.getScrollOffSet()" accesskey="e"'
            )
        ),

        'operations' => array
        (
            'edit' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['edit'],
                'href'          => 'act=edit',
                'icon'          => 'edit.gif',
            ),
            'copy' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['copy'],
                'href'          => 'act=copy',
                'icon'          => 'copy.gif',
            ),
            'delete' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['delete'],
                'href'          => 'act=delete',
                'icon'          => 'delete.gif',
                'attributes'    => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false;Backend.getScrollOffset()"',
            ),
            'show' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['show'],
                'href'          => 'act=show',
                'icon'          => 'show.gif',

            ),
            'toggle' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['toggle'],
                'icon'                => 'visible.gif',
                'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'     => array('tl_c4g_reservation_object', 'toggleIcon')
           )
        )
    ),

    //Palettes
    'palettes' => array
    (
        'default'   =>  $default,

 /*       '__selector__' => array('event_selection')
    ),

    //Subpalettes
       'subpalettes' => array(
        'event_selection_event_object'             =>'{event_object_legend}, event_dayBegin, event_timeBegin, event_dayEnd, event_timeEnd ;',
       'event_selection_contao_event'             =>'{event_object_legend}, event_id  ;'
*/

    ),

    //Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'               => "int(10) unsigned NOT NULL auto_increment"
        ),

        'tstamp' => array
        (
            'sql'               => "int(10) unsigned NOT NULL default 0"
        ),

        'uuid' => array
        (
            'label'             => array('uuid','uuid'),
            'exclude'           => true,
            'inputType'         => 'text',
            'search'            => true,
            'eval'              => array('doNotCopy'=>true, 'maxlength'=>128),
            'save_callback'     => array(array('tl_c4g_reservation_object','generateUuid')),
            'sql'               => "varchar(128) COLLATE utf8_bin NOT NULL default ''"
        ),

        'caption' => array
        (
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['caption'],
            'sorting'           => true,
            //'flag'              => 1,
            'search'            => true,
            'inputType'         => 'text',
            'eval'              => array('mandatory' => true, 'tl_class' => 'long', 'maxlength' => 255),
            'sql'               => "varchar(255) NOT NULL default ''"
        ),

    /*    'options' => array
        (
            'label'			=> &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['options'],
            'exclude' 		=> true,
            'inputType'     => 'multiColumnWizard',
            'eval' 			=> array
            (
                'columnFields' => array
                (
                    'caption' => array
                    (
                        'label'                 => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['option'],
                        'exclude'               => true,
                        'inputType'             => 'text', clr
                        'eval' 			        => array('tl_class'=>'w50')
                    ),
                    'language' => array
                    (
                        'label'                 => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['language'],
                        'exclude'               => true,
                        'inputType'             => 'select',
                        'options'               => \System::getLanguages(),
                        'eval'                  => array('chosen' => true, style=>'width: 200px')
                    )
                )
            ),

            'sql' => "blob NULL"
        ),
*/

        'quantity' => array
        (
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['quantity'],
            'inputType'         => 'text',
            'default'           => '1',
            'eval'              => array('rgxp'=>'digit', 'mandatory'=>true, 'tl_class'=>'long'),
            'sql'               => "smallint(5) unsigned NOT NULL default 1"
        ),

//
//        'multiple_booking' => array(
//            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['multiple_booking'],
//            'exclude'           => true,
//            'filter'            => true,
//            'inputType'         => 'checkbox',
//            'sql'               => "int(1) unsigned NULL default 1"
//        ),

        'desiredCapacityMin' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['desiredCapacityMin'],
            'exclude'                 => false,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>3, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'w50 clr'),
            'sql'                     => "int(3) unsigned NOT NULL default 0"
        ),


        'desiredCapacityMax' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['desiredCapacityMax'],
            'exclude'                 => false,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>3, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'w50'),
            'sql'                     => "int(3) unsigned NOT NULL default 0"
        ),

        'viewableTypes' => array
        (
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['viewableTypes'],
            'inputType'         => 'checkbox',
            'foreignKey'        => 'tl_c4g_reservation_type.caption',
            'eval'              => array('mandatory'=>'true','multiple'=>true, 'tl_class'=>'long clr','alwaysSave'=> true,),
            'sql'               => "blob NULL "
        ),

/*        'additionalBookingParams' => array
        (
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['additionalBookingParams'],
            'inputType'         => 'checkbox',
            'foreignKey'        => 'tl_c4g_reservation_params.caption' ,
            'eval'              => array('mandatory'=>false,'multiple'=>true, 'tl_class'=>'long clr','alwaysSave'=> true,),
            'sql'               => "blob NULL default ''"
        ),
*/

/*        'event_selection' => array(
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['event_selection'],
            'exclude'           => true,
            'inputType'         => 'select',
            'options'           => array('event_object','contao_event'),
            'reference'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object'],
            'eval'              => array('tl_class'=>'w50', 'feEditable'=>true, 'feViewable'=>true, 'mandatory'=>false, 'submitOnChange' => true),
            'sql'               => "char(25) NOT NULL default ''"
        ),
*/
    /*   'minute_interval' => array
        (
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['minute_interval'],
            'inputType'         => 'text',
            'eval'              => array('rgxp'=>'digit', 'maxval'=> 60, 'mandatory'=>'false', 'tl_class'=>'long clr'),
            'sql'               => "smallint(5) unsigned NOT NULL default '0'"
        ),
*/
//        'time_selection' => array
//        (
//            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_selection'],
//            'exclude'               => true,
//            'inputType'             => 'select',
//            'default'               => 'time_int',
//            'reference'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_object'],
//            'options'               => array('time_int','customers_determined'),
//            'eval'                  => array('tl_class'=>'w50', 'feEditable'=>true, 'feViewable'=>true, 'mandatory'=>false, 'submitOnChange' => true),
//            'sql'                   => "char(25) default ''"
//
//        ),
        'time_interval' => array
        (
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_interval'],
            'inputType'         => 'text',
            'eval'              => array('rgxp'=>'digit', 'maxval'=> 60, 'mandatory'=>false, 'tl_class'=>'long clr'),
            'sql'               => "smallint(5) unsigned NOT NULL default 0"
        ),
        'oh_monday' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['oh_monday'],
            'default'                 => time(),
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('columnFields'	=> array
            (
                'time_begin' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_begin'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                ),
                'time_end' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_end'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                )
            )),
            'sql'                     => "blob NULL"
        ),

        'oh_tuesday' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['oh_tuesday'],
            'default'                 => time(),
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('columnFields'	=> array
            (
                'time_begin' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_begin'],
                    'exclude'                 => true,
                    'default'                 => '',
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                ),
                'time_end' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_end'],
                    'exclude'                 => true,
                    'default'                 => '',
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                )
            )),
            'sql'                     => "blob NULL"
        ),

        'oh_wednesday' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['oh_wednesday'],
            'default'                 => time(),
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('columnFields'	=> array
            (
                'time_begin' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_begin'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                ),
                'time_end' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_end'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                )
            )),
            'sql'                     => "blob NULL"
        ),

        'oh_thursday' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['oh_thursday'],
            'default'                 => time(),
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('columnFields'	=> array
            (
                'time_begin' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_begin'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                ),
                'time_end' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_end'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                )
            )),
            'sql'                     => "blob NULL"
        ),

        'oh_friday' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['oh_friday'],
            'default'                 => time(),
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('columnFields'	=> array
            (
                'time_begin' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_begin'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                ),
                'time_end' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_end'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                )
            )),
            'sql'                     => "blob NULL"
        ),

        'oh_saturday' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['oh_saturday'],
            'default'                 => time(),
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('columnFields'	=> array
            (
                'time_begin' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_begin'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                ),
                'time_end' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_end'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                )
            )),
            'sql'                     => "blob NULL"
        ),

        'oh_sunday' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['oh_sunday'],
            'default'                 => time(),
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('columnFields'	=> array
            (
                'time_begin' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_begin'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                ),
                'time_end' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_end'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                )
            )),
            'sql'                     => "blob NULL"
        ),

        /*'type_select' => array(
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['type_select'],
            'exclude'           => true,
            'inputType'         => 'select',
            'options'           => array('fixed','individual','openingHours','event'),
            'reference'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object'],
            'eval'              => array('tl_class'=>'w50', 'feEditable'=>true, 'feViewable'=>true, 'mandatory'=>true, 'submitOnChange' => true),
            'sql'               => "char(25) NOT NULL default ''"

        ),*/

/*        'event_id' => array
        (
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['event_id'],
            'inputType'         => 'select',
            'foreignKey'        => 'tl_calendar_events.title',
            'eval'              => array('mandatory' => false, 'tl_class' => 'long'),
            'sql'               => "int(10) unsigned NOT NULL default 0",
            'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),
        ),

        'event_dayBegin' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['event_dayBegin'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'date', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                     => "int(10) unsigned NULL default 0"
        ),

        'event_timeBegin' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['event_timeBegin'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'tl_class'=>'w50 wizard', 'datepicker'=>true),
            'sql'                     => "int(10) unsigned NULL default 0"
        ),

        'event_dayEnd' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['event_dayEnd'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'date', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                     => "int(10) unsigned NULL default 0"
        ),

        'event_timeEnd' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['event_timeEnd'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'tl_class'=>'w50 wizard', 'datepicker'=>true),
            'sql'                     => "int(10) unsigned NULL default 0"
        ),
*/
        'days_exclusion' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['days_exclusion'],
            'default'                 => time(),
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('columnFields'	=> array
            (
                'date_exclusion' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['date_exclusion'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'date', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                ),
                'date_exclusion_end' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['date_exclusion_end'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'date', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                )

            )),
            'sql'                     => "blob NULL"
        ),

        'min_reservation_day' => array(
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['min_reservation_day'],
            'sorting'                 => false,
            'flag'                    => 1,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'rgxp'=>'digit', 'tl_class'=>'w50 clr'),
            'sql'                     => "smallint(3) NOT NULL default 1"
        ),

        'max_reservation_day' => array(
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_object']['max_reservation_day'],
            'sorting'                 => false,
            'flag'                    => 1,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                     => "smallint(3) NOT NULL default 365"
        ),

        'description' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['description'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'textarea',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'rte'=>'tinyMCE', 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'long'),
            'sql'                     => "text NULL"
        ),

        'locgeox' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['locgeox'],
            'exclude'                 => true,
            'inputType'               => 'c4g_text',
            'default'                 => '',
            'eval'                    => array('tl_class'=>'w50 wizard', 'require_input'=>true ),
            'save_callback'           => array(array('tl_c4g_reservation_object','setCenterLon')),
            'wizard'                  => [['con4gis\MapsBundle\Classes\GeoPicker', 'getPickerLink']],
            'sql'                     =>"varchar(20) NOT NULL default ''"
        ),

        'locgeoy' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['locgeoy'],
            'exclude'                 => true,
            'inputType'               => 'c4g_text',
            'default'                 => '',
            'eval'                    => array('tl_class'=>'w50 wizard', 'require_input'=>true ),
            'save_callback'           => array(array('tl_c4g_reservation_object','setCenterLat')),
            'wizard'                  => [['con4gis\MapsBundle\Classes\GeoPicker', 'getPickerLink']],
            'sql'                     =>"varchar(20) NOT NULL default ''"
        ),

        'published' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['published'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'sql'               => "int(1) unsigned NULL default 1"
        )

    )


);

/**
 * Class tl_c4g_reservation_object
 */
class tl_c4g_reservation_object extends Backend
{
    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    public function generateUuid($varValue, DataContainer $dc)
    {
        if ($varValue == '') {
            return \c4g\projects\C4GBrickCommon::getGUID();
        } else {
            return $varValue;
        }
    }


    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $this->import('BackendUser', 'User');

        if (strlen($this->Input->get('tid'))) {
            $this->toggleVisibility($this->Input->get('tid'), ($this->Input->get('state') == 0));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_c4g_reservation_object::published', 'alexf')) {
            return '';
        }

        $href .= '&amp;id=' . $this->Input->get('id') . '&amp;tid=' . $row['id'] . '&amp;state=' . $row[''];

        if (!$row['published']) {
            $icon = 'invisible.gif';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $this->generateImage($icon, $label) . '</a> ';
    }


    /**
     * Disable/enable a user group
     *
     * @param integer $intId
     * @param boolean $blnVisible
     * @param DataContainer $dc
     *
     * @throws Contao\CoreBundle\Exception\AccessDeniedException
     */
    public function toggleVisibility($intId, $blnPublished)
    {
        // Check permissions to publish
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_c4g_reservation_object::published', 'alexf')) {
            $this->log('Not enough permissions to show/hide record ID "' . $intId . '"', 'tl_c4g_reservation_object toggleVisibility', TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        $this->createInitialVersion('tl_c4g_reservation_object', $intId);

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_c4g_reservation_object']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_c4g_reservation_object']['fields']['published']['save_callback'] as $callback) {
                $this->import($callback[0]);
                $blnPublished = $this->$callback[0]->$callback[1]($blnPublished, $this);
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE tl_c4g_reservation_object SET tstamp=" . time() . ", published='" . ($blnPublished ? '0' : '1') . "' WHERE id=?")
            ->execute($intId);
        $this->createNewVersion('tl_c4g_reservation_object', $intId);
    }


    /**
     * Return all themes as array
     * @return array
     */
    public function getAllThemes()
    {
        $return = array();
        $themes = $this->Database->prepare("SELECT id,name FROM tl_c4g_reservation_object ORDER BY name")
            ->execute();
        while ($themes->next()) {
            $return[$themes->id] = $themes->name;
        }
        return $return;
    }


    /** Validate Center Lon*/
    public function setCenterLon($varValue, DataContainer $dc)
    {
        if (C4GVersionProvider::isInstalled('con4gis/maps') && !\con4gis\MapsBundle\Classes\Utils::validateLon($varValue)) {
            throw new Exception($GLOBALS['TL_LANG']['tl_c4g_reservation_object']['geox_invalid']);
        }
        return $varValue;
    }

    /** Validate Center Lat*/
    public function setCenterLat($varValue, DataContainer $dc)
    {
        if (C4GVersionProvider::isInstalled('con4gis/maps') && !\con4gis\MapsBundle\Classes\Utils::validateLat($varValue)) {
            throw new Exception($GLOBALS['TL_LANG']['tl_c4g_reservation_object']['geoy_invalid']);
        }
        return $varValue;
    }

    public function listFields($arrRow)
    {
        $type_ids = unserialize($arrRow['viewableTypes']);

        $reservationTypes = '';
        foreach ($type_ids as $type_id) {
            $reservation_type = \con4gis\ReservationBundle\Resources\contao\models\C4gReservationTypeModel::findByPk($type_id);
            if ($reservation_type) {
                if ($reservationTypes == '') {
                    $reservationTypes .= $reservation_type->caption;
                } else {
                    $reservationTypes .= ','.$reservation_type->caption;
                }
            }
        }

        $arrRow['viewableTypes'] = $reservationTypes;

        $result = [
            $arrRow['caption'],
            $arrRow['quantity'],
            $arrRow['desiredCapacityMin'],
            $arrRow['desiredCapacityMax'],
            $arrRow['viewableTypes'],
            $arrRow['time_interval']
        ];
        return $result;
    }
//    public function multi_booking()
//    {
//        $db = $this->Database->prepare("SELECT * FROM tl_c4g_reservation_object ")
//            ->execute()->fetchAllAssoc();
//
//        foreach($db as $entry)
//        {
//
//            $id    = $entry['id'];
//            $value  = $entry['desiredCapacityMax'];
//
//            if($entry['multiple_booking'] === '1')
//            {
//
//                $this->Database->prepare("UPDATE tl_c4g_reservation_object SET quantity = $value  WHERE id=$id")
//                    ->execute($entry['id']);
//
//
//            }
//
//        }
//    }
}
