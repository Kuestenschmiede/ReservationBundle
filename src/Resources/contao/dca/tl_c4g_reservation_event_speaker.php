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

use con4gis\ReservationBundle\Classes\Callbacks\C4gReservationEventSpeaker;
use con4gis\ReservationBundle\Classes\Models\C4gReservationSettingsModel;
use Contao\DC_Table;
use Contao\Config;


$cbClass = C4gReservationEventSpeaker::class;

$GLOBALS['TL_DCA']['tl_c4g_reservation_event_speaker'] = array
(
    //config
    'config' => array
    (
        'dataContainer'     => DC_Table::class,
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
            'fields'            => array('title','firstname','lastname'),
            'panelLayout'       => 'filter;sort,search,limit'
        ),

        'label' => array
        (
            'fields'            => array('title','firstname','lastname'),
            'showColumns'       => true,
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
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['edit'],
                'href'          => 'act=edit',
                'icon'          => 'edit.gif',
            ),
            'copy' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['copy'],
                'href'          => 'act=copy',
                'icon'          => 'copy.gif',
            ),
            'delete' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['delete'],
                'href'          => 'act=delete',
                'icon'          => 'delete.gif',
                'attributes'    => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\')) return false;Backend.getScrollOffset()"',
            ),
            'show' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['show'],
                'href'          => 'act=show',
                'icon'          => 'show.gif',
            ),
            'toggle' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['toggle'],
                'icon'                => 'visible.gif',
                'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'     => array($cbClass, 'toggleIcon')
            )
        )
    ),

    //Palettes
    'palettes' => array
    (
        'default'   =>  '{speaker_legend},title, firstname, lastname, alias, email, phone, address, postal, city, website, vita, photo, speakerForwarding, sorting, published;'
    ),


    //Fields
    'fields' => array
    (
        'id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['id'],
            'sql'               => "int(10) unsigned NOT NULL auto_increment"
        ),

        'tstamp' => array
        (
            'sql'               => "int(10) unsigned NOT NULL default '0'"
        ),

        //ToDo memberId [OPTIONAL] for member linking

        'title' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['title'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'w50 clr'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'firstname' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['firstname'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => true,
            'sorting'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'w50 clr'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')

        ),

          'lastname' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['lastname'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => true,
            'sorting'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'w50'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')

        ),

        'alias' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['alias'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => array('rgxp'=>'alias', 'doNotCopy'=>true, 'unique'=>true, 'maxlength'=>255, 'tl_class'=>'long clr'),
            'save_callback' => array
            (
                array($cbClass, 'generateAlias')
            ),
            'sql' => "varchar(255) BINARY NOT NULL default ''"
        ),

        'email' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['email'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'rgxp'=>'email', 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'w50 clr'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'phone' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['phone'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>64, 'rgxp'=>'phone', 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'w50'),
            'sql'                     => array('type' => 'string', 'length' => 64, 'default' => '')
        ),

        'address' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['address'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long clr'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'postal' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['postal'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>32, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50 clr'),
            'sql'                     => array('type' => 'string', 'length' => 32, 'default' => '')

        ),

        'city' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['city'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')

        ),

        'vita' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['vita'],
            'default'                 => '',
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'				  => 'textarea',
            'eval'                    => ['mandatory'=>false, 'rte'=>'tinyMCE', 'helpwizard'=>true, 'tl_class'=>'long clr'],
            'explanation'             => 'insertTags',
            'sql'                     => "text NULL"
        ),

        'photo' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['photo'],
            'exclude'           => true,
            'inputType'         => 'fileTree',
            'sorting'           => false,
            'search'            => false,
            'extensions'        => 'jpg, jpeg, png, tif',
            'exclude'           => true,
            'eval'              => array('filesOnly'=>true, 'files'=>true, 'fieldType'=>'radio', 'tl_class'=>'long clr', 'extensions'=>Config::get('validImageTypes')),
            'sql'               => "blob NULL"
        ),

        'website' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['website'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'url', 'decodeEntities'=>true, 'maxlength'=>254, 'fieldType'=>'radio', 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'forum', 'memberLink' => true, 'tl_class'=>'clr w50'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'speakerForwarding' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['speakerForwarding'],
            'exclude'                 => true,
            'inputType'               => 'pageTree',
            'foreignKey'              => 'tl_page.title',
            'eval'                    => array('tl_class'=>'w50 wizard','mandatory'=>false, 'fieldType'=>'radio'),
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'relation'                => array('type'=>'hasOne', 'load'=>'eager')
        ),

        'sorting' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['sorting'],
            'exclude'           => true,
            'default'           => '0',
            'sorting'           => true,
            'search'            => false,
            'inputType'         => 'text',
            'eval'              => array('rgxp'=>'digit','mandatory'=>false, 'tl_class'=>'w50 clr'),
            'sql'               => "int(5) unsigned NOT NULL default '0'"
        ),
        'published' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['published'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'sql'               => "int(1) unsigned NULL default 1"
        )
    )
);