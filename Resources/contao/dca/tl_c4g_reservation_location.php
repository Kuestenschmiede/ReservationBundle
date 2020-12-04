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

use con4gis\CoreBundle\Classes\C4GVersionProvider;

$GLOBALS['TL_DCA']['tl_c4g_reservation_location'] = array
(
    //config
    'config' => array
    (
        'dataContainer'     => 'Table',
        'enableVersioning'  => 'true',
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
            'fields'            => array('name'),
            'panelLayout'       => 'filter;sort,search,limit'
        ),

        'label' => array
        (
            'fields'            => array('name'),
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
            )
        ),

        'operations' => array
        (
            'edit' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_location']['edit'],
                'href'          => 'act=edit',
                'icon'          => 'edit.gif',
            ),
            'copy' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_location']['copy'],
                'href'          => 'act=copy',
                'icon'          => 'copy.gif',
            ),
            'delete' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_location']['delete'],
                'href'          => 'act=delete',
                'icon'          => 'delete.gif',
                'attributes'    => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false;Backend.getScrollOffset()"',
            ),
            'show' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_location']['show'],
                'href'          => 'act=show',
                'icon'          => 'show.gif',
            )
        )
    ),

    //Palettes
    'palettes' => array
    (
        '__selector__' => ['ics'],
        'default'   =>  '{location_legend}, name, locgeox, locgeoy;{contact_legend},contact_name,contact_phone,contact_email,contact_street,contact_postal,contact_city,ics;'
    ),

    'subpalettes' => array
    (
        'ics' => 'icsAlert, icsPath',
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
            'sql'               => "int(10) unsigned NOT NULL default '0'"
        ),

        'name' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_location']['name'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'long'),
            'sql'                     => "varchar(255) NOT NULL default ''"

        ),

        'locgeox' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['locgeox'],
            'exclude'                 => true,
            'inputType'               => 'c4g_text',
            'default'                 => '',
            'eval'                    => array('tl_class'=>'w50 wizard', 'require_input'=>true ),
            'save_callback'           => array(array('tl_c4g_reservation_location','setCenterLon')),
            'wizard'                  => [['con4gis\MapsBundle\Classes\GeoPicker', 'getPickerLink']],
            'sql'                     =>"varchar(20) NOT NULL default ''"
        ),

        'locgeoy' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['locgeoy'],
            'exclude'                 => true,
            'inputType'               => 'c4g_text',
            'default'                 => '',
            'eval'                    => array('tl_class'=>'w50 wizard', 'require_input'=>true ),
            'save_callback'           => array(array('tl_c4g_reservation_location','setCenterLat')),
            'wizard'                  => [['con4gis\MapsBundle\Classes\GeoPicker', 'getPickerLink']],
            'sql'                     =>"varchar(20) NOT NULL default ''"
        ),

        'contact_name' => array
        (
            'label'             =>$GLOBALS['TL_LANG']['tl_c4g_reservation_location']['contact_name'],
            'default'           => '',
            'exclude'           => true,
            'inputType'         => 'text',
            'eval'              =>array('mandatory'=>false, 'tl_class'=>'w50 clr'),
            'sql'               =>"varchar(255) NOT NULL default ''"
        ),

        'contact_phone' => array
        (
            'label'             =>$GLOBALS['TL_LANG']['tl_c4g_reservation_location']['contact_phone'],
            'default'           => '',
            'exclude'           => true,
            'inputType'         => 'text',
            'eval'              =>array('rgxp'=>'digit','mandatory'=>false, 'tl_class'=>'long clr '),
            'sql'               =>"varchar(255) NOT NULL default ''"
        ),
        'contact_email' => array
        (
            'label'             =>$GLOBALS['TL_LANG']['tl_c4g_reservation_location']['contact_email'],
            'default'           => '',
            'exclude'           => true,
            'inputType'         => 'text',
            'eval'              =>array('rgxp'=>'email','mandatory'=>false, 'tl_class'=>'long clr'),
            'sql'               =>"varchar(255) NOT NULL default ''"
        ),
        'contact_street' => array
        (
            'label'             =>$GLOBALS['TL_LANG']['tl_c4g_reservation_location']['contact_street'],
            'default'           => '',
            'exclude'           => true,
            'inputType'         => 'text',
            'eval'              =>array('mandatory'=>false, 'tl_class'=>'long clr'),
            'sql'               =>"varchar(255) NOT NULL default ''"
        ),
        'contact_postal' => array
        (
            'label'             =>$GLOBALS['TL_LANG']['tl_c4g_reservation_location']['contact_postal'],
            'default'           => '',
            'exclude'           => true,
            'inputType'         => 'text',
            'eval'              =>array('rgxp'=>'digit','mandatory'=>false, 'tl_class'=>'w50'),
            'sql'               =>"varchar(255) NOT NULL default ''"
        ),
        'contact_city' => array
        (
            'label'             =>$GLOBALS['TL_LANG']['tl_c4g_reservation_location']['contact_city'],
            'default'           => '',
            'exclude'           => true,
            'inputType'         => 'text',
            'eval'              =>array('mandatory'=>false, 'tl_class'=>'w50'),
            'sql'               =>"varchar(255) NOT NULL default ''"
        ),
        'ics' => array
        (
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_location']['ics'],
            'inputType'         => 'checkbox',
            'eval'              => array('mandatory'=>false,'tl_class'=>'long clr','alwaysSave'=> true,),
            'sql'               => "blob NULL "
        ),
        'icsAlert' => array
        (
            'label'             =>$GLOBALS['TL_LANG']['tl_c4g_reservation_location']['icsAlert'],
            'exclude'           => true,
            'inputType'         => 'text',
            'eval'              => array('rgxp'=>'digit', 'maxval'=> '480', 'mandatory'=>false, 'tl_class'=>'w50 clr'),
            'sql'               => "smallint(5) unsigned NOT NULL default 0"
        ),
        'icsPath'  => array
        (
            'label'             =>$GLOBALS['TL_LANG']['tl_c4g_reservation_location']['icsPath'],
            'exclude'           => true,
            'exclude'           => true,
            'default'           => null,
            'inputType'         => 'fileTree',
            'eval'              => array('fieldType' => 'radio', 'tl_class' => 'clr', 'mandatory' => true),
            'sql'               => "blob NULL"
        ),
    )
);


/**
 * Class tl_c4G_reservation_params
 */
class tl_c4g_reservation_location extends Backend
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

    /** Validate Center Lon*/
    public function setCenterLon($varValue, DataContainer $dc)
    {
        if (C4GVersionProvider::isInstalled('con4gis/maps') && !\con4gis\MapsBundle\Classes\Utils::validateLon($varValue)) {
            throw new Exception($GLOBALS['TL_LANG']['tl_c4g_reservation_location']['geox_invalid']);
        }
        return $varValue;
    }

    /** Validate Center Lat*/
    public function setCenterLat($varValue, DataContainer $dc)
    {
        if (C4GVersionProvider::isInstalled('con4gis/maps') && !\con4gis\MapsBundle\Classes\Utils::validateLat($varValue)) {
            throw new Exception($GLOBALS['TL_LANG']['tl_c4g_reservation_location']['geoy_invalid']);
        }
        return $varValue;
    }

}