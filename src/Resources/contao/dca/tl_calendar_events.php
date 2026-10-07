<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @author con4gis contributors (see "authors.md")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design
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
use Contao\System;
use Contao\Input;
// use Contao\InsertTags;

$str = 'tl_calendar_events';

$GLOBALS['TL_DCA'][$str]['config']['ctable'][] = 'tl_c4g_reservation_event';
$GLOBALS['TL_DCA'][$str]['config']['onload_callback'][] = ['tl_c4g_reservation_event_bridge','c4gLoadReservationData'];
$GLOBALS['TL_DCA'][$str]['list']['sorting']['child_record_callback'] = ['tl_c4g_reservation_event_bridge','loadChildRecord'];

$GLOBALS['TL_DCA'][$str]['list']['operations']['c4gEditEvent'] = [
    'label'               => &$GLOBALS['TL_LANG'][$str]['c4gEditEvent'],
    'icon'                => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation_types.svg',
    'button_callback'     => ['tl_c4g_reservation_event_bridge','c4gEditEvent'],
    'exclude'             => true
];
$GLOBALS['TL_DCA'][$str]['list']['operations']['c4gExportReservations'] = [
    'href'                => 'key=runexport',
    'label'               => &$GLOBALS['TL_LANG'][$str]['c4gExportReservations'],
    'icon'                => 'bundles/con4gisexport/images/be-icons/export.svg',
    'button_callback'     => [\con4gis\ReservationBundle\Classes\Callbacks\ReservationEvents::class,'runExport'],
    'exclude'             => true
];

$GLOBALS['TL_DCA'][$str]['list']['operations']['c4g_participant_list'] = [
    'label'               => &$GLOBALS['TL_LANG'][$str]['c4g_participant_list'],
    'icon'                => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation_audience.svg',
    'button_callback'     => ['tl_c4g_reservation_event_bridge','c4gShowAllParticipants'],
    'exclude'             => true
];

