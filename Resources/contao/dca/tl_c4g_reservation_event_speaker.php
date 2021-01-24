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

$GLOBALS['TL_DCA']['tl_c4g_reservation_event_speaker'] = array
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
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['edit'],
                'href'          => 'act=edit',
                'icon'          => 'edit.gif',
            ),
            'copy' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['copy'],
                'href'          => 'act=copy',
                'icon'          => 'copy.gif',
            ),
            'delete' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['delete'],
                'href'          => 'act=delete',
                'icon'          => 'delete.gif',
                'attributes'    => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false;Backend.getScrollOffset()"',
            ),
            'show' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['show'],
                'href'          => 'act=show',
                'icon'          => 'show.gif',
            )
        )
    ),

    //Palettes
    'palettes' => array
    (
        'default'   =>  '{speaker_legend},title, firstname, lastname, email, phone, address, postal, city, website, vita, photo, speakerForwarding;'
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
            'sql'                     => "varchar(255) NOT NULL default ''"

        ),

        'firstname' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['firstname'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => true,
            'sorting'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'w50 clr'),
            'sql'                     => "varchar(255) NOT NULL default ''"

        ),

          'lastname' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['lastname'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => true,
            'sorting'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"

        ),
         'email' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['email'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'rgxp'=>'email', 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'w50 clr'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),

        'phone' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['phone'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>64, 'rgxp'=>'phone', 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'w50'),
            'sql'                     => "varchar(64) NOT NULL default ''"
        ),

        'address' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['address'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long clr'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),

        'postal' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['postal'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>32, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50 clr'),
            'sql'                     => "varchar(32) NOT NULL default ''"

        ),

        'city' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['city'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"

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
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_event_speaker']['photo'],
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
            'eval'                    => array('rgxp'=>'url', 'decodeEntities'=>true, 'maxlength'=>255, 'fieldType'=>'radio', 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'forum', 'memberLink' => true, 'tl_class'=>'clr w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
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
        )

    )
);

class tl_c4g_reservation_event_speaker extends Backend
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
}