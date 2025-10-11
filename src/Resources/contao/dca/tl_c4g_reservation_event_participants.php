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
use con4gis\ReservationBundle\Classes\Callbacks\C4gReservationEventParticipants;
use Contao\DC_Table;

$cbClass = C4gReservationEventParticipants::class;

/**
 * Table tl_module
 */

$GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants'] = array
(
    //config
    'config' => array
    (
        'dataContainer'     => DC_Table::class,
        'enableVersioning'  => true,
        'ptable'            => 'tl_calendar_events',
        'onload_callback'   => [[$cbClass, 'setLabel']],
        'doNotCopyRecords'  => true,
        'notCreatable'      => true,
        'sql'               => array
        (
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
            'fields'            => array('id' , /* 'title', */'lastname','firstname','email'),
            'panelLayout'       => 'filter;sort,search,limit',
        ),

        'label' => array
        (
            'label_callback'    => array($cbClass, 'listFields'),
            'showColumns'       => true,
        ),

        'global_operations' => array
        (
            /* 'all' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'          => 'act=select',
                'class'         => 'header_edit_all',
                'attributes'    => 'onclick="Backend.getScrollOffSet()" accesskey="e"'
            ) */
        ), 

        'operations' => array
        (
            
            // 'edit' => array
            // (
            //     'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['edit'],
            //     'href'          => 'act=edit',
            //     'icon'          => 'edit.gif',
            // ),
            // 'copy' => array
            // (
            //     'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['copy'],
            //     'href'          => 'act=copy',
            //     'icon'          => 'copy.gif',
            // ),
            // 'delete' => array
            // (
            //     'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['delete'],
            //     'href'          => 'act=delete',
            //     'icon'          => 'delete.gif',
            //     'attributes'    => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\')) return false;Backend.getScrollOffset()"',
            // ),
            // 'show' => array
            // (
            //     'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['show'],
            //     'href'          => 'act=show',
            //     'icon'          => 'show.gif',
            // ),
            // 'toggle' => array
            // (
            //     'label'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['TOGGLE'],
            //     'icon'                => 'visible.gif',
            //     'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
            //     'button_callback'     => array('tl_c4g_reservation_event_participants', 'toggleIcon')
            // )
        )
    ),

    //Palettes
    'palettes' => array
    (
        'default'   =>  '{participants_legend}, title, lastname, firstname, email, comment, participant_params, payed, checkedIn, cancellation;',
    ),


    //Fields
    'fields' => array
    (
        'id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['id'],
            'sql'               => "int(10) unsigned NOT NULL auto_increment",
            'sorting'           => true,
            'search'            => false
        ),

        'pid' => array
        (
            'sql'               => "int(10) unsigned NOT NULL default '0'"
        ),

        'reservation_id' => array
        (
            'sql'               => "int(10) unsigned NOT NULL default '0'"
        ),

        'participant_id' => array
        (
            'sql'               => "int(10) unsigned NOT NULL default '0'"
        ),

        'tstamp' => array
        (
            'sql'               => "int(10) unsigned NOT NULL default 0"
        ),

        'title' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['title'],
            'exclude'                 => true,
            'search'                  => false,
            'sorting'                 => false,
            //'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'clr'),
            'sql'                     => array('type' => 'string', 'length' => 50, 'default' => '')
        ),

        'lastname' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['lastname'],
            'exclude'                 => true,
            'search'                  => true,
            'sorting'                 => true,
            'flag'                    => 11,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'clr'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'firstname' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['firstname'],
            'exclude'                 => true,
            'search'                  => false,
            'sorting'                 => false,
            // 'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'long'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'dateOfBirth' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['dateOfBirth'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            //'flag'                    => 6,
            'eval'                    => array('rgxp'=>'date', 'mandatory'=>false, 'doNotCopy'=>false, 'datepicker'=>true, 'tl_class'=>'w50 wizard clr'),
            'sql'                     => "int(10) signed NULL"
        ),

        'email' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['email'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'rgxp'=>'email', 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'long'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'phone' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['phone'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>64, 'rgxp'=>'phone', 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'long'),
            'sql'                     => array('type' => 'string', 'length' => 64, 'default' => '')
        ),
       
        'address' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['address'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'postal' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['postal'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>32, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => array('type' => 'string', 'length' => 32, 'default' => '')

        ),

        'city' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['city'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')

        ),

        'comment' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['comment'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'textarea',
            'default'                 => '',
            'eval'                    => array('mandatory'=>false, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'long'),
            'sql'                     => "text NULL"
        ),

        'participant_params' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['reservation_participant_option'],
            'exclude'           => true,
            'sorting'           => false,
            'filter'            => false,
            'inputType'         => 'select',
            'foreignKey'        => 'tl_c4g_reservation_params.caption',
            'eval'              => array('chosen'=>true,'mandatory'=>false,'multiple'=>true, 'tl_class'=>'long clr','alwaysSave'=> true),
            'sql'               => "blob NULL",
        ),

        'booker' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['booked_by'],
            'exclude'                 => true,
            'search'                  => false,
            'sorting'                 => false,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array(
                                            'mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'clr'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'additional1' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['additional1'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'additional2' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['additional2'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'additional3' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['additional3'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'payed' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['payed'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'eval'              => array('tl_class'=>'long clr', 'feEditable'=>true, 'feViewable'=>true, 'submitOnChange'=>false),
            'sql'               => "char(1) NOT NULL default ''"
        ),

        'checkedIn' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['checkedIn'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'eval'              => array('tl_class'=>'long clr', 'feEditable'=>true, 'feViewable'=>true, 'submitOnChange'=>false),
            'sql'               => "char(1) NOT NULL default ''"
        ),

        'cancellation' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['cancellation'],
            'exclude'           => true,
            'filter'            => false,
            'inputType'         => 'checkbox',
            'eval'              => array('tl_class'=>'w50'),
            'sql'               => "char(1) NOT NULL default ''"
        )
    )
);