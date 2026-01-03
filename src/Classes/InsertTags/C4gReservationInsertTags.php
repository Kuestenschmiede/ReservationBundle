<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @author con4gis contributors (see "authors.md")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */
namespace con4gis\ReservationBundle\Classes\InsertTags;

use con4gis\CoreBundle\Classes\C4GUtils;
use con4gis\CoreBundle\Classes\Helper\StringHelper;
use con4gis\ReservationBundle\Classes\Models\C4gReservationParamsModel;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationHandler;
use Contao\Controller;
use Contao\Database;
use Contao\Input;
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
            $this->db = Database::getInstance();
        }
    }

    /**
     * @param $fieldname
     * $param $label
     * @param $value
     * @param string $className
     */
    private function getHtmlSkeleton($fieldname, $label, $value, $className = 'c4g_reservation_details', $withValueSpan = true)
    {
        $result = '';
        if ($value) {
            if ($withValueSpan) {
                $result = '<div class="' . $className . ' ' . $className . '_' . $fieldname . '">' .
                    '<label class="' . $className . '_label" for="' . $className . '_value">' . $label . '</label>' .
                    '<span class="' . $className . '_value ' . $fieldname . '">' . $value . '</span></div>';
            } else {
                $result = '<div class="' . $className . ' ' . $className . '_' . $fieldname . '">' .
                    '<label class="' . $className . '_label" for="' . $className . '_value">' . $label . '</label>' .
                    $value . '</div>';
            }
        }

        return $result;
    }

    //ToDo find central point for saving state to db
    /**
     * @param $reservationEventObject
     * @param $calendarEvent
     */
    private function getState($id, $max, $minReservationDay, $reservationType, $calendarEvent)
    {
        $result = 0;
//        $id = $reservationEventObject->pid;
//        $max = $reservationEventObject->maxParticipants;

        $today = date('Y.m.d', time());
        $startdate = $calendarEvent->startDate ? date('Y.m.d', $calendarEvent->startDate) : false;
        $minReservationDates =  date('Y.m.d', time() + ($minReservationDay * 86400));
        if (!$reservationType || ($startdate && $startdate < $today) || (($calendarEvent->startTime &&
                    ($calendarEvent->startTime < time())) && ($startdate && $startdate == $today))) {
            $result = 3;
        } elseif ($id && $max > 0) {
            $tableReservation = 'tl_c4g_reservation';
            $capacitySum = 0;
            $participants = Database::getInstance()->prepare(
                "SELECT SUM(tl_c4g_reservation.desiredCapacity) AS 'capacitySum' FROM tl_c4g_reservation 
                        WHERE tl_c4g_reservation.reservation_object=? AND (tl_c4g_reservation.reservationObjectType=2) AND NOT tl_c4g_reservation.cancellation = '1'")->execute($id)->fetchAssoc();
            if ($participants && is_array($participants)) {
                $capacitySum = $participants['capacitySum'] ?: 0;
            }

//            $reservationType = $reservationEventObject->reservationType;
            if ($reservationType) {
                $tableReservationType = 'tl_c4g_reservation_type';
                $reservationType = $this->db->prepare("SELECT almostFullyBookedAt FROM $tableReservationType WHERE `id`=?")
                    ->limit(1)
                    ->execute($reservationType);
                $almostFullyBookedAt = $reservationType && $reservationType->almostFullyBookedAt ? $reservationType->almostFullyBookedAt : 0;

                if ($minReservationDay && ($minReservationDates >= $startdate)){
                    $result = 3;
                }else{
                    $percent = number_format($capacitySum ? ($capacitySum / $max) * 100 : 0,0);
                    if ($percent >= 100) {
                        $result = 3;
                    } elseif ($almostFullyBookedAt && ($percent >= $almostFullyBookedAt)) {
                        $result = 2;
                    } else {
                        $result = 1;
                    }
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

        $isEvent = $arrSplit && (($arrSplit[0] == 'c4gevent'));
        $isObject = $arrSplit && (($arrSplit[0] == 'c4gobj'));

        $tableEventObject = 'tl_c4g_reservation_event';
        $tableCalendarEvent = 'tl_calendar_events';
        $tableSettings = 'tl_c4g_settings';
        $tableAudience = 'tl_c4g_reservation_event_audience';
        $tableSpeaker = 'tl_c4g_reservation_event_speaker';
        $tableTopic = 'tl_c4g_reservation_event_topic';
        $tableLocation = 'tl_c4g_reservation_location';
        $tableObject = 'tl_c4g_reservation_object';

        if (($isEvent || $isObject) && isset($arrSplit[1])) {
            if (count($arrSplit) == 2) {
                $key = $arrSplit[1];
                $eventId = Input::get('event') ?: 0;

                if ($isEvent) {
                    if ($eventId) {
                        $reservationObject = $this->db->prepare("SELECT * FROM $tableEventObject WHERE pid = ?")
                            ->execute($eventId)->fetchAllAssoc();
                    } else {
                        $reservationObject = $this->db->prepare("SELECT * FROM $tableEventObject")
                            ->execute()->fetchAllAssoc();
                    }
                } else {
                    $reservationObject = $this->db->prepare("SELECT * FROM $tableObject")
                        ->execute()->fetchAllAssoc();
                }

                if ($reservationObject) {
                    System::loadLanguageFile('fe_c4g_reservation');

                    switch ($key) {
                        case 'check':
                            return true;
                        case 'caption':
                            $result = '';
                            if ($eventId) {
                                $calendarEvent = $this->db->prepare("SELECT title FROM $tableCalendarEvent WHERE id = ?")
                                    ->execute($eventId)->fetchAssoc();
                                if ($calendarEvent) {
                                    $result = $calendarEvent['title'];
                                }
                            }
                            return $result;
                        case 'audience_raw':

                            $audienceIds = [];
                            foreach ($reservationObject as $object) {
                                if ($object['targetAudience']) {
                                    $audiences = StringUtil::deserialize($object['targetAudience']);
                                    foreach ($audiences as $audienceId) {
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
                                $audienceElements = $this->db->prepare("SELECT targetAudience FROM $tableAudience WHERE `id` IN $audiences")
                                    ->execute()->fetchAllAssoc();

                                foreach ($audienceElements as $audience) {
                                    $result[] = $audience['targetAudience'];
                                }

                                return $result ? serialize($result) : [];
                            }

                            break;
                        case 'speaker_raw':
                            $speakerIds = [];
                            foreach ($reservationObject as $object) {
                                if ($object['speaker']) {
                                    $speakers = StringUtil::deserialize($object['speaker']);
                                    foreach ($speakers as $speakerId) {
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
                                $speakerElements = $this->db->prepare("SELECT id,title,firstname,lastname,speakerForwarding FROM $tableSpeaker WHERE `id` IN $speakers")
                                    ->execute()->fetchAllAssoc();

                                foreach ($speakerElements as $speaker) {
                                    $speakerStr = $speaker['title'] ? $speaker['lastname'] . ', ' . $speaker['firstname'] . ', ' . $speaker['title'] : $speaker['lastname'] . ', ' . $speaker['firstname'];

                                    $result[] = $speakerStr;
                                }

                                return $result ? serialize($result) : [];
                            };

                            break;
                        case 'topic_raw':
                            $topicIds = [];
                            foreach ($reservationObject as $object) {
                                if ($object['topic']) {
                                    $topics = StringUtil::deserialize($object['topic']);
                                    foreach ($topics as $topicId) {
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
                                $topicElements = $this->db->prepare("SELECT topic FROM $tableTopic WHERE `id` IN $topics")
                                    ->execute()->fetchAllAssoc();

                                foreach ($topicElements as $topic) {
                                    $result[] = $topic['topic'];
                                }

                                return $result ? serialize($result) : [];
                            }

                            break;
                        case 'eventlocation_raw':
                            $locationIds = [];
                            foreach ($reservationObject as $object) {
                                $locationId = $object['location'];
                                if ($locationId) {
                                    $locationIds[$locationId] = $locationId;
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
                                $locationElements = $this->db->prepare("SELECT name FROM $tableLocation WHERE `id` IN $locations")
                                    ->execute()->fetchAllAssoc();

                                foreach ($locationElements as $location) {
                                    $result[] = $location['name'];
                                }

                                return $result ? serialize($result) : [];
                            }

                            break;
                        case 'city_raw':
                            $locationIds = [];
                            foreach ($reservationObject as $object) {
                                $locationId = $object['location'];
                                if ($locationId) {
                                    $locationIds[$locationId] = $locationId;
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
                                $locationElements = $this->db->prepare("SELECT contact_city FROM $tableLocation WHERE `id` IN $locations")
                                    ->execute()->fetchAllAssoc();

                                foreach ($locationElements as $location) {
                                    $result[] = $location['contact_city'];
                                }

                                return $result ? serialize($result) : [];
                            }

                            break;
                        case 'included_raw':
                            $ids = [];

                            foreach ($reservationObject as $object) {
                                if ($isEvent) {
                                    $ids[] = $reservationObject->reservationType;
                                } else if ($reservationObject->viewableTypes) {
                                    $idArr = StringUtil::deserialize($reservationObject->viewableTypes);
                                    $ids = array_merge($ids, $idArr);
                                }
                            }

                            $ids = explode(',',$ids);
                            $ids = array_values(array_filter((array) $ids, static function ($v) {
                                return $v !== null && $v !== '';
                            }));
                            if (!empty($ids)) {
                                $in = C4GUtils::buildInString($ids);
                                $stmt = $this->db->prepare('SELECT included_params FROM tl_c4g_reservation_type WHERE `id` ' . $in);
                                $includedParams = $stmt->execute(...$ids)->fetchAssoc();
                            } else {
                                $includedParams = false;
                            }
                            $params = $includedParams && $includedParams['included_params'] ? StringUtil::deserialize($includedParams['included_params']) : [];
                            $includedParamsArr = [];
                            foreach ($params as $param) {
                                $includedParam = C4gReservationParamsModel::findByPk($param);
                                if ($includedParam && $includedParam->caption) {
                                    $includedParamsArr[$param] = $includedParam->caption;
                                }
                            }

                            return serialize($includedParamsArr);
                        case 'additional_raw':
                            $ids = [];
                            foreach ($reservationObject as $object) {
                                if ($isEvent) {
                                    $ids[] = $reservationObject->reservationType;
                                } else if ($reservationObject->viewableTypes) {
                                    $idArr = StringUtil::deserialize($reservationObject->viewableTypes);
                                    $ids = array_merge($ids, $idArr);
                                }
                            }

                            $ids = explode(',',$ids);
                            $ids = array_values(array_filter((array) $ids, static function ($v) {
                                return $v !== null && $v !== '';
                            }));
                            if (!empty($ids)) {
                                $in = C4GUtils::buildInString($ids);
                                $stmt = $this->db->prepare('SELECT additional_params FROM tl_c4g_reservation_type WHERE `id` ' . $in);
                                $additionalParams = $stmt->execute(...$ids)->fetchAssoc();
                            } else {
                                $additionalParams = false;
                            }
                            $params = $additionalParams ? StringUtil::deserialize($additionalParams['additional_params']) : [];
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
            } elseif (($isEvent || $isObject) && $arrSplit[1] && $arrSplit[2]) {
                $pid = $arrSplit[1];
                $key = $arrSplit[2];
                $startDate = key_exists(3,$arrSplit) ? $arrSplit[3] : 0;

                if ($isEvent) {
                    $reservationObject = $this->db->prepare("SELECT * FROM $tableEventObject WHERE `pid`=?")
                        ->limit(1)
                        ->execute($pid/* , 1 */);

                    $calendarEvent = $this->db->prepare("SELECT * FROM $tableCalendarEvent WHERE `id`=?")
                        ->limit(1)
                        ->execute($pid/* , 1 */);

                    $calendarObject = $calendarEvent && $calendarEvent->numRows ? $this->db->prepare("SELECT * FROM tl_calendar where `id`=?")
                        ->limit(1)
                        ->execute($calendarEvent->pid/* , 1 */) : false;
                } else {
                    $reservationObject = $this->db->prepare("SELECT * FROM $tableObject WHERE `id`=?")
                        ->limit(1)
                        ->execute($pid/* , 1 */);
                }

                //ToDo with default objects!!!
                if ((($reservationObject && $reservationObject->numRows) || ($calendarObject && $calendarObject->numRows)) &&  $calendarEvent->numRows) {
                    System::loadLanguageFile('fe_c4g_reservation');
                    $dateFormat = $GLOBALS['TL_CONFIG']['dateFormat'];
                    $datimFormat = $GLOBALS['TL_CONFIG']['datimFormat'];
                    $timeFormat = $GLOBALS['TL_CONFIG']['timeFormat'];

                    $maxParticipants = $reservationObject ? $reservationObject->maxParticipants : $calendarObject->maxParticipants;
                    $minReservationDay = $reservationObject ? $reservationObject->min_reservation_day : $calendarObject->min_reservation_day;
                    $reservationType = $reservationObject ? $reservationObject->reservationType : $calendarObject->reservationType;

                    $clock = '';
                    if (!strpos($timeFormat, 'A')) {
                        $clock = ' ' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['clock'];
                    }

                    $state = $this->getState($pid, $maxParticipants, $minReservationDay, $reservationType, $calendarEvent);

                    switch ($key) {
                        case 'check':
                            return true;
                        case 'caption':
                            return $calendarEvent ? $calendarEvent->title : $reservationObject->caption; //ToDo check
                        case 'tlState':
                            if ($state) {
                                switch ($state) {
                                    case '1':
                                        return $this->getHtmlSkeleton(
                                            'state',
                                            $GLOBALS['TL_LANG']['fe_c4g_reservation']['state'],
                                            '<div class="c4g_reservation_details_value state"></div>'.
                                            '<div class="c4g_reservation_details_value state"></div>'.
                                            '<div class="c4g_reservation_details_value state c4g_reservation_state_green" title="'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['state_green'].'"><span class="invisible">'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['state_green'].'</span></div>',
                                            'c4g_reservation_details',
                                            false
                                        );
                                    case '2':
                                        return $this->getHtmlSkeleton(
                                            'state',
                                            $GLOBALS['TL_LANG']['fe_c4g_reservation']['state'],
                                            '<div class="c4g_reservation_details_value state"></div>'.
                                            '<div class="c4g_reservation_details_value state c4g_reservation_state_orange" title="'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['state_orange'].'"><span class="invisible">'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['state_orange'].'</span></div>'.
                                            '<div class="c4g_reservation_details_value state"></div>',
                                            'c4g_reservation_details',
                                            false
                                        );
                                    case '3':
                                        return $this->getHtmlSkeleton(
                                            'state',
                                            $GLOBALS['TL_LANG']['fe_c4g_reservation']['state'],
                                            '<div class="c4g_reservation_details_value state c4g_reservation_state_red" title="'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['state_red'].'"><span class="invisible">'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['state_red'].'</span></div>'.
                                            '<div class="c4g_reservation_details_value state"></div>'.
                                            '<div class="c4g_reservation_details_value state"></div>',
                                            'c4g_reservation_details',
                                            false
                                        );
                                }
                            }

                            return '';
                        case 'state':
                            if ($state) {
                                switch ($state) {
                                    case '1':
                                        return $this->getHtmlSkeleton(
                                            'state',
                                            $GLOBALS['TL_LANG']['fe_c4g_reservation']['state_green'],
                                            '<img class="c4g_reservation_state c4g_reservation_state_green img-fluid" loading="lazy" height="32px" width="32px" src="bundles/con4gisreservation/images/circle_green.svg" alt="' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['state_green'] . '" title="' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['state_green'] . '">'
                                        );
                                    case '2':
                                        return $this->getHtmlSkeleton(
                                            'state',
                                            $GLOBALS['TL_LANG']['fe_c4g_reservation']['state_orange'],
                                            '<img class="c4g_reservation_state c4g_reservation_state_orange img-fluid" loading="lazy" height="32px" width="32px" src="bundles/con4gisreservation/images/circle_orange.svg" alt="' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['state_orange'] . '" title="' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['state_orange'] . '">'
                                        );
                                    case '3':
                                        return $this->getHtmlSkeleton(
                                            'state',
                                            $GLOBALS['TL_LANG']['fe_c4g_reservation']['state_red'],
                                            '<img class="c4g_reservation_state c4g_reservation_state_red img-fluid" loading="lazy" height="32px" width="32px" src="bundles/con4gisreservation/images/circle_red.svg" alt="' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['state_red'] . '" title="' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['state_red'] . '">'
                                        );
                                }
                            }

                            return '';
                        case 'state_raw':
                            return $state;
                        case 'headline':
                            return '<div class="c4g_reservation_details_headline">' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['detailsHeaadline'] . '</div>';
                        case 'headline_raw':
                            return $GLOBALS['TL_LANG']['fe_c4g_reservation']['detailsHeaadline'];
                        case 'button':
                            if ($state !== 3) {
                                $calendarId = $calendarEvent->pid;
                                $utl = '';
                                $buttonCaption = '';

                                if ($reservationObject->reservationForwarding) {
                                    $url = C4GUtils::replaceInsertTags('{{link_url::' . $reservationObject->reservationForwarding . '}}');
                                }

                                if ($reservationObject->reservationForwardingButtonCaption) {
                                    $buttonCaption = $reservationObject->reservationForwardingButtonCaption;
                                }

                                if ($calendarId && (!$url || !$buttonCaption)) {
                                    $calendar = $this->db->prepare("SELECT reservationForwarding, reservationForwardingButtonCaption FROM tl_calendar WHERE id=?")
                                        ->limit(1)
                                        ->execute($calendarId);
                                    if ($calendar) {
                                        if ($calendar->numRows) {
                                            if (!$url && $calendar->reservationForwarding) {
                                                // $url = C4GUtils::replaceInsertTags('{{link_url::' . $calendar->reservationForwarding . '}}');
                                                $url = System::getContainer()->get('contao.insert_tag.parser')->replace('{{link_url::' . $calendar->reservationForwarding . '}}');
                                            }

                                            if (!$buttonCaption) {
                                                $buttonCaption = $calendar->reservationForwardingButtonCaption;
                                            }
                                        }
                                    }
                                }

                                if (!$url || !$buttonCaption)
                                $settings = $this->db->prepare("SELECT reservationForwarding, reservationForwardingButtonCaption FROM $tableSettings")
                                    ->limit(1)
                                    ->execute();
                                if ($settings->numRows) {
                                    if ($settings->reservationForwarding) {
                                        $url = $url ?: C4GUtils::replaceInsertTags('{{link_url::' . $settings->reservationForwarding . '}}');
                                    }
                                    $buttonCaption = $buttonCaption ?: $settings->reservationForwardingButtonCaption;
                                }

                                $buttonCaption = $buttonCaption ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['eventForwardingButtonText'];

                                if ($url) {
                                    if ($startDate) {
                                        return '<a class="c4g_reservation_details_book-button c4g__btn c4g__btn-primary" href="' . $url . '?event=' . $pid . '&date=' . $startDate . '" title="'.$buttonCaption.'" itemprop="url">' . $buttonCaption . '</a>';
                                    } else {
                                        return '<a class="c4g_reservation_details_book-button c4g__btn c4g__btn-primary" href="' . $url . '?event=' . $pid . '" title="'.$buttonCaption.'" itemprop="url">' . $buttonCaption . '</a>';
                                    }
                                }
                            }

                            return '';
                        case 'lon':
                            return $calendarEvent->loc_geox;
                        case 'lat':
                            return $calendarEvent->loc_geoy;
                        case 'number':
                            $value = $reservationObject ? $reservationObject->number : '';
                            if ($value) {
                                $value = $this->getHtmlSkeleton('eventnumber', $GLOBALS['TL_LANG']['fe_c4g_reservation']['eventnumber'], $value);
                            }

                            return $value;
                        case 'number_raw':
                            return $reservationObject ? $reservationObject->number : '';
                        case 'audience':
                            if (($reservationObject && $reservationObject->targetAudience) || ($calendarObject && $calendarObject->reservationTargetAudience)) {
                                $targetAudience = $reservationObject && $reservationObject->targetAudience ? $reservationObject->targetAudience : $calendarObject->reservationTargetAudience;
                                $audienceIds = StringUtil::deserialize($targetAudience);
                                if ($audienceIds && count($audienceIds) > 0) {
                                    $audiences = '(' ;
                                    foreach ($audienceIds as $key => $audienceId) {
                                        $audiences .= "\"$audienceId\"";
                                        if (!(array_key_last($audienceIds) === $key)) {
                                            $audiences .= ',';
                                        }
                                    }
                                    $audiences .= ')';
                                    $audienceElements = $this->db->prepare("SELECT targetAudience FROM $tableAudience WHERE `id` IN $audiences")
                                        ->execute()->fetchAllAssoc();

                                    $result = '';
                                    foreach ($audienceElements as $audience) {
                                        $result = $result ? $result . ', ' . $audience['targetAudience'] : $audience['targetAudience'];
                                    }
                                }
                            }

                            return $result ? $this->getHtmlSkeleton('targetAudience', $GLOBALS['TL_LANG']['fe_c4g_reservation']['targetAudience'], $result) : '';

                            break;
                        case 'audience_raw':
                            if (($reservationObject && $reservationObject->targetAudience) || ($calendarObject && $calendarObject->reservationTargetAudience)) {
                                $targetAudience = $reservationObject && $reservationObject->targetAudience ? $reservationObject->targetAudience : $calendarObject->reservationTargetAudience;
                                $audienceIds = StringUtil::deserialize($targetAudience);
                                if ($audienceIds && count($audienceIds) > 0) {
                                    $audiences = '(' ;
                                    foreach ($audienceIds as $key => $audienceId) {
                                        $audiences .= "\"$audienceId\"";
                                        if (!(array_key_last($audienceIds) === $key)) {
                                            $audiences .= ',';
                                        }
                                    }
                                    $audiences .= ')';
                                    $audienceElements = $this->db->prepare("SELECT targetAudience FROM $tableAudience WHERE `id` IN $audiences")
                                        ->execute()->fetchAllAssoc();

                                    $result = [];
                                    foreach ($audienceElements as $audience) {
                                        $result[] = $audience['targetAudience'];
                                    }
                                }
                            }

                            return $result ? serialize($result) : '';
                        case 'price':
                            $price = $reservationObject && $reservationObject->price ? $reservationObject->price : $calendarObject->price;
                            return C4gReservationHandler::formatPrice($price);
                        case 'speakerId':
                            if (($reservationObject && $reservationObject->speaker) || ($calendarObject && $calendarObject->reservationSpeaker)) {
                                $speaker = $reservationObject && $reservationObject->speaker ? $reservationObject->speaker : $calendarObject->reservationSpeaker;
                                $speakerIds = StringUtil::deserialize($speaker);
                            }
                            return $speakerIds ? $speakerIds[0] : 0;
                        case 'speakerIds':
                            $speakerIds = '';
                            if (($reservationObject && $reservationObject->speaker) || ($calendarObject && $calendarObject->reservationSpeaker)) {
                                $speaker = $reservationObject && $reservationObject->speaker ? $reservationObject->speaker : $calendarObject->reservationSpeaker;
                                $speakerIds = explode(',', StringUtil::deserialize($speaker));
                            }
                            return $speakerIds;
                        case 'speaker':
                            if (($reservationObject && $reservationObject->speaker) || ($calendarObject && $calendarObject->reservationSpeaker)) {
                                $speaker = $reservationObject && $reservationObject->speaker ? $reservationObject->speaker : $calendarObject->reservationSpeaker;
                                $speakerIds = StringUtil::deserialize($speaker);
                                if ($speakerIds && count($speakerIds) > 0) {
                                    $speakers = '(' ;
                                    foreach ($speakerIds as $key => $speakerId) {
                                        $speakers .= "\"$speakerId\"";
                                        if (!(array_key_last($speakerIds) === $key)) {
                                            $speakers .= ',';
                                        }
                                    }
                                    $speakers .= ')';
                                    $speakerElements = $this->db->prepare("SELECT id,alias,title,firstname,lastname,speakerForwarding FROM $tableSpeaker WHERE `id` IN $speakers")
                                        ->execute()->fetchAllAssoc();

                                    $result = '';
                                    foreach ($speakerElements as $speaker) {
                                        $speakerStr = $speaker['title'] ? $speaker['title'] . ' ' . $speaker['firstname'] . ' ' . $speaker['lastname'] : $speaker['firstname'] . ' ' . $speaker['lastname'];
                                        if ($speakerStr && $speaker['speakerForwarding']) {
                                            $url = C4GUtils::replaceInsertTags('{{link_url::' . $speaker['speakerForwarding'] . '}}');
                                            if ($url) {
                                                $speakerAlias = $speaker['id'];
                                                if ($speaker['alias']) {
                                                    $speakerAlias = $speaker['alias'];
                                                }

                                                $speakerStr = '<a href="' . $url . '?speaker=' . $speakerAlias . '" title="' . $speakerStr . '" itemprop="url">' . $speakerStr . '</a>';
                                            }
                                        }

                                        $result = $result ? $result . ', ' . $speakerStr : $speakerStr;
                                    }

                                    return $result ? $this->getHtmlSkeleton('speaker', $GLOBALS['TL_LANG']['fe_c4g_reservation']['speaker'], $result) : '';
                                };
                            }

                            return '';
                        case 'speaker_raw':
                            if (($reservationObject && $reservationObject->speaker) || ($calendarObject && $calendarObject->reservationSpeaker)) {
                                $speaker = $reservationObject && $reservationObject->speaker ? $reservationObject->speaker : $calendarObject->reservationSpeaker;
                                $speakerIds = StringUtil::deserialize($speaker);
                                if ($speakerIds && count($speakerIds) > 0) {
                                    $speakers = '(' ;
                                    foreach ($speakerIds as $key => $speakerId) {
                                        $speakers .= "\"$speakerId\"";
                                        if (!(array_key_last($speakerIds) === $key)) {
                                            $speakers .= ',';
                                        }
                                    }
                                    $speakers .= ')';
                                    $speakerElements = $this->db->prepare("SELECT id,title,firstname,lastname,speakerForwarding FROM $tableSpeaker WHERE `id` IN $speakers")
                                        ->execute()->fetchAllAssoc();
                                    $result = [];
                                    foreach ($speakerElements as $speaker) {
                                        $speakerStr = $speaker['title'] ? $speaker['lastname'] . ', ' . $speaker['firstname'] . ', ' . $speaker['title'] : $speaker['lastname'] . ', ' . $speaker['firstname'];

                                        $result[] = $speakerStr;
                                    }

                                    return $result ? serialize($result) : '';
                                };
                            }

                            return '';
                        case 'topic':
                            if (($reservationObject && $reservationObject->topic) || ($calendarObject && $calendarObject->reservationTopic)) {
                                $topic = $reservationObject && $reservationObject->topic ? $reservationObject->topic : $calendarObject->reservationTopic;
                                $topicIds = StringUtil::deserialize($topic);
                                if ($topicIds && count($topicIds) > 0) {
                                    $topics = '(' ;
                                    foreach ($topicIds as $key => $topicId) {
                                        $topics .= "\"$topicId\"";
                                        if (!(array_key_last($topicIds) === $key)) {
                                            $topics .= ',';
                                        }
                                    }
                                    $topics .= ')';
                                    $topicElements = $this->db->prepare("SELECT topic FROM $tableTopic WHERE `id` IN $topics")
                                        ->execute()->fetchAllAssoc();

                                    $result = '';
                                    foreach ($topicElements as $topic) {
                                        $result = $result ? $result . ', ' . $topic['topic'] : $topic['topic'];
                                    }

                                    return $result ? $this->getHtmlSkeleton('topic', $GLOBALS['TL_LANG']['fe_c4g_reservation']['topic'], $result) : '';
                                }
                            }

                            return '';
                        case 'topic_raw':
                            if (($reservationObject && $reservationObject->topic) || ($calendarObject && $calendarObject->reservationTopic)) {
                                $topic = $reservationObject && $reservationObject->topic ? $reservationObject->topic : $calendarObject->reservationTopic;
                                $topicIds = StringUtil::deserialize($topic);
                                if ($topicIds && count($topicIds) > 0) {
                                    $topics = '(' ;
                                    foreach ($topicIds as $key => $topicId) {
                                        $topics .= "\"$topicId\"";
                                        if (!(array_key_last($topicIds) === $key)) {
                                            $topics .= ',';
                                        }
                                    }
                                    $topics .= ')';
                                    $topicElements = $this->db->prepare("SELECT topic FROM $tableTopic WHERE `id` IN $topics")
                                        ->execute()->fetchAllAssoc();

                                    $result = [];
                                    foreach ($topicElements as $topic) {
                                        $result[] = $topic['topic'];
                                    }

                                    return $result ? serialize($result) : '';
                                }
                            }

                            return '';
                        case 'beginDate':
                            if ($startDate) {
                                $value = is_numeric($startDate) ? date($dateFormat, intval($startDate)) : $startDate;
                            } else {
                                $value = date($dateFormat, $calendarEvent->startDate);
                            }
                            if ($value) {
                                $value = $this->getHtmlSkeleton('beginDate', $GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDateEvent'], $value);
                            }

                            return $value;
                        case 'beginDate_raw':
                            if ($startDate) {
                                return is_numeric($startDate) ? date($dateFormat, intval($startDate)) : $startDate;
                            } else {
                                return date($dateFormat, $calendarEvent->startDate);
                            }
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
                            if (($reservationObject && $reservationObject->location) || ($calendarObject && $calendarObject->reservationLocation)) {
                                $location = $reservationObject && $reservationObject->location ? $reservationObject->location : $calendarObject->reservationLocation;
                                $locationElement = $this->db->prepare("SELECT name FROM $tableLocation WHERE `id`= ?")
                                    ->execute($location)->fetchAssoc();

                                $result = '';
                                if ($locationElement) {
                                    $result = $locationElement['name'];
                                }

                                return $result ? $this->getHtmlSkeleton('eventlocation', $GLOBALS['TL_LANG']['fe_c4g_reservation']['eventlocation'], $result) : '';
                            }

                            break;
                        case 'eventlocation_raw':
                            if (($reservationObject && $reservationObject->location) || ($calendarObject && $calendarObject->reservationLocation)) {
                                $location = $reservationObject && $reservationObject->location ? $reservationObject->location : $calendarObject->reservationLocation;
                                $locationElement = $this->db->prepare("SELECT name FROM $tableLocation WHERE id=?")
                                    ->execute($location)->fetchAssoc();

                                $result = '';
                                if ($locationElement) {
                                    $result = $locationElement['name'];
                                }

                                return $result ? $result : '';
                            }

                            break;
                        case 'city':
                            if (($reservationObject && $reservationObject->location) || ($calendarObject && $calendarObject->reservationLocation)) {
                                $location = $reservationObject && $reservationObject->location ? $reservationObject->location : $calendarObject->reservationLocation;
                                $locationElement = $this->db->prepare("SELECT contact_city FROM $tableLocation WHERE `id`=?")
                                    ->execute($location)->fetchAssoc();

                                $result = '';
                                if ($locationElement) {
                                    $result = $locationElement['contact_city'];
                                }

                                return $result ? $this->getHtmlSkeleton('city', $GLOBALS['TL_LANG']['fe_c4g_reservation']['eventlocation'], $result) : '';
                            }

                            break;
                        case 'city_raw':
                            if (($reservationObject && $reservationObject->location) || ($calendarObject && $calendarObject->reservationLocation)) {
                                $location = $reservationObject && $reservationObject->location ? $reservationObject->location : $calendarObject->reservationLocation;
                                $locationElement = $this->db->prepare("SELECT contact_city FROM $tableLocation WHERE `id`=?")
                                    ->execute($location)->fetchAssoc();

                                $result = '';
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
                            if (($reservationObject && $reservationObject->location) || ($calendarObject && $calendarObject->reservationLocation)) {
                                $location = $reservationObject && $reservationObject->location ? $reservationObject->location : $calendarObject->reservationLocation;
                                $locationElement = $this->db->prepare("SELECT contact_street,contact_postal,contact_city FROM $tableLocation WHERE `id`=?")
                                    ->execute($location)->fetchAssoc();

                                $result = '';
                                if ($locationElement && $locationElement['contact_street'] && $locationElement['contact_postal'] && $locationElement['contact_city']) {
                                    $result = $locationElement['contact_street'] . ', ' . $locationElement['contact_postal'] . ' ' . $locationElement['contact_city'];
                                }

                                return $result ? $this->getHtmlSkeleton('eventaddress', $GLOBALS['TL_LANG']['fe_c4g_reservation']['eventaddress'], $result) : '';
                            }

                            break;
                        case 'eventaddress_raw':
                            if (($reservationObject && $reservationObject->location) || ($calendarObject && $calendarObject->reservationLocation)) {
                                $location = $reservationObject && $reservationObject->location ? $reservationObject->location : $calendarObject->reservationLocation;
                                $locationElement = $this->db->prepare("SELECT contact_street,contact_postal,contact_city FROM $tableLocation WHERE `id`=?")
                                    ->execute($location)->fetchAssoc();

                                $result = '';
                                if ($locationElement && $locationElement['contact_street'] && $locationElement['contact_postal'] && $locationElement['contact_city']) {
                                    $result = $locationElement['contact_street'] . ', ' . $locationElement['contact_postal'] . ' ' . $locationElement['contact_city'];
                                }

                                return $result ? $result : '';
                            }

                            break;
                        case 'included':
                            $reservationType = $reservationObject && $reservationObject->reservationType ? $reservationObject->reservationType : $calendarObject->reservationType;

                            $includedParams = $this->db->prepare('SELECT included_params FROM tl_c4g_reservation_type WHERE `id` = ?')
                                ->execute($reservationType)->fetchAssoc();
                            $params = $includedParams && $includedParams['included_params'] ? StringUtil::deserialize($includedParams['included_params']) : [];
                            $result = '';
                            foreach ($params as $param) {
                                $includedParam = C4gReservationParamsModel::findByPk($param);
                                if ($includedParam && $includedParam->caption) {
                                    $result = $result ? $result . ', ' . $includedParam->caption : $includedParam->caption;
                                }
                            }

                            return $result ? $this->getHtmlSkeleton('includedParams','', $result) : '';
                        case 'included_raw':
                            $reservationType = $reservationObject && $reservationObject->reservationType ? $reservationObject->reservationType : $calendarObject->reservationType;
                            $includedParams = $this->db->prepare('SELECT included_params FROM tl_c4g_reservation_type WHERE id = ?')
                                ->execute($reservationType)->fetchAssoc();
                            $params = $includedParams && $includedParams['included_params'] ? StringUtil::deserialize($includedParams['included_params']) : [];
                            $includedParamsArr = [];
                            foreach ($params as $param) {
                                $includedParam = C4gReservationParamsModel::findByPk($param);
                                if ($includedParam && $includedParam->caption) {
                                    $includedParamsArr[$param] = $includedParam->caption;
                                }
                            }

                            return serialize($includedParamsArr);
                        case 'additional':
                            $reservationType = $reservationObject && $reservationObject->reservationType ? $reservationObject->reservationType : $calendarObject->reservationType;
                            $additionalParams = $this->db->prepare('SELECT additional_params FROM tl_c4g_reservation_type WHERE id = ?')
                                ->execute($reservationType)->fetchAssoc();
                            $params = $additionalParams && $additionalParams['additional_params'] ? StringUtil::deserialize($additionalParams['additional_params']) : [];
                            $result = '';
                            foreach ($params as $param) {
                                $additionalParam = C4gReservationParamsModel::findByPk($param);
                                if ($additionalParam && $additionalParam->caption) {
                                    $result = $result ? $result . ', ' . $additionalParam->caption : $additionalParam->caption;
                                }
                            }

                            return $result ? $this->getHtmlSkeleton('additionalParams', /*$GLOBALS['TL_LANG']['fe_c4g_reservation']['additionalParams']*/'', $result) : '';
                        case 'additional_raw':
                            $reservationType = $reservationObject && $reservationObject->reservationType ? $reservationObject->reservationType : $calendarObject->reservationType;
                            $additionalParams = $this->db->prepare('SELECT additional_params FROM tl_c4g_reservation_type WHERE id = ?')
                                ->execute($reservationType)->fetchAssoc();
                            $params = $additionalParams ? StringUtil::deserialize($additionalParams) : [];
                            $additionalParamsArr = [];
                            foreach ($params as $param) {
                                $additionalParam = C4gReservationParamsModel::findByPk($param);
                                if ($additionalParam && $additionalParam->caption) {
                                    $additionalParamsArr[$param] = $additionalParam->caption;
                                }
                            }

                            return serialize($additionalParamsArr);
                    }
                } else {
                    return '';
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

                            return $this->getHtmlSkeleton($key, '', C4GUtils::replaceInsertTags("{{image::$uuid?height=400&mode=proportional&class=img-fluid}}"), 'c4g_speaker_details');
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
