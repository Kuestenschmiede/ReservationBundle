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

use con4gis\CoreBundle\Resources\contao\models\C4gLogModel;
use con4gis\DataBundle\Classes\Contao\Hooks\ReplaceInsertTags;
use con4gis\ReservationBundle\Classes\Models\C4gReservationTypeModel;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationHandler;
use Contao\Calendar;
use Contao\Config;
use Contao\Controller;
use Contao\Database;
use Contao\Date;
use Contao\Image;
use Contao\StringUtil;

$str = 'tl_calendar_events';

$GLOBALS['TL_DCA'][$str]['config']['ctable'][] = 'tl_c4g_reservation_event';
$GLOBALS['TL_DCA'][$str]['config']['onload_callback'][] = ['tl_c4g_reservation_event_bridge', 'c4gLoadReservationData'];
$GLOBALS['TL_DCA'][$str]['list']['sorting']['child_record_callback'] = ['tl_c4g_reservation_event_bridge', 'loadChildRecord'];

$GLOBALS['TL_DCA'][$str]['list']['operations']['c4gEditEvent'] = [
    'label'               => &$GLOBALS['TL_LANG'][$str]['c4gEditEvent'],
    'icon'                => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation_types.svg',
    'button_callback'     => ['tl_c4g_reservation_event_bridge', 'c4gEditEvent'],
    'exclude'             => true
];
$GLOBALS['TL_DCA'][$str]['list']['operations']['c4gExportReservations'] = [
    'href'                => 'key=runexport',
    'label'               => &$GLOBALS['TL_LANG'][$str]['c4gExportReservations'],
    'icon'                => 'bundles/con4gisexport/images/be-icons/export.svg',
    'button_callback'     => [\con4gis\ReservationBundle\Classes\Callbacks\ReservationEvents::class, 'runExport'],
    'exclude'             => true
];

$GLOBALS['TL_DCA'][$str]['list']['operations']['c4g_participant_list'] = [
    'label'               => &$GLOBALS['TL_LANG'][$str]['c4g_participant_list'],
    'icon'                => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation_audience.svg',
    'button_callback'     => ['tl_c4g_reservation_event_bridge', 'c4gShowAllParticipants'],
    'exclude'             => true
];

$GLOBALS['TL_DCA'][$str]['list']['operations']['c4gEditReservations'] = [
    'label'               => &$GLOBALS['TL_LANG'][$str]['c4gEditReservations'],
    'icon'                => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation.svg',
    'button_callback'     => ['tl_c4g_reservation_event_bridge', 'c4gShowReservations'],
    'exclude'             => true
];

$GLOBALS['TL_DCA'][$str]['fields']['c4g_reservation_number'] = [
    'label'                   => &$GLOBALS['TL_LANG'][$str]['c4g_reservation_number'],
    'default'                 => '',
    'sorting'                 => true,
    'search'                  => true,
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('doNotCopy' => true),
    'sql'                     => "varchar(128) NOT NULL default ''"
];

/**
 * Class tl_c4g_reservation
 */
class tl_c4g_reservation_event_bridge extends tl_calendar_events
{
    private $states = [];

    public function c4gLoadReservationData()
    {
        $noReservationEvents = true;
        $reservationEvents = $this->Database->prepare("SELECT pid, number FROM tl_c4g_reservation_event WHERE `pid`<>0 AND `number`<>''")->execute()->fetchAllAssoc();
        if ($reservationEvents) {
            foreach ($reservationEvents as $reservationEvent) {
                $this->Database->prepare("UPDATE tl_calendar_events SET c4g_reservation_number=? WHERE `id`=?")->execute($reservationEvent['number'], $reservationEvent['pid']);
                $noReservationEvents = false;
            }
        }
        if ($noReservationEvents) {
            $GLOBALS['TL_DCA']['tl_calendar_events']['fields']['c4g_reservation_number']['sorting'] = false;
            $GLOBALS['TL_DCA']['tl_calendar_events']['fields']['c4g_reservation_number']['search'] = false;
        }
    }

