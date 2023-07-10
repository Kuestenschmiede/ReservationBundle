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

use con4gis\CoreBundle\Classes\Helper\ArrayHelper;
use con4gis\ReservationBundle\Classes\Models\C4gReservationObjectModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationTypeModel;
use Contao\CalendarBundle\Security\ContaoCalendarPermissions;



//$default = '{type_legend}, caption, alias, options, quantity, priority, description, image, desiredCapacityMin, desiredCapacityMax, viewableTypes, min_reservation_day, max_reservation_day, maxBeginTime;{time_interval_legend},time_interval,duration;{booking_wd_legend}, oh_monday,oh_tuesday, oh_wednesday,oh_thursday, oh_friday,oh_saturday,oh_sunday;{event_legend},event_selection;{exclusion_legend}, days_exclusion;{event_legend:hide},location, speaker, topic, targetAudience; {price_legend:hide},price,taxOptions,priceoption;{expert_legend:hide},allTypesQuantity, allTypesValidity, allTypesEvents, switchAllTypes, notification_type;{publish_legend}, published, member_id';

$GLOBALS['TL_DCA']['tl_c4g_reservation_object'] = array
(
    //config
    'config' => array
    (   //'onsubmit_callback' => [['tl_c4g_reservation_object', 'multi_booking']],
        'dataContainer'     => 'Table',
        'enableVersioning'  => true,
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
                'label'         => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'          => 'act=select',
                'class'         => 'header_edit_all',
                'attributes'    => 'onclick="Backend.getScrollOffSet()" accesskey="e"'
            )
        ),

        'operations' => array
        (
            'edit' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['edit'],
                'href'          => 'act=edit',
                'icon'          => 'edit.gif',
            ),
            'copy' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['copy'],
                'href'          => 'act=copy',
                'icon'          => 'copy.gif',
            ),
            'delete' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['delete'],
                'href'          => 'act=delete',
                'icon'          => 'delete.gif',
                'attributes'    => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\')) return false;Backend.getScrollOffset()"',
            ),
            'show' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['show'],
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
    'palettes' => array(
        '__selector__' => ['typeOfObject'],
        'default'   =>  '{type_legend}, caption, alias, options, quantity, priority, description, image, desiredCapacityMin, desiredCapacityMax, viewableTypes, typeOfObject, min_reservation_day, max_reservation_day, maxBeginTime;,{time_interval_legend},time_interval,duration;{booking_wd_legend}, oh_monday,oh_tuesday, oh_wednesday,oh_thursday, oh_friday,oh_saturday,oh_sunday;{event_legend},event_selection;{exclusion_legend}, days_exclusion;{event_legend:hide},location, speaker, topic, targetAudience; {price_legend:hide},price,taxOptions,priceoption;{expert_legend:hide},allTypesQuantity, allTypesValidity, allTypesEvents, switchAllTypes, notification_type;{publish_legend}, published, member_id;',
        'fixed_date' => '{type_legend}, caption, alias, options, quantity, priority, description, image, desiredCapacityMin, desiredCapacityMax, viewableTypes, typeOfObject, min_reservation_day, max_reservation_day, maxBeginTime;{event_legend},event_selection;{event_legend:hide},location, speaker, topic, targetAudience; {price_legend:hide},price,taxOptions,priceoption;{expert_legend:hide},allTypesQuantity, allTypesValidity, allTypesEvents, switchAllTypes, notification_type;{publish_legend}, published, member_id;',
    ),

    //Subpalettes
    'subpalettes' => [
        'typeOfObject_standard' => 'default',
        'typeOfObject_fixed_date' => 'dateTimeBegin, typeOfObjectDuration',
    ],

    //Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'               => "int(10) unsigned NOT NULL auto_increment"
        ),

        'member_id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['memberId'],
            'default'           => 0,
            'inputType'         => 'select',
            'exclude'           => true,
            'options_callback'  => array('tl_c4g_reservation_object', 'loadMemberOptions'),
            'eval'              => array('mandatory'=>false, 'disabled' => true, 'tl_class' => 'clr long', 'includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['noMember']),
            'filter'            => true,
            'sql'               => "int(10) unsigned NOT NULL default 0"
        ),

        'tstamp' => array
        (
            'sql'               => "int(10) unsigned NOT NULL default 0"
        ),

        'caption' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['caption'],
            'exclude'           => true,
            'sorting'           => true,
            'search'            => true,
            'inputType'         => 'text',
            'eval'              => array('mandatory' => true, 'tl_class' => 'w50', 'maxlength' => 254),
            'sql'               => "varchar(254) NOT NULL default ''"
        ),

        'alias' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['alias'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => array('rgxp'=>'alias', 'doNotCopy'=>true, 'unique'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'save_callback' => array
            (
               array('tl_c4g_reservation_object', 'generateAlias')
            ),
            'sql' => "varchar(255) BINARY NOT NULL default ''"
        ),

        'options' => array
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
                        'inputType'             => 'text',
                        'eval' 			        => array('tl_class'=>'w50')
                    ),
                    'language' => array
                    (
                        'label'                 => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['language'],
                        'exclude'               => true,
                        'inputType'             => 'select',
                        'options'               => ['de' => 'Deutsch', 'en' => 'Englisch'],
                        'eval'                  => array('chosen' => false, 'style'=>'width: 200px')
                    )
                ),
                'tl_class'=>'clr',
            ),

            'sql' => "blob NULL"
        ),

        'quantity' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['quantity'],
            'exclude'           => true,
            'inputType'         => 'text',
            'default'           => '1',
            'eval'              => array('rgxp'=>'digit', 'mandatory'=>true, 'tl_class'=>'long'),
            'sql'               => "smallint(5) unsigned NOT NULL default 1"
        ),

        'allTypesQuantity' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['allTypesQuantity'],
            'exclude'           => true,
            'filter'            => false,
            'default'           => '1',
            'inputType'         => 'checkbox',
            'sql'               => "int(1) unsigned NULL default 1"
        ),

        'allTypesValidity' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['allTypesValidity'],
            'exclude'           => true,
            'filter'            => false,
            'default'           => '0',
            'inputType'         => 'checkbox',
            'sql'               => "int(1) unsigned NULL default 0"
        ),

        'allTypesEvents' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['allTypesEvents'],
            'filter'            => false,
            'exclude'           => true,
            'inputType'         => 'checkbox',
            'foreignKey'        => 'tl_calendar.title',
            'eval'              => array('mandatory'=>false,'multiple'=>true, 'tl_class'=>'long clr'),
            'sql'               => "blob NULL"
        ),

        'switchAllTypes' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['switchAllTypes'],
            'filter'            => false,
            'exclude'           => true,
            'inputType'         => 'checkbox',
            'options_callback'  =>  ['tl_c4g_reservation_object', 'getTypes'],
            'eval'              => array('mandatory'=>false,'multiple'=>true, 'tl_class'=>'long clr'),
            'sql'               => "blob NULL"
        ),

        'priority' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['priority'],
            'exclude'           => true,
            'filter'            => false,
            'default'           => '0',
            'inputType'         => 'checkbox',
            'sql'               => "int(1) unsigned NULL default 0"
        ),

        'location'  => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['location'],
            'exclude'           => true,
            'default'           => 0,
            'inputType'         => 'select',
            'foreignKey'        => 'tl_c4g_reservation_location.name',
            'eval'              => array('chosen' => true, 'includeBlankOption' => true, 'mandatory' => false, 'tl_class' => 'long'),
            'sql'               => "int(10) unsigned NOT NULL default 0",
            'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),
        ),


        'speaker' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['speaker'],
            'exclude'           => true,
            'inputType'         => 'checkbox',
            'options_callback'  => ['tl_c4g_reservation_object', 'getSpeakerName'],
            'eval'              => array('mandatory' => false, 'tl_class' => 'long clr', 'multiple' => true, 'chosen' => true,'includeBlankOption'=>true, 'doNotCopy' => true),
            'sql'               => "blob NULL"
        ),

        'topic' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['topic'],
            'exclude'           => true,
            'inputType'         => 'checkbox',
            'foreignKey'        => 'tl_c4g_reservation_event_topic.topic',
            'eval'              => array('mandatory' => false, 'tl_class' => 'long clr', 'multiple' => true, 'chosen' => true,'includeBlankOption'=>true, 'doNotCopy' => true),
            'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),
            'sql'               => "blob NULL"
        ),

        'targetAudience' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['targetAudience'],
            'exclude'           => true,
            'inputType'         => 'checkbox',
            'foreignKey'        => 'tl_c4g_reservation_event_audience.targetAudience',
            'eval'              => array('mandatory' => false, 'tl_class' => 'long clr', 'multiple' => true, 'chosen' => true,'includeBlankOption'=>true, 'doNotCopy' => true),
            'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),
            'sql'               => "blob NULL"
        ),

        'desiredCapacityMin' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['desiredCapacityMin'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>3, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'w50 clr'),
            'sql'                     => "int(3) unsigned NOT NULL default 0"
        ),

        'desiredCapacityMax' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['desiredCapacityMax'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>3, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'w50'),
            'sql'                     => "int(3) unsigned NOT NULL default 0"
        ),

        'viewableTypes' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['viewableTypes'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'options_callback'  => ['tl_c4g_reservation_object', 'getTypes'],
            'eval'              => array('mandatory'=>true,'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'qualifications','multiple'=>true, 'tl_class'=>'long clr','alwaysSave'=> true),
            'sql'               => "blob NULL "
        ),

        'typeOfObject' => array(
            'label'       => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['typeOfObject'],
            'exclude'     => true,
            'default'     => 'standard',
            'inputType'   => 'select',
            'options'     => array('standard', 'fixed_date'),
            'reference'   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['references'],
            'eval'        => array('chosen' => true, 'mandatory' => true, 'tl_class' => 'long', 'submitOnChange' => true),
            'sql'         => "varchar(10) NOT NULL default ''",

        ),

        'dateTimeBegin' => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['dateTimeBegin'],
            'default'   => time(),
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array('rgxp' => 'datim', 'mandatory' => true, 'doNotCopy' => true, 'datepicker' => true, 'tl_class' => 'w50 wizard'),
            'sql'       => "varchar(10)",
        ),

        'typeOfObjectDuration' => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['typeOfObjectDuration'],
            'default'   => '',
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array('rgxp' => 'digit', 'mandatory' => true, 'doNotCopy' => true, 'tl_class' => 'w50'),
            'sql'       => "int(10) unsigned NOT NULL default '0'",
        ),

        'time_interval' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_interval'],
            'exclude'           => true,
            'inputType'         => 'text',
            'eval'              => array('rgxp'=>'digit', 'minval'=>1, 'mandatory'=>true, 'tl_class'=>'w50'),
            'sql'               => "smallint(5) unsigned NOT NULL default 1"
        ),

        'duration' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['duration'],
            'exclude'           => true,
            'inputType'         => 'text',
            'eval'              => array('rgxp'=>'digit', 'mandatory'=>false, 'tl_class'=>'w50'),
            'sql'               => "smallint(5) unsigned NOT NULL default 0"
        ),

        'oh_monday' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['oh_monday'],
            'default'                 => time(),
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('style'=>'min-width: 320px','columnFields'	=> array
            (
                'time_begin' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_begin'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                ),
                'time_end' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_end'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'min-width: 60px;'),
                    'sql'                     => "varchar(10) NOT NULL default ''"
                ),
                'date_from' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['date_from'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'datim', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                ),
                'date_to' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['date_to'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'datim', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                )
            )),
            'sql'                     => "blob NULL"
        ),

        'oh_tuesday' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['oh_tuesday'],
            'default'                 => time(),
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('style'=>'min-width: 320px','columnFields'	=> array
            (
                'time_begin' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_begin'],
                    'exclude'                 => true,
                    'default'                 => '',
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                ),
                'time_end' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_end'],
                    'exclude'                 => true,
                    'default'                 => '',
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                ),
                'date_from' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['date_from'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'datim', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                ),
                'date_to' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['date_to'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'datim', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                )
            )),
            'sql'                     => "blob NULL"
        ),

        'oh_wednesday' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['oh_wednesday'],
            'default'                 => time(),
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('style'=>'min-width: 320px','columnFields'	=> array
            (
                'time_begin' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_begin'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                ),
                'time_end' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_end'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                ),
                'date_from' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['date_from'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'datim', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                ),
                'date_to' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['date_to'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'datim', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                )
            )),
            'sql'                     => "blob NULL"
        ),

        'oh_thursday' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['oh_thursday'],
            'default'                 => time(),
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('style'=>'min-width: 320px','columnFields'	=> array
            (
                'time_begin' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_begin'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                ),
                'time_end' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_end'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                ),
                'date_from' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['date_from'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'datim', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                ),
                'date_to' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['date_to'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'datim', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                )
            )),
            'sql'                     => "blob NULL"
        ),

        'oh_friday' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['oh_friday'],
            'default'                 => time(),
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('style'=>'min-width: 320px','columnFields'	=> array
            (
                'time_begin' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_begin'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                ),
                'time_end' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_end'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) efault ''"
                ),
                'date_from' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['date_from'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'datim', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                ),
                'date_to' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['date_to'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'datim', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                )
            )),
            'sql'                     => "blob NULL"
        ),

        'oh_saturday' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['oh_saturday'],
            'default'                 => time(),
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('style'=>'min-width: 320px','columnFields'	=> array
            (
                'time_begin' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_begin'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                ),
                'time_end' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_end'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                ),
                'date_from' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['date_from'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'datim', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                ),
                'date_to' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['date_to'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'datim', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                )
            )),
            'sql'                     => "blob NULL"
        ),

        'oh_sunday' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['oh_sunday'],
            'default'                 => time(),
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('style'=>'min-width: 320px','columnFields'	=> array
            (
                'time_begin' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_begin'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                ),
                'time_end' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['time_end'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'flag'                    => 8,
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                ),
                'date_from' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['date_from'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'datim', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                ),
                'date_to' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['date_to'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'datim', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
                    'sql'                     => "varchar(10) default ''"
                )
            )),
            'sql'                     => "blob NULL"
        ),

        'days_exclusion' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['days_exclusion'],
            'default'                 => time(),
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('style'=>'min-width: 320px', 'columnFields'	=> array
            (
                'date_exclusion' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['date_exclusion'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'datim', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 120px;'),
                    'sql'                     => "varchar(10) default ''"
                ),
                'date_exclusion_end' => array
                (
                    'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['date_exclusion_end'],
                    'default'                 => '',
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'datim', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 120px;'),
                    'sql'                     => "varchar(10) default ''"
                )

            )),
            'sql'                     => "blob NULL"
        ),

        'days_exclusion_text' => array (
            'sql'                     => "text NULL"
        ),

        'min_reservation_day' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['min_reservation_day'],
            'exclude'                 => true,
            'sorting'                 => false,
            'flag'                    => 0,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'rgxp'=>'digit', 'tl_class'=>'w50 clr'),
            'sql'                     => "smallint(3) NOT NULL default 0"
        ),

        'max_reservation_day' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['max_reservation_day'],
            'exclude'                 => true,
            'sorting'                 => false,
            'flag'                    => 1,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                     => "smallint(3) NOT NULL default 365"
        ),

        'maxBeginTime' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['maxBeginTime'],
            'default'                 => '',
            'exclude'                 => true,
            'inputType'               => 'text',
            'flag'                    => 8,
            'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard', 'style'=>'display: inline-block; min-width: 60px;'),
            'sql'                     => "varchar(10) default ''"
        ],

        'description' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['description'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'textarea',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>2048, 'rte'=>'tinyMCE', 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'long'),
            'sql'                     => "text NULL"
        ),

        'image' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['image'],
            'exclude'           => true,
            'inputType'         => 'fileTree',
            'sorting'           => false,
            'search'            => false,
            'extensions'        => 'jpg, jpeg, png, tif',
            'exclude'           => true,
            'eval'              => array('filesOnly'=>true, 'files'=>true, 'fieldType'=>'radio', 'tl_class'=>'long clr', 'extensions'=>Config::get('validImageTypes')),
            'sql'               => "blob NULL"
        ),

        'notification_type'  => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['notification_type'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'foreignKey'              => 'tl_nc_notification.title',
            'eval'                    => array('multiple' => true),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'price' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['price'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'default'                 => '0.00',
            'eval'                    => array('rgxp'=>'digit','mandatory'=>false, 'maxlength'=>10, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'w50 clr'),
            'sql'                     => "double(7,2) unsigned default '0'"
        ),

        'priceoption' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['priceoption'],
            'exclude'                 => true,
            'inputType'               => 'radio',
            'options'                 => array('pReservation','pPerson','pWeek','pDay','pNight','pNightPerson','pHour','pMin','pAmount'),
            'default'                 => '',
            'reference'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['references'],
            'eval'                    => array('mandatory'=>false, 'tl_class' => 'long clr'),
            'sql'                     => "varchar(50) NOT NULL default ''"
        ),
        'taxOptions' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['taxOptions'],
            'exclude'                 => true,
            'inputType'               => 'radio',
            'default'                 => 'tNone',
            'options'                 => array( 'tNone', 'tStandard', 'tReduced',),
            'reference'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['references'],
            'eval'                    => array('submitOnChange' => true, 'tl_class' => 'long clr', 'fieldType'=>'radio'),
            'sql'                     => "varchar(50) NOT NULL default 'tNone'"
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

        $href .= '&amp;id=' . $this->Input->get('id') . '&amp;tid=' . $row['id'] . '&amp;state='.($row['published'] ? '' : 1);

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
        $this->Database->prepare("UPDATE tl_c4g_reservation_object SET tstamp=" . time() . ", published='" . ($blnPublished ? '0' : '1') . "' WHERE `id`=?")
            ->execute($intId);
        $this->createNewVersion('tl_c4g_reservation_object', $intId);
    }

    public function listFields($arrRow)
    {
        $type_ids = \Contao\StringUtil::deserialize($arrRow['viewableTypes']);

        $reservationTypes = '';
        foreach ($type_ids as $type_id) {
            $reservation_type = \con4gis\ReservationBundle\Classes\Models\C4gReservationTypeModel::findByPk($type_id);
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

    public function getTypes(DataContainer $dc)
    {
        $return = [];

        $types = $this->Database->prepare("SELECT id, caption, reservationObjectType, published FROM tl_c4g_reservation_type")
            ->execute()->fetchAllAssoc();
        foreach ($types as $type) {
            if ($type['reservationObjectType'] != '2') {
                $key = $type['id'];
                $return[$key] = $type['caption'];
            }
        }

        asort($return);
        return $return;
    }

    /**
     * @param $dc
     * @return array
     */
    public function loadMemberOptions($dc) {
        $options = [];
        $options[$dc->activeRecord->id] = '';

        $stmt = $this->Database->prepare("SELECT id, firstname, lastname FROM tl_member WHERE `disable` != 1");
        $result = $stmt->execute()->fetchAllAssoc();

        foreach ($result as $row) {
            $options[$row['id']] = $row['lastname'] . ', ' . $row['firstname'];
        }
        return $options;
    }

    /**
     * Return all speaker as array
     * @return array
     */
    public function getSpeakerName(DataContainer $dc)
    {
        $return = [];

        $objects = $this->Database->prepare("SELECT id,title,firstname,lastname FROM tl_c4g_reservation_event_speaker ORDER BY lastname")
            ->execute();

        while ($objects->next()) {
            $name = '';
            if ($objects->title) {
                $name = $objects->lastname.','.$objects->firstname.','.$objects->title;
            } else {
                $name = $objects->lastname.','.$objects->firstname;
            }

            $return[$objects->id] = $name;
        }

        return $return;
    }

    /**
     * Auto-generate the object alias if it has not been set yet
     *
     * @param mixed         $varValue
     * @param DataContainer $dc
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function generateAlias($varValue, \Contao\DataContainer $dc)
    {
        $aliasExists = function (string $alias) use ($dc): bool
        {
            return $this->Database->prepare("SELECT id FROM tl_c4g_reservation_object WHERE alias=? AND id!=?")->execute($alias, $dc->id)->numRows > 0;
        };

        // Generate the alias if there is none
        if (!$varValue)
        {
            $varValue = \Contao\System::getContainer()->get('contao.slug')->generate($dc->activeRecord->caption, C4gReservationObjectModel::findByPk($dc->activeRecord->id)->jumpTo, $aliasExists);
        }
        elseif (preg_match('/^[1-9]\d*$/', $varValue))
        {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasNumeric'], $varValue));
        }
        elseif ($aliasExists($varValue))
        {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        return $varValue;
    }

}
