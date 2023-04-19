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

use Contao\Controller;
use Contao\Database;

$GLOBALS['TL_DCA']['tl_c4g_reservation_settings'] = array
(
    //config
    'config' => array
    (
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
            'fields'            => array('caption'),
            'panelLayout'       => 'filter;sort,search,limit'
        ),

        'label' => array
        (
            'fields'            => array('caption'),
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
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['edit'],
                'href'          => 'act=edit',
                'icon'          => 'edit.gif',
            ),
            'copy' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['copy'],
                'href'          => 'act=copy',
                'icon'          => 'copy.gif',
            ),
            'delete' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['delete'],
                'href'          => 'act=delete',
                'icon'          => 'delete.gif',
                'attributes'    => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\')) return false;Backend.getScrollOffset()"',
            ),
            'show' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['show'],
                'href'          => 'act=show',
                'icon'          => 'show.gif',
            )
        )
    ),

    //Palettes
    'palettes' => array
    (
        'default'   =>  '{settings_legend}, caption;'.
                        '{form_legend:hide}, withCapacity, fieldSelection, privacy_policy_text, privacy_policy_site, reservationButtonCaption, showDetails, showPrices, showPricesWithTaxes, showEndTime, showInlineDatepicker, removeBookedDays,showArrivalAndDeparture;'.
                        '{object_legend:hide}, emptyOptionLabel, showDateTime;'.
                        '{type_legend:hide}, reservation_types, typeDefault, typeHide, objectHide, hideReservationKey, typeWithEmptyOption;'.
                        '{notification_legend:hide}, notification_type;'.
                        '{redirect_legend:hide}, reservation_redirect_site, speaker_redirect_site, location_redirect_site;'.
                        '{expert_legend:hide}, specialParticipantMechanism, hideParticipantsEmail, showMemberData, postals;'
    ),


    //Fields
    'fields' => array
    (
        'id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['id'],
            'sql'               => "int(10) unsigned NOT NULL auto_increment"
        ),

        'tstamp' => array
        (
            'sql'               => "int(10) unsigned NOT NULL default '0'"
        ),

        'caption' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['caption'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"

        ),

        'reservation_addition_booking_params' => array (

        ),

        'reservation_types' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['reservation_types'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'options_callback'        => [\con4gis\ReservationBundle\Classes\Callbacks\ReservationType::class,'getAllTypes'],
            'eval'                    => array('mandatory'=>false, 'multiple'=>true),
            'sql'                     => "blob NULL"
        ),
        'typeDefault' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['typeDefault'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options_callback'        => [\con4gis\ReservationBundle\Classes\Callbacks\ReservationType::class,'getAllTypes'],
            'eval'                    => array('mandatory'=>false, 'multiple'=>false, 'chosen'=>true, 'includeBlankOption' => true),
            'sql'                     => "int(5) unsigned NULL default 0"
        ),
        'typeHide' => array
        (   'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['typeHide'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'eval'              => array('tl_class'=>'long'),
            'sql'               => "int(1) unsigned NULL default 0"
        ),
        'typeWithEmptyOption' => array
        (   'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['typeWithEmptyOption'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'eval'              => array('tl_class'=>'long'),
            'sql'               => "int(1) unsigned NULL default 0"
        ),
       'withCapacity' => array
        (   'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['withCapacity'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'sql'               => "int(1) unsigned NULL default 0"
        ),
        'showFreeSeats' => array
        (   'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['showFreeSeats'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'sql'               => "int(1) unsigned NULL default 0"
        ),
        'showEndTime' => array
        (   'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['showEndTime'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'sql'               => "int(1) unsigned NULL default 0"
        ),
        'showPrices' => array
        (   'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['showPrices'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'sql'               => "int(1) unsigned NULL default 0"
        ),
        'showPricesWithTaxes' => array
        (   'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['showPricesWithTaxes'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'sql'               => "int(1) unsigned NULL default 0"
        ),
        'showDateTime' => array
        (   'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['showDateTime'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'sql'               => "int(1) unsigned NULL default 0"
        ),
        'specialParticipantMechanism' => array
        (   'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['specialParticipantMechanism'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'sql'               => "int(1) unsigned NULL default 0"
        ),
        'hideParticipantsEmail' => array
        (   'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['hideParticipantsEmail'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'sql'               => "int(1) unsigned NULL default 0"
        ),
        'hideReservationKey' => array
        (   'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['hideReservationKey'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'sql'               => "int(1) unsigned NULL default 0"
        ),
        'objectHide' => array
        (   'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['objectHide'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'sql'               => "int(1) unsigned NULL default 0"
        ),
        'showMemberData' => array
        (   'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['showMemberData'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'sql'               => "int(1) unsigned NULL default 0"
        ),
        'showDetails' => array
        (   'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['showDetails'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'sql'               => "int(1) unsigned NULL default 0"
        ),
        'removeBookedDays' => array
        (   'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['removeBookedDays'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'sql'               => "int(1) unsigned NULL default 0"
        ),
        'showArrivalAndDeparture' => array
        (   'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['showArrivalAndDeparture'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'sql'               => "int(1) unsigned NULL default 0"
        ),
        'showInlineDatepicker' => array
        (   'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['showInlineDatepicker'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'sql'               => "int(1) unsigned NULL default 0"
        ),
        'emptyOptionLabel' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['emptyOptionLabel'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),
