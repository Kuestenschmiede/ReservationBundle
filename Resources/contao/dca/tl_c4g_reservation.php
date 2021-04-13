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

/**
 * Table tl_module
 */

use con4gis\CoreBundle\Classes\Helper\InputHelper;

$GLOBALS['TL_DCA']['tl_c4g_reservation'] = array
(
    //config
    'config' => array
    (
        'dataContainer'      => 'Table',
        'enableVersioning'   => 'true',
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
            'fields'            => ['id','beginDate','lastname'],
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
                'label'         => $GLOBALS['TL_LANG']['MSC']['all'],
                'href'          => 'act=select',
                'class'         => 'header_edit_all',
                'attributes'    => 'onclick="Backend.getScrollOffSet()" accesskey="e"'
            ),
            'back' => [
                'class'               => 'header_back',
                'href'                => Input::get('pid') ? 'do=calendar&table=tl_calendar_events&id='.Input::get('pid') : 'do=calendar&table=tl_calendar_events&id='.Input::get('id'),
                'icon'                => 'back.svg',
                'label'               => &$GLOBALS['TL_LANG']['MSC']['backBT'],
            ]
        ),

        'operations' => array
        (
            'edit' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation']['edit'],
                'href'          => 'act=edit',
                'icon'          => 'edit.gif',
            ),
            'copy' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation']['copy'],
                'href'          => 'act=copy',
                'icon'          => 'copy.gif',
            ),
            'delete' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation']['delete'],
                'href'          => 'act=delete',
                'icon'          => 'delete.gif',
                'attributes'    => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false;Backend.getScrollOffset()"',
            ),
            'show' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation']['show'],
                'href'          => 'act=show',
                'icon'          => 'show.gif',
            ),
            'participants' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['participants'],
                'href'                => 'table=tl_c4g_reservation_participants',
                'icon'                => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation_participants.svg',
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
        '__selector__' => ['reservationObjectType'],
        'default'   =>  '{reservation_legend}, reservation_type, included_params, additional_params, desiredCapacity, beginDate, endDate, beginTime, endTime, reservationObjectType, reservation_id, confirmed, cancellation; {person_legend}, organisation,salutation, lastname, firstname, email, phone, address, postal, city; {person2_legend}, organisation2, salutation2, title2, lastname2, firstname2, email2, phone2, address2, postal2, city2; {comment_legend}, comment,internal_comment, agreed, member_id;',
    ),

    // Subpalettes
    'subpalettes' =>
    [
        'reservationObjectType_1' => 'reservation_object, duration',
        'reservationObjectType_2' => 'reservation_object',
    ],