$GLOBALS['TL_DCA'][$str]['list']['operations']['c4gEditReservations'] = [
    'label'               => &$GLOBALS['TL_LANG'][$str]['c4gEditReservations'],
    'icon'                => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation.svg',
    'button_callback'     => ['tl_c4g_reservation_event_bridge','c4gShowReservations'],
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
        System::loadLanguageFile('fe_c4g_reservation');

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
        $event = ($date) ? ('<div style="clear:both"><div style="float:left;width:150px"><strong>' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['event'] . ':</strong></div><div>' . $date . '</div></div>') : '';

        $arrChildRow = Database::getInstance()->prepare('SELECT * FROM tl_c4g_reservation_event WHERE pid=?')->execute($row['id'])->fetchAssoc();
        $calendarRow = Database::getInstance()->prepare('SELECT * FROM tl_calendar WHERE id=? AND activateEventReservation="1"')->execute($row['pid'])->fetchAssoc();

        //topics
        $topics = '';
        if (($arrChildRow && $arrChildRow['topic']) || ($calendarRow && $calendarRow['reservationTopic'])) {
            $topicIds = StringUtil::deserialize($arrChildRow['topic'] ?: $calendarRow['reservationTopic']);
            if ($topicIds && is_array($topicIds)) {
                $topic = Database::getInstance()->prepare('SELECT * FROM tl_c4g_reservation_event_topic WHERE id IN ('.implode(',',$topicIds).')')->execute()->fetchAllAssoc();
                $topicNames = [];
                foreach ($topic as $topicElement) {
                    $topicNames[] = $topicElement['topic'];
                }
                if (!empty($topicNames)) {
                    $topics = '<div style="clear:both"><div style="float:left;width:150px"><strong>'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['topic'].':</strong></div><div>' . implode(',',$topicNames) . '</div></div>';
                }
            }
        }

        //speaker
        $speakers = '';
        if (($arrChildRow && $arrChildRow['speaker']) || ($calendarRow && $calendarRow['reservationSpeaker'])) {
            $speakerIds = StringUtil::deserialize($arrChildRow['speaker'] ?: $calendarRow['reservationSpeaker']);
            if ($speakerIds && is_array($speakerIds)) {
                $speaker = Database::getInstance()->prepare('SELECT * FROM tl_c4g_reservation_event_speaker WHERE id IN ('.implode(',',$speakerIds).')')->execute()->fetchAllAssoc();
                $speakerNames = [];
                foreach ($speaker as $speakerElement) {
                    $speakerNames[] = $speakerElement['title'] ? $speakerElement['title'] . ' ' . $speakerElement['firstname'] . ' ' . $speakerElement['lastname'] : $speakerElement['firstname'] .' '.$speakerElement['lastname'];
                }
                if (!empty($speakerNames)) {
                    $speakers = '<div style="clear:both"><div style="float:left;width:150px"><strong>'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['speaker'].':</strong></div><div>' . implode(',',$speakerNames) . '</div></div>';
                }
            }
        }

        //price
        $price = '';
        $priceValue = ($arrChildRow && $arrChildRow['price']) ? $arrChildRow['price'] : ($calendarRow['reservationPrice'] ?? null);
        if ($priceValue) {
            $price = '<div style="clear:both"><div style="float:left;width:150px"><strong>'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['price'].':</strong></div><div>'.C4gReservationHandler::formatPrice($priceValue) . '</div></div>';
        }

        //location
        $location = '';
        if (($arrChildRow && $arrChildRow['location']) || ($calendarRow && $calendarRow['reservationLocation'])) {
            $locationId = ($arrChildRow && $arrChildRow['location']) ? $arrChildRow['location'] : ($calendarRow['reservationLocation'] ?? null);
            if ($locationId) {
                $locationResult = Database::getInstance()->prepare('SELECT name FROM tl_c4g_reservation_location WHERE id = ?')->execute($locationId)->fetchAssoc();
                $locationName = $locationResult['name'] ?? '';
                if ($locationName) {
                    $location = '<div style="clear:both"><div style="float:left;width:150px"><strong>'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['eventlocation'].':</strong></div><div>' . $locationName . '</div></div>';
                }
            }
        }

        //organizer
        $organizer = '';
        if (($arrChildRow && $arrChildRow['organizer']) || ($calendarRow && $calendarRow['reservationOrganizer'])) {
            $organizerId = ($arrChildRow && $arrChildRow['organizer']) ? $arrChildRow['organizer'] : ($calendarRow['reservationOrganizer'] ?? null);
            if ($organizerId) {
                $organizerResult = Database::getInstance()->prepare('SELECT name FROM tl_c4g_reservation_location WHERE id = ?')->execute($organizerId)->fetchAssoc();
                $organizerName = $organizerResult['name'] ?? '';
                if ($organizerName) {
                    $organizer = '<div style="clear:both"><div style="float:left;width:150px"><strong>'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['organizer'].':</strong></div><div>' . $organizerName . '</div></div>';
                }
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

            $maxParticipants = ($arrChildRow && $arrChildRow['maxParticipants']) ? $arrChildRow['maxParticipants'] : ($calendarRow['reservationMaxParticipants'] ?? 0);

            $showCount = $capacitySum . '/' . $maxParticipants;
            $percent = number_format($maxParticipants ? ($capacitySum / $maxParticipants) * 100 : 0,0);
            $showCount .= ' ('.$percent.'%)';

            if ($capacitySum >= $maxParticipants) {
                $state = 3;
            } else if (($arrChildRow && $arrChildRow['reservationType']) || ($calendarRow && $calendarRow['reservationType'])) {
                $reservationType = ($arrChildRow && $arrChildRow['reservationType']) ? $arrChildRow['reservationType'] : ($calendarRow['reservationType'] ?? null);
                if ($reservationType) {
                    $type = C4gReservationTypeModel::findByPk($reservationType);
                    if ($type) {
                        $almostFullyBookedAt = $type->almostFullyBookedAt;
                        If ($almostFullyBookedAt && ($percent >= $almostFullyBookedAt)) {
                            $state = 2;
                        }
                    }
                }
            } else if ($capacitySum < $maxParticipants) {
                $state = 1;
            }
            $this->states[($arrChildRow['pid'] ?? 0)] = $state;

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
            if (is_array($title)) {
                $title = $title[0] ?? '';
            }
            if (is_array($label)) {
                $label = $label[0] ?? '';
            }

        if (is_array($title)) {
            $title = $title[0] ?? '';
        }

        $calendar = Database::getInstance()->prepare("SELECT activateEventReservation FROM tl_calendar WHERE `id`=?")->execute($row['pid'])->fetchAssoc();
        if ($calendar && $calendar['activateEventReservation']) {
            $rt = Input::get('rt');
            $ref = Input::get('ref');
            $do = Input::get('do');

            $attributes = 'style="margin-right:3px"';
            $imgAttributes = 'style="width: 18px; height: 18px"';

            $result = Database::getInstance()->prepare("SELECT id FROM tl_c4g_reservation_event WHERE `pid`=?")->execute($row['id'])->fetchAllAssoc();

            if ($result && count($result) > 1) {
                C4gLogModel::addLogEntry('reservation','There are more than one event connections. Check Event: '. $row['id']);
            } else if ($result && count($result) == 1) {
                $href = System::getContainer()->get('router')->generate('contao_backend')."?do=$do&table=tl_c4g_reservation_event&amp;act=edit&amp;id=".$result[0]['id']."&amp;pid=".$row['id']."&amp;rt=".$rt;
            } else {
                $href = System::getContainer()->get('router')->generate('contao_backend')."?do=$do&table=tl_c4g_reservation_event&amp;act=create&amp;mode=2&amp;pid=".$row['id']."&amp;rt=".$rt;
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
            if (is_array($title)) {
                $title = $title[0] ?? '';
            }
            if (is_array($label)) {
                $label = $label[0] ?? '';
            }

        if (is_array($title)) {
            $title = $title[0] ?? '';
        }

        $calendar = Database::getInstance()->prepare("SELECT activateEventReservation FROM tl_calendar WHERE `id`=?")->execute($row['pid'])->fetchAssoc();
        if ($calendar && $calendar['activateEventReservation']) {
            $rt = Input::get('rt');
            $ref = Input::get('ref');
            $do = Input::get('do');

            $attributes = 'style="margin-right:3px"';
            $imgAttributes = 'style="width: 18px; height: 18px"';

            $href = System::getContainer()->get('router')->generate('contao_backend')."?do=".$do."&table=tl_c4g_reservation&id=" . $row['id'] . "&pid=" . $row['pid'] . "&rt=" . $rt . "&ref=" . $ref;

            if (isset($this->states[$row['id']])) {
                $state = $this->states[$row['id']];
            } else {
                // $state = InsertTags::replaceInsertTags('{{c4gevent::' . $row['id'] . '::state_raw}}');

                $state = '{{c4gevent::' . $row['id'] . '::state_raw}}';
                $parser = System::getContainer()->get('contao.insert_tag.parser');
                $state = html_entity_decode($parser->replace($state));
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
            if (is_array($title)) {
                $title = $title[0] ?? '';
            }
            if (is_array($label)) {
                $label = $label[0] ?? '';
            }

        if (is_array($title)) {
            $title = $title[0] ?? '';
        }

        $calendar = Database::getInstance()->prepare("SELECT activateEventReservation FROM tl_calendar WHERE `id`=?")->execute($row['pid'])->fetchAssoc();

        if ($calendar && $calendar['activateEventReservation']) {
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
            if (isset($cep)) {
                $currentEventReservationId = array_unique($cep);
                foreach ($currentEventReservationId as $cerId){
                    $stillExist = Database::getInstance()->prepare("SELECT id FROM tl_c4g_reservation WHERE `id`=?")->execute($cerId)->fetchAllAssoc();
                    if (!$stillExist) {
                        Database::getInstance()->prepare("DELETE FROM tl_c4g_reservation_event_participants WHERE `reservation_id`=?")->execute($cerId);
                    } 
                }
            }

            foreach ($eventReservations as $evRes) {
                $deleted = false;
                $booker = $evRes['firstname'] . ' ' . $evRes['lastname'];
                $exist = Database::getInstance()->prepare("SELECT reservation_id FROM tl_c4g_reservation_event_participants WHERE `reservation_id`=?")->execute($evRes['id'])->fetchAssoc() ? true : false;
                $onlyParticipants = Database::getInstance()->prepare("SELECT onlyParticipants FROM tl_c4g_reservation_settings WHERE id=? ")->execute($evRes['formular_id'])->fetchAssoc();
            
                $onlyParticipants = isset($onlyParticipants['onlyParticipants']) ? $onlyParticipants['onlyParticipants'] : null;
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
                
                if (isset($stillExist) && !intval($evRes['cancellation'])) {
                     $currentData = Database::getInstance()->prepare("SELECT reservation_id FROM tl_c4g_reservation_event_participants WHERE `reservation_id`=? AND `pid`=?")->execute($evRes['id'],$row['id'])->fetchAllAssoc();
                    if($currentData) {
                        $countBegin = count($currentData);
                    } else {
                        $countBegin = 0;
                    }
                    $unknown = $GLOBALS['TL_LANG']['tl_calendar_events']['unknown'] ?? 'unknown';
                    for ($i = $countBegin; $i < intval($evRes['desiredCapacity']); $i++) {
                        Database::getInstance()->prepare("INSERT INTO `tl_c4g_reservation_event_participants` (`pid`,`reservation_id`,`tstamp`, `lastname`, `firstname`, `booker`) VALUES (?,?,?,?,?,?)")->execute($row['id'],$evRes['id'],$evRes['tstamp'],$unknown,$unknown,$booker);
                    }
                }
            }

            $attributes = 'style="margin-right:3px"';
            $imgAttributes = 'style="width: 18px; height: 18px"';

            $label_new = 'Label Umbenannt';

            $href = System::getContainer()->get('router')->generate('contao_backend')."?do=".$do."&table=tl_c4g_reservation_event_participants&id=" . $row['id'];

            return '<a href="' . $href . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, $imgAttributes) . '</a> ';
        }
    }
}
