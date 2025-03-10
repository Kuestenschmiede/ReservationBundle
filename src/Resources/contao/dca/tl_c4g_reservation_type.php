<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 10
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2025, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

/**
 * Table tl_module
 */

use con4gis\ReservationBundle\Classes\Callbacks\C4gReservationType;
use Contao\DataContainer;
use Contao\Config;
use Contao\DC_Table;
// use con4gis\ReservationBundle\Classes\Models\C4gReservationTypeModel;
// use Contao\Backend;
// use Contao\BackendUser;
// use Contao\Input;
// use Contao\StringUtil;
// use Contao\Image;
// use Contao\Versions;

$cbClass = C4gReservationType::class;


$GLOBALS['TL_DCA']['tl_c4g_reservation_type'] = array
(
    //config
    'config' => array
    (
        'dataContainer'     => DC_Table::class,
        'enableVersioning'  => true,
        'onload_callback'   => [[$cbClass, 'showInfoMessage']],
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
            'fields'            => array('caption','reservationObjectType','periodType','objectCount'),
            'panelLayout'       => 'filter;sort,search,limit',
            'headerFields'      => array('caption','periodType','objectCount'),
        ),

        'label' => array
        (
            'fields'            => array('caption','reservationObjectType','periodType','objectCount'),
            // 'label_callback'    => array($cbClass, 'listTypes'),
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
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['edit'],
                'href'          => 'act=edit',
                'icon'          => 'edit.gif',
            ),
            'copy' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['copy'],
                'href'          => 'act=copy',
                'icon'          => 'copy.gif',
            ),
            'delete' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['delete'],
                'href'          => 'act=delete',
                'icon'          => 'delete.gif',
                'attributes'    => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\')) return false;Backend.getScrollOffset()"',
            ),
            'show' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['show'],
                'href'          => 'act=show',
                'icon'          => 'show.gif',
            ),
             'toggle' => array
             (
                'label'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['TOGGLE'],
                'icon'                => 'visible.gif',
                'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'     => array($cbClass, 'toggleIcon')
            )
        )
    ),

    //Palettes
    'palettes' => array
    (
        '__selector__'  => array('periodType','auto_del','reservationObjectType'),
        'default'       =>  '{type_legend},caption,alias,options,description;{object_legend},reservationObjectType,bookRunning,minParticipantsPerBooking,maxParticipantsPerBooking,almostFullyBookedAt,included_params,additional_params,additionalParamsFieldType,additionalParamsMandatory,participant_params,participantParamsFieldType,participantParamsMandatory,location,published;{notification_legend:hide},notification_type,notification_confirmation_type,notification_special_type;{expert_legend:hide},member_id,group_id,auto_del,auto_send;'
    ),

    //Subpalettes
   'subpalettes' => array(
        'auto_del_daily'                => 'del_time;',
        'reservationObjectType_1'       => 'cloneObject,periodType,objectCount,min_residence_time,max_residence_time,default_residence_time,severalBookings,directBooking',
        'reservationObjectType_2'       => '',
        'reservationObjectType_3'       => 'cloneObject,periodType,objectCount,min_residence_time,max_residence_time,default_residence_time,severalBookings,directBooking',
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

        'member_id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['memberId'],
            'exclude'           => true,
            'default'           => 0,
            'inputType'         => 'select',
            'options_callback'  => array($cbClass, 'loadMemberOptions'),
            'eval'              => array('mandatory'=>false, 'chosen'=>true,
                'disabled' => false, 'includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['emptyMemberId']),
            'filter'            => true,
            'sql'               => "int(10) unsigned NOT NULL default 0"
        ),

        'group_id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['groupId'],
            'exclude'           => true,
            'default'           => 0,
            'inputType'         => 'select',
            'options_callback'  => array($cbClass, 'loadGroupOptions'),
            'eval'              => array('mandatory'=>false, 'chosen'=>true,
                'disabled' => false, 'includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['emptyGroupId']),
            'filter'            => true,
            'sql'               => "int(10) unsigned NOT NULL default 0"
        ),

        'caption' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['caption'],
            'exclude'           => true,
            'default'           => '',
            'sorting'           => true,
            'flag'              => 1,
            'search'            => true,
            'inputType'         => 'text',
            'eval'              => array('mandatory' => true, 'tl_class' => 'w50'),
            'sql'               => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'alias' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['alias'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => array('rgxp'=>'alias', 'doNotCopy'=>true, 'unique'=>true, 'tl_class'=>'w50'),
            'save_callback' => array
            (
                array($cbClass, 'generateAlias')
            ),
            // 'sql' => "varchar(255) BINARY NOT NULL default ''"
            'sql'    => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'options' => array
        (
            'label'			=> &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['options'],
            'exclude' 		=> true,
            'inputType'     => 'multiColumnWizard',
            'eval' 			=> array
            (
                'columnFields' => array
                (
                    'caption' => array
                    (
                        'label'                 => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['option'],
                        'exclude'               => true,
                        'inputType'             => 'text',
                        'eval' 			        => array('tl_class'=>'w50')
                    ),
                    'language' => array
                    (
                        'label'                 => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['language'],
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

        'reservationObjectType' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['reservationObjectType'],
            'exclude'                 => true,
            'inputType'               => 'radio',
            'default'                 => '1',
            'options'                 => ['1','3','2'],
            'reference'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['referencesObjectType'],
            'eval'                    => ['tl_class'=>'clr long','submitOnChange' => true],
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '1')
        ),

       'periodType' => array
       (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['periodType'],
            'default'           => '',
            'exclude'           => true,
            'inputType'         => 'select',
            'options'           => array('minute','hour','day','overnight','week'),
            'reference'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type'],
            'eval'              => array('tl_class'=>'w50', 'feEditable'=>true, 'feViewable'=>true, 'mandatory'=>true, 'submitOnChange' => true),
            'sql'               => "char(25) NOT NULL default ''"
       ),

       'min_residence_time' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['min_residence_time'],
            'exclude'           => true,
            'inputType'         => 'text',
            'default'           => '0',
            'eval'              => array('rgxp'=>'digit', 'mandatory'=>false, 'tl_class'=>'w50'),
            'sql'               => "smallint(5) unsigned NOT NULL default 0"
        ),
        'max_residence_time' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['max_residence_time'],
            'exclude'           => true,
            'inputType'         => 'text',
            'default'           => '0',
            'eval'              => array('rgxp'=>'digit', 'mandatory'=>false, 'tl_class'=>'w50'),
            'sql'               => "smallint(5) unsigned NOT NULL default 0"
        ),
        'default_residence_time' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['default_residence_time'],
            'exclude'           => true,
            'inputType'         => 'text',
            'default'           => '0',
            'eval'              => array('rgxp'=>'digit', 'mandatory'=>false, 'tl_class'=>'w50'),
            'sql'               => "smallint(5) unsigned NOT NULL default 0"
        ),

        'objectCount' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['objectCount'],
            'exclude'           => true,
            'default'           => 0,
            'inputType'         => 'text',
            'eval'              => array('rgxp'=>'digit', 'mandatory'=>false, 'tl_class'=>'w50'),
            'sql'               => "smallint(5) unsigned NULL default 0"
        ),

        'severalBookings' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['severalBookings'],
            'default'                 => 0,
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('mandatory'=>false, 'multiple'=>false, 'tl_class'=>'w50 clr'),
            'sql'                     => "int(1) unsigned NULL default 0"
        ),

        'directBooking' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['directBooking'],
            'default'                 => 0,
            'exclude'                 => true,
            'filter'                  => false,
            'inputType'               => 'checkbox',
            'eval'                    => array('mandatory'=>false, 'multiple'=>false, 'tl_class'=>'w50 clr'),
            'sql'                     => "int(1) unsigned NULL default 0"
        ),

        'cloneObject' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['cloneObject'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'select',
            'foreignKey'              => 'tl_c4g_reservation_object.caption',
            'eval'                    => array('mandatory'=>false, 'includeBlankOption' => true, 'tl_class' => 'long clr', 'multiple'=>false, 'chosen'=>true),
            'sql'                     => array('type' => 'string', 'length' => 10, 'default' => '')
        ),

        'minParticipantsPerBooking' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['minParticipantsPerBooking'],
            'exclude'           => true,
            'default'           => 1,
            'inputType'         => 'text',
            'eval'              => array('rgxp'=>'digit', 'mandatory'=>false, 'tl_class'=>'w50 clr'),
            'sql'               => "smallint(5) unsigned NULL default 1"
        ),

        'maxParticipantsPerBooking' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['maxParticipantsPerBooking'],
            'exclude'           => true,
            'default'           => 0,
            'inputType'         => 'text',
            'eval'              => array('rgxp'=>'digit', 'mandatory'=>false, 'tl_class'=>'w50'),
            'sql'               => "smallint(5) unsigned NULL default 0"
        ),

        'almostFullyBookedAt' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['almostFullyBookedAt'],
            'exclude'           => true,
            'default'           => 0,
            'inputType'         => 'text',
            'eval'              => array('rgxp'=>'digit', 'mandatory'=>false, 'tl_class'=>'w50'),
            'sql'               => "smallint(3) unsigned NULL default 0"
        ),

        'bookRunning' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['bookRunning'],
            'default'                 => 0,
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('mandatory'=>false, 'multiple'=>false, 'tl_class'=>'w50 clr'),
            'sql'                     => "int(1) unsigned NULL default 0"
        ),

        'location'  => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['location'],
            'exclude'           => true,
            'default'           => 0,
            'inputType'         => 'select',
            'foreignKey'        => 'tl_c4g_reservation_location.name',
            'eval'              => array('chosen' => true, 'includeBlankOption' => true, 'mandatory' => false, 'tl_class' => 'long clr'),
            'sql'               => "int(10) unsigned NOT NULL default 0",
            'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),
        ),

        'notification_type'  => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['notification_type'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'foreignKey'              => 'tl_nc_notification.title',
            'eval'                    => array('multiple' => true),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'notification_confirmation_type'  => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['notification_confirmation_type'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'foreignKey'              => 'tl_nc_notification.title',
            'eval'                    => array('multiple' => true),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'notification_special_type'  => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['notification_special_type'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'foreignKey'              => 'tl_nc_notification.title',
            'eval'                    => array('multiple' => true),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),
        'auto_send' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['auto_send'],
            'exclude'               => true,
            'inputType'             => 'select',
            'reference'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type'],
            'default'               =>'no_sending',
            'options'               => array('minutely','no_sending'),
            'eval'                  => array('tl_class'=>'long', 'feEditable'=>true, 'feViewable'=>true, 'mandatory'=>false, 'submitOnChange' => false),
            'sql'                   => "char(25) default 'no_sending'"
        ),
        'auto_del' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['auto_del'],
            'exclude'               => true,
            'inputType'             => 'select',
            'reference'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type'],
            'default'               =>'no_delete',
            'options'               => array('daily','no_delete'),
            'eval'                  => array('tl_class'=>'long', 'feEditable'=>true, 'feViewable'=>true, 'mandatory'=>false, 'submitOnChange' => true),
            'sql'                   => "char(25) default 'no_delete'"
        ),
        'del_time' => array
        (
            'label'                    =>&$GLOBALS['TL_LANG']['tl_c4g_reservations_type']['del_time'],
            'exclude'                  =>true,
            'inputType'                =>'text',
            'default'                  =>'30',
            'eval'                     =>array('rgxp'=>'digit', 'mandatory'=>false, 'tl_class'=>'long wizard'),
            'sql'                      =>"smallint(5) unsigned NULL"

        ),

        'beginDate' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['beginDate'],
            'default'                 => time(),
            'filter'                  => true,
            'sorting'                 => true,
            'search'                  => false,
            'exclude'                 => true,
            'inputType'               => 'text',
            'flag'                    => 6,
            'eval'                    => array('rgxp'=>'date', 'mandatory'=>true, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                     => "int(10) unsigned NULL default 0"
        ),

        'endDate' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['endDate'],
            'default'                 => time(),
            'filter'                  => true,
            'sorting'                 => true,
            'search'                  => false,
            'exclude'                 => true,
            'inputType'               => 'text',
            'flag'                    => 6,
            'eval'                    => array('rgxp'=>'date', 'mandatory'=>true, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                     => "int(10) unsigned NULL default 0"
        ),

        'event_id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event_id'],
            'exclude'           => true,
            'default'           => 0,
            'inputType'         => 'select',
            'foreignKey'        => 'tl_calendar_events.title',
            'eval'              => array('mandatory' => false, 'tl_class' => 'long'),
            'sql'               => "int(10) unsigned NOT NULL default 0",
            'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),
        ),

        'event_dayBegin' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event_dayBegin'],
            'default'                 => 0,
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'date', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                     => "int(10) unsigned NULL default 0"
        ),

        'event_timeBegin' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event_timeBegin'],
            'default'                 => 0,
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'tl_class'=>'w50 wizard', 'datepicker'=>true),
            'sql'                     => "int(10) unsigned NULL default 0"
        ),

        'event_dayEnd' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event_dayEnd'],
            'default'                 => 0,
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'date', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                     => "int(10) unsigned NULL default 0"
        ),

        'event_timeEnd' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event_timeEnd'],
            'default'                 => 0,
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'tl_class'=>'w50 wizard', 'datepicker'=>true),
            'sql'                     => "int(10) unsigned NULL default 0"
        ),

        'included_params' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['ts_c4g_reservation_type']['included_params'],
            'exclude'                 => true,
            'inputType'               => 'checkboxWizard',
            'foreignKey'              => 'tl_c4g_reservation_params.caption',
            'eval'                    => array('chosen'=>true, 'mandatory'=>false,'multiple'=>true, 'tl_class'=>'long clr','alwaysSave'=> true),
            'sql'                     => "blob NULL"
        ),

