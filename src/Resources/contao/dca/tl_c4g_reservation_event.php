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
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['edit'],
                'href'          => 'act=edit',
                'icon'          => 'edit.gif',
            ),
            'copy' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['copy'],
                'href'          => 'act=copy',
                'icon'          => 'copy.gif',
            ),
            'delete' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['delete'],
                'href'          => 'act=delete',
                'icon'          => 'delete.gif',
                'attributes'    => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\')) return false;Backend.getScrollOffset()"',
            ),
            'show' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['show'],
                'href'          => 'act=show',
                'icon'          => 'show.gif',
            )
        )
    ),

    //Palettes
    'palettes' => array
    (
        'default'   =>  '{event_legend}, pid, number, location, organizer, speaker, topic, targetAudience; {reservation_legend}, reservationType, minParticipants, maxParticipants, min_reservation_day, price, taxOptions, priceoption, participant_params, participantParamsFieldType; {team_legend}, team;',
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
            'default'           => 0,
            'options_callback'  => ['tl_c4g_reservation_event', 'getActEvent'],
            'eval'              => array('mandatory' => false, 'disabled' => true, 'tl_class' => 'long clr', 'unique' => true, 'doNotCopy' => true, 'includeBlankOption' => true, 'blankOptionLabel' => 'OOPS! ERROR?', 'doNotSaveEmpty' => true),
            'sql'               => "int(10) unsigned NOT NULL default 0"
        ),
        'tstamp' => array
        (
            'sql'               => "int(10) unsigned NOT NULL default 0"
        ),

        'number' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['number'],
            'exclude'           => true,
            'default'           => '',
            'sorting'           => true,
            'flag'              => 1,
            'search'            => true,
            'inputType'         => 'text',
            'eval'              => array('mandatory'=>false, 'tl_class'=>'w50 clr', 'doNotCopy' => true),
            'sql'               => "varchar(128) NOT NULL default ''"
        ),

        'location'  => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['location'],
            'exclude'           => true,
            'default'           => 0,
            'inputType'         => 'select',
            'foreignKey'        => 'tl_c4g_reservation_location.name',
            'eval'              => array('chosen' => true, 'mandatory' => false, 'tl_class' => 'long clr','includeBlankOption'=>true, 'doNotCopy' => true),
            'sql'               => "int(10) unsigned NOT NULL default 0",
            'relation'          => array('type' => 'hasOne', 'load' => 'eager'),
        ),

        'organizer'  => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['organizer'],
            'exclude'           => true,
            'default'           => 0,
            'inputType'         => 'select',
            'foreignKey'        => 'tl_c4g_reservation_location.name',
            'eval'              => array('chosen' => true, 'mandatory' => false, 'tl_class' => 'long clr','includeBlankOption'=>true, 'doNotCopy' => true),
            'sql'               => "int(10) unsigned NOT NULL default 0",
            'relation'          => array('type' => 'hasOne', 'load' => 'eager'),
        ),

        'reservationType' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['reservationType'],
            'exclude'           => true,
            'inputType'         => 'select',
            'options_callback'  => ['tl_c4g_reservation_event', 'getReservationTypes'],
            'eval'              => ['mandatory' => true, 'tl_class' => 'long clr', 'doNotCopy' => true],
            'sql'               => "int(10) unsigned NOT NULL default 0"
        ),

        'minParticipants' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['minParticipants'],
            'exclude'           => true,
            'default'           => 1,
            'sorting'           => false,
            'flag'              => 1,
            'search'            => false,
            'inputType'         => 'text',
            'eval'              => array('mandatory'=>false, 'rgxp'=>'digit', 'minval' => 1, 'tl_class'=>'w50', 'doNotCopy' => true),
            'sql'               => "smallint(3) NOT NULL default 1"
        ),

        'maxParticipants' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['maxParticipants'],
            'exclude'           => true,
            'sorting'           => false,
            'flag'              => 1,
            'search'            => false,
            'inputType'         => 'text',
            'eval'              => array('mandatory'=>false, 'rgxp'=>'digit', 'minval' => 0, 'tl_class'=>'w50', 'doNotCopy' => true),
            'sql'               => "smallint(3) NOT NULL default 0"
        ),

        'min_reservation_day' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object']['min_reservation_day'],
            'exclude'                 => true,
            'sorting'                 => false,
            'flag'                    => 0,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                     => "smallint(3) NOT NULL default 0"
        ),

        'speaker' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['speaker'],
            'exclude'           => true,
            'inputType'         => 'checkbox',
            'options_callback'  => ['tl_c4g_reservation_event', 'getSpeakerName'],
            'eval'              => array('mandatory' => false, 'tl_class' => 'long clr', 'multiple' => true, 'chosen' => true,'includeBlankOption'=>true, 'doNotCopy' => true),
            'sql'               => "blob NULL"
        ),

        'topic' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['topic'],
            'exclude'           => true,
            'inputType'         => 'checkbox',
            'foreignKey'        => 'tl_c4g_reservation_event_topic.topic',
            'eval'              => array('mandatory' => false, 'tl_class' => 'long clr', 'multiple' => true, 'chosen' => true,'includeBlankOption'=>true, 'doNotCopy' => true),
            'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),
            'sql'               => "blob NULL"
        ),

        'targetAudience' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['targetAudience'],
            'exclude'           => true,
            'inputType'         => 'checkbox',
            'foreignKey'        => 'tl_c4g_reservation_event_audience.targetAudience',
            'eval'              => array('mandatory' => false, 'tl_class' => 'long clr', 'multiple' => true, 'chosen' => true,'includeBlankOption'=>true, 'doNotCopy' => true),
            'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),
            'sql'               => "blob NULL"
        ),

        'price' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['price'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'default'                 => '0.00',
            'eval'                    => array('rgxp'=>'digit','mandatory'=>false, 'maxlength'=>10, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'w50 clr'),
            'sql'                     => "double(7,2) unsigned default '0'"
        ),
        'participant_params' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['participant_params'],
            'inputType'         => 'select',
            'default'           => '-',
            'exclude'           => true,
            'foreignKey'        => 'tl_c4g_reservation_params.caption',
            'eval'              => array('chosen'=>true,'mandatory'=>false,'multiple'=>true,'tl_class'=>'w50 clr','alwaysSave'=> true, 'default' => '-'),
            'sql'               => "blob NULL",
        ),
        'participantParamsFieldType' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['participantParamsFieldType'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'default'                 => 'multi',
            'reference'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event'],
            'options'                 => array('multi','radio'),
            'eval'                    => array('tl_class'=>'w50','feViewable'=>true, 'mandatory'=>false),
            'sql'                     => "char(25) NOT NULL default 'multi'"
        ),
        'taxOptions' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['taxOptions'],
            'exclude'                 => true,
            'inputType'               => 'radio',
            'default'                 => 'tNone',
            'options'                 => array( 'tNone', 'tStandard', 'tReduced'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['references'],
            'eval'                    => array('submitOnChange' => true, 'tl_class' => 'long clr', 'fieldType'=>'radio'),
            'sql'                     => "varchar(50) NOT NULL default 'tNone'"
        ),
        'priceoption' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['priceoption'],
            'exclude'                 => true,
            'inputType'               => 'radio',
            'options'                 => array('pReservation','pPerson','pDay','pNight','pNightPerson','pHour','pMin','pAmount'),
            'default'                 => '',
            'reference'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['references'],
            'eval'                    => array('mandatory'=>false, 'tl_class'=>'w50 clr'),
            'sql'                     => "varchar(50) NOT NULL default ''"
        ),

        'state' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['state'],
            'exclude'           => true,
            'filter'            => true,
            'options'           => [0,1,2,3], //none, green, orange, red
            'inputType'         => 'select',
            'eval'              => array('tl_class'=>'w50', 'feEditable'=>true, 'feViewable'=>true, 'doNotCopy' => true),
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

    /**
     * @param \Contao\DataContainer $dc
     */
    public function setParent(Contao\DataContainer $dc)
    {
        \Contao\Message::addInfo($GLOBALS['TL_LANG']['tl_c4g_reservation_event']['infoEvent']);

        $pid = intval(Input::get('pid'));

        if (!$pid) {
            $pid = $dc->activeRecord->pid;
            if (!$pid) {
              return $dc;
            }
        }

        $dc->pid = $pid;
        $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['pid']['default'] = $pid;
        $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['pid']['sql'] = "int(10) unsigned NOT NULL default ".$pid;

        $calendarId = \Database::getInstance()->prepare('SELECT pid FROM tl_calendar_events WHERE id=?')->execute($pid)->fetchAssoc();
        $calendarRow =  $calendarId ? \Database::getInstance()->prepare('SELECT * FROM tl_calendar WHERE id=? AND activateEventReservation="1"')->execute($calendarId['pid'])->fetchAssoc() : false;
        if ($calendarRow) {
            $dc->location = $calendarRow['reservationLocation'];
            $dc->organizer = $calendarRow['reservationOrganizer'];
            $dc->reservationType = $calendarRow['reservationType'];
            $dc->minParticipants = $calendarRow['reservationMinParticipants'];
            $dc->maxParticipants = $calendarRow['reservationMaxParticipants'];
            $dc->speaker = $calendarRow['reservationSpeaker'];
            $dc->topic = $calendarRow['reservationTopic'];
            $dc->targetAudience = $calendarRow['reservationtargetAudience'];
            $dc->price = $calendarRow['reservationPrice'];
//            $dc->taxOptions = $calendarRow['reservationTaxOptions'];
            $dc->priceoption = $calendarRow['reservationPriceOption'];

            $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['location']['default'] = $calendarRow['reservationLocation'];
            $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['organizer']['default'] = $calendarRow['reservationOrganizer'];
            $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['reservationType']['default'] = $calendarRow['reservationType'];
            $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['minParticipants']['default'] = $calendarRow['reservationMinParticipants'];
            $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['maxParticipants']['default'] = $calendarRow['reservationMaxParticipants'];
            $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['speaker']['default'] = $calendarRow['reservationSpeaker'];
            $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['topic']['default'] = $calendarRow['reservationTopic'];
            $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['targetAudience']['default'] = $calendarRow['reservationtargetAudience'];
            $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['price']['default'] = $calendarRow['reservationPrice'];
            $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['priceoption']['default'] = $calendarRow['reservationPriceOption'];
        }

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
            $return[intval($events->id)] = $events->title;
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

        $objects = $this->Database->prepare("SELECT id,caption FROM tl_c4g_reservation_type WHERE `reservationObjectType` = 2 ORDER BY caption")
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