//Fields
    'fields' => array
    (
        'id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['id'],
            'sql'               => "int(10) unsigned NOT NULL auto_increment",
            'sorting'           => true,
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
            'search'            => false,
            'eval'              => array('doNotCopy'=>true, 'maxlength'=>128),
            'save_callback'     => array(array('tl_c4g_reservation','generateUuid')),
            'sql'               => "varchar(128) COLLATE utf8_bin NOT NULL default ''"
        ),

        'member_id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['memberId'],
            'default'           => 0,
            'inputType'         => 'select',
            'options_callback'  => array('tl_c4g_reservation_type', 'loadMemberOptions'),
            'eval'              => array('mandatory'=>false, 'disabled' => true),
            'filter'            => true,
            'sql'               => "int(10) unsigned NOT NULL default 0"
        ),

        'reservation_type' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_type'],
            'inputType'         => 'select',
            'foreignKey'        => 'tl_c4g_reservation_type.caption',
            'eval'              => array('mandatory' => true, 'tl_class' => 'long'),
            'sql'               => "int(10) unsigned NOT NULL default 0",
            'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),
        ),

        'included_params' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_included_option'],
            'inputType'         => 'select',
            'foreignKey'        => 'tl_c4g_reservation_params.caption',
            'eval'              => array('chosen'=>true,'mandatory'=>false,'multiple'=>true, 'disabled' => true, 'tl_class'=>'long clr','alwaysSave'=> true),
            'sql'               => "blob NULL",
        ),

        'additional_params' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_additional_option'],
            'inputType'         => 'select',
            'foreignKey'        => 'tl_c4g_reservation_params.caption',
            'eval'              => array('chosen'=>true,'mandatory'=>false,'multiple'=>true, 'tl_class'=>'long clr','alwaysSave'=> true),
            'sql'               => "blob NULL",

        ),

        'desiredCapacity' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['desiredCapacity'],
            'exclude'                 => false,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>3, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'w50'),
            'sql'                     => "int(3) unsigned NOT NULL default 1"
        ),

        'duration' => array
        (
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation']['duration'],
            'inputType'         => 'text',
            'default'           => '1',
            'eval'              => array('rgxp'=>'digit', 'mandatory'=>false, 'tl_class'=>'w50'),
            'sql'               => "smallint(5) unsigned NOT NULL default 1"
        ),

        'periodType' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['periodType'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'select',
            'options'                 => array('minute','hour','openingHours','md'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation'],
            'eval'                    => array('tl_class'=>'w50','unique' =>true,'feViewable'=>true, 'mandatory'=>true),
            'sql'                     => "char(25) NOT NULL default ''"
        ),

         'beginDate' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['beginDate'],
            'default'                 => time(),
            'filter'                  => true,
            'sorting'                 => true,
            'search'                  => false,
            'exclude'                 => true,
            'inputType'               => 'text',
            'flag'                    => 6,
            'eval'                    => array('rgxp'=>'date', 'mandatory'=>true, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard clr'),
            'sql'                     => "int(10) unsigned NULL"
        ),

        'endDate' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['endDate'],
            'default'                 => time(),
            'filter'                  => true,
            'sorting'                 => false,
            'search'                  => false,
            'exclude'                 => true,
            'inputType'               => 'text',
            'flag'                    => 6,
            'eval'                    => array('rgxp'=>'date', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                     => "int(10) unsigned unsigned NULL"
        ),

        'beginTime' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['beginTime'],
            'default'                 => time(),
            'exclude'                 => true,
            'filter'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'tl_class'=>'w50 clr','datepicker'=>true),
            'sql'                     => "int(10) unsigned NOT NULL default 0"
        ),

        'endTime' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['endTime'],
            'exclude'                 => true,
            'filter'                  => false,
            'default'                 => 0,//time()+3600,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'tl_class'=>'w50','date','datepicker'=>true),
            'sql'                     => "int(10) unsigned NULL"
        ),

        'reservationObjectType' =>
        [
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['reservationObjectType'],
            'exclude'                 => true,
            'inputType'               => 'radio',
            'default'                 => '1',
            'options'                 => ['1','2'],
            'reference'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['referencesObjectType'],
            'eval'                    => ['tl_class'=>'clr long','submitOnChange' => true],
            'sql'                     => "varchar(255) NOT NULL default '1'"
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
            'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'long clr'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),

        'salutation' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['salutation'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'default'                 => 'various',
            'reference'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation'],
            'options'                 => array('man','woman','various'),
            'eval'                    => array('tl_class'=>'w50 clr','feViewable'=>true, 'mandatory'=>false),
            'sql'                     => "char(25) NOT NULL default ''"

        ),

        'title' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['title'],
            'exclude'                 => true,
            'search'                  => true,
            'sorting'                 => true,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'clr'),
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
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'clr'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),

        'firstname' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['firstname'],
            'exclude'                 => true,
            'search'                  => false,
            'sorting'                 => false,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'long'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),

        'email' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['email'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'rgxp'=>'email', 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'long'),
            'sql'                     => "varchar(255) NOT NULL default ''"
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
            'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => "varchar(255) NOT NULL default ''"
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
            'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => "varchar(255) NOT NULL default ''"

        ),

        'organisation2' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['organisation2'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'long clr'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),

        'salutation2' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['salutation2'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'default'                 => 'various',
            'reference'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation'],
            'options'                 => array('man','woman','various'),
            'eval'                    => array('tl_class'=>'w50 clr','feViewable'=>true, 'mandatory'=>false),
            'sql'                     => "char(25) NOT NULL default ''"

        ),

        'title2' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['title2'],
            'exclude'                 => true,
            'search'                  => true,
            'sorting'                 => true,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'clr'),
            'sql'                     => "varchar(50) NOT NULL default ''"
        ),

        'lastname2' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['lastname2'],
            'exclude'                 => true,
            'search'                  => true,
            'sorting'                 => true,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'clr'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),

        'firstname2' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['firstname2'],
            'exclude'                 => true,
            'search'                  => false,
            'sorting'                 => false,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'long'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),

        'email2' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['email2'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'rgxp'=>'email', 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'long'),
            'sql'                     => "varchar(255) NOT NULL default ''"
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
            'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => "varchar(255) NOT NULL default ''"
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
            'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => "varchar(255) NOT NULL default ''"

        ),

        'reservation_id' => array
        (
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation']['reservation_id'],
            'flag'              => 1,
            'sorting'           => false,
            'search'            => true,
            'inputType'         => 'text',
            'eval'              => array('mandatory' => false, 'maxlength'=>255, 'tl_class' => 'long'),
            'sql'               => "varchar(255) NOT NULL default ''"
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

        'internal_comment' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['internal_comment'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'textarea',
            'default'                 => '',
            'eval'                    => array('mandatory'=>false, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'long'),
            'sql'                     => "text NULL"
        ),

        'cancellation' => array(
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation']['cancellation'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'eval'              => array('tl_class'=>'w50'),
            'sql'               => "char(1) NOT NULL default ''"
        ),

        'agreed' => array(
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation']['agreed'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'eval'              => array('tl_class'=>'w50', 'feEditable'=>true, 'feViewable'=>true, 'mandatory'=>false, 'disabled'=>true),
            'sql'               => "char(1) NOT NULL default ''"

        ),

        'confirmed' => array(
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation']['confirmed'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'eval'              => array('tl_class'=>'w50', 'feEditable'=>true, 'feViewable'=>true,),
            'sql'               => "char(1) NOT NULL default ''"
        ),

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

    public function generateUuid($varValue, DataContainer $dc)
    {
        if ($varValue == '') {
            return \c4g\projects\C4GBrickCommon::getGUID();
        }
        else {
            return $varValue;
        }
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
        $this->Database->prepare("UPDATE tl_c4g_reservation SET tstamp=". time() .", cancellation='" . ($blnCancellation ? '0' : '1') . "' WHERE id=?")
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
            $reservation_object = \con4gis\ReservationBundle\Resources\contao\models\C4gReservationObjectModel::findByPk($object_id);
            if ($reservation_object) {
                $object = $reservation_object->caption;
            }
        }


        $arrRow['reservation_object'] = $object;

        if ($arrRow['beginDate']) {
            $arrRow['beginDate'] = $arrRow['beginDate'] && $arrRow['beginTime'] ? date($GLOBALS['TL_CONFIG']['dateFormat'],$arrRow['beginDate']). ' ' .date($GLOBALS['TL_CONFIG']['timeFormat'],$arrRow['beginTime']) : date($GLOBALS['TL_CONFIG']['dateFormat'],$arrRow['beginDate']);
        } else {
            $arrRow['beginDate'] = '';
        }

        if ($arrRow['endDate']) {
            $arrRow['endDate'] = $arrRow['endDate'] && $arrRow['endTime'] ? date($GLOBALS['TL_CONFIG']['dateFormat'],$arrRow['endDate']). ' ' .date($GLOBALS['TL_CONFIG']['timeFormat'],$arrRow['endTime']) : date($GLOBALS['TL_CONFIG']['dateFormat'],$arrRow['endDate']);
        } else {
            $arrRow['endDate'] = '';
        }

        $type = \con4gis\ReservationBundle\Resources\contao\models\C4gReservationTypeModel::findByPk($arrRow['reservation_type']);
        if ($type) {
            $arrRow['reservation_type'] = $type->caption;
        }

        $result = [
            $arrRow['id'],
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
     * Return all themes as array
     * @return array
     */
    public function getActObjects(DataContainer $dc)
    {
        $return = [];

        if ($dc && ($dc->activeRecord) && ($dc->activeRecord->reservationObjectType === '2')) {
            $events = $this->Database->prepare("SELECT id,title FROM tl_calendar_events")
                ->execute();

            while ($events->next()) {
                $return[$events->id] = $events->title;
            }
        } else if ($dc && ($dc->activeRecord) && ($dc->activeRecord->reservationObjectType === '1')) {
            $dc->reservationObjectType = '1';
            $objects = $this->Database->prepare("SELECT id,caption FROM tl_c4g_reservation_object")
                ->execute();

            while ($objects->next()) {
                $return[$objects->id] = $objects->caption;
            }
        } else {
            //ToDo all elemente for filter -> duplicated ids
            $objects = $this->Database->prepare("SELECT id,caption FROM tl_c4g_reservation_object")
                ->execute();

            while ($objects->next()) {
                $return[$objects->id] = $objects->caption;
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
            ['id','beginDate','endTime','desiredCapacity','reservation_type:tl_c4g_reservation_type.caption','lastname','firstname','reservation_object'];

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
    }

    /**
     * @param $dc
     * @return array
     */
    public function loadMemberOptions($dc) {
        $options = [];
        $options[$dc->activeRecord->id] = '';

        $stmt = $this->Database->prepare("SELECT id, firstname, lastname FROM tl_member");
        $result = $stmt->execute()->fetchAllAssoc();

        foreach ($result as $row) {
            $options[$row['id']] = $row['lastname'] . ', ' . $row['firstname'];
        }
        return $options;
    }
}
