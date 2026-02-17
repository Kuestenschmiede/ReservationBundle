<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @author con4gis contributors (see "authors.md")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

 use con4gis\ReservationBundle\Classes\Callbacks\C4gReservationEvent;
 use Contao\Input;
 use Contao\DC_Table;

 $cbClass = C4gReservationEvent::class;

/**
 * Table tl_module
 */

$GLOBALS['TL_DCA']['tl_c4g_reservation_event'] = array
(
    //config
    'config' => array
    (
        'dataContainer'     => DC_Table::class,
        'enableVersioning'  => true,
        'ptable'            => 'tl_calendar_events',
        'onload_callback'   => [[$cbClass, 'setParent']],
        'sql'               => array(
            'keys' => array
            (
                'id' => 'primary',
                'pid' => 'index'
            )
        )
    ),


    //List
    'list' => array
    (
        'sorting' => array
        (
            'mode'              => 2,
            'fields'            => array('number','pid','reservationType'),
            'panelLayout'       => 'filter;sort,search,limit',
        ),

        'label' => array
        (
            'fields'            => array('number','pid:tl_calendar_events.title','reservationType:tl_c4g_reservation_type.caption'),
            'showColumns'       => true,
        ),

        'global_operations' => array
        (
            'back' => [
                'class'               => 'header_back',
                'href'                => 'do=calendar&table=tl_calendar_events&id='.Input::get('pid'),
                'icon'                => 'back.svg',
                'label'               => &$GLOBALS['TL_LANG']['MSC']['backBT'],
            ]
        ),

        'operations' => array
        (
            'edit' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['edit'],
                'href'          => 'act=edit',
                'icon'          => 'edit.gif',
            ),
            'copy' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['copy'],
                'href'          => 'act=copy',
                'icon'          => 'copy.gif',
            ),
            'delete' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['delete'],
                'href'          => 'act=delete',
                'icon'          => 'delete.gif',
                'attributes'    => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\')) return false;Backend.getScrollOffset()"',
            ),
            'show' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['show'],
                'href'          => 'act=show',
                'icon'          => 'show.gif',
            )
        )
    ),

    //Palettes
    'palettes' => array
    (
        'default'   =>  '{event_legend}, pid, number, location, organizer, speaker, topic, targetAudience; {reservation_legend}, reservationType, minParticipants, maxParticipants,maxParticipantsPerEventBooking, min_reservation_day, price, taxOptions, priceoption,showParticipantInfoFields, participant_params, participantParamsFieldType,participantParamsMandatory,reservationForwarding,reservationForwardingButtonCaption,discountCode,discountPercent,conferenceLink; {team_legend}, team;',
    ),

    //Fields
    'fields' => array
    (
        'id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['id'],
            'sql'               => "int(10) unsigned NOT NULL auto_increment",
            'sorting'           => true,
        ),
        'pid' => array
        (
            'inputType'         => 'select',
            'exclude'           => true,
            'default'           => 0,
            'options_callback'  => [$cbClass, 'getActEvent'],
            'eval'              => array('mandatory' => false, 'disabled' => true, 'tl_class' => 'long clr', 'unique' => true, 'doNotCopy' => true, 'includeBlankOption' => true, 'blankOptionLabel' => 'OOPS! ERROR?', 'doNotSaveEmpty' => true),
            'sql'               => "int(10) unsigned NOT NULL default 0"
        ),
        'tstamp' => array
        (
            'sql'               => "int(10) unsigned NOT NULL default 0"
        ),

        'number' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['number'],
            'exclude'           => true,
            'default'           => '',
            'sorting'           => true,
            'flag'              => 1,
            'search'            => true,
            'inputType'         => 'text',
            'eval'              => array('mandatory'=>false, 'tl_class'=>'w50 clr', 'doNotCopy' => true),
            'sql'               => array('type' => 'string', 'length' => 128, 'default' => '')
        ),

        'location'  => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['location'],
            'exclude'           => true,
            'default'           => 0,
            'inputType'         => 'select',
            'foreignKey'        => 'tl_c4g_reservation_location.name',
            'eval'              => array('chosen' => true, 'mandatory' => false, 'tl_class' => 'long clr','includeBlankOption'=>true, 'doNotCopy' => true),
            'sql'               => "int(10) unsigned NOT NULL default 0",
            'relation'          => array('type' => 'hasOne', 'load' => 'eager'),
        ),

        'organizer'  => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['organizer'],
            'exclude'           => true,
            'default'           => 0,
            'inputType'         => 'select',
            'foreignKey'        => 'tl_c4g_reservation_location.name',
            'eval'              => array('chosen' => true, 'mandatory' => false, 'tl_class' => 'long clr','includeBlankOption'=>true, 'doNotCopy' => true),
            'sql'               => "int(10) unsigned NOT NULL default 0",
            'relation'          => array('type' => 'hasOne', 'load' => 'eager'),
        ),

        'reservationType' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['reservationType'],
            'exclude'           => true,
            'inputType'         => 'select',
            'options_callback'  => [$cbClass, 'getReservationTypes'],
            'eval'              => ['mandatory' => true, 'tl_class' => 'long clr', 'doNotCopy' => true],
            'sql'               => "int(10) unsigned NOT NULL default 0"
        ),

        'minParticipants' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['minParticipants'],
            'exclude'           => true,
            'default'           => 1,
            'sorting'           => false,
            'flag'              => 1,
            'search'            => false,
            'inputType'         => 'text',
            'eval'              => array('mandatory'=>false, 'rgxp'=>'digit', 'minval' => 1, 'tl_class'=>'w50', 'doNotCopy' => true),
            'sql'               => "smallint(3) NOT NULL default 1"
        ),

        'maxParticipants' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['maxParticipants'],
            'exclude'           => true,
            'sorting'           => false,
            'flag'              => 1,
            'search'            => false,
            'inputType'         => 'text',
            'eval'              => array('mandatory'=>false, 'rgxp'=>'digit', 'minval' => 0, 'tl_class'=>'w50', 'doNotCopy' => true),
            'sql'               => "smallint(3) NOT NULL default 0"
        ),

        'maxParticipantsPerEventBooking' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['maxParticipantsPerEventBooking'],
            'exclude'           => true,
            'default'           => 0,
            'inputType'         => 'text',
            'eval'              => array('rgxp'=>'digit', 'mandatory'=>false, 'tl_class'=>'w50'),
            'sql'               => "smallint(5) unsigned NULL default 0"
        ),

        'min_reservation_day' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['min_reservation_day'],
            'exclude'                 => true,
            'sorting'                 => false,
            'flag'                    => 0,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                     => "smallint(3) NOT NULL default 0"
        ),

        'speaker' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['speaker'],
            'exclude'           => true,
            'inputType'         => 'checkbox',
            'options_callback'  => [$cbClass, 'getSpeakerName'],
            'eval'              => array('mandatory' => false, 'tl_class' => 'long clr', 'multiple' => true, 'chosen' => true,'includeBlankOption'=>true, 'doNotCopy' => true),
            'sql'               => "blob NULL"
        ),

        'topic' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['topic'],
            'exclude'           => true,
            'inputType'         => 'checkbox',
            'foreignKey'        => 'tl_c4g_reservation_event_topic.topic',
            'eval'              => array('mandatory' => false, 'tl_class' => 'long clr', 'multiple' => true, 'chosen' => true,'includeBlankOption'=>true, 'doNotCopy' => true),
            'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),
            'sql'               => "blob NULL"
        ),

        'targetAudience' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['targetAudience'],
            'exclude'           => true,
            'inputType'         => 'checkbox',
            'foreignKey'        => 'tl_c4g_reservation_event_audience.targetAudience',
            'eval'              => array('mandatory' => false, 'tl_class' => 'long clr', 'multiple' => true, 'chosen' => true,'includeBlankOption'=>true, 'doNotCopy' => true),
            'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),
            'sql'               => "blob NULL"
        ),

        'price' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['price'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'default'                 => '0.00',
            'eval'                    => array('rgxp'=>'digit','mandatory'=>false, 'maxlength'=>10, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'w50 clr'),
            'sql'                     => "double(7,2) unsigned default '0'"
        ),
        'participant_params' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['participant_params'],
            'inputType'         => 'select',
            'default'           => '-',
            'foreignKey'        => 'tl_c4g_reservation_params.caption',
            'exclude'           => true,
            'eval'              => array('chosen'=>true,'mandatory'=>false,'multiple'=>true,'tl_class'=>'w50 clr','alwaysSave'=> true, 'default' => '-'),
            'sql'               => "blob NULL",
        ),
        'participantParamsFieldType' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['participantParamsFieldType'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'default'                 => 'multi',
            'reference'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event'],
            'options'                 => array('multi','radio'),
            'eval'                    => array('tl_class'=>'w50','feViewable'=>true, 'mandatory'=>false),
            'sql'                     => "char(25) NOT NULL default 'multi'"
        ),
        'participantParamsMandatory' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['participantParamsMandatory'],
            'exclude'           => true,
            'filter'            => false,
            'default'           => '0',
            'inputType'         => 'checkbox',
            'eval'               => array('tl_class'=>'w50 clr'),
            'sql'               => "int(1) unsigned NULL default 0"
        ),
        'taxOptions' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['taxOptions'],
            'exclude'                 => true,
            'inputType'               => 'radio',
            'default'                 => 'tNone',
            'options'                 => array( 'tNone', 'tStandard', 'tReduced'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['references'],
            'eval'                    => array('submitOnChange' => true, 'tl_class' => 'long clr', 'fieldType'=>'radio'),
            // 'sql'                     => "varchar(50) NOT NULL default 'tNone'"
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => 'tNone')
        ),
        'priceoption' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['priceoption'],
            'exclude'                 => true,
            'inputType'               => 'radio',
            'options'                 => array('pReservation','pPerson','pDay','pNight','pNightPerson','pHour','pMin','pAmount'),
            'default'                 => '',
            'reference'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['references'],
            'eval'                    => array('mandatory'=>false, 'tl_class'=>'w50 clr'),
            // 'sql'                     => "varchar(50) NOT NULL default ''"
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'reservationForwarding' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['reservationForwarding'],
            'exclude'                 => true,
            'inputType'               => 'pageTree',
            'foreignKey'              => 'tl_page.title',
            'eval'                    => array('tl_class'=>'w50 clr wizard','mandatory'=>false, 'fieldType'=>'radio'),
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'relation'                => array('type'=>'hasOne', 'load'=>'eager')
        ],

        'reservationForwardingButtonCaption' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['reservationForwardingButtonCaption'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'w50'),
            // 'sql'                     => "varchar(254) NOT NULL default ''"
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ],

        'state' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['state'],
            'exclude'           => true,
            'filter'            => true,
            'options'           => [0,1,2,3], //none, green, orange, red
            'inputType'         => 'select',
            'eval'              => array('tl_class'=>'w50', 'feEditable'=>true, 'feViewable'=>true, 'doNotCopy' => true),
            'sql'               => "char(1) NOT NULL default '0'"
        ),

        'showParticipantInfoFields' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['showParticipantInfoFields'],
            'exclude'           => true,
            'inputType'         => 'checkbox',
            'options'           => array('email','phone','address','postal','city','dateOfBirth','comment','reservation_participant_option','additional1','additional2','additional3','booker'),
            'reference'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['references'],
            'eval'              => array('mandatory' => false, 'tl_class' => 'long clr', 'multiple' => true, 'chosen' => true,'includeBlankOption'=>true, 'doNotCopy' => true),
            'sql'               => "blob NULL"
        ),

        'discountCode' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['discountCode'],
            'default'           => "",
            'exclude'           => true,
            'sorting'           => false,
            'inputType'         => 'text',
            'eval'              => array('doNotCopy' => true, 'unique' => false, 'mandatory' => false, 'maxlength'=>254, 'tl_class' => 'w50 clr'),
            'sql'               => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'discountPercent' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['discountPercent'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'default'                 => '0',
            'eval'                    => array('rgxp'=>'digit','mandatory'=>false, 'maxlength'=>4, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'w50'),
            'sql'                     => "double(5,2) unsigned default '0'"
        ),

        'conferenceLink' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['conferenceLink'],
            'exclude'           => true,
            'search'            => true,
            'inputType'         => 'text',
            'eval'              => array('rgxp'=>'url', 'decodeEntities'=>true, 'maxlength'=>254, 'fieldType'=>'radio', 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'forum', 'memberLink' => true, 'tl_class'=>'clr w50'),
            'sql'               => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

    )
);