    public function loadChildRecord(array $row) {
        \System::loadLanguageFile('fe_c4g_reservation');
        $arrChildRow = \Database::getInstance()->prepare('SELECT * FROM tl_c4g_reservation_event WHERE pid=?')->execute($row['id'])->fetchAssoc();
        $calendarRow =  \Database::getInstance()->prepare('SELECT * FROM tl_calendar WHERE id=? AND activateEventReservation="1"')->execute($row['pid'])->fetchAssoc();

        $span = Calendar::calculateSpan($row['startTime'], $row['endTime']);

        if ($span > 0)
        {
            $date = Date::parse(Config::get(($row['addTime'] ? 'datimFormat' : 'dateFormat')), $row['startTime']) . '' . ' - ' . Date::parse(Config::get(($row['addTime'] ? 'datimFormat' : 'dateFormat')), $row['endTime']) . '';
        }
        elseif ($row['startTime'] == $row['endTime'])
        {
            $date = Date::parse(Config::get('dateFormat'), $row['startTime']) . ($row['addTime'] ? ' ' . Date::parse(Config::get('timeFormat'), $row['startTime']) : '');
        }
        else
        {
            $date = Date::parse(Config::get('dateFormat'), $row['startTime']) . ($row['addTime'] ? ' ' . Date::parse(Config::get('timeFormat'), $row['startTime']) . ' - ' . Date::parse(Config::get('timeFormat'), $row['endTime']) . ' ' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['clock'] : '');
        }
        if ($date) {
            $event = '<div style="clear:both"><div style="float:left;width:150px"><strong>'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['event'].':</strong></div><div>' . $date . '</div></div>';
        }

        //topics
        $topics = '';
        if (($arrChildRow && $arrChildRow['topic']) || ($calendarRow && $calendarRow['reservationTopic'])) {
            $topic = Database::getInstance()->prepare('SELECT * FROM tl_c4g_reservation_event_topic WHERE id IN ('.implode(',',unserialize($arrChildRow['topic'] ?: $calendarRow['reservationTopic'])).')')->execute()->fetchAllAssoc();
            $topicNames = [];
            foreach ($topic as $topicElement) {
                $topicNames[] = $topicElement['topic'];
            }
            if (!empty($topicNames)) {
                $topics = '<div style="clear:both"><div style="float:left;width:150px"><strong>'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['topic'].':</strong></div><div>' . implode(', ',$topicNames) . '</div></div>';
            }
        }

        //speaker
        $speakers = '';
        if (($arrChildRow && $arrChildRow['speaker']) || ($calendarRow && $calendarRow['reservationSpeaker'])) {
            $speaker = Database::getInstance()->prepare('SELECT * FROM tl_c4g_reservation_event_speaker WHERE id IN ('.implode(',',unserialize($arrChildRow['speaker'] ?: $calendarRow['reservationSpeaker'])).')')->execute()->fetchAllAssoc();
            $speakerNames = [];
            foreach ($speaker as $speakerElement) {
                $speakerNames[] = $speakerElement['title'] ? $speakerElement['title'] . ' ' . $speakerElement['firstname'] . ' ' . $speakerElement['lastname'] : $speakerElement['firstname'] .' '.$speakerElement['lastname'];
            }
            if (!empty($speakerNames)) {
                $speakers = '<div style="clear:both"><div style="float:left;width:150px"><strong>'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['speaker'].':</strong></div><div>' . implode(', ',$speakerNames) . '</div></div>';
            }
        }

        //price
        $price = '';
        if (($arrChildRow && $arrChildRow['price']) || ($calendarRow && $calendarRow['reservationPrice'])) {
            $price = '<div style="clear:both"><div style="float:left;width:150px"><strong>'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['price'].':</strong></div><div>'.C4gReservationHandler::formatPrice($arrChildRow['price'] ?: $calendarRow['reservationPrice']) . '</div></div>';
        }

        //location
        $location = '';
        if (($arrChildRow && $arrChildRow['location']) || ($calendarRow && $calendarRow['reservationLocation'])) {
            $locationId = $arrChildRow['location'] ?: $calendarRow['reservationLocation'];
            $locationResult = Database::getInstance()->prepare('SELECT name FROM tl_c4g_reservation_location WHERE id = '.$locationId)->execute()->fetchAssoc();
            $locationName = $locationResult['name'];
            if ($locationName) {
                $location = '<div style="clear:both"><div style="float:left;width:150px"><strong>'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['eventlocation'].':</strong></div><div>' . $locationName . '</div></div>';
            }
        }

        //location
        $organizer = '';
        if (($arrChildRow && $arrChildRow['organizer']) || ($calendarRow && $calendarRow['reservationOrganizer'])) {
            $organizerId = $arrChildRow['organizer'] ?: $calendarRow['reservationOrganizer'];
            $organizerResult = Database::getInstance()->prepare('SELECT name FROM tl_c4g_reservation_location WHERE id = '.$organizerId)->execute()->fetchAssoc();
            $organizerName = $organizerResult['name'];
            if ($organizerName) {
                $organizer = '<div style="clear:both"><div style="float:left;width:150px"><strong>'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['organizer'].':</strong></div><div>' . $organizerName . '</div></div>';
            }
        }

        //participants & state
        $participants = '';
        if (($arrChildRow && $arrChildRow['maxParticipants']) || ($calendarRow && $calendarRow['reservationMaxParticipants'])) {
            $state = 0;
            $capacitySum = 0;
            $participants = Database::getInstance()->prepare(
                "SELECT SUM(tl_c4g_reservation.desiredCapacity) AS 'capacitySum' FROM tl_c4g_reservation 
                        WHERE tl_c4g_reservation.reservation_object=? AND (tl_c4g_reservation.reservationObjectType=2) AND NOT tl_c4g_reservation.cancellation = '1'")->execute($row['id'])->fetchAssoc();
            if ($participants && is_array($participants)) {
                $capacitySum = $participants['capacitySum'] ?: 0;
            }

            $maxParticipants = $arrChildRow['maxParticipants'] ?: $calendarRow['reservationMaxParticipants'];

            $showCount = $capacitySum . '/' . $maxParticipants;
            $percent = number_format($capacitySum ? ($capacitySum / $maxParticipants) * 100 : 0,0);
            $showCount .= ' ('.$percent.'%)';

            if ($capacitySum >= $maxParticipants) {
                $state = 3;
            } else if ($arrChildRow['reservationType'] || $calendarRow['reservationType']) {
                $reservationType = $arrChildRow['reservationType'] ?: $calendarRow['reservationType'];
                $type = C4gReservationTypeModel::findByPk($reservationType);
                if ($type) {
                    $almostFullyBookedAt = $type->almostFullyBookedAt;
                    If ($almostFullyBookedAt && ($percent >= $almostFullyBookedAt)) {
                        $state = 2;
                    }
                }
            } else if ($capacitySum < $maxParticipants) {
                $state = 1;
            }
            $this->states[$arrChildRow['pid']] = $state;

            $participants = '<div style="clear:both"><div style="float:left;width:150px"><strong>'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['participants'].':</strong></div><div>' . $showCount . '</div></div>';
        }

        return '<strong><div style="margin-bottom:10px">' . $row['title'] . '</div></strong>' . $event . $topics . $speakers . $price . $location . $organizer . $participants;
    }

    /**
     * @param $row
     * @param $href
     * @param $label
     * @param $title
     * @param $icon
     * @return string
     */
    public function c4gEditEvent($row, $href, $label, $title, $icon)
    {
        $calendar = Database::getInstance()->prepare("SELECT activateEventReservation FROM tl_calendar WHERE `id`=?")->execute($row['pid'])->fetchAssoc();
        if ($calendar['activateEventReservation']) {
            $rt = Input::get('rt');
            $ref = Input::get('ref');
            $do = Input::get('do');

            $attributes = 'style="margin-right:3px"';
            $imgAttributes = 'style="width: 18px; height: 18px"';

            $result = Database::getInstance()->prepare("SELECT id FROM tl_c4g_reservation_event WHERE `pid`=?")->execute($row['id'])->fetchAllAssoc();

            if ($result && count($result) > 1) {
                C4gLogModel::addLogEntry('reservation', 'There are more than one event connections. Check Event: '. $row['id']);
            } else if ($result && count($result) == 1) {
                $href = "/contao?do=$do&table=tl_c4g_reservation_event&amp;act=edit&amp;id=".$result[0]['id']."&amp;pid=".$row['id']."&amp;rt=".$rt;
            } else {
                $href = "/contao?do=$do&table=tl_c4g_reservation_event&amp;act=create&amp;mode=2&amp;pid=".$row['id']."&amp;rt=".$rt;
            }

            $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['pid']['default'] = $row['id'];

            return '<a href="' . $href . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, $imgAttributes) . '</a>';
        }
    }