//        'additionalDuration' => array
//        (
//            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['additionalDuration'],
//            'exclude'                 => true,
//            'inputType'               => 'text',
//            'eval'                    => array('maxlength'=>3, 'multiple' => false,'mandatory'=>false),
//            'sql'                     => "int(3) unsigned NULL default 0"
//        ),
        'fieldSelection' => array
        (
            'label'			=> &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['fieldSelection'],
            'exclude' 		=> true,
            'inputType'     => 'multiColumnWizard',
            'eval' 			=> array
            (
                'columnFields' => array
                (
                    'additionaldatas' => array
                    (
                        'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['additionaldatas'],
                        'exclude'                 => true,
                        'default'                 =>'',
                        'inputType'               => 'select',
                        'options_callback'        => array('tl_c4g_reservation_settings','getOptional'),
                        'eval'                    => array('multiple' => false,'mandatory'=>false,'includeBlankOption'=>true,'chosen' => true, 'style'=>'width: 100%')
                    ),
                    'individualLabel' => array
                    (
                        'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['individualLabel'],
                        'exclude'                 => true,
                        'default'                 => '',
                        'inputType'               => 'text',
                        'eval'                    => array('multiple' => false,'mandatory'=>false, 'style'=>'width: 100%')
                    ),
                    'initialValue' => array
                    (
                        'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['initialValue'],
                        'exclude'                 => true,
                        'default'                 => '',
                        'inputType'               => 'text',
                        'eval'                    => array('multiple' => false,'mandatory'=>false, 'style'=>'width: 100%')
                    ),
                    'binding' => array
                    (
                        'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['binding'],
                        'exclude'                 => true,
                        'inputType'               => 'checkbox',
                        'eval'                    => array('multiple' => false,'mandatory'=>false,'alwaysSave'=>true, 'style'=>'width: 33%')
                    )
                ),
            ),
            'sql' => "blob NULL"
        ),
        'notification_type' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['notification_type'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'foreignKey'              => 'tl_nc_notification.title',
            'eval'                    => array('multiple' => true),
            'sql'                     => "varchar(100) NOT NULL default ''"
        ),
        'reservationButtonCaption' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['reservationButtonCaption'],
            'exclude'           => true,
            'sorting'           => true,
            'search'            => true,
            'inputType'         => 'text',
            'eval'              => array('mandatory' => false, 'tl_class' => 'long', 'maxlength' => 254),
            'sql'               => "varchar(100) NOT NULL default ''"
        ),
        'reservation_redirect_site' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['redirect_site'],
            'exclude'                 => true,
            'inputType'               => 'pageTree',
            'foreignKey'              => 'tl_page.title',
            'eval'                    => array('mandatory'=>true, 'fieldType'=>'radio'),
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'relation'                => array('type'=>'hasOne', 'load'=>'eager')
        ),
        'speaker_redirect_site' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['speaker_redirect_site'],
            'exclude'                 => true,
            'inputType'               => 'pageTree',
            'foreignKey'              => 'tl_page.title',
            'eval'                    => array('mandatory'=>false, 'fieldType'=>'radio'),
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'relation'                => array('type'=>'hasOne', 'load'=>'eager')
        ),
        'location_redirect_site' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['location_redirect_site'],
            'exclude'                 => true,
            'inputType'               => 'pageTree',
            'foreignKey'              => 'tl_page.title',
            'eval'                    => array('mandatory'=>false, 'fieldType'=>'radio'),
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'relation'                => array('type'=>'hasOne', 'load'=>'eager')
        ),
        'privacy_policy_text' =>  array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['privacy_policy_text'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'textarea',
            'default'                 => '',
            'eval'                    => array('mandatory'=>false, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'long'),
            'sql'                     => "text NULL"
        ),
        'privacy_policy_site' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['privacy_policy_site'],
            'exclude'                 => true,
            'inputType'               => 'pageTree',
            'foreignKey'              => 'tl_page.title',
            'eval'                    => array('mandatory'=>false, 'fieldType'=>'radio'),
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'relation'                => array('type'=>'hasOne', 'load'=>'eager')
        ),
        'postals' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_settings']['postals'],
            'default' => '',
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => false
            ],
            'sql' => 'TEXT NULL'
        ]
    )
);


/**
 * Class tl_c4G_reservation_settings
 */
class tl_c4g_reservation_settings extends Backend
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

    public function getOptional($dc)
    {
        \Contao\System::loadLanguageFile('tl_c4g_reservation');
        $columnsFormatted=[];
        $columnsFormatted['organisation'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['organisation'][0];
        $columnsFormatted['salutation'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['salutation'][0];
        $columnsFormatted['title'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['title'][0];
        $columnsFormatted['firstname'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['firstname'][0];
        $columnsFormatted['lastname'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['lastname'][0];
        $columnsFormatted['email'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['email'][0];
        $columnsFormatted['phone'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['phone'][0];
        $columnsFormatted['address'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['address'][0];
        $columnsFormatted['postal'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['postal'][0];
        $columnsFormatted['city'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['city'][0];
        $columnsFormatted['dateOfBirth'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['dateOfBirth'][0];
        $columnsFormatted['comment'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['comment'][0];
        $columnsFormatted['participants'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['participants'];
        $columnsFormatted['organisation2'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['organisation2'][0];
        $columnsFormatted['salutation2'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['salutation2'][0];
        $columnsFormatted['title2'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['title2'][0];
        $columnsFormatted['firstname2'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['firstname2'][0];
        $columnsFormatted['lastname2'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['lastname2'][0];
        $columnsFormatted['email2'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['email2'][0];
        $columnsFormatted['phone2'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['phone2'][0];
        $columnsFormatted['address2'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['address2'][0];
        $columnsFormatted['postal2'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['postal2'][0];
        $columnsFormatted['city2'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['city2'][0];
        $columnsFormatted['additional1'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['additional1'][0];
        $columnsFormatted['additional2'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['additional2'][0];
        $columnsFormatted['additional3'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['additional3'][0];
        $columnsFormatted['additionalHeadline'] = $GLOBALS['TL_LANG']['tl_c4g_reservation']['additionalHeadline'];

        return $columnsFormatted;

    }

}