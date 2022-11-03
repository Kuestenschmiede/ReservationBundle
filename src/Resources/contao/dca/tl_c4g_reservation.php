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

use con4gis\CoreBundle\Classes\Helper\InputHelper;
use con4gis\CoreBundle\Resources\contao\models\C4gLogModel;
use con4gis\ProjectsBundle\Classes\Common\C4GBrickCommon;
use con4gis\ProjectsBundle\Classes\Notifications\C4GNotification;
use con4gis\ReservationBundle\Classes\Notifications\C4gReservationConfirmation;
use con4gis\ReservationBundle\Classes\Models\C4gReservationParamsModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationTypeModel;
use Contao\Controller;
use Contao\Database;
use Contao\Image;
use Contao\StringUtil;

$GLOBALS['TL_DCA']['tl_c4g_reservation'] = array
(
    //config
    'config' => array
    (
        'dataContainer'      => 'Table',
        'enableVersioning'   => true,
        'ctable'             => ['tl_c4g_reservation_participants'],
        'onload_callback'    => [['tl_c4g_reservation', 'setParent']],
        'doNotDeleteRecords' => true,
        'doNotCopyRecords'   => true,
        'sql'                => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'reservation_type' => 'index',
                'reservation_object' => 'index'
            )
        )
    ),


    //List
    'list' => array
    (
        'sorting' => array
        (
            'mode'              => 2,
            'fields'            => ['beginDate DESC','lastname'],
            'filter'            => (Input::get('do') == "calendar") ? array(array('reservation_object=? AND reservationObjectType=2',Input::get('id'))) : null,
            'panelLayout'       => 'filter;sort,search,limit',
        ),

        'label' => array
        (
            'label_callback'    => ['tl_c4g_reservation', 'listFields'],
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
            ),
            'back' => [
                'class'               => 'header_back',
                'href'                => Input::get('pid') ? 'do=calendar&table=tl_calendar_events&id='.Input::get('pid') : 'key=back',
                'icon'                => 'back.svg',
                'label'               => &$GLOBALS['TL_LANG']['MSC']['backBT'],
            ]
        ),

        'operations' => array
        (
            'edit' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['edit'],
                'href'          => 'act=edit',
                'icon'          => 'edit.gif',
            ),
            'copy' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['copy'],
                'href'          => 'act=copy',
                'icon'          => 'copy.gif',
            ),
            'delete' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['delete'],
                'href'          => 'act=delete',
                'icon'          => 'delete.gif',
                'attributes'    => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\')) return false;Backend.getScrollOffset()"',
            ),
            'show' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['show'],
                'href'          => 'act=show',
                'icon'          => 'show.gif',
            ),
            'participants' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['participants'],
                'href'                => 'table=tl_c4g_reservation_participants',
                'icon'                => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation_participants.svg',
            ),
            'confirmationEmail' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['confirmationEmail'],
                //'href'                => 'key=sendNotification',
                'icon'                => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation_notification.svg',
                'button_callback'     => ['tl_c4g_reservation', 'sendNotification'],
            ),
            'toggle' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['TOGGLE'],
                'icon'                => 'visible.gif',
                'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'     => array('tl_c4g_reservation', 'toggleIcon')
            )
        )
    ),

    //Palettes
    'palettes' => array
    (
        '__selector__' => ['reservationObjectType'/*, 'confirmed'*/],
        'default'   =>  '{reservation_legend}, reservation_type, included_params, additional_params, desiredCapacity, beginDate, endDate, beginTime, endTime, reservationObjectType, reservation_id; {person_legend}, organisation,salutation, lastname, firstname, email, phone, address, postal, city, dateOfBirth; {person2_legend}, organisation2, salutation2, title2, lastname2, firstname2, email2, phone2, address2, postal2, city2; {additional_legend:hide}, additional1, additional2, additional3; {comment_legend}, comment, fileUpload; {notification_legend}, confirmed, internal_comment, specialNotification, emailConfirmationSend; {state_legend}, cancellation, agreed, member_id, group_id, tstamp, bookedAt;',
    ),

    // Subpalettes
    'subpalettes' =>
    [
        'reservationObjectType_1' => 'reservation_object, duration',
        'reservationObjectType_2' => 'reservation_object',
        'reservationObjectType_3' => 'reservation_object, duration',
        //'confirmed' => 'internal_comment, specialNotification, emailConfirmationSend'
    ],
