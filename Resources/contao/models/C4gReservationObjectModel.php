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
namespace con4gis\ReservationBundle\Resources\contao\models;

use con4gis\CoreBundle\Classes\Helper\ArrayHelper;
use con4gis\CoreBundle\Resources\contao\models\C4gLogModel;
use con4gis\ReservationBundle\Classes\C4gReservationFrontendObject;
use Contao\StringUtil;

/**
 * Class C4gReservationObjectModel
 * @package c4g\projects
 */
class C4gReservationObjectModel extends \Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_c4g_reservation_object';

    private static function check($date)
    {
        if ($date != '') {
            return $date . ',';
        } else {
            return $date;
        }
    }

    public static function getWeekdayExclusionString($list)
    {
        $result = '';
        if ($list) {
            $weekdays = array();
            foreach ($list as $object) {
                $we = $object->getWeekdayExclusion();
                foreach ($we as $key => $value) {
                    $weekdays[$key] = ($value || $weekdays[$key]);
                }
            }

            foreach ($weekdays as $key => $value) {
                if ($value == false) {
                    $result = self::check($result) . $key;
                }
            }
        }

        return $result;
    }

    public static function getDateExclusionString($list)
    {
        $result = '';
        if ($list) {
            $dates = array();
            foreach ($list as $object) {
                $exclusionPeriods = $object->getDatesExclusion();

                foreach ($exclusionPeriods as $period) {
                    if ($period) {
                        $exclusionBegin = $period['date_exclusion'];
                        $exclusionEnd =  $period['date_exclusion_end'];

                        $current = $exclusionBegin;
                        while($current <= $exclusionEnd) {
                            $dates[] = $current;
                            $current = $current + 86400;
                        }
                    }
                }
            }

            foreach ($dates as $date) {
                //ToDo Datum prüfen
                //foreach ($date as $key => $value) {
                    if ($date) {
                        $result = self::check($result) . $date;
                    }
                //}
            }
        }

        return $result;
    }

    public static function isSunday($date)
    {

        $date = strtotime($date);
        if ($date && (date("w", $date) == 0)) {
            return true;
        } else {
            return false;
        }
    }

    public static function isMonday($date)
    {   
        $date = strtotime($date);
        if ($date && (date("w", $date) == 1)) {
            return true;
        } else {
            return false;
        }
    }

    public static function isTuesday($date)
    {
        $date = strtotime($date);;
        if ($date && (date("w", $date) == 2)) {
            return true;
        } else {
            return false;
        }
    }

    public static function isWednesday($date)
    {
        $date = strtotime($date);
        if ($date && (date("w", $date) == 3)) {
            return true;
        } else {
            return false;
        }
    }

    public static function isThursday($date)
    {
        $date = strtotime($date);
        if ($date && (date("w", $date) == 4)) {
            return true;
        } else {
            return false;
        }
    }

    public static function isFriday($date)
    {
        $date = strtotime($date);
        if ($date && (date("w", $date) == 5)) {
            return true;
        } else {
            return false;
        }
    }

    public static function isSaturday($date)
    {
        $date = strtotime($date);
        if ($date && (date("w", $date) == 6)) {
            return true;
        } else {
            return false;
        }
    }

    private static function addTime($list, $time, $obj)
    {
        if ($obj == -1) {
            /*foreach ($list as $key => $item) {
                //Ist der Termin schon in der Auflistung?
                if ($item['id'] == $time) {
                    return $list;
                }
            }*/

            //$key = '_' . $time;
            $key = $time;
            $list[$key] = array('id' => $time, 'name' => date($GLOBALS['TL_CONFIG']['timeFormat'], $time), 'object' => $obj);
        } else {
            foreach ($list as $key => $item) {
                //Ist der Termin schon in der Auflistung?
                if ($item['id'] == $time) {
                    $first_obj = $item['object'];
                    $rnd = rand(2, 4);

                    if (($first_obj != -1) && (($first_obj != $obj) && ($rnd % 2))) {
                        $list[$key]['object'] = $obj;
                    }                                      
                    return $list;
                }
            }

            //$key = $obj . $time;
            $key = $time;
            $list[$key] = array('id' => $time, 'name' => date($GLOBALS['TL_CONFIG']['timeFormat'], $time), 'object' => $obj);
        }

        return $list;
    }

    /**
     * @param $list
     */
    public static function getMinDate($objects)
    {
        $result = '';
        $firstmin = 0;
        $today = time();
        if ($objects) {

            foreach ($objects as $object) {
                $min = $object->getMinReservationDay();
                if ($min && ($min > $firstmin)) {
                    $firstmin = $min;
                }
            }

            return $today + ($firstmin * 3600 * 24);
        }
    }

    /**
     * @param $list
     */
    public static function getMaxDate($objects)
    {
        $result = '';
        $lastmax = 365;
        $today = time();
        if ($objects) {

            foreach ($objects as $object) {
                $max = intval($object->getMaxReservationDay());
              //  $max_str = ' +' . $max . ' days';

                if($max === 0){

                    $result = $today + (365 * 3600 * 24);
                }


                elseif ($max < $lastmax) {

                    $result = $today + ($max * 3600 * 24);

                }
                /*if ($max > $lastmax) */else{
                    $lastmax = $max;

                    $result = $today + ($lastmax * 3600 * 24);

                }





            }

        }

        return $result;
    }

    /**
     * @param $objects
     * @param $date
     * @return bool
     */
    public static function checkMinMaxDate($objects, $date)
    {
        $format = $GLOBALS['TL_CONFIG']['dateFormat'];
        $today = date_create('Today');
        $tsdate = \DateTime::createFromFormat($format, $date);

        if ($today && $tsdate) {
            $diff = date_diff($today, $tsdate);
            $days = $diff->format('%d');
            if ($days) {
                $d = intval($days);

                if ($d && $objects) {
                    foreach ($objects as $object) {
                        $min = $object->getMinReservatioDay();
                        $max = $object->getMaxReservationDay();

                        if (!$min) {
                            $min = 0;
                        }

                        if (!$max) {
                            $max = 999999;
                        }

                        if (($d >= $min) && ($d <= $max)) {
                            return true;
                        }
                    }
                }
            }

        }

        return false;
    }

    public static function getReservationTimes($list, $type, $weekday = -1, $date = null,$duration=0)
    {
        $result = array();

        if ($list) {
            shuffle($list);

            if ($date) {
                $format = $GLOBALS['TL_CONFIG']['dateFormat'];
                $tsdate = \DateTime::createFromFormat($format, $date);
                if ($tsdate) {
                    $tsdate->Format($format);
                    $tsdate->setTime(0, 0, 0);
                    $tsdate = $tsdate->getTimestamp();
                }
            }

            if (!$type) {
                return [];
            }

            $typeObject = C4gReservationTypeModel::findByPk($type);
            if (!$typeObject) {
                return [];
            }
            $periodType = $typeObject->periodType;
            $maxCount = $typeObject->objectCount;
            $count = [];
            //$objectQuantity = [];
            foreach ($list as $object) {
                $found = false;
                $objectCount = [];
                $objectQuantity = $object->getQuantity();
                $reservationTypes = $object->getReservationTypes();
                
                if ($reservationTypes) {
                    foreach ($reservationTypes as $reservationType) {
                        if ($reservationType == $type) {
                            $found = true;
                            break;
                        }
                    }
                }

                if (!$found) {
                    continue;
                }

                if($duration >= 1)
                {
                    $oh = $object->getOpeningHours();
                    switch ($periodType) {
                        case 'minute':
                            $interval = 1 * 60;
                            break;
                        case 'hour':
                            $interval =1 * 3600;
                            break;
                        default: '';
                    }
                }else{
                    $oh = $object->getOpeningHours();
                    switch ($periodType) {
                        case 'minute':
                            $interval = $object->getTimeinterval() * 60;
                            break;
                        case 'hour':
                            $interval = $object->getTimeinterval() * 3600;
                            break;
//                    case 'minute_period':
//                        $interval = $object->getTimeinterval() * 60;
//                        break;
//                    case 'hour_period':
//                        $interval = $object->getTimeinterval() * 3600;
//                        break;
                        default: '';
                    }
                }



                if ($interval && ($interval > 0)) {
                    if ($interval > 0) {
                        foreach ($oh as $key => $day) {
                            if (($day != -1) && ($key == $weekday)) {
                                foreach ($day as $period) {
                                    $time_begin = $period['time_begin'];
                                    $time_end = $period['time_end'];

                                    if ($time_begin && $time_end) {
                                        $time = $time_begin;

                                        $reservation = null;
                                        while ($time <= $time_end) {
                                            //$foundObject = false;
                                            $id = $object->getId();
                                            if ($date && $tsdate) {
                                                $t = 'tl_c4g_reservation';
                                                //$objectCount = [];
                                                $arrColumns = array("$t.beginDate=$tsdate AND $t.beginTime=$time AND NOT $t.cancellation=1");
                                                $arrValues = array();
                                                $arrOptions = array();

                                                $reservations = C4gReservationModel::findBy($arrColumns, $arrValues, $arrOptions);
                                                if ($reservations) {
                                                    foreach ($reservations as $reservation) {
                                                        if ($reservation->reservation_object) {
//                                                            foreach (unserialize($reservation->reservation_object) as $key=>$value) {
                                                                if ($reservation->reservation_object == $id) {
                                                                   // $foundObject = true;
                                                                    $count[$tsdate][$time] = $count[$tsdate][$time] ? $count[$tsdate][$time] + 1 : 1;
                                                                    $objectCount[$tsdate][$time] = $objectCount[$tsdate][$time] ? $objectCount[$tsdate][$time] + 1 : 1;
                                                                }
//                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            if ($tsdate) {
                                                if ($maxCount && ($count[$tsdate][$time] >= intval($maxCount))) {
                                                    $result = self::addTime($result, $time, -1);
                                                } /*else if ($objectQuantity && ($objectCount[$tsdate][$time] >= intval($objectQuantity))) {
                                                   $result = self::addTime($result, $time, -1);
                                                }*/ else {
                                                    $result = self::addTime($result, $time, $id);
                                                }
                                            }

                                            $time = $time + $interval;
                                        }
                                    }
                                }
                            }

                        }
                    }
                }
            }

            return ArrayHelper::sortArrayByFields($result,'name');

        }
    }

        /**
         * @return array
         */
        public static function getReservationObjectList($moduleTypes = null)
        {
            $objectList = array();
            $t = static::$strTable;
            $arrOptions = array();
            $allObjects = self::findBy('published','1');
            if ($moduleTypes) {
                $types = $moduleTypes;
                $objects = [];
                foreach ($allObjects as $object) {
                    $objectTypes = unserialize($object->viewableTypes);
                    foreach($objectTypes as $objectType) {
                        if (in_array($objectType, $types)) {
                            $objects[] = $object;
                            break;
                        }
                    }
                }
            } else {
                $objects = $allObjects;
            }

            if ($objects) {
                foreach ($objects as $object) {
                    $frontendObject = new C4gReservationFrontendObject();
                    $frontendObject->setId($object->id);

                    $frontendObject->setCaption($object->caption);
                    $captions = $object->options;
                    if ($captions) {
                        foreach ($captions as $caption) {
                            if ($caption['language'] == $GLOBALS['TL_LANGUAGE']) {
                                $frontendObject->setCaption($caption['caption']);
                                break;
                            }
                        }
                    }

                    $frontendObject->setTimeinterval($object->time_interval);
                    $frontendObject->setMinReservationDay($object->min_reservation_day);
                    $frontendObject->setMaxReservationDay($object->max_reservation_day);
                    $frontendObject->setReservationTypes(unserialize($object->viewableTypes));
                    $frontendObject->setDesiredCapacity([$object->desiredCapacityMin, $object->desiredCapacityMax]);
                    $frontendObject->setQuantity($object->quantity);

                    $opening_hours = array();
                    $weekdays = array('0'=>false,'1'=>false,'2'=>false,'3'=>false,'4'=>false,'5'=>false,'6'=>false);

                    $opening_hours['su'] = unserialize($object->oh_sunday);
                    $opening_hours['mo'] = unserialize($object->oh_monday);
                    $opening_hours['tu'] = unserialize($object->oh_tuesday);
                    $opening_hours['we'] = unserialize($object->oh_wednesday);
                    $opening_hours['th'] = unserialize($object->oh_thursday);
                    $opening_hours['fr'] = unserialize($object->oh_friday);
                    $opening_hours['sa'] = unserialize($object->oh_saturday);

                   //ToDo array duchgehen falls nur der erste Datensatz leer ist.
                    if ($opening_hours['su'] != false) {
                        if ($opening_hours['su'][0]['time_begin'] && $opening_hours['su'][0]['time_end']) {
                            $weekdays['0'] = true;
                        }
                    }
                    if ($opening_hours['mo'] != false) {
                        if ($opening_hours['mo'][0]['time_begin'] && $opening_hours['mo'][0]['time_end']) {
                            $weekdays['1'] = true;
                        }
                    }
                    if ($opening_hours['tu'] != false) {
                        if ($opening_hours['tu'][0]['time_begin'] && $opening_hours['tu'][0]['time_end']) {
                            $weekdays['2'] = true;
                        }
                    }
                    if ($opening_hours['we'] != false) {
                        if ($opening_hours['we'][0]['time_begin'] && $opening_hours['we'][0]['time_end']) {
                            $weekdays['3'] = true;
                        }
                    }
                    if ($opening_hours['th'] != false) {
                        if ($opening_hours['th'][0]['time_begin'] && $opening_hours['th'][0]['time_end']) {
                            $weekdays['4'] = true;
                        }
                    }
                    if ($opening_hours['fr'] != false) {
                        if ($opening_hours['fr'][0]['time_begin'] && $opening_hours['fr'][0]['time_end']) {
                            $weekdays['5'] = true;
                        }
                    }
                    if ($opening_hours['sa'] != false) {
                        if ($opening_hours['sa'][0]['time_begin'] && $opening_hours['sa'][0]['time_end']) {
                            $weekdays['6'] = true;
                        }
                    }

                    $frontendObject->setWeekdayExclusion($weekdays);

                    $frontendObject->setOpeningHours($opening_hours);

                    $frontendObject->setDatesExclusion(unserialize($object->days_exclusion));
                    $objectList[] = $frontendObject;
                }
            }

            return $objectList;
        }
}