    /**
     * @param $row
     * @param $href
     * @param $label
     * @param $title
     * @param $icon
     * @return string
     */
    public function c4gShowReservations($row, $href, $label, $title, $icon)
    {
        $calendar = Database::getInstance()->prepare("SELECT activateEventReservation FROM tl_calendar WHERE `id`=?")->execute($row['pid'])->fetchAssoc();
        if ($calendar['activateEventReservation']) {
            $rt = Input::get('rt');
            $ref = Input::get('ref');
            $do = Input::get('do');

            $attributes = 'style="margin-right:3px"';
            $imgAttributes = 'style="width: 18px; height: 18px"';

            $href = "/contao?do=".$do."&table=tl_c4g_reservation&id=" . $row['id'] . "&pid=" . $row['pid'] . "&rt=" . $rt . "&ref=" . $ref;

            if ($this->states[$row['id']]) {
                $state = $this->states[$row['id']];
            } else {
                $state = InsertTags::replaceInsertTags('{{c4gevent::' . $row['id'] . '::state_raw}}');
            }
            $stop = 1;
            switch ($state) {
                case '1':
                    $icon = 'bundles/con4gisreservation/images/circle_green.svg';
                    break;
                case '2':
                    $icon = 'bundles/con4gisreservation/images/circle_orange.svg';
                    break;
                case '3':
                    $icon = 'bundles/con4gisreservation/images/circle_red.svg';
                    break;
                default:
                    $icon = 'bundles/con4gisreservation/images/circle_red.svg';
                    break;
            }

            return '<a href="' . $href . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, $imgAttributes) . '</a> ';
        }
    }

    public function c4gShowAllParticipants($row, $href, $label, $title, $icon)
    {
        $calendar = Database::getInstance()->prepare("SELECT activateEventReservation FROM tl_calendar WHERE `id`=?")->execute($row['pid'])->fetchAssoc();

        if ($calendar['activateEventReservation']) {
            $rt = Input::get('rt');
            $ref = Input::get('ref');
            $do = Input::get('do');

            $eventReservations = Database::getInstance()->prepare("SELECT id,additional_params,title,lastname,firstname,email,tstamp,phone, postal,address,city,comment,cancellation,formular_id,desiredCapacity,additional1,additional2,additional3,dateOfBirth FROM tl_c4g_reservation WHERE `reservation_object`=?")->execute($row['id'])->fetchAllAssoc();
            $stop= 1;

            $currentEventParticipantList =  Database::getInstance()->prepare("SELECT reservation_id FROM tl_c4g_reservation_event_participants WHERE reservation_id != 0")->execute()->fetchAllAssoc();

            $i = 0;
            foreach ($currentEventParticipantList as $cepl) {
                $cep[$i++] = $cepl['reservation_id'];
            }
            if ($cep) {
                $currentEventReservationId = array_unique($cep);
                foreach ($currentEventReservationId as $cerId){
                    $stillExist = Database::getInstance()->prepare("SELECT id FROM tl_c4g_reservation WHERE `id`=?")->execute($cerId)->fetchAllAssoc();
                    if (!$stillExist) {
                        Database::getInstance()->prepare("DELETE FROM tl_c4g_reservation_event_participants WHERE `reservation_id`=?")->execute($cerId);
                    } 
                }
            }

            foreach ($eventReservations as $evRes) {
                $booker = $evRes['firstname'] . ' ' . $evRes['lastname'];
                $exist = Database::getInstance()->prepare("SELECT reservation_id FROM tl_c4g_reservation_event_participants WHERE `reservation_id`=?")->execute($evRes['id'])->fetchAssoc() ? true : false;
                $onlyParticipants = Database::getInstance()->prepare("SELECT onlyParticipants FROM tl_c4g_reservation_settings WHERE id=? ")->execute($evRes['formular_id'])->fetchAssoc();
            
                $onlyParticipants = $onlyParticipants['onlyParticipants'];
                if (!intval($evRes['cancellation']) && !$exist && !$onlyParticipants) {
                    Database::getInstance()->prepare("INSERT INTO `tl_c4g_reservation_event_participants` (`pid`, `reservation_id`, `participant_id`, `tstamp`, `title`, `lastname`, `firstname`, `email`, `phone`, `address`, `postal`, `city`, `comment`, `participant_params`, `booker`,`additional1`,`additional2`,`additional3`, `dateOfBirth`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")->execute($row['id'],$evRes['id'],$evRes['id'],$evRes['tstamp'],$evRes['title'],$evRes['lastname'],$evRes['firstname'],$evRes['email'],$evRes['phone'],$evRes['address'],$evRes['postal'],$evRes['city'],$evRes['comment'],$evRes['additional_params'],$booker,$evRes['additional1'],$evRes['additional2'],$evRes['additional3'],$evRes['dateOfBirth']);
                    $deleted = false;
                } else if ($evRes['cancellation'] && $exist) {
                    Database::getInstance()->prepare("DELETE FROM tl_c4g_reservation_event_participants WHERE `reservation_id`=?")->execute($evRes['id']);
                    $deleted = true;
                }      
              
                $participantData = Database::getInstance()->prepare("SELECT * FROM tl_c4g_reservation_participants WHERE `pid`=?")->execute($evRes['id'])->fetchAllAssoc();

                if ($participantData) {
                    foreach ($participantData as $pd) {
                            if (!$pd['cancellation'] && !$exist && !$deleted) {
                                Database::getInstance()->prepare("INSERT INTO `tl_c4g_reservation_event_participants` (`pid`, `reservation_id`, `participant_id`, `tstamp`, `title`, `lastname`, `firstname`, `email`, `phone`, `address`, `postal`, `city`, `comment`, `participant_params`, `cancellation`, `booker`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")->execute($row['id'],$evRes['id'],$pd['id'],$pd['tstamp'],$pd['title'],$pd['lastname'],$pd['firstname'],$pd['email'],$pd['phone'],$pd['address'],$pd['postal'],$pd['city'],$pd['comment'],$pd['participant_params'],$pd['cancellation'], $booker);
                        } else if ($pd['cancellation']) {
                            Database::getInstance()->prepare("DELETE FROM tl_c4g_reservation_event_participants WHERE `participant_id`=? AND `reservation_id`!=?")->execute($pd['id'],$pd['id']);
                        }
                    }                    
                }
                
                if ($stillExist && !intval($evRes['cancellation'])) {
                     $currentData = Database::getInstance()->prepare("SELECT reservation_id FROM tl_c4g_reservation_event_participants WHERE `reservation_id`=? AND `pid`=?")->execute($evRes['id'],$row['id'])->fetchAllAssoc();
                    if($currentData) {
                        $countBegin = count($currentData);
                    } else {
                        $countBegin = 0;
                    }
          
                    $unknown = $GLOBALS['TL_LANG']['tl_calendar_events']['unknown'];
                    for ($i = $countBegin; $i < intval($evRes['desiredCapacity']); $i++) {
                        Database::getInstance()->prepare("INSERT INTO `tl_c4g_reservation_event_participants` (`pid`,`reservation_id`,`tstamp`, `lastname`, `firstname`, `booker`) VALUES (?,?,?,?,?,?)")->execute($row['id'],$evRes['id'],$evRes['tstamp'],$unknown,$unknown,$booker);
                    }
                }
            }

            $attributes = 'style="margin-right:3px"';
            $imgAttributes = 'style="width: 18px; height: 18px"';

            $label_new = 'Label Umbenannt';

            $href = "/contao?do=".$do."&table=tl_c4g_reservation_event_participants&id=" . $row['id'];

            return '<a href="' . $href . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, $imgAttributes) . '</a> ';
        }
    }
}