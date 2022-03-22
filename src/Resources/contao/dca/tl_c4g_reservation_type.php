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

use con4gis\ReservationBundle\Classes\Models\C4gReservationTypeModel;

$GLOBALS['TL_DCA']['tl_c4g_reservation_type'] = array
(
    //config
    'config' => array
    (
        'dataContainer'     => 'Table',
        'enableVersioning'  => true,
        'onload_callback'   => [['tl_c4g_reservation_type', 'showInfoMessage']],
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
            //'label_callback'    => array('tl_c4g_reservation_type', 'listTypes'),
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
                'button_callback'     => array('tl_c4g_reservation_type', 'toggleIcon')
            )
        )
    ),

    //Palettes
    'palettes' => array
    (
        '__selector__'  => array('periodType','auto_del','reservationObjectType'),
        'default'       =>  '{type_legend},caption,alias,options,description;{object_legend},reservationObjectType,bookRunning,minParticipantsPerBooking,maxParticipantsPerBooking,almostFullyBookedAt,included_params,additional_params,participant_params,location,published;{notification_legend:hide},notification_type,notification_confirmation_type,notification_special_type;{expert_legend:hide},member_id,group_id,auto_del,auto_send;'
    ),

    //Subpalettes
   'subpalettes' => array(
        'auto_del_daily'                => 'del_time;',
        'reservationObjectType_1'       => 'cloneObject,periodType,objectCount,min_residence_time,max_residence_time,severalBookings,directBooking',
        'reservationObjectType_2'       => '',
        'reservationObjectType_3'       => 'cloneObject,periodType,objectCount,min_residence_time,max_residence_time,severalBookings,directBooking',
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
            'options_callback'  => array('tl_c4g_reservation_type', 'loadMemberOptions'),
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
            'options_callback'  => array('tl_c4g_reservation_type', 'loadGroupOptions'),
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
            'eval'              => array('mandatory' => true, 'tl_class' => 'w50', 'maxlength' => 254),
            'sql'               => "varchar(254) NOT NULL default ''"
        ),

        'alias' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['alias'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => array('rgxp'=>'alias', 'doNotCopy'=>true, 'unique'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'save_callback' => array
            (
                array('tl_c4g_reservation_type', 'generateAlias')
            ),
            'sql' => "varchar(255) BINARY NOT NULL default ''"
        ),
//
//        'event_caption' => array
//        (
//            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event_caption'],
//            'exclude'           => true,
//            'default'           => '',
//            'sorting'           => true,
//            'flag'              => 1,
//            'search'            => true,
//            'inputType'         => 'text',
//            'eval'              => array('mandatory' => true, 'tl_class' => 'long', 'maxlength' => 254),
//            'sql'               => "varchar(254) NOT NULL default ''"
//        ),

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
                )
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
            'sql'                     => "varchar(254) NOT NULL default '1'"
        ),

       'periodType' => array
       (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['periodType'],
            'default'           => '',
            'exclude'           => true,
            'inputType'         => 'select',
            'options'           => array('minute','hour','day','week'),
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
            'sql'                     => "varchar(10) NOT NULL default ''"
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
            'eval'              => array('chosen' => true, 'includeBlankOption' => true, 'mandatory' => false, 'tl_class' => 'long'),
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
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'notification_confirmation_type'  => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['notification_confirmation_type'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'foreignKey'              => 'tl_nc_notification.title',
            'eval'                    => array('multiple' => true),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'notification_special_type'  => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['notification_special_type'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'foreignKey'              => 'tl_nc_notification.title',
            'eval'                    => array('multiple' => true),
            'sql'                     => "varchar(254) NOT NULL default ''"
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
            'inputType'               => 'select',
            'foreignKey'              => 'tl_c4g_reservation_params.caption',
            'eval'                    => array('chosen'=>true, 'mandatory'=>false,'multiple'=>true, 'tl_class'=>'long clr','alwaysSave'=> true),
            'sql'                     => "blob NULL"
        ),

        'additional_params' => array
        (
        'label'                   => &$GLOBALS['TL_LANG']['ts_c4g_reservation_type']['additional_params'],
        'exclude'                 => true,
        'inputType'               => 'select',
        'foreignKey'              => 'tl_c4g_reservation_params.caption',
        'eval'                    => array('chosen'=>true,'mandatory'=>false,'multiple'=>true, 'tl_class'=>'long clr','alwaysSave'=> true),
        'sql'                     => "blob NULL"
        ),

        'participant_params' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['ts_c4g_reservation_type']['participant_params'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'foreignKey'              => 'tl_c4g_reservation_params.caption',
            'eval'                    => array('chosen'=>true,'mandatory'=>false,'multiple'=>true, 'tl_class'=>'long clr','alwaysSave'=> true),
            'sql'                     => "blob NULL"
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


/**
 * Class tl_c4g_reservation_type
 */
class tl_c4g_reservation_type extends Backend
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
            $this->toggleVisibility($this->Input->get('tid'), ($this->Input->get('state') == '0'));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_c4g_reservation_type::published', 'alexf')) {
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
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_c4g_reservation_type::published', 'alexf')) {
            $this->log('Not enough permissions to show/hide record ID "' . $intId . '"', 'tl_c4g_reservation_type toggleVisibility', TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        $this->createInitialVersion('tl_c4g_reservation_type', $intId);

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_c4g_reservation_type']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_c4g_reservation_type']['fields']['published']['save_callback'] as $callback) {
                $this->import($callback[0]);
                $blnPublished = $this->$callback[0]->$callback[1]($blnPublished, $this);
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE tl_c4g_reservation_type SET tstamp=" . time() . ", published='" . ($blnPublished ? '0' : '1') . "' WHERE `id`=?")
            ->execute($intId);
        $this->createNewVersion('tl_c4g_reservation_type', $intId);
    }

    /**
     * @param $dc
     * @return array
     */
    public function loadMemberOptions($dc) {
        $options = [];

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

        $stmt = $this->Database->prepare("SELECT id, name FROM tl_member_group WHERE `disable` != 1");
        $result = $stmt->execute()->fetchAllAssoc();

        foreach ($result as $row) {
            $options[$row['id']] = $row['name'];
        }
        return $options;
    }

    /**
     * @param \Contao\DataContainer $dc
     */
    public function showInfoMessage(Contao\DataContainer $dc)
    {
        \Contao\Message::addInfo($GLOBALS['TL_LANG']['tl_c4g_reservation_type']['infotext']);
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
            return $this->Database->prepare("SELECT id FROM tl_c4g_reservation_type WHERE alias=? AND id!=?")->execute($alias, $dc->id)->numRows > 0;
        };

        // Generate the alias if there is none
        if (!$varValue)
        {
            $varValue = \Contao\System::getContainer()->get('contao.slug')->generate($dc->activeRecord->caption, C4gReservationTypeModel::findByPk($dc->activeRecord->id)->jumpTo, $aliasExists);
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
