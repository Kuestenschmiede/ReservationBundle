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
namespace con4gis\ReservationBundle\Classes;

use con4gis\CoreBundle\Classes\Helper\StringHelper;
use con4gis\ReservationBundle\Resources\contao\models\C4GReservationParamsModel;
use Contao\Controller;
use Contao\Database;
use Contao\StringUtil;
use Contao\System;

/**
 * Class C4gReservationInsertTags
 * @package con4gis/reservation
 */
class C4gReservationInsertTags
{
    /**
     * Instanz von \Contao\Database
     * @var Database|null
     */
    protected $db = null;

    /**
     * ReplaceInsertTags constructor.
     * @param null $db
     */
    public function __construct($db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $this->db = \Contao\Database::getInstance();
        }
    }

    /**
     * @param $fieldname
     * $param $label
     * @param $value
     * @param string $className
     */
    private function getHtmlSkeleton($fieldname, $label, $value, $className = 'c4g_reservation_details')
    {
        $result = '';
        if ($value) {
            $result = '<p class="' . $className . ' ' . $className . '_' . $fieldname . '">' .
                '<label class="' . $className . '_label" for="' . $className . '_value">' . $label . '</label>' .
                '<span class="' . $className . '_value ' . $fieldname . '">' . $value . '</span></p>';
        }

        return $result;
    }

    //ToDo find central point for saving state to db
    /**
     * @param $reservationEventObject
     * @param $calendarEvent
     */
    private function getState($reservationEventObject, $calendarEvent)
    {
        $result = 0;
        $id = $reservationEventObject->pid;
        $max = $reservationEventObject->maxParticipants;

        $today = date('Y.m.d', time());
        $startdate = $calendarEvent->startDate ? date('Y.m.d', $calendarEvent->startDate) : false;
        if (!$reservationEventObject->reservationType || ($startdate && $startdate < $today) || (($calendarEvent->startTime &&
                    ($calendarEvent->startTime < time())) && ($startdate && $startdate == $today))) {
            $result = 3;
        } elseif ($id && $max > 0) {
            $tableReservation = 'tl_c4g_reservation';
            $reservationObject = $this->db->prepare("SELECT COUNT(id) AS reservationCount FROM $tableReservation WHERE reservation_object = $id AND reservationObjectType = '2' AND NOT cancellation = '1'")->execute()->fetchAllAssoc();
            if ($reservationObject) {
                $reservationCount = $reservationObject[0]['reservationCount'];
            }

            $reservationType = $reservationEventObject->reservationType;
            if ($reservationType) {
                $tableReservationType = 'tl_c4g_reservation_type';
                $almostFullyBookedAt = $this->db->prepare("SELECT almostFullyBookedAt FROM $tableReservationType WHERE id=?")
                    ->limit(1)
                    ->execute($reservationType, 1);

                $percent = $reservationCount ? ($reservationCount / $max) * 100 : 0;
                if ($percent >= 100) {
                    $result = 3;
                } elseif ($percent >= $almostFullyBookedAt) {
                    $result = 2;
                } else {
                    $result = 1;
                }
            }
        } elseif ($id && !$max) {
            $result = 1;
        }

        return $result;
    }

    /**
     * @param $tag
     * @return bool
     */
    public function replaceTag($strTag)
    {
        if ($strTag) {
            $arrSplit = explode('::', $strTag);
        }

        //{{c4gevent::ID::KEY}}
        //{{c4gobject::ID::KEY}}

        //event reservations
        if ($arrSplit && (($arrSplit[0] == 'c4gevent')) && isset($arrSplit[1])) {
            if (count($arrSplit) == 2) {
                $key = $arrSplit[1];
                $tableEventObject = 'tl_c4g_reservation_event';
                $tableCalendarEvent = 'tl_calendar_events';
                $tableSettings = 'tl_c4g_settings';
                $tableAudience = 'tl_c4g_reservation_event_audience';
                $tableSpeaker = 'tl_c4g_reservation_event_speaker';
                $tableTopic = 'tl_c4g_reservation_event_topic';
                $tableLocation = 'tl_c4g_reservation_location';

                $reservationEventObject = $this->db->prepare("SELECT * FROM $tableEventObject")
                    ->execute()->fetchAllAssoc();

                if ($reservationEventObject) {
                    System::loadLanguageFile('fe_c4g_reservation');

                    switch ($key) {
                        case 'check':
                            return true;
                        case 'audience_raw':
                            if ($reservationEventObject) {
                                $audienceIds = [];
                                foreach ($reservationEventObject as $eventObject) {
                                    $eventAudiences = unserialize($eventObject['targetAudience']);
                                    foreach ($eventAudiences as $audienceId) {
                                        $audienceIds[$audienceId] = $audienceId;
                                    }
                                }
                            }

                            if ($audienceIds && count($audienceIds) > 0) {
                                $audiences = '(';
                                foreach ($audienceIds as $key => $audienceId) {
                                    $audiences .= "\"$audienceId\"";
                                    if (!(array_key_last($audienceIds) === $key)) {
                                        $audiences .= ',';
                                    }
                                }
                                $audiences .= ')';
                                $audienceElements = $this->db->prepare("SELECT targetAudience FROM $tableAudience WHERE id IN $audiences")
                                    ->execute()->fetchAllAssoc();

                                foreach ($audienceElements as $audience) {
                                    $result[] = $audience['targetAudience'];
                                }

                                return $result ? serialize($result) : [];
                            }

                            break;
                        case 'speaker_raw':
                            if ($reservationEventObject) {
                                $speakerIds = [];
                                foreach ($reservationEventObject as $eventObject) {
                                    $eventSpeakers = unserialize($eventObject['speaker']);
                                    foreach ($eventSpeakers as $speakerId) {
                                        $speakerIds[$speakerId] = $speakerId;
                                    }
                                }
                            }

                            if ($speakerIds && count($speakerIds) > 0) {
                                $speakers = '(';
                                foreach ($speakerIds as $key => $speakerId) {
                                    $speakers .= "\"$speakerId\"";
                                    if (!(array_key_last($speakerIds) === $key)) {
                                        $speakers .= ',';
                                    }
                                }
                                $speakers .= ')';
                                $speakerElements = $this->db->prepare("SELECT id,title,firstname,lastname,speakerForwarding FROM $tableSpeaker WHERE id IN $speakers")
                                    ->execute()->fetchAllAssoc();

                                foreach ($speakerElements as $speaker) {
                                    $speakerStr = $speaker['title'] ? $speaker['lastname'] . ', ' . $speaker['firstname'] . ', ' . $speaker['title'] : $speaker['lastname'] . ', ' . $speaker['firstname'];

                                    $result[] = $speakerStr;
                                }

                                return $result ? serialize($result) : [];
                            };

                            break;
                        case 'topic_raw':
                            if ($reservationEventObject) {
                                $topicIds = [];
                                foreach ($reservationEventObject as $eventObject) {
                                    $eventTopics = unserialize($eventObject['topic']);
                                    foreach ($eventTopics as $topicId) {
                                        $topicIds[$topicId] = $topicId;
                                    }
                                }
                            }

                            if ($topicIds && count($topicIds) > 0) {
                                $topics = '(';
                                foreach ($topicIds as $key => $topicId) {
                                    $topics .= "\"$topicId\"";
                                    if (!(array_key_last($topicIds) === $key)) {
                                        $topics .= ',';
                                    }
                                }
                                $topics .= ')';
                                $topicElements = $this->db->prepare("SELECT topic FROM $tableTopic WHERE id IN $topics")
                                    ->execute()->fetchAllAssoc();

                                foreach ($topicElements as $topic) {
                                    $result[] = $topic['topic'];
                                }

                                return $result ? serialize($result) : [];
                            }

                            break;
                        case 'eventlocation_raw':
                            if ($reservationEventObject) {
                                $locationIds = [];
                                foreach ($reservationEventObject as $eventObject) {
                                    $locationId = $eventObject['location'];
                                    if ($locationId) {
                                        $locationIds[$locationId] = $locationId;
                                    }
                                }
                            }

                            if ($locationIds && count($locationIds) > 0) {
                                $locations = '(';
                                foreach ($locationIds as $key => $locationId) {
                                    $locations .= "\"$locationId\"";
                                    if (!(array_key_last($locationIds) === $key)) {
                                        $locations .= ',';
                                    }
                                }
                                $locations .= ')';
                                $locationElements = $this->db->prepare("SELECT name FROM $tableLocation WHERE id IN $locations")
                                    ->execute()->fetchAllAssoc();

                                foreach ($locationElements as $location) {
                                    $result[] = $location['name'];
                                }

                                return $result ? serialize($result) : [];
                            }

                            break;
                        case 'city_raw':
                            if ($reservationEventObject) {
                                $locationIds = [];
                                foreach ($reservationEventObject as $eventObject) {
                                    $locationId = $eventObject['location'];
                                    if ($locationId) {
                                        $locationIds[$locationId] = $locationId;
                                    }
                                }
                            }

                            if ($locationIds && count($locationIds) > 0) {
                                $locations = '(';
                                foreach ($locationIds as $key => $locationId) {
                                    $locations .= "\"$locationId\"";
                                    if (!(array_key_last($locationIds) === $key)) {
                                        $locations .= ',';
                                    }
                                }
                                $locations .= ')';
                                $locationElements = $this->db->prepare("SELECT contact_city FROM $tableLocation WHERE id IN $locations")
                                    ->execute()->fetchAllAssoc();

                                foreach ($locationElements as $location) {
                                    $result[] = $location['contact_city'];
                                }

                                return $result ? serialize($result) : [];
                            }

                            break;
                        case 'included_raw':
                            $includedParams = $this->db->prepare('SELECT included_params FROM tl_c4g_reservation_type WHERE id = ?')
                                ->execute($reservationEventObject->reservationType)->fetchAssoc();
                            $params = $includedParams ? unserialize($includedParams['included_params']) : [];
                            $includedParamsArr = [];
                            foreach ($params as $param) {
                                $includedParam = C4gReservationParamsModel::findByPk($param);
                                if ($includedParam && $includedParam->caption) {
                                    $includedParamsArr[$param] = $includedParam->caption;
                                }
                            }

                            return serialize($includedParamsArr);
                        case 'additional_raw':
                            $additionalParams = $this->db->prepare('SELECT additional_params FROM tl_c4g_reservation_type WHERE id = ?')
                                ->execute($reservationEventObject->reservationType)->fetchAssoc();
                            $params = $additionalParams ? unserialize($additionalParams['additional_params']) : [];
                            $additionalParamsArr = [];
                            foreach ($params as $param) {
                                $additionalParam = C4gReservationParamsModel::findByPk($param);
                                if ($additionalParam && $additionalParam->caption) {
                                    $additionalParamsArr[$param] = $additionalParam->caption;
                                }
                            }

                            return serialize($additionalParamsArr);
                    }
                }
            } elseif ($arrSplit[1] && $arrSplit[2]) {
                $pid = $arrSplit[1];
                $key = $arrSplit[2];
                $tableEventObject = 'tl_c4g_reservation_event';
                $tableCalendarEvent = 'tl_calendar_events';
                $tableSettings = 'tl_c4g_settings';
                $tableAudience = 'tl_c4g_reservation_event_audience';
                $tableSpeaker = 'tl_c4g_reservation_event_speaker';
                $tableTopic = 'tl_c4g_reservation_event_topic';
                $tableLocation = 'tl_c4g_reservation_location';

                $reservationEventObject = $this->db->prepare("SELECT * FROM $tableEventObject WHERE pid=?")
                    ->limit(1)
                    ->execute($pid, 1);

                $calendarEvent = $this->db->prepare("SELECT * FROM $tableCalendarEvent WHERE id=?")
                    ->limit(1)
                    ->execute($pid, 1);

                if ($reservationEventObject->numRows && $calendarEvent->numRows) {
                    System::loadLanguageFile('fe_c4g_reservation');
                    $dateFormat = $GLOBALS['TL_CONFIG']['dateFormat'];
                    $datimFormat = $GLOBALS['TL_CONFIG']['datimFormat'];
                    $timeFormat = $GLOBALS['TL_CONFIG']['timeFormat'];

                    $clock = '';
                    if (!strpos($timeFormat, 'A')) {
                        $clock = ' ' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['clock'];
                    }

                    switch ($key) {
                        case 'check':
                            return true;
                        case 'state':
                            $state = $this->getState($reservationEventObject, $calendarEvent);
                            if ($state) {
                                switch ($state) {
                                    case '1':
                                        return $this->getHtmlSkeleton(
                                            'state',
                                            $GLOBALS['TL_LANG']['fe_c4g_reservation']['state'],
                                            '<img class="c4g_reservation_state c4g_reservation_state_green img-fluid" loading="lazy" height="32px" width="32px" src="bundles/con4gisreservation/images/circle_green.svg" alt="' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['state_green'] . '" title="' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['state_green'] . '">'
                                        );
                                    case '2':
                                        return $this->getHtmlSkeleton(
                                            'state',
                                            $GLOBALS['TL_LANG']['fe_c4g_reservation']['state'],
                                            '<img class="c4g_reservation_state c4g_reservation_state_orange img-fluid" loading="lazy" height="32px" width="32px" src="bundles/con4gisreservation/images/circle_orange.svg" alt="' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['state_orange'] . '" title="' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['state_orange'] . '">'
                                        );
                                    case '3':
                                        return $this->getHtmlSkeleton(
                                            'state',
                                            $GLOBALS['TL_LANG']['fe_c4g_reservation']['state'],
                                            '<img class="c4g_reservation_state c4g_reservation_state_red img-fluid" loading="lazy" height="32px" width="32px" src="bundles/con4gisreservation/images/circle_red.svg" alt="' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['state_red'] . '" title="' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['state_red'] . '">'
                                        );
                                }
                            }

                            return '';
                        case 'state_raw':
                            return $this->getState($reservationEventObject, $calendarEvent);
                        case 'headline':
                            return '<div class=" c4g_reservation_details_headline">' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['detailsHeaadline'] . '</div>';
                        case 'headline_raw':
                            return $GLOBALS['TL_LANG']['fe_c4g_reservation']['detailsHeaadline'];
                        case 'button':
                            if ($this->getState($reservationEventObject, $calendarEvent) !== 3) {
                                $settings = $this->db->prepare("SELECT reservationForwarding FROM $tableSettings")
                                    ->limit(1)
                                    ->execute();
                                if ($settings->numRows && $settings->reservationForwarding) {
                                    $url = Controller::replaceInsertTags('{{link_url::' . $settings->reservationForwarding . '}}');
                                    if ($url) {
                                        return '<a href="' . $url . '?event=' . $pid . '" title="Reservieren" itemprop="url">' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['eventForwardingButtonText'] . '</a>';
                                    }
                                }
                            }

                            break;
                        case 'lon':
                            return $calendarEvent->loc_geox;
                        case 'lat':
                            return $calendarEvent->loc_geoy;
                        case 'number':
                            $value = $reservationEventObject->number;
                            if ($value) {
                                $value = $this->getHtmlSkeleton('eventnumber', $GLOBALS['TL_LANG']['fe_c4g_reservation']['eventnumber'], $value);
                            }

                            return $value;
                        case 'number_raw':
                            return $reservationEventObject->number;
                        case 'audience':
                            $audienceIds = unserialize($reservationEventObject->targetAudience);
                            if ($audienceIds && count($audienceIds) > 0) {
                                $audiences = '(' ;
                                foreach ($audienceIds as $key => $audienceId) {
                                    $audiences .= "\"$audienceId\"";
                                    if (!(array_key_last($audienceIds) === $key)) {
                                        $audiences .= ',';
                                    }
                                }
                                $audiences .= ')';
                                $audienceElements = $this->db->prepare("SELECT targetAudience FROM $tableAudience WHERE id IN $audiences")
                                    ->execute()->fetchAllAssoc();

                                foreach ($audienceElements as $audience) {
                                    $result = $result ? $result . ', ' . $audience['targetAudience'] : $audience['targetAudience'];
                                }

                                return $result ? $this->getHtmlSkeleton('targetAudience', $GLOBALS['TL_LANG']['fe_c4g_reservation']['targetAudience'], $result) : '';
                            }

                            break;
                        case 'audience_raw':
                            $audienceIds = unserialize($reservationEventObject->targetAudience);
                            if ($audienceIds && count($audienceIds) > 0) {
                                $audiences = '(' ;
                                foreach ($audienceIds as $key => $audienceId) {
                                    $audiences .= "\"$audienceId\"";
                                    if (!(array_key_last($audienceIds) === $key)) {
                                        $audiences .= ',';
                                    }
                                }
                                $audiences .= ')';
                                $audienceElements = $this->db->prepare("SELECT targetAudience FROM $tableAudience WHERE id IN $audiences")
                                    ->execute()->fetchAllAssoc();

                                foreach ($audienceElements as $audience) {
                                    $result[] = $audience['targetAudience'];
                                }

                                return $result ? serialize($result) : [];
                            }

                            break;
                        case 'speaker':
                            $speakerIds = unserialize($reservationEventObject->speaker);
                            if ($speakerIds && count($speakerIds) > 0) {
                                $speakers = '(' ;
                                foreach ($speakerIds as $key => $speakerId) {
                                    $speakers .= "\"$speakerId\"";
                                    if (!(array_key_last($speakerIds) === $key)) {
                                        $speakers .= ',';
                                    }
                                }
                                $speakers .= ')';
                                $speakerElements = $this->db->prepare("SELECT id,title,firstname,lastname,speakerForwarding FROM $tableSpeaker WHERE id IN $speakers")
                                    ->execute()->fetchAllAssoc();

                                foreach ($speakerElements as $speaker) {
                                    $speakerStr = $speaker['title'] ? $speaker['title'] . ' ' . $speaker['firstname'] . ' ' . $speaker['lastname'] : $speaker['firstname'] . ' ' . $speaker['lastname'];
                                    if ($speakerStr && $speaker['speakerForwarding']) {
                                        $url = Controller::replaceInsertTags('{{link_url::' . $speaker['speakerForwarding'] . '}}');
                                        if ($url) {
                                            $speakerStr = '<a href="' . $url . '?#speaker' . $speaker['id'] . '" title="' . $speakerStr . '" itemprop="url">' . $speakerStr . '</a>';
                                        }
                                    }

                                    $result = $result ? $result . ', ' . $speakerStr : $speakerStr;
                                }

                                return $result ? $this->getHtmlSkeleton('speaker', $GLOBALS['TL_LANG']['fe_c4g_reservation']['speaker'], $result) : '';
                            };

                            break;
                        case 'speaker_raw':
                            $speakerIds = unserialize($reservationEventObject->speaker);
                            if ($speakerIds && count($speakerIds) > 0) {
                                $speakers = '(' ;
                                foreach ($speakerIds as $key => $speakerId) {
                                    $speakers .= "\"$speakerId\"";
                                    if (!(array_key_last($speakerIds) === $key)) {
                                        $speakers .= ',';
                                    }
                                }
                                $speakers .= ')';
                                $speakerElements = $this->db->prepare("SELECT id,title,firstname,lastname,speakerForwarding FROM $tableSpeaker WHERE id IN $speakers")
                                    ->execute()->fetchAllAssoc();

                                foreach ($speakerElements as $speaker) {
                                    $speakerStr = $speaker['title'] ? $speaker['lastname'] . ', ' . $speaker['firstname'] . ', ' . $speaker['title'] : $speaker['lastname'] . ', ' . $speaker['firstname'];

                                    $result[] = $speakerStr;
                                }

                                return $result ? serialize($result) : [];
                            };

                            break;
                        case 'topic':
                            $topicIds = unserialize($reservationEventObject->topic);
                            if ($topicIds && count($topicIds) > 0) {
                                $topics = '(' ;
                                foreach ($topicIds as $key => $topicId) {
                                    $topics .= "\"$topicId\"";
                                    if (!(array_key_last($topicIds) === $key)) {
                                        $topics .= ',';
                                    }
                                }
                                $topics .= ')';
                                $topicElements = $this->db->prepare("SELECT topic FROM $tableTopic WHERE id IN $topics")
                                    ->execute()->fetchAllAssoc();

                                foreach ($topicElements as $topic) {
                                    $result = $result ? $result . ', ' . $topic['topic'] : $topic['topic'];
                                }

                                return $result ? $this->getHtmlSkeleton('topic', $GLOBALS['TL_LANG']['fe_c4g_reservation']['topic'], $result) : '';
                            }

                            break;
                        case 'topic_raw':
                            $topicIds = unserialize($reservationEventObject->topic);
                            if ($topicIds && count($topicIds) > 0) {
                                $topics = '(' ;
                                foreach ($topicIds as $key => $topicId) {
                                    $topics .= "\"$topicId\"";
                                    if (!(array_key_last($topicIds) === $key)) {
                                        $topics .= ',';
                                    }
                                }
                                $topics .= ')';
                                $topicElements = $this->db->prepare("SELECT topic FROM $tableTopic WHERE id IN $topics")
                                    ->execute()->fetchAllAssoc();

                                foreach ($topicElements as $topic) {
                                    $result[] = $topic['topic'];
                                }

                                return $result ? serialize($result) : [];
                            }

                            break;
                        case 'beginDate':
                            $value = date($dateFormat, $calendarEvent->startDate);
                            if ($value) {
                                $value = $this->getHtmlSkeleton('beginDate', $GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDateEvent'], $value);
                            }

                            return $value;
                        case 'beginDate_raw':
                            return date($dateFormat, $calendarEvent->startDate);
                        case 'endDate':
                            $value = '';
                            if ($calendarEvent->startDate != $calendarEvent->endDate) {
                                $value = $calendarEvent->endDate ? date($dateFormat, $calendarEvent->endDate) : false;
                                if ($value) {
                                    $value = $this->getHtmlSkeleton('endDate', $GLOBALS['TL_LANG']['fe_c4g_reservation']['endDateEvent'], $value);
                                }
                            }

                            return $value;
                        case 'endDate_raw':
                            $value = '';
                            if ($calendarEvent->startDate != $calendarEvent->endDate) {
                                return $calendarEvent->endDate ? date($dateFormat, $calendarEvent->endDate) : '';
                            }

                            break;
                        case 'beginTime':
                            $value = $calendarEvent->startTime && date('H', $calendarEvent->startTime) != '00' ? date($timeFormat, $calendarEvent->startTime) : false;
                            if ($value) {
                                $value = $this->getHtmlSkeleton('beginTime', $GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeEvent'], $value . ' ' . $clock);
                            } else {
                                $value = '';
                            }

                            return $value;
                        case 'beginTime_raw':
                            return $calendarEvent->startTime && date('H', $calendarEvent->startTime) != '00' ? date($timeFormat, $calendarEvent->startTime) : '';
                        case 'endTime':
                            $value = '';
                            if (!$calendarEvent->endDate || ($calendarEvent->startDate == $calendarEvent->endDate)) {
                                $value = $calendarEvent->startTime < $calendarEvent->endTime ? date($timeFormat, $calendarEvent->endTime) : false;
                                if ($value) {
                                    $value = $this->getHtmlSkeleton('endTime', $GLOBALS['TL_LANG']['fe_c4g_reservation']['endTimeEvent'], $value . $clock);
                                }
                            }

                            return $value;
                        case 'endTime_raw':
                            $value = '';
                            if (!$calendarEvent->endDate || ($calendarEvent->startDate == $calendarEvent->endDate)) {
                                return $calendarEvent->endTime ? date($timeFormat, $calendarEvent->endTime) : '';
                            }

                            return $value;
                        case 'title':
                            $value = $calendarEvent->title;
                            if ($value) {
                                $value = $this->getHtmlSkeleton('title', $GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object_event'], $value);
                            }

                            return $value ? $value : '';
                        case 'title_raw':
                            return $calendarEvent->title;
                        case 'location':
                            $value = $calendarEvent->location;
                            if ($value) {
                                $value = $this->getHtmlSkeleton('location', $GLOBALS['TL_LANG']['fe_c4g_reservation']['eventlocation'], $value);
                            }

                            return $value;
                        case 'location_raw':
                            return $calendarEvent->location;
                        case 'eventlocation':
                            $locationId = $reservationEventObject->location;
                            if ($locationId) {
                                $locationElement = $this->db->prepare("SELECT name FROM $tableLocation WHERE id=$locationId")
                                    ->execute()->fetchAssoc();

                                if ($locationElement) {
                                    $result = $locationElement['name'];
                                }

                                return $result ? $this->getHtmlSkeleton('eventlocation', $GLOBALS['TL_LANG']['fe_c4g_reservation']['eventlocation'], $result) : '';
                            }

                            break;
                        case 'eventlocation_raw':
                            $locationId = $reservationEventObject->location;
                            if ($locationId) {
                                $locationElement = $this->db->prepare("SELECT name FROM $tableLocation WHERE id=$locationId")
                                    ->execute()->fetchAssoc();

                                if ($locationElement) {
                                    $result = $locationElement['name'];
                                }

                                return $result ? $result : '';
                            }

                            break;
                        case 'city':
                            $locationId = $reservationEventObject->location;
                            if ($locationId) {
                                $locationElement = $this->db->prepare("SELECT contact_city FROM $tableLocation WHERE id=$locationId")
                                    ->execute()->fetchAssoc();

                                if ($locationElement) {
                                    $result = $locationElement['contact_city'];
                                }

                                return $result ? $this->getHtmlSkeleton('city', $GLOBALS['TL_LANG']['fe_c4g_reservation']['eventlocation'], $result) : '';
                            }

                            break;
                        case 'city_raw':
                            $locationId = $reservationEventObject->location;
                            if ($locationId) {
                                $locationElement = $this->db->prepare("SELECT contact_city FROM $tableLocation WHERE id=$locationId")
                                    ->execute()->fetchAssoc();

                                if ($locationElement) {
                                    $result = $locationElement['contact_city'];
                                }

                                return $result ? $result : '';
                            }

                            break;
                        case 'address':
                            $value = $calendarEvent->address;
                            if ($value) {
                                $value = $this->getHtmlSkeleton('address', $GLOBALS['TL_LANG']['fe_c4g_reservation']['eventaddress'], $value);
                            }

                            return $value;
                        case 'address_raw':
                            return $calendarEvent->address;
                        case 'eventaddress':
                            $locationId = $reservationEventObject->location;
                            if ($locationId) {
                                $locationElement = $this->db->prepare("SELECT contact_street,contact_postal,contact_city FROM $tableLocation WHERE id=$locationId")
                                    ->execute()->fetchAssoc();

                                if ($locationElement && $locationElement['contact_street'] && $locationElement['contact_postal'] && $locationElement['contact_city']) {
                                    $result = $locationElement['contact_street'] . ', ' . $locationElement['contact_postal'] . ' ' . $locationElement['contact_city'];
                                }

                                return $result ? $this->getHtmlSkeleton('eventaddress', $GLOBALS['TL_LANG']['fe_c4g_reservation']['eventaddress'], $result) : '';
                            }

                            break;
                        case 'eventaddress_raw':
                            $locationId = $reservationEventObject->location;
                            if ($locationId) {
                                $locationElement = $this->db->prepare("SELECT contact_street,contact_postal,contact_city FROM $tableLocation WHERE id=$locationId")
                                    ->execute()->fetchAssoc();

                                if ($locationElement && $locationElement['contact_street'] && $locationElement['contact_postal'] && $locationElement['contact_city']) {
                                    $result = $locationElement['contact_street'] . ', ' . $locationElement['contact_postal'] . ' ' . $locationElement['contact_city'];
                                }

                                return $result ? $result : '';
                            }

                            break;
                        case 'included':
                            $includedParams = $this->db->prepare('SELECT included_params FROM tl_c4g_reservation_type WHERE id = ?')
                                ->execute($reservationEventObject->reservationType)->fetchAssoc();
                            $params = $includedParams ? unserialize($includedParams['included_params']) : [];
                            $result = '';
                            foreach ($params as $param) {
                                $includedParam = C4gReservationParamsModel::findByPk($param);
                                if ($includedParam && $includedParam->caption) {
                                    $result = $result ? $result . ', ' . $includedParam->caption : $includedParam->caption;
                                }
                            }

                            return $result ? $this->getHtmlSkeleton('includedParams', /*$GLOBALS['TL_LANG']['fe_c4g_reservation']['includedParams']*/'', $result) : '';
                        case 'included_raw':
                            $includedParams = $this->db->prepare('SELECT included_params FROM tl_c4g_reservation_type WHERE id = ?')
                                ->execute($reservationEventObject->reservationType)->fetchAssoc();
                            $params = $includedParams ? unserialize($includedParams['included_params']) : [];
                            $includedParamsArr = [];
                            foreach ($params as $param) {
                                $includedParam = C4gReservationParamsModel::findByPk($param);
                                if ($includedParam && $includedParam->caption) {
                                    $includedParamsArr[$param] = $includedParam->caption;
                                }
                            }

                            return serialize($includedParamsArr);
                        case 'additional':
                            $additionalParams = $this->db->prepare('SELECT additional_params FROM tl_c4g_reservation_type WHERE id = ?')
                                ->execute($reservationEventObject->reservationType)->fetchAssoc();
                            $params = $additionalParams ? unserialize($additionalParams['additional_params']) : [];
                            $result = '';
                            foreach ($params as $param) {
                                $additionalParam = C4gReservationParamsModel::findByPk($param);
                                if ($additionalParam && $additionalParam->caption) {
                                    $result = $result ? $result . ', ' . $additionalParam->caption : $additionalParam->caption;
                                }
                            }

                            return $result ? $this->getHtmlSkeleton('additionalParams', /*$GLOBALS['TL_LANG']['fe_c4g_reservation']['additionalParams']*/'', $result) : '';
                        case 'additional_raw':
                            $additionalParams = $this->db->prepare('SELECT additional_params FROM tl_c4g_reservation_type WHERE id = ?')
                                ->execute($reservationEventObject->reservationType)->fetchAssoc();
                            $params = unserialize($additionalParams);
                            $additionalParamsArr = [];
                            foreach ($params as $param) {
                                $additionalParam = C4gReservationParamsModel::findByPk($param);
                                if ($additionalParam && $additionalParam->caption) {
                                    $additionalParamsArr[$param] = $additionalParam->caption;
                                }
                            }

                            return serialize($additionalParamsArr);
                    }
                }
            }
        } elseif ($arrSplit && (($arrSplit[0] == 'c4gspeaker')) && isset($arrSplit[1])) {
            $speakerId = $arrSplit[1];
            $key = $arrSplit[2];
            $tableSpeaker = 'tl_c4g_reservation_event_speaker';

            $speakerObject = $this->db->prepare("SELECT * FROM $tableSpeaker WHERE id=?")
                ->limit(1)
                ->execute($speakerId);

            if ($speakerObject->numRows) {
                System::loadLanguageFile('fe_c4g_reservation');
                $dateFormat = $GLOBALS['TL_CONFIG']['dateFormat'];
                $datimFormat = $GLOBALS['TL_CONFIG']['datimFormat'];
                $timeFormat = $GLOBALS['TL_CONFIG']['timeFormat'];

                switch ($key) {
                    case 'check':
                        return true;
                    case 'name':
                        $speakerStr = $this->getHtmlSkeleton($key, '', $speakerObject->title ? $speakerObject->title . ' ' . $speakerObject->firstname . ' ' . $speakerObject->lastname : $speakerObject->firstname . ' ' . $speakerObject->lastname, 'c4g_speaker_details');

                        return $speakerStr;
                    case 'zipAndCity':
                        $cityStr = $this->getHtmlSkeleton($key, '', $speakerObject->postal . ' ' . $speakerObject->city, 'c4g_speaker_details');

                        return $cityStr;
                    case 'website':
                        $websiteStr = $speakerObject->website;
                        if ($websiteStr && strpos($websiteStr, 'http') === false) {
                            $websiteStr = 'https://' . $websiteStr;
                        }
                        $websiteStr = $this->getHtmlSkeleton($key, '', '<a rel="noopener" target="_blank" href="' . $websiteStr . '" title="' . $speakerObject->website . '" itemprop="url">' . $speakerObject->website . '</a>', 'c4g_speaker_details');

                        return $websiteStr;
                    case 'email':
                        $emailStr = $this->getHtmlSkeleton($key, '', $speakerObject->email, 'c4g_speaker_details'); //ToDo link
                        return $emailStr;
                    case 'photo':
                        $uuid = $speakerObject->photo;
                        if ($uuid) {
                            if (StringHelper::isBinary($uuid)) {
                                $uuid = StringUtil::binToUuid($uuid);
                            }

                            return $this->getHtmlSkeleton($key, '', Controller::replaceInsertTags("{{image::$uuid?height=400&mode=proportional&class=img-fluid}}"), 'c4g_speaker_details');
                        }

                        break;
                    default:
                        if ($speakerObject->$key) {
                            return $this->getHtmlSkeleton($key, '', $speakerObject->$key, 'c4g_speaker_details');
                        }
                }
            }
        }

        return false;
    }
}