//Fields
    'fields' => array
    (
        'id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['id'],
            'inputType'         => 'text',
            'sql'               => "int(10) unsigned NOT NULL auto_increment",
            'sorting'           => false,
        ),

        'member_id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['memberId'],
            'default'           => 0,
            'inputType'         => 'select',
            'exclude'           => true,
            'options_callback'  => array('tl_c4g_reservation', 'loadMemberOptions'),
            'eval'              => array('mandatory'=>false, 'disabled' => true, 'tl_class' => 'clr long', 'includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['noMember']),
            'filter'            => true,
            'sql'               => "int(10) unsigned NOT NULL default 0"
        ),

        'group_id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['groupId'],
            'default'           => 0,
            'inputType'         => 'select',
            'exclude'           => true,
            'options_callback'  => array('tl_c4g_reservation', 'loadGroupOptions'),
            'eval'              => array('mandatory'=>false, 'disabled' => true, 'tl_class' => 'clr long', 'includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['noGroup']),
            'filter'            => true,
            'sql'               => "int(10) unsigned NOT NULL default 0"
        ),

        'reservation_type' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_type'],
            'inputType'         => 'select',
            'filter'            => true,
            'exclude'           => true,
            'foreignKey'        => 'tl_c4g_reservation_type.caption',
            'eval'              => array('mandatory' => true, 'tl_class' => 'long'),
            'sql'               => "int(10) unsigned NOT NULL default 0",
            'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),
        ),

        'included_params' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_included_option'],
            'inputType'         => 'select',
            'exclude'           => true,
            'foreignKey'        => 'tl_c4g_reservation_params.caption',
            'eval'              => array('chosen'=>true,'mandatory'=>false,'multiple'=>true, 'disabled' => true, 'tl_class'=>'long clr','alwaysSave'=> true),
            'sql'               => "blob NULL",
        ),

        'additional_params' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_additional_option'],
            'inputType'         => 'select',
            'exclude'           => true,
            'foreignKey'        => 'tl_c4g_reservation_params.caption',
            'eval'              => array('chosen'=>true,'mandatory'=>false,'multiple'=>true, 'tl_class'=>'long clr','alwaysSave'=> true),
            'sql'               => "blob NULL",

        ),

        'desiredCapacity' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['desiredCapacity'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>3, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'w50'),
            'sql'                     => "int(3) unsigned NOT NULL default 1"
        ),

        'duration' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['duration'],
            'exclude'           => true,
            'inputType'         => 'text',
            'default'           => '1',
            'eval'              => array('rgxp'=>'digit', 'mandatory'=>false, 'tl_class'=>'w50'),
            'sql'               => "smallint(5) unsigned NOT NULL default 1"
        ),

        'beginDate' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['beginDate'],
            'default'                 => time(),
            'filter'                  => true,
            'sorting'                 => true,
            'flag'                    => 6,
            'search'                  => false,
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'date', 'mandatory'=>true, 'doNotCopy'=>false, 'datepicker'=>true, 'tl_class'=>'w50 wizard clr'),
            'sql'                     => "int(10) signed NULL"
        ),

        'endDate' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['endDate'],
            'default'                 => time(),
            'filter'                  => false,
            'sorting'                 => false,
            'search'                  => false,
            'exclude'                 => true,
            'inputType'               => 'text',
            'flag'                    => 6,
            'eval'                    => array('rgxp'=>'date', 'mandatory'=>false, 'doNotCopy'=>false, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                     => "int(10) signed NULL"
        ),

        'beginTime' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['beginTime'],
            'default'                 => time(),
            'exclude'                 => true,
            'filter'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'flag'                    => 12,
            'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>false, 'tl_class'=>'w50 clr','datepicker'=>true),
            'sql'                     => "int(10) signed NULL"
        ),

        'endTime' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['endTime'],
            'exclude'                 => true,
            'filter'                  => false,
            'default'                 => time(), 'duration',//time()+3600,
            'sorting'                 => false,
            'inputType'               => 'text',
            'flag'                    => 12,
            'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>false, 'tl_class'=>'w50','date','datepicker'=>true),
            'sql'                     => "int(10) signed NULL"
        ),

        'reservationObjectType' =>
        [
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservationObjectType'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'radio',
            'default'                 => '1',
            'options'                 => ['1','3','2'],
            'reference'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['referencesObjectType'],
            'eval'                    => ['tl_class'=>'clr long','submitOnChange' => true],
            'sql'                     => "varchar(254) NOT NULL default '1'"
        ],

        'reservation_object' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_object'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'select',
            'options_callback'        => ['tl_c4g_reservation', 'getActObjects'],
            'eval'                    => array('mandatory'=>false, 'includeBlankOption' => true, 'tl_class' => 'long clr', 'multiple'=>false, 'chosen'=>true),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'organisation' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['organisation'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'long clr'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'salutation' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['salutation'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'default'                 => 'various',
            'reference'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation'],
            'options'                 => array('various','man','woman','divers'),
            'eval'                    => array('tl_class'=>'w50 clr','feViewable'=>true, 'mandatory'=>false),
            'sql'                     => "char(25) NOT NULL default ''"

        ),

        'title' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['title'],
            'exclude'                 => true,
            'search'                  => true,
            'sorting'                 => false,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'clr'),
            'sql'                     => "varchar(50) NOT NULL default ''"
        ),

        'lastname' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['lastname'],
            'exclude'                 => true,
            'search'                  => true,
            'sorting'                 => true,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'clr'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'firstname' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['firstname'],
            'exclude'                 => true,
            'search'                  => false,
            'sorting'                 => false,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'email' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['email'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'rgxp'=>'email', 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'phone' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['phone'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>64, 'rgxp'=>'phone', 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'long'),
            'sql'                     => "varchar(64) NOT NULL default ''"
        ),
       
        'address' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['address'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'postal' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['postal'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>32, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => "varchar(32) NOT NULL default ''"

        ),

        'city' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['city'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"

        ),

        'dateOfBirth' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['dateOfBirth'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'flag'                    => 6,
            'eval'                    => array('rgxp'=>'date', 'mandatory'=>false, 'doNotCopy'=>false, 'datepicker'=>true, 'tl_class'=>'w50 wizard clr'),
            'sql'                     => "int(10) signed NULL"
        ),

        'organisation2' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['organisation2'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'long clr'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'salutation2' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['salutation2'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'default'                 => 'various',
            'reference'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation'],
            'options'                 => array('various','man','woman'),
            'eval'                    => array('tl_class'=>'w50 clr','feViewable'=>true, 'mandatory'=>false),
            'sql'                     => "char(25) NOT NULL default ''"

        ),

        'title2' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['title2'],
            'exclude'                 => true,
            'search'                  => true,
            'sorting'                 => false,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'clr'),
            'sql'                     => "varchar(50) NOT NULL default ''"
        ),

        'lastname2' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['lastname2'],
            'exclude'                 => true,
            'search'                  => true,
            'sorting'                 => false,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'clr'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'firstname2' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['firstname2'],
            'exclude'                 => true,
            'search'                  => false,
            'sorting'                 => false,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'email2' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['email2'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'rgxp'=>'email', 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'phone2' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['phone2'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>64, 'rgxp'=>'phone', 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'long'),
            'sql'                     => "varchar(64) NOT NULL default ''"
        ),

        'address2' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['address2'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'postal2' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['postal2'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>32, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => "varchar(32) NOT NULL default ''"
        ),

        'city2' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['city2'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'additional1' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['additional1'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'additional2' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['additional2'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'additional3' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['additional3'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'reservation_id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_id'],
            'default'           => C4GBrickCommon::getUUID(),
            'flag'              => 1,
            'exclude'           => true,
            'sorting'           => false,
            'search'            => true,
            'inputType'         => 'text',
            'eval'              => array('doNotCopy' => true, 'unique' => false, 'mandatory' => false, 'maxlength'=>254, 'tl_class' => 'long'),
            'save_callback' => array
            (
                array('tl_c4g_reservation', 'generateKey')
            ),
            'sql'               => "varchar(254) NOT NULL default ''"
        ),

        'comment' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['comment'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'textarea',
            'default'                 => '',
            'eval'                    => array('mandatory'=>false, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'long'),
            'sql'                     => "text NULL"
        ),

        'fileUpload' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['fileUpload'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'fileTree',
            'default'                 => null,
            'eval'                    => array('files' => true, 'filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => false),
            'sql'                     => "binary(16)"
        ),

        'signature' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['signature'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'textarea',
            'default'                 => null,
            'eval'                    => ['preserve_tags'=>true, 'style'=>'width: calc(100% - 50px); max-height: 480px'],
            'sql'                     => "text NULL"
        ),

        'cancellation' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['cancellation'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'eval'              => array('tl_class'=>'w50'),
            'sql'               => "char(1) NOT NULL default ''"
        ),

        'agreed' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['agreed'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'eval'              => array('tl_class'=>'w50', 'feEditable'=>true, 'feViewable'=>true, 'mandatory'=>false, 'disabled'=>true),
            'sql'               => "char(1) NOT NULL default ''"

        ),

        'confirmed' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['confirmed'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'eval'              => array('tl_class'=>'long clr', 'feEditable'=>true, 'feViewable'=>true, 'submitOnChange'=>false),
            'sql'               => "char(1) NOT NULL default ''"
        ),

        'specialNotification' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['specialNotification'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'eval'              => array('tl_class'=>'long clr', 'feEditable'=>true, 'feViewable'=>true,),
            'sql'               => "char(1) NOT NULL default ''"
        ),

        'emailConfirmationSend' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['emailConfirmationSend'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'eval'              => array('tl_class'=>'long clr', 'feEditable'=>true, 'feViewable'=>true,),
            'sql'               => "char(1) NOT NULL default '0'"
        ),

        'internal_comment' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['internal_comment'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'textarea',
            'default'                 => '',
            'eval'                    => array('mandatory'=>false, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'long clr'),
            'sql'                     => "text NULL"
        ),

        'tstamp' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['tstamp'],
            'inputType'         => 'text',
            'sql'               => "int(10) unsigned NOT NULL default 0",
            'sorting'           => true,
            'flag'              => 12,
            'default'           => time(),
            'eval' => array('rgxp'=>'datim', 'doNotCopy'=>true, 'tl_class'=>'w50', 'disabled'=>true)
        ),

        'bookedAt' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['bookedAt'],
            'inputType'         => 'text',
            'sql'               => "int(10) unsigned NOT NULL default 0",
            'sorting'           => true,
            'flag'              => 12,
            'default'           => time(),
            'eval' => array('rgxp'=>'datim', 'doNotCopy'=>true, 'tl_class'=>'w50', 'disabled'=>true )
        )

    )
);


