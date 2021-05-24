<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 8
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2021, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

/**
 * Table tl_module
 */

use con4gis\CoreBundle\Classes\Helper\ArrayHelper;
use Contao\Image;
use Contao\StringUtil;

$GLOBALS['TL_DCA']['tl_c4g_reservation_event'] = array
(
    //config
    'config' => array
    (
        'dataContainer'     => 'Table',
        'enableVersioning'  => true,
        'ptable'            => 'tl_calendar_events',
        'onload_callback'   => [['tl_c4g_reservation_event', 'setParent']],
        'doNotCopyRecords'  => true,
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
                'href'                => 'do=calendar&table=tl_calendar_events&id='.$this->Input->get('pid'),
                'icon'                => 'back.svg',
                'label'               => &$GLOBALS['TL_LANG']['MSC']['backBT'],
            ]
        ),

        'operations' => array
        (
            'edit' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_event']['edit'],
                'href'          => 'act=edit',
                'icon'          => 'edit.gif',
            ),
            'copy' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_event']['copy'],
                'href'          => 'act=copy',
                'icon'          => 'copy.gif',
            ),
            'delete' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_event']['delete'],
                'href'          => 'act=delete',
                'icon'          => 'delete.gif',
                'attributes'    => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false;Backend.getScrollOffset()"',
            ),
            'show' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_event']['show'],
                'href'          => 'act=show',
                'icon'          => 'show.gif',
            )
        )
    ),

    //Palettes
    'palettes' => array
    (
        'default'   =>  '{event_legend}, pid, number, location, speaker, topic, targetAudience; {reservation_legend}, reservationType, minParticipants, maxParticipants;',
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
            'options_callback'  => ['tl_c4g_reservation_event', 'getActEvent'],
            'eval'              => array('mandatory' => false, 'disabled' => true, 'tl_class' => 'long clr'),
            'sql'               => "int(10) unsigned NOT NULL default 0"
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
            'save_callback'     => array(array('tl_c4g_reservation_event','generateUuid')),
            'sql'               => "varchar(128) COLLATE utf8_bin NOT NULL default ''"
        ),

        'number' => array(
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_event']['number'],
            'exclude'           => true,
            'default'           => '',
            'sorting'           => true,
            'flag'              => 1,
            'search'            => true,
            'inputType'         => 'text',
            'eval'              => array('mandatory'=>false, 'tl_class'=>'w50 clr'),
            'sql'               => "varchar(128) NOT NULL default ''"
        ),

        'location'  => array
        (
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_event']['location'],
            'exclude'           => true,
            'default'           => 0,
            'inputType'         => 'select',
            'foreignKey'        => 'tl_c4g_reservation_location.name',
            'eval'              => array('mandatory' => false, 'tl_class' => 'long clr','includeBlankOption'=>true),
            'sql'               => "int(10) unsigned NOT NULL default 0",
            'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),
        ),

        'reservationType' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['reservationType'],
            'exclude'           => true,
            'inputType'         => 'select',
            'options_callback'  => ['tl_c4g_reservation_event', 'getReservationTypes'],
            'eval'              => ['mandatory' => true, 'tl_class' => 'long clr'],
            'sql'               => "int(10) unsigned NOT NULL default 0"
        ),

        'minParticipants' => array(
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_event']['minParticipants'],
            'exclude'           => true,
            'sorting'           => false,
            'flag'              => 1,
            'search'            => false,
            'inputType'         => 'text',
            'eval'              => array('mandatory'=>false, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'               => "smallint(3) NOT NULL default 1"
        ),

        'maxParticipants' => array(
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_event']['maxParticipants'],
            'exclude'           => true,
            'sorting'           => false,
            'flag'              => 1,
            'search'            => false,
            'inputType'         => 'text',
            'eval'              => array('mandatory'=>false, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'               => "smallint(3) NOT NULL default 0"
        ),

        'speaker' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['speaker'],
            'exclude'           => true,
            'inputType'         => 'checkbox',
            'options_callback'  => ['tl_c4g_reservation_event', 'getSpeakerName'],
            'eval'              => array('mandatory' => false, 'tl_class' => 'long clr', 'multiple' => true, 'chosen' => true,'includeBlankOption'=>true),
            'sql'               => "blob NULL"
        ),

        'topic' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['topic'],
            'exclude'           => true,
            'inputType'         => 'checkbox',
            'foreignKey'        => 'tl_c4g_reservation_event_topic.topic',
            'eval'              => array('mandatory' => false, 'tl_class' => 'long clr', 'multiple' => true, 'chosen' => true,'includeBlankOption'=>true),
            'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),
            'sql'               => "blob NULL"
        ),

        'targetAudience' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['targetAudience'],
            'exclude'           => true,
            'inputType'         => 'checkbox',
            'foreignKey'        => 'tl_c4g_reservation_event_audience.targetAudience',
            'eval'              => array('mandatory' => false, 'tl_class' => 'long clr', 'multiple' => true, 'chosen' => true,'includeBlankOption'=>true),
            'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),
            'sql'               => "blob NULL"
        ),

        'state' => array(
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_event']['state'],
            'exclude'           => true,
            'filter'            => true,
            'options'           => [0,1,2,3], //none, green, orange, red
            'inputType'         => 'select',
            'eval'              => array('tl_class'=>'w50', 'feEditable'=>true, 'feViewable'=>true),
            'sql'               => "char(1) NOT NULL default '0'"
        )
    )
);


/**
 * Class tl_c4g_reservation
 */
class tl_c4g_reservation_event extends Backend
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

    /**
     * @param \Contao\DataContainer $dc
     */
    public function setParent(Contao\DataContainer $dc)
    {
        \Contao\Message::addInfo($GLOBALS['TL_LANG']['tl_c4g_reservation_event']['infoEvent']);

        $pid = Input::get('pid');

        if (!$pid) {
            return;
        }

        $dc->pid = $pid;
        $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['pid']['default'] = $pid;

        return $dc;
    }

    /**
     * Return all themes as array
     * @return array
     */
    public function getActEvent(DataContainer $dc)
    {
        $return = [];

        $events = $this->Database->prepare("SELECT id,title FROM tl_calendar_events ORDER BY title")->execute();

        while ($events->next()) {
            $return[$events->id] = $events->title;
        }

        return $return;
    }

    /**
     * Return all event types as array
     * @return array
     */
    public function getReservationTypes(DataContainer $dc)
    {
        $return = [];

        $objects = $this->Database->prepare("SELECT id,caption FROM tl_c4g_reservation_type WHERE reservationObjectType = 2 ORDER BY caption")
            ->execute();

        while ($objects->next()) {
            $return[$objects->id] = $objects->caption;
        }

        return $return;
    }

    /**
     * Return all speaker as array
     * @return array
     */
    public function getSpeakerName(DataContainer $dc)
    {
        $return = [];

        $objects = $this->Database->prepare("SELECT id,title,firstname,lastname FROM tl_c4g_reservation_event_speaker ORDER BY lastname")
            ->execute();

        while ($objects->next()) {
            $name = '';
            if ($objects->title) {
                $name = $objects->lastname.','.$objects->firstname.','.$objects->title;
            } else {
                $name = $objects->lastname.','.$objects->firstname;
            }

            $return[$objects->id] = $name;
        }

        return $return;
    }
}
