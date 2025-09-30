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

use con4gis\ReservationBundle\Classes\Callbacks\C4gReservation;
use con4gis\ProjectsBundle\Classes\Common\C4GBrickCommon;
use Contao\Input;
use Contao\DC_Table;

$cbClass = C4gReservation::class;

$GLOBALS['TL_DCA']['tl_c4g_reservation'] = array
(
    //config
    'config' => array
    (
        'dataContainer'      => DC_Table::class,
        'enableVersioning'   => true,
        'ctable'             => ['tl_c4g_reservation_participants'],
        'onload_callback'    => [[$cbClass, 'setParent']],
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
            'label_callback'    => [$cbClass, 'listFields'],
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
                'button_callback'     => [$cbClass, 'sendNotification'],
            ),
            'toggle' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['TOGGLE'],
                'icon'                => 'visible.gif',
                'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'     => array($cbClass, 'toggleIcon')
            )
        )
    ),

    //Palettes
    'palettes' => array
    (
        '__selector__' => ['reservationObjectType'],
        'default'   =>  '{reservation_legend}, reservation_type, included_params, additional_params, desiredCapacity, beginDate, endDate, beginTime, endTime, reservationObjectType, reservation_id, discountCode; {person_legend}, organisation,salutation, lastname, firstname, email, phone, address, postal, city, dateOfBirth; {person2_legend}, organisation2, salutation2, title2, lastname2, firstname2, email2, phone2, address2, postal2, city2; {additional_legend:hide}, additional1, additional2, additional3; {comment_legend}, comment, fileUpload; {notification_legend}, confirmed, internal_comment, specialNotification, emailConfirmationSend; {state_legend}, payed, checkedIn, cancellation, agreed, member_id, group_id, tstamp, bookedAt;',
    ),

    // Subpalettes
    'subpalettes' =>
    [
        'reservationObjectType_1' => 'reservation_object, duration',
        'reservationObjectType_2' => 'reservation_object',
        'reservationObjectType_3' => 'reservation_object, duration',
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
            'options_callback'  => array($cbClass, 'loadMemberOptions'),
            'eval'              => array('mandatory'=>false, 'disabled' => false, 'tl_class' => 'clr long', 'includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['noMember']),
            'filter'            => true,
            'sql'               => "int(10) unsigned NOT NULL default 0"
        ),

        'group_id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['groupId'],
            'default'           => 0,
            'inputType'         => 'select',
            'exclude'           => true,
            'options_callback'  => array($cbClass, 'loadGroupOptions'),
            'eval'              => array('mandatory'=>false, 'disabled' => false, 'tl_class' => 'clr long', 'includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['noGroup']),
            'filter'            => true,
            'sql'               => "int(10) unsigned NOT NULL default 0"
        ),

        'formular_id' => array
        (
            'sql'               => "int(10) unsigned NOT NULL default '0'"
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
            'default'                 => time(),
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
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '1')
        ],

        'reservation_object' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_object'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'select',
            'options_callback'        => [$cbClass, 'getActObjects'],
            'eval'                    => array('mandatory'=>false, 'includeBlankOption' => true, 'tl_class' => 'long clr', 'multiple'=>false, 'chosen'=>true),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'organisation' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['organisation'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'long clr'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
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
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
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
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
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
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'email' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['email'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'rgxp'=>'email', 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'long'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'phone' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['phone'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>64, 'rgxp'=>'phone', 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'long'),
            'sql'                     => array('type' => 'string', 'length' => 64, 'default' => '')
        ),
       
        'address' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['address'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'postal' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['postal'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>32, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => array('type' => 'string', 'length' => 32, 'default' => '')

        ),

        'city' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['city'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')

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
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
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
            'sql'                     => array('type' => 'string', 'length' => 50, 'default' => '')
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
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
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
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'email2' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['email2'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'rgxp'=>'email', 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'long'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'phone2' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['phone2'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>64, 'rgxp'=>'phone', 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'long'),
            'sql'                     => array('type' => 'string', 'length' => 64, 'default' => '')
        ),

        'address2' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['address2'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'postal2' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['postal2'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>32, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => array('type' => 'string', 'length' => 32, 'default' => '')
        ),

        'city2' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['city2'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'additional1' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['additional1'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'additional2' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['additional2'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'additional3' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['additional3'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
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
                array($cbClass, 'generateKey')
            ),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'discountCode' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['discountCode'],
            'default'           => "",
            'flag'              => 1,
            'exclude'           => true,
            'sorting'           => false,
            'search'            => true,
            'inputType'         => 'text',
            'eval'              => array('doNotCopy' => true, 'unique' => false, 'mandatory' => false, 'maxlength'=>254, 'tl_class' => 'long'),
            'sql'               => array('type' => 'string', 'length' => 254, 'default' => '')
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

        'payed' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['payed'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'eval'              => array('tl_class'=>'long clr', 'feEditable'=>true, 'feViewable'=>true, 'submitOnChange'=>false),
            'sql'               => "char(1) NOT NULL default ''"
        ),

        'checkedIn' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['checkedIn'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'eval'              => array('tl_class'=>'long clr', 'feEditable'=>true, 'feViewable'=>true, 'submitOnChange'=>false),
            'sql'               => "char(1) NOT NULL default ''"
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
            'eval' => array('rgxp'=>'datim', 'doNotCopy'=>true, 'tl_class'=>'w50', 'disabled'=>true)
        ),

        'qrContent' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['qrContent'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'textarea',
            'default'                 => null,
            'editable'                => false,
            'eval'                    => ['tl_class'=>'clr long', 'feEditable'=>false, 'feViewable'=>false, 'disabled'=>true],
            'sql'                     => "text NULL"
        ),

        'qrFileName' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['qrFileName'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'textarea',
            'default'                 => null,
            'editable'                => false,
            'eval'                    => ['tl_class'=>'clr long', 'feEditable'=>false, 'feViewable'=>false, 'disabled'=>true],
            'sql'                     => "text NULL"
        )
    )
);