/**
 * Class tl_c4g_reservation
 */
class tl_c4g_reservation extends Backend
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

        if (strlen($this->Input->get('tid')))
        {
            $this->toggleVisibility($this->Input->get('tid'), ($this->Input->get('state') == 1));
            $this->redirect($this->getReferer());
        }

        $href .= '&amp;id='.$this->Input->get('id').'&amp;tid='.$row['id'].'&amp;state='.$row[''];

        if ($row['cancellation'])
        {
            $icon = 'invisible.gif';
        }

        return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';

    }

    public function toggleVisibility($intId, $blnCancellation)
    {

        $this->createInitialVersion('tl_c4g_reservation', $intId);

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_c4g_reservation']['fields']['cancellation']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_c4g_reservation']['fields']['cancellation']['save_callback'] as $callback)
            {
                $this->import($callback[0]);
                $blnCancellation = $this->$callback[0]->$callback[1](!$blnCancellation, $this);
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE tl_c4g_reservation SET tstamp=". time() .", cancellation='" . ($blnCancellation ? '0' : '1') . "' WHERE `id`=?")
            ->execute($intId);
        $this->createNewVersion('tl_c4g_reservation', $intId);
    }

    public function listFields($arrRow)
    {
        $objectType = $arrRow['reservationObjectType'];
        $object_id = $arrRow['reservation_object'];

        $reservationObjects = '';
        if ($objectType === '2') {
            $event = CalendarEventsModel::findByPk($object_id);
            if ($event) {
                $object = $event->title;
            }
        } else {
            $reservation_object = \con4gis\ReservationBundle\Classes\Models\C4gReservationObjectModel::findByPk($object_id);
            if ($reservation_object) {
                $object = $reservation_object->caption;
            }
        }


        $arrRow['reservation_object'] = $object;

        if ($arrRow['beginDate']) {
            $arrRow['beginDate'] = $arrRow['beginDate'] ? date($GLOBALS['TL_CONFIG']['dateFormat'],$arrRow['beginDate']). '&nbsp;' .date($GLOBALS['TL_CONFIG']['timeFormat'],$arrRow['beginTime']) : date($GLOBALS['TL_CONFIG']['dateFormat'],$arrRow['beginDate']);
        } else {
            $arrRow['beginDate'] = '';
        }

        if ($arrRow['endDate']) {
            $arrRow['endDate'] = $arrRow['endDate'] ? date($GLOBALS['TL_CONFIG']['dateFormat'],$arrRow['endDate']). '&nbsp;' .date($GLOBALS['TL_CONFIG']['timeFormat'],$arrRow['endTime']) : date($GLOBALS['TL_CONFIG']['dateFormat'],$arrRow['endDate']);
        } else {
            $arrRow['endDate'] = '';
        }

        $type = \con4gis\ReservationBundle\Classes\Models\C4gReservationTypeModel::findByPk($arrRow['reservation_type']);
        if ($type) {
            $arrRow['reservation_type'] = $type->caption;
        }

        $result = [
            $arrRow['beginDate'],
            $arrRow['endDate'],
            $arrRow['desiredCapacity'],
            $arrRow['reservation_type'],
            $arrRow['lastname'],
            $arrRow['firstname'],
            $arrRow['reservation_object']
        ];
        return $result;
    }

    /**
     * @param DataContainer|array $dc
     * @return array
     */
    public function getActObjects($dc)
    {
        $return = [];
        if ($dc instanceof DataContainer && $dc->activeRecord->reservationObjectType) {
            $reservationObjectType = $dc->activeRecord->reservationObjectType;
        } elseif (is_array($dc)) {
            $reservationObjectType = $dc['reservationObjectType'];
        }

        if (!$reservationObjectType) {
            $objects = $this->Database->prepare("SELECT id,caption FROM tl_c4g_reservation_object")
                ->execute();

            while ($objects->next()) {
                $return[$objects->id] = $objects->caption;
            }

            $events = $this->Database->prepare("SELECT id,title,startDate FROM tl_calendar_events")
                ->execute();

            while ($events->next()) {
                $return[$events->id] = date($GLOBALS['TL_CONFIG']['dateFormat'],$events->startDate).': '.$events->title;
            }
        } else {
            switch ($reservationObjectType) {
                case '1':
                case '3':
                    $objects = $this->Database->prepare("SELECT id,caption FROM tl_c4g_reservation_object")
                        ->execute();

                    while ($objects->next()) {
                        $return[$objects->id] = $objects->caption;
                    }
                    break;
                case '2':
                    $events = $this->Database->prepare("SELECT id,title,startDate FROM tl_calendar_events")
                        ->execute();

                    while ($events->next()) {
                        $return[$events->id] = date($GLOBALS['TL_CONFIG']['dateFormat'],$events->startDate).': '.$events->title;
                    }
                    break;
            }
        }

        return $return;
    }

    /**
     * @param DataContainer $dc
     */
    public function doNotDeleteDataWithoutParent(DataContainer $dc)
    {
        //return;
    }

    /**
     * @param \Contao\DataContainer $dc
     */
    public function setParent(Contao\DataContainer $dc)
    {
        \Contao\Message::addInfo($GLOBALS['TL_LANG']['tl_c4g_reservation']['infoReservation']);

        $do = $this->Input->get('do');
        $id = $this->Input->get('id');

        $GLOBALS['TL_DCA']['tl_c4g_reservation']['list']['label']['fields'] =
            ['beginDate','endDate','desiredCapacity','reservation_type:tl_c4g_reservation_type.caption','lastname','firstname','reservation_object'];

        if ($id && $do && ($do == 'calendar')) {
            $GLOBALS['TL_DCA']['tl_c4g_reservation']['fields']['reservationObjectType']['default'] = '2';
            $GLOBALS['TL_DCA']['tl_c4g_reservation']['fields']['reservationObjectType']['eval']['disabled'] = true;
            $GLOBALS['TL_DCA']['tl_c4g_reservation']['fields']['reservation_object']['default'] = $id;
            $GLOBALS['TL_DCA']['tl_c4g_reservation']['fields']['reservation_object']['eval']['chosen'] = false;
            $GLOBALS['TL_DCA']['tl_c4g_reservation']['fields']['reservation_object']['eval']['disabled'] = true;

            $GLOBALS['TL_DCA']['tl_c4g_reservation']['fields']['beginDate']['eval']['disabled'] = true;
            $GLOBALS['TL_DCA']['tl_c4g_reservation']['fields']['beginTime']['eval']['disabled'] = true;
            $GLOBALS['TL_DCA']['tl_c4g_reservation']['fields']['endDate']['eval']['disabled'] = true;
            $GLOBALS['TL_DCA']['tl_c4g_reservation']['fields']['endTime']['eval']['disabled'] = true;
        }

        // Check current action
        $key = Contao\Input::get('key');
        $reservationType = 0;
        if ($id && $key && ($key == 'sendNotification')) {
            C4gReservationConfirmation::sendNotification($id);
            //delete key per redirect
            Controller::redirect(str_replace('&key='.$key, '', \Environment::get('request')));
        }
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
     * @param $dc
     * @return array
     */
    public function loadGroupOptions($dc) {
        $options = [];
        $options[$dc->activeRecord->id] = '';

        $stmt = $this->Database->prepare("SELECT id, name FROM tl_member_group WHERE `disable` != 1");
        $result = $stmt->execute()->fetchAllAssoc();

        foreach ($result as $row) {
            $options[$row['id']] = $row['name'];
        }
        return $options;
    }

    public function sendNotification($row, $href, $label, $title, $icon) {
        $rt = Input::get('rt');
        $do = Input::get('do');

        $attributes = 'style="margin-right:3px"';
        $imgAttributes = 'style="width: 18px; height: 18px"';

        $showButton = false;

        if (($row['confirmed'] || $row['specialNotification']) && (!$row['emailConfirmationSend'])) {
            $type = $row['reservation_type'];
            if ($type) {
                $reservationType = Database::getInstance()->prepare("SELECT * FROM tl_c4g_reservation_type WHERE `id`=? LIMIT 1")->execute($type)->fetchAssoc();

                if ($reservationType) {
                    if ($row['confirmed']) {
                        $notificationConifrmationType = StringUtil::deserialize($reservationType['notification_confirmation_type']);

                        if ($notificationConifrmationType && (count($notificationConifrmationType) > 0)) {
                            $showButton = true;
                        }
                    }

                    if ($row['specialNotification']) {
                        $notificationSpecialType = StringUtil::deserialize($reservationType['notification_special_type']);

                        if ($notificationSpecialType && (count($notificationSpecialType) > 0)) {
                            $showButton = true;
                        }
                    }
                }


            }
        }

        if (!$showButton) {
            return '';
        }

        $href = "/contao?do=$do&key=sendNotification&id=".$row['id'];
        return '<a href="' . $href . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>'.Image::getHtml($icon, $label, $imgAttributes).'</a> ';
    }

    /**
     * @param $varValue
     * @param DataContainer $dc
     * @return string
     */
    public function generateKey($value, $dc) {
        if (!$value) {
            $value = C4GBrickCommon::getUUID();
            $database = Database::getInstance();
            $reservations = $database->prepare("SELECT * FROM tl_c4g_reservation where `reservation_id`=?")
                ->execute($value)->fetchAllAssoc();
            if ($reservations && count($reservations) > 0) {
                $value = C4GBrickCommon::getUUID();
            }
        }

        return $value;
    }
}