//        'includedParamsFieldType' => array
//        (
//            'label'                   => &$GLOBALS['TL_LANG']['ts_c4g_reservation_type']['includedParamsFieldType'],
//            'exclude'                 => true,
//            'inputType'               => 'select',
//            'default'                 => 'multi',
//            'reference'               => &$GLOBALS['TL_LANG']['ts_c4g_reservation_type'],
//            'options'                 => array('multi','radio'),
//            'eval'                    => array('tl_class'=>'w50','feViewable'=>true, 'mandatory'=>false),
//            'sql'                     => "char(25) NOT NULL default 'multi'"
//        ),
//
//        'includedParamsMandatory' => array
//        (
//            'label'             => &$GLOBALS['TL_LANG']['ts_c4g_reservation_type']['includedParamsMandatory'],
//            'exclude'           => true,
//            'filter'            => false,
//            'default'           => '0',
//            'inputType'         => 'checkbox',
//            'eval'              => array('tl_class'=>'w50 clr'),
//            'sql'               => "int(1) unsigned NULL default 0"
//        ),

        'additional_params' => array
        (
        'label'                   => &$GLOBALS['TL_LANG']['ts_c4g_reservation_type']['additional_params'],
        'exclude'                 => true,
        'inputType'               => 'checkboxWizard',
        'foreignKey'              => 'tl_c4g_reservation_params.caption',
        'eval'                    => array('chosen'=>true,'mandatory'=>false,'multiple'=>true, 'tl_class'=>'long clr','alwaysSave'=> true),
        'sql'                     => "blob NULL"
        ),

        'additionalParamsFieldType' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['ts_c4g_reservation_type']['additionalParamsFieldType'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'default'                 => 'multi',
            'reference'               => &$GLOBALS['TL_LANG']['ts_c4g_reservation_type'],
            'options'                 => array('multi','radio'),
            'eval'                    => array('tl_class'=>'w50','feViewable'=>true, 'mandatory'=>false),
            'sql'                     => "char(25) NOT NULL default 'multi'"
        ),

        'additionalParamsMandatory' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['ts_c4g_reservation_type']['additionalParamsMandatory'],
            'exclude'           => true,
            'filter'            => false,
            'default'           => '0',
            'inputType'         => 'checkbox',
            'eval'              => array('tl_class'=>'w50 clr'),
            'sql'               => "int(1) unsigned NULL default 0"
        ),

        'participant_params' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['ts_c4g_reservation_type']['participant_params'],
            'exclude'                 => true,
            'inputType'               => 'checkboxWizard',
            'foreignKey'              => 'tl_c4g_reservation_params.caption',
            'eval'                    => array('chosen'=>true,'mandatory'=>false,'multiple'=>true, 'tl_class'=>'long clr','alwaysSave'=> true),
            'sql'                     => "blob NULL"
        ),

        'participantParamsFieldType' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['ts_c4g_reservation_type']['participantParamsFieldType'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'default'                 => 'multi',
            'reference'               => &$GLOBALS['TL_LANG']['ts_c4g_reservation_type'],
            'options'                 => array('multi','radio'),
            'eval'                    => array('tl_class'=>'w50','feViewable'=>true, 'mandatory'=>false),
            'sql'                     => "char(25) NOT NULL default 'multi'"
        ),

        'participantParamsMandatory' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['ts_c4g_reservation_type']['participantParamsMandatory'],
            'exclude'           => true,
            'filter'            => false,
            'default'           => '0',
            'inputType'         => 'checkbox',
            'eval'               => array('tl_class'=>'w50 clr'),
            'sql'               => "int(1) unsigned NULL default 0"
        ),

        'description' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['description'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'textarea',
            'default'                 => '',
            'eval'                    => array('mandatory'=>false, 'rte'=>'tinyMCE', 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'long'),
            'sql'                     => "text NULL"
        ),

        'published' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['published'],
            'default'                 => 1,
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('mandatory'=>false, 'multiple'=>false,'alwaysSave'=>true),
            'sql'                     => "int(1) unsigned NULL default 1"
        )
    )
);