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

use con4gis\ProjectsBundle\Classes\Dialogs\C4GBrickDialogParams;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GGalleryField;
use Contao\Controller;
use Contao\Database;
use Contao\Frontend;
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
     * @param string $itemProp
     */
    private function getHtmlSkeleton($fieldname, $label, $value, $itemProp = '') {
        $result = '';
        if ($value) {
            $result = '<p class="c4g_reservation_details c4g_reservation_details_'.$fieldname.'">'.
                '<label class="c4g_reservation_details_label" for="c4g_reservation_details_value">'.$label.'</label>'.
                '<span class="c4g_reservation_details_value '.$fieldname.'">'.$value.'</span></p>';
        }
        return $result;
    }

    //ToDo find central point for saving state to db
    /**
     * @param $reservationEventObject
     * @param $calendarEvent
     */
    private function getState($reservationEventObject)
    {
        $result = 0;
        $id = $reservationEventObject->pid;
        $max = $reservationEventObject->maxParticipants;
        if ($id && $max > 0) {
            $tableReservation = 'tl_c4g_reservation';
            $reservationObject = $this->db->prepare("SELECT COUNT(id) AS reservationCount, reservation_type FROM $tableReservation WHERE reservation_object = $id AND reservationObjectType = '2'")->execute()->fetchAllAssoc();;
            if ($reservationObject) {
                $reservationType = $reservationObject[0]['reservation_type'];
                $reservationCount = $reservationObject[0]['reservationCount'];

                if ($reservationType && $reservationCount > 0) {
                    $tableReservationType = 'tl_c4g_reservation_type';
                    $almostFullyBookedAt = $this->db->prepare("SELECT almostFullyBookedAt FROM $tableReservationType WHERE id=?")
                        ->limit(1)
                        ->execute($reservationType,1);

                    $percent = ($reservationCount / $max) * 100;
                    if ($percent > 100) {
                        $result = 3;
                    } else if ($percent > $almostFullyBookedAt) {
                        $result = 2;
                    } else {
                        $result = 1;
                    }
                }
            }
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
        if ($arrSplit && (($arrSplit[0] == 'c4gevent')) && isset($arrSplit[1]))
        {
            $pid = $arrSplit[1];
            $key = $arrSplit[2];

            if ($pid && $key) {
                $tableEventObject = 'tl_c4g_reservation_event';
                $tableCalendarEvent = 'tl_calendar_events';
                $tableSettings = 'tl_c4g_settings';
                $tableAudience = 'tl_c4g_reservation_event_audience';
                $tableSpeaker = 'tl_c4g_reservation_event_speaker';
                $tableTopic = 'tl_c4g_reservation_event_topic';

                $reservationEventObject = $this->db->prepare("SELECT * FROM $tableEventObject WHERE pid=?")
                    ->limit(1)
                    ->execute($pid,1);

                $calendarEvent = $this->db->prepare("SELECT * FROM $tableCalendarEvent WHERE id=?")
                    ->limit(1)
                    ->execute($pid,1);

                if ($reservationEventObject->numRows && $calendarEvent->numRows) {
                    System::loadLanguageFile('fe_c4g_reservation');
                    $dateFormat = $GLOBALS['TL_CONFIG']['dateFormat'];
                    $datimFormat = $GLOBALS['TL_CONFIG']['datimFormat'];
                    $timeFormat = $GLOBALS['TL_CONFIG']['timeFormat'];

                    switch($key) {
                        case 'check':
                            return true;
                        case 'state':
                            $state = $this->getState($reservationEventObject);
                            if ($state) {
                                switch($state) {
                                    case '1': return $this->getHtmlSkeleton('state', $GLOBALS['TL_LANG']['fe_c4g_reservation']['state'], '<img class="c4g_reservation_state c4g_reservation_state_green img-fluid" loading="lazy" height="32px" width="32px" src="bundles/con4gisreservation/images/circle_green.svg" alt="'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['state_green'].'" title="'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['state_green'].'">');
                                    case '2': return $this->getHtmlSkeleton('state', $GLOBALS['TL_LANG']['fe_c4g_reservation']['state'], '<img class="c4g_reservation_state c4g_reservation_state_green img-fluid" loading="lazy" height="32px" width="32px" src="bundles/con4gisreservation/images/circle_orange.svg" alt="'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['state_orange'].'" title="'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['state_orange'].'">');
                                    case '3': return $this->getHtmlSkeleton('state', $GLOBALS['TL_LANG']['fe_c4g_reservation']['state'], '<img class="c4g_reservation_state c4g_reservation_state_green img-fluid" loading="lazy" height="32px" width="32px" src="bundles/con4gisreservation/images/circle_red.svg" alt="'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['state_red'].'" title="'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['state_red'].'">');
                                }
                            }
                            return '';
                        case 'headline':
                            return '<div class=" c4g_reservation_details_headline">'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['detailsHeaadline'].'</div>';
                        case 'button':
                            $settings = $this->db->prepare("SELECT reservationForwarding FROM $tableSettings")
                                ->limit(1)
                                ->execute();
                            if ($settings->numRows && $settings->reservationForwarding) {
                                $url = Controller::replaceInsertTags("{{link_url::".$settings->reservationForwarding."}}");
                                if ($url) {
                                    return '<a href="'.$url.'?event='.$pid.'" title="Reservieren" itemprop="url">'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['eventForwardingButtonText'].'</a>';
                                }
                            }
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
                        case 'audience':
                            $audienceIds = unserialize($reservationEventObject->targetAudience);
                            if ($audienceIds && count($audienceIds) > 0) {
                                $audiences = "(" ;
                                foreach ($audienceIds as $key => $audienceId) {
                                    $audiences .= "\"$audienceId\"";
                                    if (!(array_key_last($audienceIds) === $key)) {
                                        $audiences .= ",";
                                    }
                                }
                                $audiences .= ")";
                                $audienceElements = $this->db->prepare("SELECT targetAudience FROM $tableAudience WHERE id IN $audiences")
                                    ->execute()->fetchAllAssoc();

                                foreach ($audienceElements as $audience) {
                                    $result = $result ? $result.', '.$audience['targetAudience'] : $audience['targetAudience'];
                                }

                                return $result ? $this->getHtmlSkeleton('targetAudience',$GLOBALS['TL_LANG']['fe_c4g_reservation']['targetAudience'],$result) : '';
                            }
                            break;
                        case 'speaker':
                            $speakerIds = unserialize($reservationEventObject->speaker);
                            if ($speakerIds && count($speakerIds) > 0) {
                                $speakers = "(" ;
                                foreach ($speakerIds as $key => $speakerId) {
                                    $speakers .= "\"$speakerId\"";
                                    if (!(array_key_last($speakerIds) === $key)) {
                                        $speakers .= ",";
                                    }
                                }
                                $speakers .= ")";
                                $speakerElements = $this->db->prepare("SELECT id,title,firstname,lastname,speakerForwarding FROM $tableSpeaker WHERE id IN $speakers")
                                    ->execute()->fetchAllAssoc();

                                foreach ($speakerElements as $speaker) {
                                    $speakerStr = $speaker['title'] ? $speaker['title'].' '.$speaker['firstname'].' '.$speaker['lastname'] : $speaker['firstname'].' '.$speaker['lastname'];
                                    if ($speakerStr && $speaker['speakerForwarding']) {
                                        $url = Controller::replaceInsertTags("{{link_url::" . $speaker['speakerForwarding'] . "}}");
                                        if ($url) {
                                            $speakerStr = '<a href="' . $url . '?speaker=' . $speaker['id'] . '" title="' . $speakerStr . '" itemprop="url">' . $speakerStr . '</a>';
                                        }
                                    }

                                    $result = $result ? $result.', '.$speakerStr : $speakerStr;
                                }

                                return $result ? $this->getHtmlSkeleton('speaker',$GLOBALS['TL_LANG']['fe_c4g_reservation']['speaker'],$result) : '';
                            };
                            break;
                        case 'topic';
                            $topicIds = unserialize($reservationEventObject->topic);
                            if ($topicIds && count($topicIds) > 0) {
                                $topics = "(" ;
                                foreach ($topicIds as $key => $topicId) {
                                    $topics .= "\"$topicId\"";
                                    if (!(array_key_last($topicIds) === $key)) {
                                        $topics .= ",";
                                    }
                                }
                                $topics .= ")";
                                $topicElements = $this->db->prepare("SELECT topic FROM $tableTopic WHERE id IN $topics")
                                    ->execute()->fetchAllAssoc();

                                foreach ($topicElements as $topic) {
                                    $result = $result ? $result.', '.$topic['topic'] : $topic['topic'];
                                }

                                return $result ? $this->getHtmlSkeleton('topic', $GLOBALS['TL_LANG']['fe_c4g_reservation']['topic'],$result) : '';
                            }
                            break;
                        case 'beginDate':
                            $value = date($dateFormat, $calendarEvent->startDate);
                            if ($value) {
                                $value = $this->getHtmlSkeleton('beginDate',$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDateEvent'],$value);
                            }
                            return $value;
                        case 'endDate':
                            $value = $calendarEvent->endDate ? date($dateFormat, $calendarEvent->endDate) : false;
                            if ($value) {
                                $value = $this->getHtmlSkeleton('endDate',$GLOBALS['TL_LANG']['fe_c4g_reservation']['endDateEvent'],$value);
                            }
                            return $value;
                        case 'beginTime':
                            $value = $calendarEvent->startTime ? date($timeFormat, $calendarEvent->startTime) : false;
                            if ($value) {
                                $value = $this->getHtmlSkeleton('beginTime',$GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeEvent'],$value.' '.$GLOBALS['TL_LANG']['fe_c4g_reservation']['clock']);
                            }
                            return $value;
                        case 'endTime':
                            $value = $calendarEvent->startTime < $calendarEvent->endTime ? date($timeFormat, $calendarEvent->endTime) : false;
                            if ($value) {
                                $value = $this->getHtmlSkeleton('endTime',$GLOBALS['TL_LANG']['fe_c4g_reservation']['endTimeEvent'],$value.' '.$GLOBALS['TL_LANG']['fe_c4g_reservation']['clock']);
                            }
                            return $value;
                        case 'title':
                            $value = $calendarEvent->title;
                            if ($value) {
                                $value = $this->getHtmlSkeleton('title',$GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object_event'],$value);
                            }
                            return $value;
                        case 'location':
                            $value = $calendarEvent->location;
                            if ($value) {
                                $value = $this->getHtmlSkeleton('location',$GLOBALS['TL_LANG']['fe_c4g_reservation']['eventlocation'],$value);
                            }
                            return $value;
                        case 'address':
                            $value = $calendarEvent->address;
                            if ($value) {
                                $value = $this->getHtmlSkeleton('address',$GLOBALS['TL_LANG']['fe_c4g_reservation']['eventaddress'],$value);
                            }
                            return $value;
                    }
                }
            }
        }

        return false;
    }
}
