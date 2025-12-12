<?php

namespace con4gis\ReservationBundle\Classes\Utils;

use con4gis\CoreBundle\Classes\Helper\ArrayHelper;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationDateChecker;
use con4gis\ReservationBundle\Classes\Objects\C4gReservationFrontendObject;
use con4gis\ReservationBundle\Classes\Calculator\C4gReservationCalculator;
use Contao\Database;
use Contao\System;
use Contao\Date;
use Contao\StringUtil;

class C4gReservationHandler
{
    // Per-request caches to reduce repeated computations during form build
    protected static array $ohEndCache = [];        // key: object-hash_weekday_overnight
    protected static array $captionCache = [];      // key: objectId_lang
    protected static ?string $langLower = null;     // normalized current language
    protected static array $weekdaysCache = [];     // key: signature(opening_hours + periodType)

    /**
     * @param $date
     * @return string|null
     */

    private static function addComma($date)
    {
        if ($date != '') {
            return $date . ',';
        } else {
            return $date;
        }
    }

    /**
     * @param $list
     * @return string
     */
    public static function getWeekdayExclusionString($list)
    {
        $result = '';
        if ($list) {
            $weekdays = array();
            foreach ($list as $object) {
                $we = $object->getWeekdayExclusion();
                foreach ($we as $key => $value) {
                    $weekdays[$key] = $key;
                    if (!$value) {
                        $weekdays[$key] = $weekdays[$key] !== false ? true : false;
                    } else {
                        $weekdays[$key] = false;
                    }
                }
            }

            foreach ($weekdays as $key => $value) {
               if ($value) {
                   $result = self::addComma($result) . intval($key);
               }
            }
        }

        return $result;
    }

    /**
     * @param $list
     * @param $date
     * @param $type
     * @return int
     */
    public static function getMaxObjectCountPerDate($list, $date, $type) {
        $result = 0;
        $database = Database::getInstance();
        $periodType = $type['periodType'];
        $maxPerTime = $type['objectCount'];

        foreach ($list as $object) {
            $id= $object->getId();
            $objectData = $database->prepare(
                "SELECT quantity,oh_sunday,oh_monday,oh_tuesday,oh_wednesday,oh_thursday,oh_friday,oh_saturday".
                         "FROM `tl_c4g_reservation_object` WHERE `id`=? AND `published`='1'")
                ->execute($id)->fetchAssoc();
            $weekday = date("w",$date);
            $quantity = $objectData['quantity'];

            if ($maxPerTime < $quantity) {
                $quantity = $maxPerTime; //ToDo check max count per interval for all objects
            }

            $interval = $object->getTimeInterval();

            if($weekday == 0){
                $array = \Contao\StringUtil::deserialize($objectData['oh_sunday'], true);
            }
            if($weekday == 1){
                $array = \Contao\StringUtil::deserialize($objectData['oh_monday'], true);
            }
            if($weekday == 2){
                $array = \Contao\StringUtil::deserialize($objectData['oh_tuesday'], true);
            }
            if($weekday == 3){
                $array = \Contao\StringUtil::deserialize($objectData['oh_wednesday'], true);
            }
            if($weekday == 4){
                $array = \Contao\StringUtil::deserialize($objectData['oh_thursday'], true);
            }
            if($weekday == 5){
                $array = \Contao\StringUtil::deserialize($objectData['oh_friday'], true);
            }
            if($weekday == 6){
                $array = \Contao\StringUtil::deserialize($objectData['oh_saturday'], true);
            }

            $possibleBookings = 0;
            foreach ($array as $timeset) {

                if (intval($timeset['time_end']) < intval($timeset['time_begin'])) { //nxtday
                    $possibleSeconds = intval(86400+$timeset['time_end']) + intval($timeset['time_begin']);
                } else {
                    $possibleSeconds = intval($timeset['time_end']) - intval($timeset['time_begin']);
                }

                $toSecond = self::getPeriodFaktor($periodType);
               
                if ($possibleSeconds) {
                    $possibleBookings = $possibleBookings + (($possibleSeconds / $toSecond / $interval) * $quantity);
                }
            }

            if ($possibleBookings > 0) {
                $result++;
            }
        }

        return $result;
    }

    /**
     * @param $list
     * @param $type
     * @param $removeBookedDays
     * @return string
     * @throws \Exception
     */
    public static function getDateExclusionString($list, $type, $removeBookedDays=1, $asArray=0)
    {
        $result = '';
        if ($list) {
            $alldates = [];
            $minDate = 0;
            $maxDate = 7;

            //remove configured date exclusion
            $allObjectDates = [];
            $excludePeriodArr = [];
            foreach ($list as $object) {
                if(!$object instanceof C4gReservationFrontendObject){
                    break;
                }
                $exclusionPeriods = $object->getDatesExclusion();
                if ($exclusionPeriods) {
                    foreach ($exclusionPeriods as $period) {
                        if ($period && $period['date_exclusion'] && $period['date_exclusion_end']) {
                            $exclusionBegin = $period['date_exclusion'] == -3600 ? 0 : $period['date_exclusion'];
                            $exclusionEnd = $period['date_exclusion_end'] == -3600 ? 0 : $period['date_exclusion_end'];

                            if ($exclusionBegin && $exclusionEnd) {
                                $excludePeriodArr[] = ['begin'=>$exclusionBegin, 'end'=> $exclusionEnd];
                            }
                        }
                    }
                }
                $minReservationDay = $object->getMinReservationDay() ?: 0;
                if ($minDate && ($minDate > 0) && ($minDate > $minReservationDay)) {
                    $minDate = $minReservationDay;
                }
                $maxReservationDay = $object->getMaxReservationDay() ?: 365;
                if ($maxDate && ($maxDate < 365) && ($maxDate < $maxReservationDay)) {
                    $maxDate = $maxReservationDay;
                }
            }

            //check if all objects exluded
            foreach ($allObjectDates as $date => $dateArray) {
                if (count($dateArray) === count($list)) {
                    $alldates[] = $date;
                }
            }

            //remove dates without possible times
            if ($removeBookedDays) {
                $begin = C4gReservationDateChecker::getBeginOfDate(time())+($minDate*86400);
                $end   = C4gReservationDateChecker::getEndOfDate(time())+($maxDate*86400);

                $i = $begin;
                $nextDays = 0;
                while ($i <= $end) {
                    $weekday = date('w', $i);
                    $timeArr = self::getReservationTimes($list, $type['id'], $weekday, $i);

                    if (!$timeArr || (count($timeArr) == 0)) {
                        $alldates[$i] = $i;
                    } else {
                        $excludeTime = true;
                        foreach ($timeArr as $timeElement) {
                            if ($timeElement && $timeElement['objects']) {

                                //sind noch Objekte buchbar?
                                foreach ($timeElement['objects'] as $timeElementObj) {
                                    if ($timeElementObj['id'] && $timeElementObj['id'] !== -1) {
                                        $excludeTime = false;
                                        break 2;
                                    }
                                }

                                //exclude time and check next days
                                if (($timeElement['time'] + $timeElement['interval']) > ($i+86400)) {
                                    $nd = ceil((($timeElement['time'] + $timeElement['interval']) - ($i+86400)) / 86400);
                                    $nextDays = ($nd > $nextDays) ? $nd : $nextDays;
                                }
                            }
                        }

                        if ($excludeTime) {
                            $alldates[$i] = $i;
                        } else if (!$excludeTime && $nextDays && $timeArr && (count($timeArr) > 0)) {
                            $objects = [];
                            foreach ($timeArr as $timeElement) {
                                if ($timeElement && $timeElement['objects']) {
                                    foreach ($timeElement['objects'] as $timeElementObj) {
                                        if ($timeElementObj['id'] && $timeElementObj['id'] !== -1) {
                                            $objects[$timeElementObj['id']] = true;
                                        }
                                    }
                                }
                            }

                            //ToDo Not a sufficient solution, as only partial periods could be affected.
                            if (count($objects) <= 1) {
                                $alldates[$i] = $i;
                            }
                        }

                        $nextDays = ($nextDays > 0) ? $nextDays-- : 0;
                    }

//                    if (!$alldates[$i]) {
//                        foreach ($excludePeriodArr as $period) {
//                            if ($i >= $period['begin'] && $i <= $period['end']) {
//                                $alldates[$i] = $i;
//                                break;
//                            }
//                        }
//                    }

                    $i = $i+86400;
                }
            }

            $result = [];
            $result['periods'] = $excludePeriodArr;

            if ($asArray) {
                $result['dates'] = $alldates;
            } else {
                foreach ($alldates as $date) {
                    if ($date) {
                        $result['dates'] = self::addComma($result['dates']) . date('d.m.Y'/*$GLOBALS['TL_CONFIG']['dateFormat']*/, $date);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param $day
     * @param $date
     * @return bool
     */
    public static function isWeekday($datestr)
    {
        if ($pos = strpos($datestr, '--')) {
            $date = substr($datestr, 0, $pos);
            $day  = substr($datestr, $pos+2);
            $date = strtotime($date);
            if ($date && (date("w", $date) == $day)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $list
     * @param $time
     * @param $obj
     * @param $interval
     * @param $endTime
     * @return array|mixed
     */
    private static function addTime($list, $time, $obj, $interval, $endTime = 0, $nxtDay = false)
    {
        $clock = '';

        $langCookie = key_exists('langCookie', $list) ? $list['langCookie'] : '';
        $withEndTimes = key_exists('withEndTimes', $list) ? $list['withEndTimes'] : false;
        $departureTimeEndStamp = 0;

        if ($langCookie) {
            \Contao\System::loadLanguageFile('fe_c4g_reservation',$langCookie);
        }

        if (!strpos($GLOBALS['TL_CONFIG']['timeFormat'],'A')) {
            if ($GLOBALS['TL_LANG']['fe_c4g_reservation']['clock']) {
                $clock = ' '.$GLOBALS['TL_LANG']['fe_c4g_reservation']['clock'];
            }
        }

        $withoutTime = false;
        $description = '';
        $weekday = key_exists('weekday',$list) ? C4gReservationDateChecker::getWeekdayStr($list['weekday']) : '';
        if (isset($list['showArrivalAndDeparture'][$weekday]) && $list['showArrivalAndDeparture'][$weekday] && is_array($list['showArrivalAndDeparture'][$weekday])) {
            $arrivalTimeBegin =  date($GLOBALS['TL_CONFIG']['timeFormat'], $list['showArrivalAndDeparture'][$weekday][0]['time_begin']);
            $arrivalTimeEnd =  date($GLOBALS['TL_CONFIG']['timeFormat'], $list['showArrivalAndDeparture'][$weekday][0]['time_end_org']);
            $departureTimeBegin =  date($GLOBALS['TL_CONFIG']['timeFormat'], $list['showArrivalAndDeparture'][$weekday][1]['time_begin']);

            $departureTimeEnd = $list['showArrivalAndDeparture'][$weekday][1]['time_end'];

            if (($list['type']['periodType'] == 'overnight') || ($departureTimeEnd < 86400)){
                $departureTimeEndStamp = $departureTimeEnd;
                $departureTimeEnd = date($GLOBALS['TL_CONFIG']['timeFormat'],$departureTimeEndStamp);
            } else {
                $departureTimeEnd = date($GLOBALS['TL_CONFIG']['timeFormat'],$departureTimeEnd);
            }

            $description = $GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_arrivalTime'] . $arrivalTimeBegin . '-' . $arrivalTimeEnd.$clock.", " . $GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_departureTime'] . $departureTimeBegin . '-' . $departureTimeEnd.$clock;
            $withoutTime = true;
        }

        if (key_exists('result', $list)) {
            foreach ($list['result'] as $key => $item) {
                if ($key === $time || ($interval && ($key === ($time.'#'.$interval))) || ($endTime && ($key === ($time.'#'.($endTime-$time))))) {
                    $list['result'][$key]['objects'][] = $obj;
                    return $list['result'];
                }
            }
        }
        $format = $GLOBALS['TL_CONFIG']['timeFormat'];
        //$begin = date('I', $list['tsdate']) ? date($format, $time+3600).$clock : date($format, $time).$clock; 
        $begin = date($GLOBALS['TL_CONFIG']['timeFormat'], $time).$clock;
        $mergedTime = false;
        if (key_exists('mergedTime',$obj) && $obj['mergedTime'] && key_exists('mergedEndTime',$obj) && $obj['mergedEndTime']) {
            $clockEx = $withoutTime ? '' : $clock;
            $format = $withoutTime ? $GLOBALS['TL_CONFIG']['dateFormat'] : $GLOBALS['TL_CONFIG']['datimFormat'];
            $begin = date('I', $obj['mergedTime']) ? date($format, $obj['mergedTime']-3600).$clockEx : date($format, $obj['mergedTime']).$clockEx ;

            if ($list['type']['periodType'] == 'overnight'){
                $actDurationStamp = $list['actDuration'] == '-1' ? ($list['actDuration']  * $interval) * -1 : $list['actDuration'] * 86400;
                $end = date($format, $list['tsdate'] + $departureTimeEndStamp + $actDurationStamp).$clockEx;
            }else{
                $summertime = C4gReservationDateChecker::getTimeDiff($time);
                $end = date($format, $obj['mergedEndTime'] + $summertime).$clockEx;
            }

            $mergedTime = true;
        }

        $beginStamp = $time;
        if ($nxtDay) {
            $beginStamp+=86400;
        }
    
        if ($obj && ($obj['id'] == -1)) {
            if ($withEndTimes && $interval) {
                $key = $time.'#'.$interval;
                if (!$mergedTime) {
                    date_default_timezone_set('Europe/Berlin');
                    $summertime = C4gReservationDateChecker::getTimeDiff($time);
                    $end = date($format, $time + $interval + $summertime-3600).$clock;
                }
                $list['result'][$key] = array('id' => $key, 'time' => $time, 'interval' => $interval, 'name' => $begin.' - '.$end, 'objects' => [$obj], 'begin' => $beginStamp, 'description' => $description);
            } else if ($endTime && ($endTime != $time)) {
                $key = $time.'#'.($endTime-$time);
                if (!$mergedTime) {
                    $end = date($GLOBALS['TL_CONFIG']['timeFormat'], $endTime).$clock;
                }
                $list['result'][$key] = array('id' => $key, 'time' => $time, 'interval' => ($endTime-$time), 'name' => $begin.' - '.$end, 'objects' => [$obj], 'begin' => $beginStamp, 'description' => $description);
            } else {
                $key = $time.'#'.$interval;
                $list['result'][$key] = array('id' => $key, 'time' => $time, 'interval' => 0, 'name' => $begin, 'objects' => [$obj], 'description' => $description);
            }
        } else {
            if ($withEndTimes && $interval) {
                $key = $time.'#'.$interval;
                if (!$mergedTime) {
                    date_default_timezone_set('Europe/Berlin');
                    $summertime = C4gReservationDateChecker::getTimeDiff($time);
                    $end = date($format, $time + $interval+$summertime-3600).$clock;
                    $stop = 1;
                }
                $list['result'][$key] = array('id' => $key, 'time' => $time, 'interval' => $interval, 'name' => $begin.' - '.$end, 'objects' => [$obj], 'begin' => $beginStamp, 'description' => $description);
            } else if ($endTime && ($endTime != $time)) {
                $key = $time.'#'.($endTime-$time);
                if (!$mergedTime) {
                    $end = date($GLOBALS['TL_CONFIG']['timeFormat'], $endTime).$clock;
                }
                $list['result'][$key] = array('id' => $key, 'time' => $time, 'interval' => ($endTime-$time), 'name' => $begin.' - '.$end, 'objects' => [$obj], 'begin' => $beginStamp, 'description' => $description);
            } else {
                $key = $time.'#'.$interval;
                $list['result'][$key] = array('id' => $key, 'time' => $time, 'interval' => 0, 'name' => $begin, 'objects' => [$obj], 'begin' => $beginStamp, 'description' => $description);
//                $end = date('I', $list['tsdate']) ? date($format, $time+$interval+3600).$clock : date($format, $time+$interval).$clock;

            }
        }

        return $list['result'];
    }

    /**
     * @param $objects
     * @return float|int|void
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
     * @param $type
     * @return float|int|mixed
     */
    public static function getBookableMinDate($list, $type) {
        $result = self::getMinDate($list);

        if ($list) {
            $alldates = [];
            $minDate = 0;
            $maxDate = 7;

            //remove configured date exclusion
            $allObjectDates = [];
            $excludePeriodArr = [];
            foreach ($list as $object) {
                if (!$object instanceof C4gReservationFrontendObject) {
                    break;
                }
                $exclusionPeriods = $object->getDatesExclusion();
                if ($exclusionPeriods) {
                    foreach ($exclusionPeriods as $period) {
                        if ($period && $period['date_exclusion'] && $period['date_exclusion_end']) {
                            $exclusionBegin = $period['date_exclusion'] == -3600 ? 0 : $period['date_exclusion'];
                            $exclusionEnd = $period['date_exclusion_end'] == -3600 ? 0 : $period['date_exclusion_end'];

                            if ($exclusionBegin && $exclusionEnd) {
                                $excludePeriodArr[] = ['begin' => $exclusionBegin, 'end' => $exclusionEnd];
                            }
                        }
                    }
                }

                $minReservationDay = $object->getMinReservationDay() ?: 0;
                if ($minReservationDay > $minDate) {
                    $minDate = $minReservationDay;
                }

                $maxReservationDay = $object->getMaxReservationDay() ?: 365;
                if ($maxDate && ($maxDate < 365) && ($maxDate < $maxReservationDay)) {
                    $maxDate = $maxReservationDay;
                }
            }

            //check if all objects exluded
            foreach ($allObjectDates as $date => $dateArray) {
                if (count($dateArray) === count($list)) {
                    $alldates[] = $date;
                }
            }

            //remove dates without possible times
            $begin = C4gReservationDateChecker::getBeginOfDate(time())+($minDate*86400);
            $end   = C4gReservationDateChecker::getEndOfDate(time())+($maxDate*86400);

            $i = $begin;
            $nextDays = 0;
            while ($i <= $end) {
                $weekday = date('w', $i);
                $timeArr = self::getReservationTimes($list, $type['id'], $weekday, $i);

                if (!$timeArr || (count($timeArr) == 0)) {
                    $alldates[$i] = $i;
                } else {
                    $excludeTime = true;
                    foreach ($timeArr as $timeElement) {
                        if ($timeElement && $timeElement['objects']) {

                            //sind noch Objekte buchbar?
                            foreach ($timeElement['objects'] as $timeElementObj) {
                                if ($timeElementObj['id'] && $timeElementObj['id'] !== -1) {
                                    $excludeTime = false;
                                    break 2;
                                }
                            }

                            //exclude time and check next days
                            if (($timeElement['time'] + $timeElement['interval']) > ($i + 86400)) {
                                $nd = ceil((($timeElement['time'] + $timeElement['interval']) - ($i + 86400)) / 86400);
                                $nextDays = ($nd > $nextDays) ? $nd : $nextDays;
                            }
                        }
                    }

                    if ($excludeTime) {
                        $alldates[$i] = $i;
                    }

                   $nextDays = ($nextDays > 0) ? $nextDays-- : 0;
                }

                $alldates[$i] = $i;
                if (!$alldates[$i]) {
                    $hitIt = true;
                    foreach ($excludePeriodArr as $period) {
                        if ($i >= $period['begin'] && $i <= $period['end']) {
                            $hitIt = false;
                            break;
                        }
                    }

                    if ($hitIt) {
                        return ($i > $result) ? $i : $result;
                    }
                }

                $i = $i + 86400;
            }
        }

        return $result;
    }

    /**
     * @param $objects
     * @param $weekday
     * @return float|int
     */
    public static function getNextWeekday($objects, $weekday)
    {
        $result = '';
        $firstmin = 0;
        $today = time();
        $wd = date('N', $today);
        $diff = $weekday - $wd;
        if ($diff < 0) {
            $diff = 7 - $diff;
        }

        if ($objects) {
            foreach ($objects as $object) {
                $min = $object->getMinReservationDay();
                if ($min && ($min > $firstmin)) {
                    $firstmin = $min;
                }
            }
        }

        return $today + (($firstmin+$diff) * 3600 * 24);
    }

    /**
     * @param $objects
     * @return float|int|string
     */
    public static function getMaxDate($objects)
    {
        $result = '';
        $today = time();
        if ($objects) {
            foreach ($objects as $object) {
                $max = intval($object->getMaxReservationDay());

                if ($max === 0) {
                    $result = $today + (365 * 3600 * 24);
                } else{
                    $result = $today + ($max * 3600 * 24);
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

    /**
     * @param $tstamp
     * @param $period
     * @param $weekday
     * @return bool
     */
    public static function checkValidPeriod($tstamp, $period) {
        $tstamp = intval($tstamp);

        $date_from = intval($period['date_from']);
        $date_to = intval($period['date_to']);

        if ($tstamp && ($date_from || $date_to)) {
            //hit the date
            if ($date_from && $date_to && ($tstamp >= $date_from) && ($tstamp <= $date_to)) {
                return true;
            } else if (!$date_to && $date_from && ($tstamp >= $date_from)) {
                return true;
            } else if (!$date_from && $date_to && ($tstamp <= $date_to)) {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $object
     * @param $weekday
     * @return false|mixed
     */
    public static function getEndTimeForMultipleDays($object, $weekday, $isOvernight=false) {
        // Memoize end time per object+weekday+overnight to avoid repeated array work
        $hash = is_object($object) ? spl_object_hash($object) : (string)($object['id'] ?? uniqid('obj_', true));
        $cacheKey = $hash.'_'.$weekday.'_'.($isOvernight ? 1 : 0);
        if (isset(self::$ohEndCache[$cacheKey])) {
            return self::$ohEndCache[$cacheKey];
        }
        $weekdayStr = C4gReservationDateChecker::getWeekdayStr($weekday);
        $ohString = "oh_".C4gReservationDateChecker::getWeekdayFullStr($weekday);
        $oh = StringUtil::deserialize($object->$ohString, true);

        if ($isOvernight) {
            if (key_exists(1, $oh)) {
                return self::$ohEndCache[$cacheKey] = $oh[1]['time_end'];
            }
        } else {
            foreach ($oh as $key => $period) {
                return self::$ohEndCache[$cacheKey] = $period['time_end'];
            }
        }

        return false;
    }

    private static function getReservationTimesDefault($timeParams, $timeObjectParams, $period) {
        $time_begin = is_numeric($period['time_begin']) ? intval($period['time_begin']) : false;
        $time_end = is_numeric($period['time_end']) ? intval($period['time_end']) : false;
        $typeOfObject = $timeObjectParams['object']->getTypeOfObject();

        if (($time_begin !== false) && ($time_end !== false)) {
            $time = $time_begin;

            $periodEnd = $time_end - $timeObjectParams['interval'];
//            $periodChanged = false;
            if ($time_end <= $time) {
                $periodEnd += 86400;
//                $periodChanged = true;
            }
            $timeArray = [];
            while ($time <= $periodEnd) {
                $nxtDay = false;
                if ($timeParams['nowDate'] && ($timeParams['nowDate'] == $timeParams['tsdate']) && ($time < $timeParams['nowTime'])) {
                    $time = $time + $timeObjectParams['defaultInterval'];
                    continue;
                }

                if ($timeObjectParams['maxBeginTime'] && ($time > $timeObjectParams['maxBeginTime'])) {
                    break;
                }

                if ($timeParams['type']) {
                    if (isset($periodChanged) && $periodChanged && ($time >= 86400)) {
                        $timeParams['tsdate'] += 86400;
                        $periodChanged = false;
                        $nxtDay = true;
                    }

                    $realTime = $time;
                    if ($nxtDay) {
                        $realTime = $time - 86400;
                    }

                    $endTime = $realTime + $timeObjectParams['interval'] + $timeObjectParams['durationDiff'];

                    if ($timeParams['date'] && $timeParams['tsdate']) {
                        $timeParams['calculator']->calculate(
                            $timeParams['tsdate'],
                            $timeParams['tsdate'],
                            $realTime - $timeObjectParams['durationDiff'],
                            $endTime,
                            $timeObjectParams['object'],
                            $timeObjectParams['capacity'],
                            $timeArray
                        );
                        $calculatorResult = $timeParams['calculator']->getCalculatorResult();
                        $timeArray = $calculatorResult->getTimeArray();
                    }

                    $bookingMaxCapacity = $timeParams['type']['maxParticipantsPerBooking'];
                    $objectMaxCapacity = intval($timeObjectParams['object']->getDesiredCapacity()[1]);
                    $unlimitedMaxCapacity = PHP_INT_MAX;

                    if ($bookingMaxCapacity && $objectMaxCapacity) {
                        if($bookingMaxCapacity < $objectMaxCapacity) {
                            $timeObjMax = $bookingMaxCapacity;
                        } else {
                            $timeObjMax = $objectMaxCapacity; 
                        }
                    } else if ($bookingMaxCapacity) {
                        $timeObjMax = $bookingMaxCapacity;
                    } else if ($objectMaxCapacity) {
                        $timeObjMax = $objectMaxCapacity;
                    } else {
                        $timeObjMax = $unlimitedMaxCapacity;
                    }
                    
                    $timeObj = [
                        'id'=> -1,
                        'act'=> $calculatorResult ? $calculatorResult->getDbPersons() : 0,
                        'percent'=> $calculatorResult ? $calculatorResult->getDbPercent() : 0,
                        'max'=> $timeObjMax, //ToDo check max
                        'showSeats'=> $timeParams['showFreeSeats'],
                        'priority'=> intval($timeObjectParams['object']->getPriority()),
                        'removeButton' => false,
                        'mergedTime' => false,
                        'mergedEndTime' => false
                    ];

                    $checkTime = $time;
                    if ($timeParams['type']['bookRunning']) {
                        $checkTime = $endTime;
                    }

                    //$realTime += C4gReservationDateChecker::getCESDiffToGMT($realTime);

                    if ($typeOfObject == 'fixed_date') {
                        $realTime = $period['date_begin'];
                        $timeObjectParams['tsdate'] = $period['date_from'];
                        $timeObjectParams['date'] = $period['date_from'];
                    }

                    $timeParams['result'] = self::getTimeResult($realTime, $timeParams, $timeObjectParams, $checkTime, $calculatorResult, $timeArray, $timeObj, $nxtDay);
                }
                
                $time = $time + $timeObjectParams['defaultInterval'];
            }
        }

        return $timeParams['result'];
    }

    private static function getReservationTimesMultipleDays($timeParams, $timeObjectParams, $period) {
        $time_begin = is_numeric($period['time_begin']) ? intval($period['time_begin']) : false;
        $time_end = is_numeric($period['time_end']) ? intval($period['time_end']) : false;
        //ToDo check arrival and departure times
        $durationInterval = $timeObjectParams['interval']+$timeObjectParams['durationDiff'];
        if ($time_end >= $time_begin) {
            $durationInterval = $durationInterval - 86400; //ToDo first day counts
        }

        if (($time_begin !== false) && ($time_end !== false)) {
            $time = $time_begin;
            $periodEnd = $time_end;

            $timeArray = [];
            while ($time <= $periodEnd) {
                if ($time && $timeParams['type']) {
                    $endTime = $time + $timeObjectParams['interval'] + $timeObjectParams['durationDiff'];

                    if ($timeParams['nowDate'] && ($timeParams['nowDate'] == $timeParams['tsdate']) && ($time < $timeParams['nowTime'])) {
                        $time = $time + $timeObjectParams['defaultInterval'];
                        continue;
                    }

                    if ($timeObjectParams['maxBeginTime'] && ($time > $timeObjectParams['maxBeginTime'])) {
                        break;
                    }

                    if ($timeParams['date'] && $timeParams['tsdate']) {
                        $timeParams['calculator']->calculate(
                            $timeParams['tsdate'],
                            $timeParams['tsdate']+$time + $timeObjectParams['interval'] + $timeObjectParams['durationDiff'],
                            $time - $timeObjectParams['durationDiff'],
                            $endTime,
                            $timeObjectParams['object'],
                            $timeObjectParams['capacity'],
                            $timeArray
                        );
                        $calculatorResult = $timeParams['calculator']->getCalculatorResult();
                        $timeArray = $calculatorResult->getTimeArray();
                    }

                    $timeObjMax = intval($timeObjectParams['object']->getDesiredCapacity()[1]);
                    $timeObj = [
                        'id'=>-1,
                        'act'=> $calculatorResult ? $calculatorResult->getDbPersons() : 0,
                        'percent'=> $calculatorResult ? $calculatorResult->getDbPercent() : 0,
                        //'max'=> intval($timeObjectParams['desiredCapacity']), //ToDo check max
                        'max'=> $timeObjMax,
                        'showSeats'=> $timeParams['showFreeSeats'],
                        'priority'=> intval($timeObjectParams['object']->getPriority()),
                        'removeButton' => false,
                        'mergedTime' => false,
                        'mergedEndTime' => false
                    ];

                    $checkTime = $time;
                    if ($timeParams['type']['bookRunning']) {
                        $checkTime = $endTime;
                    }

                    if ($timeParams['tsdate']) {
                        $timeObj['mergedTime'] = C4gReservationDateChecker::mergeDateWithTime($timeParams['tsdate'], $time);

                        $wd = date('N', $timeParams['tsdate']+$durationInterval);
                        if ($wd == 7) {
                            $wd = 0;
                        }
                        $periodEndWeekday = C4gReservationDateChecker::getWeekdayStr($wd);
                        if ($periodEndWeekday) {
                            $endTimes = $timeObjectParams['object']->getOpeningHours()[$periodEndWeekday];
                            $endTime = 0;
                            foreach ($endTimes as $endTimeSet) {
                                $et = is_numeric($endTimeSet['time_end']) ? intval($endTimeSet['time_end']) : false;
                                if (($et !== false) && ($et > $endTime)) {
                                    $endTime = $et;
                                }
                                if ($timeParams['type']['periodType'] == 'overnight') {
                                    break;
                                }
                            }
                            $cts = $timeObj['mergedTime'];
                            $periodEnd = $endTime - C4gReservationDateChecker::getTimeDiff($cts);
                        }

                        $timeObj['mergedEndTime'] = C4gReservationDateChecker::mergeDateWithTime($timeParams['tsdate']+$durationInterval, $periodEnd);
                    }

                    $timeObj['mergedTime'] += C4gReservationDateChecker::getCESDiffToLocale($timeObj['mergedTime']);
                    $timeObj['mergedEndTime'] += C4gReservationDateChecker::getCESDiffToLocale($timeObj['mergedEndTime']);
                    $time += C4gReservationDateChecker::getCESDiffToGMT($time);

                    $timeParams['result'] = self::getTimeResult($time, $timeParams, $timeObjectParams, $checkTime, $calculatorResult, $timeArray, $timeObj);
                }

                $time = $time + $timeObjectParams['defaultInterval'];

                //todo Check
                if ($timeParams['type']['periodType'] == 'overnight') {
                    if (!$timeParams['result']) {
                        $timeParams = [];
                        //isnt really doing anything
                    }
                    break;
                }
            }
        }

        return $timeParams['result'];
    }

    /**
     * @param $list
     * @param $type
     * @param int $weekday
     * @param null $date
     * @param int $duration
     * @param false $withEndTimes
     * @param false $showFreeSeats
     * @return array|mixed
     */
    public static function getReservationTimes($objectList, $typeId, $weekday = -1, $date = null, $actDuration=0, $actCapacity=0, $withEndTimes=false, $showFreeSeats=false, $checkToday=false, $langCookie = '', $showArrivalAndDeparture=false)
    {
        // In-request memoization to avoid duplicate heavy computations during one request
        // Build a stable key based on parameters and involved object IDs
        static $___memo = [];
        try {
            $ids = [];
            if (is_array($objectList)) {
                foreach ($objectList as $obj) { if (is_object($obj) && method_exists($obj, 'getId')) { $ids[] = (string)$obj->getId(); } }
            } elseif (is_object($objectList) && method_exists($objectList, 'getId')) {
                $ids[] = (string)$objectList->getId();
            }
            sort($ids);
            $key = implode('|', [
                (string)$typeId,
                (string)$weekday,
                (string)$date,
                (string)$actDuration,
                (string)$actCapacity,
                $withEndTimes ? '1':'0',
                $showFreeSeats ? '1':'0',
                $checkToday ? '1':'0',
                (string)$langCookie,
                $showArrivalAndDeparture ? '1':'0',
                implode(',', $ids)
            ]);
            if (array_key_exists($key, $___memo)) {
                return $___memo[$key];
            }
        } catch (\Throwable $t) { $key = null; }

        $timeParams = [
          'tsdate' => 0,
          'objectList' => $objectList,
          'typeId' => $typeId,
          'type' => [],
          'weekday' => $weekday,
          'date' => $date,
          'nowDate' => new \DateTime(),
          'nowTime' => 0,
          'actDuration' => $actDuration,
          'actCapacity' => $actCapacity,
          'withEndTimes' => $withEndTimes,
          'showFreeSeats' => $showFreeSeats,
          'checkToday' => $checkToday,
          'langCookie' => $langCookie,
          'calculator' => null,
          'showArrivalAndDeparture' => false,
          'result' => []
        ];

        if (!is_array($timeParams['objectList'])) {
            $timeParams['objectList'] = array($timeParams['objectList']);
        }

        if ($timeParams['objectList']) {
            if (is_array($timeParams['objectList'])){
                shuffle($timeParams['objectList']);
            }

            if ($timeParams['nowDate']) {
                $format = $GLOBALS['TL_CONFIG']['dateFormat'];

                $nowDate = $timeParams['nowDate'];
                $nowDate->Format($format);
                $nowDate->setTime(0,0,0);
                $timeParams['nowDate'] = $nowDate->getTimestamp();

                if ($timeParams['date'] !== -1) {
                    $timeParams['tsdate'] = C4gReservationDateChecker::getDateAsStamp($timeParams['date']);
                } else if ($timeParams['nowDate']) {
                    $timeParams['date'] = $nowDate;
                    $timeParams['tsdate'] = $timeParams['nowDate'];
                    $timeParams['weekday'] = C4gReservationDateChecker::getWeekdayStr(date('w'));
                }
            }

            $objDate = new Date(date($GLOBALS['TL_CONFIG']['timeFormat'], time()), Date::getFormatFromRgxp('time'));
            $timeParams['nowTime'] = $objDate->tstamp;

            if (!$timeParams['typeId']) {
                return [];
            }
            $database = Database::getInstance();
            $timeParams['type'] = $database->prepare("SELECT * FROM `tl_c4g_reservation_type` WHERE `id`=? AND `published` = ?")
                ->execute($timeParams['typeId'], '1')->fetchAssoc();

            if (!$timeParams['type']) {
                return [];
            }

            $weekdayStr = C4gReservationDateChecker::getWeekdayStr($timeParams['weekday']);
            $periodType = $timeParams['type']['periodType'];

            $maxDuration = 0;
            $maxResidenceTime = $timeParams['type']['max_residence_time'];
            $minResidenceTime = $timeParams['type']['min_residence_time']; 
            $periodFaktor = self::getPeriodFaktor($periodType);
            $maxDuration = $maxResidenceTime && ($maxResidenceTime > 0) ? $maxResidenceTime * $periodFaktor : $periodFaktor;

            if ($periodType == 'overnight') {
                $showArrivalAndDeparture = $showArrivalAndDeparture ? [] : false;
            }


            $beginDate = $timeParams['tsdate'];
            $endDate = $timeParams['tsdate'] + $maxDuration;

            foreach ($timeParams['objectList'] as $object) {
                $typeOfObject = $object->getTypeOfObject();
                if ($typeOfObject !== 'fixed_date') {
                    foreach ($object->getOpeningHours() as $key => $day) {
                        if (($day != -1) && ($key == $weekdayStr)) {
                            foreach ($day as $period) {
                                if ($timeParams['date'] !== -1) {

                                    $timeBegin = is_numeric($period['time_begin']) ? intval($period['time_begin']) : false;
                                    $timeEnd = is_numeric($period['time_end']) ? intval($period['time_end']) : false;

                                    if (($timeEnd !== false) && ($timeBegin !== false)) {
                                        $periodEnd = $timeEnd;
                                        if ($periodEnd <= $timeBegin) {
                                            $periodEnd += 86400;
                                        }
                                        $endDate = (($beginDate + $periodEnd) > $endDate) ? $beginDate + $periodEnd : $endDate;
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $timestamp = $object->getDateTimeBegin();

                    $beginDate = C4gReservationDateChecker::getBeginOfDate($timestamp);
                    $summerDiff = C4gReservationDateChecker::getTimeDiff($timestamp);
                    $beginTime = $timestamp - $beginDate - $summerDiff;
                    
                    $duration = $object->getTypeOfObjectDuration();

                    $object->setBeginDate($beginDate);
                    $object->setBeginTime($beginTime);
                    $object->setDuration($duration);
                    $object->setTimeinterval($duration);
                    
                    if ($periodType == 'week') {
                        $periodFaktor = self::getPeriodFaktor($periodType) / 7;
                    } else {
                       $periodFaktor = self::getPeriodFaktor($periodType); 
                    }
                    
                    $periodType = $periodFaktor * $duration;

                    $EndTime = $beginTime + $periodType;
                    $EndDate = $beginDate + $EndTime;

                    $endTime = $beginTime + $periodType;
                    $endDate = $beginDate + $endTime;
                    $object->setEndTime($endTime);
                    $object->setEndDate($endDate);
                }
            }

            $timeParams['calculator'] = new C4gReservationCalculator(
                $beginDate, $endDate, $timeParams['typeId'],
                $timeParams['type']['reservationObjectType'], $timeParams['objectList']);

            foreach ($timeParams['objectList'] as $object) {
                $found = false;
                $desiredCapacity = $object->getDesiredCapacity()[1] ? $object->getDesiredCapacity()[1] : 0;
                $capacity = $timeParams['actCapacity'] && $timeParams['actCapacity'] > 0 ? $timeParams['actCapacity'] : $desiredCapacity;

                if ( ($timeParams['actCapacity'] > 0) &&
                    (
                        ($object->getDesiredCapacity()[0] && ($timeParams['actCapacity'] < $object->getDesiredCapacity()[0])) ||
                        ($object->getDesiredCapacity()[1] && ($timeParams['actCapacity'] > $object->getDesiredCapacity()[1]))
                    )
                ) {
                    continue; 
                }

                $timeObjectParams = [
                    'object' => $object,
                    'timeArray' => [],
                    'quantity' => $object->getQuantity() ? $object->getQuantity() : 1,
                    'capacity' => $capacity,
                    'interval' => 0, //act interval
                    'durationDiff' => 0, //interval with additional time
                    'timeInterval' => 0, //default interval
                    'severalBookings' => $timeParams['type']['severalBookings'],
                    'maxObjects' => 0,
                    'exclusionPeriods' => [],
                    'maxBeginTime' => $object->getMaxBeginTime()
                ];

                if (($timeParams['date'] !== -1) && $timeParams['tsdate']) {
                    $exclusionDates = self::getDateExclusionString([$timeObjectParams['object']],$timeParams['typeId'],0,1);

                    if ($exclusionDates) {
                        $timeObjectParams['exclusionPeriods'] = $exclusionDates['periods'];
                        foreach ($exclusionDates['dates'] as $exclusionDate) {
                            if ($exclusionDate === $timeParams['tsdate']) {
                                $timeParams['date'] = -1;
                                break;
                            }
                        }
                    }
                }

                $reservationTypes = $object->getReservationTypes();
                if ($reservationTypes) {
                    foreach ($reservationTypes as $reservationType) {
                        if ($reservationType == $timeParams['typeId']) {
                            $found = true;
                            break;
                        }
                    }
                }

                if (!$found) {
                    continue;
                }

                $durationDiff = $timeObjectParams['object']->getDuration() ? $timeObjectParams['object']->getDuration() - $timeObjectParams['object']->getTimeInterval() : 0;
                $defaultInterval = $timeObjectParams['object']->getTimeInterval();

                if (intval($timeParams['actDuration']) >= 1) //actDuration from client can be -1 (no input)
                {
                    $timeInterval = $timeParams['actDuration'];
                } else {
                    $timeInterval = $timeObjectParams['object']->getTimeinterval();
                }

                $timeParams['calculator']->loadReservations($timeParams['type'], $timeObjectParams['object']);

                switch ($timeParams['type']['periodType']) {
                    case 'minute':
                        $timeObjectParams['defaultInterval'] = $defaultInterval * 60;
                        $timeObjectParams['interval'] = $timeInterval * 60;
                        $timeObjectParams['durationDiff'] = $durationDiff ? $durationDiff * 60 : 0;
                        break;
                    case 'hour':
                        $timeObjectParams['defaultInterval'] = $defaultInterval * 3600;
                        $timeObjectParams['interval'] = $timeInterval * 3600;
                        $timeObjectParams['durationDiff'] = $durationDiff ? $durationDiff * 3600 : 0;
                        break;
                    case 'day':
                    case 'overnight':
                        $timeObjectParams['defaultInterval'] = $defaultInterval * 86400;
                        $timeObjectParams['interval'] = $timeInterval * 86400;
                        $timeObjectParams['durationDiff'] = $durationDiff ? $durationDiff * 86400 : 0;
                        break;
                    case 'week':
                        $timeObjectParams['defaultInterval'] = $defaultInterval * 604800;
                        $timeObjectParams['interval'] = $timeInterval * 604800;
                        $timeObjectParams['durationDiff'] = $durationDiff ? $durationDiff * 604800 : 0;
                        break;
                    default: '';
                }

//                $timeObjectParams['severalBookings'] = !$timeParams['type']['severalBookings'] ? 1 : 0;
                $timeObjectParams['maxObjects'] = $timeObjectParams['quantity'] ?: $timeObjectParams['severalBookings'];
                $typeOfObject = $timeObjectParams['object']->getTypeOfObject();

                if ($timeObjectParams['defaultInterval'] && ($timeObjectParams['defaultInterval'] > 0) && $typeOfObject == 'standard') {
                    foreach ($timeObjectParams['object']->getOpeningHours() as $key => $dayPeriods) {

                        if (($dayPeriods != -1) && ($key == $weekdayStr)) {
                            $dbPeriods = $dayPeriods;
                            if ($periodType == 'overnight') {
                                $timeParams['showArrivalAndDeparture'][$key] = is_array($showArrivalAndDeparture) ? $dbPeriods : false;
                            }
                            foreach ($dayPeriods as $key=>$period) {
                                if ($periodType == 'overnight') {

                                    if (!key_exists(1,$dayPeriods)) {
                                        break;
                                    }
//                                    $day[0]['time_end'] = $day[0]['time_end'];
                                    $period['time_end'] = $dayPeriods[1]['time_end']+86400;
                                }
                                if ($timeParams['date'] !== -1) {
                                    $periodValid = C4gReservationHandler::checkValidPeriod($timeParams['tsdate'], $period);
                                    if (!$periodValid && ($periodType !== 'overnight')) {
                                        continue;
                                    }
                                    $time_begin = is_numeric($period['time_begin']) ? intval($period['time_begin']) : false;
                                    $time_end = is_numeric($period['time_end']) ? intval($period['time_end']) : false;

                                    if (($time_begin === false) || ($time_end === false)) {
                                        continue;
                                    }

                                    if (($periodType == 'day') || ($periodType == 'overnight') || ($periodType == 'week')) {
                                        $timeParams['result'] = self::getReservationTimesMultipleDays($timeParams, $timeObjectParams, $period);
                                        if ($periodType == 'overnight') {
//                                            todo Check
                                            if (!$timeParams['result']) {
                                                return [];
                                                //isnt really doing anything
                                            }
                                            break;
                                        }
                                    } else {
                                        $timeParams['result'] = self::getReservationTimesDefault($timeParams, $timeObjectParams, $period);
                                    }
                                }
                            }
                        }
                    }
                } else if ($typeOfObject == 'fixed_date') {
                    $beginDate = $object->getBeginDate();
                    $beginTime = $object->getBeginTime();
                    $endTime = $object->getEndTime();

                    $period['time_begin'] = is_numeric($beginTime) ? intval($beginTime) : false;
                    $period['time_end'] = is_numeric($endTime) ? intval($endTime) : false;
                    $period['date_from'] = is_numeric($beginDate) ? intval($beginDate) : false;
                    $period['date_to'] = is_numeric($beginDate+$endTime) ? intval($beginDate+$endTime) : false;

                    $timeParams['result'] = self::getReservationTimesDefault($timeParams, $timeObjectParams, $period);
                }
            }

            if ($timeParams['result'] && is_array($timeParams['result']) && (count($timeParams['result']) > 0)) {
                return ArrayHelper::sortArrayByFields($timeParams['result'], ['begin'  => SORT_ASC, 'name'  => SORT_ASC]);
            } else {
                return [];
            }
        }
    }

    /**
     * @param $time
     * @param $timeParams
     * @param $timeObjectParams
     * @param $checkTime
     * @param $calculatorResult
     * @param $timeArray
     * @param $timeObj
     * @return array|mixed
     */
    public static function getTimeResult($time, $timeParams, $timeObjectParams, $checkTime, $calculatorResult, $timeArray, $timeObj, $nxtDay=false) {
        $reasonLog = '';
        if (date('I')) {
            $beginStamp = $timeObj['mergedTime'] ?: C4gReservationDateChecker::getBeginOfDate($timeParams['tsdate'])+$time+3600;
            $endStamp = $timeObj['mergedEndTime'] ?: C4gReservationDateChecker::getBeginOfDate($timeParams['tsdate'])+$time+$timeObjectParams['interval']+$timeObjectParams['durationDiff']; //+3600;
        } else {
            $beginStamp = $timeObj['mergedTime'] ?: C4gReservationDateChecker::getBeginOfDate($timeParams['tsdate'])+$time;
            $endStamp = $timeObj['mergedEndTime'] ?: C4gReservationDateChecker::getBeginOfDate($timeParams['tsdate'])+$time+$timeObjectParams['interval']+$timeObjectParams['durationDiff'];
        }

        $beginStamp = $timeObj['mergedTime'] ?: C4gReservationDateChecker::getBeginOfDate($timeParams['tsdate'])+$time+3600;
//        $endStamp = $timeObj['mergedEndTime'] ?: C4gReservationDateChecker::getBeginOfDate($timeParams['tsdate'])+$time+$timeObjectParams['interval']+$timeObjectParams['durationDiff']+3600; // +1 ToDo Check Why?
        foreach ($timeObjectParams['exclusionPeriods'] as $excludePeriod) {
            if (C4gReservationDateChecker::isStampInPeriod($beginStamp,$excludePeriod['begin'],$excludePeriod['end'],0) ||
                C4gReservationDateChecker::isStampInPeriod($endStamp,$excludePeriod['begin'],$excludePeriod['end'],1)) {
                $timeObj['removeButton'] = true;
            } else if (C4gReservationDateChecker::isStampInPeriod($excludePeriod['begin'],$beginStamp, $endStamp,0) ||
                C4gReservationDateChecker::isStampInPeriod($excludePeriod['end'],$beginStamp,$endStamp,1)) {
                $timeObj['removeButton'] = true;
            }
        }
        $endTimeInterval = $timeObjectParams['interval'];
        $fullDay = false;

        $periodType = $timeParams['type']["periodType"];
        if ($periodType == 'day' || $periodType == 'overnight' || $periodType == 'week') 
        {
            $periodFaktor = 86400;
        } else {
            $periodFaktor = self::getPeriodFaktor($timeObjectParams['object']->getPeriodType());
        }
        
        if ($periodType == 'day' || $periodType == 'overnight' || $periodType == 'week') {
            $bookedDays = self::getBookedDays($timeParams['type'],$timeObjectParams['object']);
            $bookedDay = explode(",",$bookedDays['dates']);
            for ($i = 0; $i < count($bookedDay); $i++) {
                $bookedDay[$i] = strtotime($bookedDay[$i]);
                if (( $bookedDay[$i]+$periodFaktor) >= $beginStamp && ($bookedDay[$i]+$periodFaktor) <= $endStamp) {
                    $fullDay = true;
                } else {
                    $fullDay = false;
                }
            }
        }
        

        $objectCount = intval($timeParams['type']['objectCount']);
        $objectQuantity = intval($timeObjectParams['quantity']);

        if ($objectCount < $objectQuantity) {
            $severalBookingsCapacity = intval($timeObjectParams['object']->getDesiredCapacity()[1]) * $objectCount;
        } else {
             $severalBookingsCapacity = intval($timeObjectParams['object']->getDesiredCapacity()[1]) * $objectQuantity;
        }
      
        if (($timeParams['date'] !== -1) && $timeParams['tsdate'] && $timeParams['nowDate'] &&
            (!$timeParams['checkToday'] || ($timeParams['nowDate'] < $timeParams['tsdate']) ||
                (($timeParams['nowDate'] == $timeParams['tsdate']) && (($checkTime === false) || ($timeParams['nowTime'] < $checkTime))))) {
            
            if ($timeObjectParams['severalBookings'] && ($timeObjectParams['capacity'] && $severalBookingsCapacity && ($severalBookingsCapacity - $calculatorResult->getDbPersons() < $timeObjectParams['capacity']))) {
                $reasonLog = 'too many persons';
            } else if (!$timeObjectParams['severalBookings'] && ($timeObjectParams['capacity'] && intval($timeObjectParams['object']->getDesiredCapacity()[1]) && (intval($timeObjectParams['object']->getDesiredCapacity()[1]) - $calculatorResult->getDbPersons() < $timeObjectParams['capacity']))) {
                $reasonLog = 'too many persons';
            } else if ($timeObjectParams['maxObjects'] && ($calculatorResult->getDbBookings() >= intval($timeObjectParams['maxObjects'])) &&
                (!$timeObjectParams['severalBookings'] || $timeObjectParams['object']->getAllTypesQuantity() || $timeObjectParams['object']->getAllTypesValidity()) || $fullDay) {
                $reasonLog = 'too many bookings';
            } else if ($timeObjectParams['capacity'] && ($timeArray && !empty($timeArray)) && (($timeArray[$timeParams['tsdate']][$time] >= intval($timeObjectParams['capacity']))/* || ($timeArray[$tsdate][$endTime] >= intval($desiredCapacity))*/)) {
                $reasonLog = 'too many bookings per object';
            } else if ($timeObj['removeButton']) {
                $reasonLog = 'exclude period with event configuration or exclude dates';
            } else {
                $timeObj['id'] = $timeObjectParams['object']->getId();
            }
            $timeObj['mergedBeginTime'] = $beginStamp;
            $timeParams['result'] = self::addTime($timeParams, $time, $timeObj, $endTimeInterval, 0, $nxtDay);
        } else if ($timeParams['date'] === -1) {
            $timeParams['result'] = self::addTime($timeParams, $time, $timeObj, $endTimeInterval, 0, $nxtDay);
        }
        if ($timeObj['removeButton'] && $timeParams['type']["periodType"] == "overnight") {
            $timeParams['result'] = [];
        }

        $result = $timeParams['result'];
        if (isset($key)) { $___memo[$key] = $result; }
        return $result;
    }

    /**
     * @param $reservationObject
     * @param $putVars
     */
    public static function preventDublicateBookings($reservationType,$reservationObject,$putVars)
    {
        $result = false;
        if ($reservationObject and $putVars) {
            $objectId = $reservationObject->id;
            $typeId   = $reservationType->id;
            $beginTime = 0;
            if ($reservationType->reservationObjectType === '3') {
                $beginDate = $putVars['beginDate_'.$typeId.'-33'.$objectId];
                foreach ($putVars as $key => $value) {
                    if (strpos($key, "beginTime_" . $typeId . '-33' . $objectId) !== false) {
                        if ($value) {
                            if (strpos($value, '#') !== false) {
                                $newValue = substr($value, 0, strpos($value, '#')); //remove frontend duration
                            } else {
                               $begindate_timestamp = strtotime($beginDate); 
                               if(date('I')){
                                    $beginTime = $value - $begindate_timestamp - 3600; 
                               } else {
                                    $beginTime = $value - $begindate_timestamp; 
                                }     
                               break;
                            }

                            $beginTime = $newValue ?: $value;
                            break;
                        }
                    }
                }
                $beginDateAsTstamp = strtotime($beginDate);
            } else {
                $beginDate = strtotime($putVars['beginDate_' . $typeId]);
                foreach ($putVars as $key => $value) {
                    if (strpos($key, "beginTime_".$typeId) !== false) {
                        if ($value) {
                            if (strpos($value, '#') !== false) {
                                $value = substr($value,0, strpos($value, '#')); //remove frontend duration
                            }

                            $beginTime = $value;
                            break;
                        }
                    }
                }
                $beginDateAsTstamp = $beginDate;
            }
            $reservationId = $putVars['reservation_id'];
            $reservationObjectType_ID = intval($putVars['reservationObjectType']);
           
            $database = Database::getInstance();
            $reservations = $database->prepare("SELECT desiredCapacity FROM `tl_c4g_reservation` WHERE `reservation_type`=? AND `reservation_object`=? AND `reservationObjectType`=? AND `beginDate`=? AND `beginTime`=? AND NOT `cancellation`=?")
            ->execute($typeId,$objectId,$reservationObjectType_ID,$beginDateAsTstamp,$beginTime,'1')->fetchAllAssoc();
            
            $capacityMax = $reservationObject->desiredCapacityMax;
            $chosenCapacity = isset($putVars['desiredCapacity_'.$typeId]) ? intval($putVars['desiredCapacity_'.$typeId]) : null;
            
            $reservationCount = C4gReservationHandler::countReservations($reservations);
            if ($reservationType->severalBookings) {
                $factor = $reservationType->objectCount && ($reservationType->objectCount < $reservationObject->quantity) ? $reservationType->objectCount : $reservationObject->quantity;
                if ($reservations && $factor) {
                    $counter = 0;
                    foreach ($reservations as $reservation) {
                        $counter = $counter + $reservation['desiredCapacity'];
                    }

                    if ($reservationObject->desiredCapacityMax && (($factor * $reservationObject->desiredCapacityMax) <= $counter)) {
                        return true;
                    }
                }
            } else {
                $maxCount = $reservationType->objectCount && ($reservationType->objectCount < $reservationObject->quantity) ? $reservationType->objectCount : $reservationObject->quantity;
                
               if($reservationObject->desiredCapacityMax){
                if ($maxCount && ($reservationObject->desiredCapacityMax && ($reservationCount >= $maxCount))) {
                    return true;
                }
               }else if($maxCount && ($reservationCount>=$maxCount)){
                    return true;
               }
            }
        }
        return $result;
    }

    /**
     * @param $object
     * @return string
     */
    public static function getButtonStateClass($object,$objectType) {
        $result = '';

        if ($object && $object->getAlmostFullyBookedAt() &&  $object->getDesiredCapacity() &&  $object->getDesiredCapacity()[1]) {  //orange state
            $id = $object->getId();
            $database = Database::getInstance();
            $reservations = $database->prepare("SELECT desiredCapacity FROM `tl_c4g_reservation` WHERE `reservation_object`=? AND `reservationObjectType`=? AND NOT `cancellation`=?")
                ->execute($id,$objectType,'1')->fetchAllAssoc();

            $percent = $object->getAlmostFullyBookedAt();
            $reservationCount = self::countReservations($reservations);
            $desiredCapacity = $object->getDesiredCapacity()[1];

            if ((($reservationCount / $desiredCapacity) * 100) >= 100) {
                $result = ' radio_object_fully_booked';
            } else if ((($reservationCount / $desiredCapacity) * 100) >= $percent) {
                $result = ' radio_object_hurry_up';
            }
        }

        return $result;
    }


    /**
     * @param $object
     * @param $withEndTimes
     * @param $showFreeSeats
     * @return array|mixed
     */
    public static function getReservationEventTime($object, $withEndTimes=false, $showFreeSeats=false) {
        $id = $object->getId();
        $database = Database::getInstance();
        $reservations = $database->prepare("SELECT desiredCapacity FROM `tl_c4g_reservation` WHERE `reservation_object`=? AND `reservationObjectType`=? AND NOT `cancellation`=?")
            ->execute($id,'2','1')->fetchAllAssoc();
        $actPersons = 0;
        $actPercent = 0;
        $desiredCapacity = $object->getDesiredCapacity()[1] ? $object->getDesiredCapacity()[1] : 1;
        $capacity = $desiredCapacity;
        if (intval($capacity) && $reservations) {
            foreach ($reservations as $reservation) {
                $actPersons = $actPersons + intval($reservation['desiredCapacity']);
            }

            if ($object->getAlmostFullyBookedAt()) {
                $percent = ($actPersons / intval($capacity)) * 100;
                if ($percent >= $object->getAlmostFullyBookedAt()) {
                    $actPercent = $percent;
                }
            }
        } else {
            $actPercent = 0;
        }
        $timeObj = ['id'=>$id,'act'=>$actPersons,'percent'=>$actPercent,'max'=>$capacity,'showSeats'=>$showFreeSeats];

        $endTime = 0;
        if ($withEndTimes) {
            $endTime = $object->getEndTime();
        }
        return self::addTime([], $object->getBeginTime(), $timeObj, 0, $endTime);
    }
    public static function getMaxParticipentsForObject($objectId,$maxParticipents){
        $id = $objectId;
        $database = Database::getInstance();
        $reservations = $database->prepare("SELECT desiredCapacity FROM `tl_c4g_reservation` WHERE `reservation_object`=? AND `reservationObjectType`=? AND NOT `cancellation`=?")
            ->execute($id,'2','1')->fetchAllAssoc();

        $actPersons = 0;
        if ($reservations) {
            foreach ($reservations as $reservation) {
                $actPersons = $actPersons + intval($reservation['desiredCapacity']);
            }
        }

        return $maxParticipents-$actPersons;
    }
    /**
     * @param $object
     * @param false $withEndTimes
     * @param false $showFreeSeats
     * @return array
     */
    public static function getReservationNowTime($object, $withEndTimes=false, $showFreeSeats=false) {
        $t = 'tl_c4g_reservation';

        if ($object && is_int($object)) {
            $database = Database::getInstance();
            $object = $database->prepare("SELECT * FROM tl_c4g_reservation_object WHERE id = ?")->execute($object)->fetchAssoc();
        }

        $id = intval($object['id']);

        $time = time();

        $oh = $object['openingHours'];
        $weekday = date("w", $time);

        $weekdayStr = C4gReservationDateChecker::getWeekdayStr($weekday);

        $validDate = false;
        foreach ($oh as $key => $day) {
            if (($day != -1) && ($key == $weekdayStr)) {
                foreach ($day as $period) {
                    if (!C4gReservationHandler::checkValidPeriod($time, $period, $weekday)) {
                        continue;
                    } else {
                        $time_begin = $period['time_begin'];
                        $time_end = $period['time_end'];

                        $objDate = new Date(date($GLOBALS['TL_CONFIG']['timeFormat'],$time), Date::getFormatFromRgxp('time'));
                        $nowTime = $objDate->tstamp;

                        if (($time_begin <= $nowTime) && ($time_end >= $nowTime)) {
                            $validDate = true;
                            break;
                        }
                    }
                }
            }
        }

        if ($validDate) {
            $database = Database::getInstance();
            $reservations = $database->prepare("SELECT desiredCapacity FROM `tl_c4g_reservation` WHERE `reservation_object`=? AND `reservationObjectType` IN(1,3) AND `beginDate`<=? AND `endTime`>=? AND NOT `cancellation`=?")
                ->execute($id,$time,$time,'1')->fetchAllAssoc();

            $actPersons = 0;
            $actPercent = 0;
            $min = $object['min'] ?: 1;
            $max = $object['max'] ?: 0;//1;

            if ($max && $reservations) {
                foreach ($reservations as $reservation) {
                    $actPersons = $actPersons + intval($reservation['desiredCapacity']);
                }

                if ($object['almostFullyBookedAt']) {
                    $percent = ($actPersons / intval($max)) * 100;
                    if ($percent >= $object['almostFullyBookedAt']) {
                        $actPercent = $percent;
                    }
                }
            }

            if (!$max) {
                $actPercent = 0;
            } else {
                if ($actPersons >= $max) {
                    $id = -1;
                }
            }

            $timeObj = ['id'=>$id,'act'=>$actPersons,'percent'=>$actPercent,'min'=>$min,'max'=>$max,'showSeats'=>$showFreeSeats];

            $endTime = 0;

            $objDate = new Date(date($GLOBALS['TL_CONFIG']['timeFormat'],time()), Date::getFormatFromRgxp('time'));
            $time = $objDate->tstamp;
            return self::addTime([], $time, $timeObj, $endTime);
        } else {
            return [];
        }
    }


    /**
     * @param null $moduleTypes
     * @param int $objectId
     * @return array
     */
    public static function getReservationObjectList($moduleTypes = null, $objectId = 0, $showPrices = false, $showPricesWithTaxes = false, $getAllTypes = false, $duration = 0, $date = 0, $langCookie = '')
    {
        \Contao\System::loadLanguageFile('fe_c4g_reservation',$langCookie ?: $GLOBALS['TL_LANGUAGE']);
        $objectlist = array();
        $allTypesList = array();
        foreach ($moduleTypes as $moduleType) {
            if ($moduleType) {
                $typeArr = [];
                if (is_array($moduleType)) {
                    $type = $moduleType;
                    $typeArr[] = $moduleType['id'];
                } else {
                    $typeArr = $moduleTypes;
                    $database = Database::getInstance();
                    $type = $database->prepare("SELECT * FROM `tl_c4g_reservation_type` WHERE `id`=?")
                        ->execute($moduleType)->fetchAssoc();
                }


                if ($type && $type['reservationObjectType'] === '2') {
                    $objectlist = C4gReservationHandler::getReservationObjectEventList($typeArr, $objectId, $type, $showPrices, $showPricesWithTaxes, $langCookie);

                    if ($getAllTypes) {
                        foreach($objectlist as $key=>$object) {
                            $allTypesList['events'][$key] = $object;
                        }
                    } else {
                        break;
                    }

                } else {
                    $objectlist = C4gReservationHandler::getReservationObjectDefaultList($typeArr, $objectId, $type, $showPrices, $showPricesWithTaxes, $duration, $date, $langCookie);

                    if ($getAllTypes) {
                        foreach($objectlist as $key=>$object) {
                            $allTypesList['default'][$key] = $object;
                        }
                    } else {
                        break;
                    }
                }
            }
        }

        if ($getAllTypes) {
            return $allTypesList;
        } else {
            return $objectlist;
        }
    }

    public static function formatPrice($price) {
        $price = number_format(floatval($price),$GLOBALS['TL_LANG']['fe_c4g_reservation']['decimals'],$GLOBALS['TL_LANG']['fe_c4g_reservation']['decimal_seperator'],$GLOBALS['TL_LANG']['fe_c4g_reservation']['thousands_seperator']);

        if ($GLOBALS['TL_LANG']['fe_c4g_reservation']['switchCurrencyPosition']) {
            return $GLOBALS['TL_LANG']['fe_c4g_reservation']['currency'] . ' ' .$price;
        } else {
            return $price.' '.$GLOBALS['TL_LANG']['fe_c4g_reservation']['currency'];
        }
    }

    /**
     * @param null $moduleTypes
     * @param int $objectId
     * @return array
     */
    public static function getReservationObjectEventList($moduleTypes = null, $objectId = 0, $type, $showPrices = false, $showPricesWithTaxes = false, $langCookie = '', $startTime = 0)
    {
        $objectList = array();
        $database = Database::getInstance();
        $almostFullyBookedAt = $type['almostFullyBookedAt'];
        if ($objectId) {
            $events = $database->prepare("SELECT * FROM tl_c4g_reservation_event WHERE `pid` = ?")->execute($objectId)->fetchAllAssoc();
            if ($events) {
                if (count($events) > 1) {
                    C4gLogModel::addLogEntry('reservation', 'There are more than one event connections. Check Event: '.$objectId);
                } else if (count($events) > 0) {
                    $event = $events[0];
                }
            }

            if ($langCookie) {
                $GLOBALS['LANGUAGE'] = $GLOBALS['LANGUAGE'] ?: $langCookie;
            }
            System::loadLanguageFile('fe_c4g_reservation', key_exists('language', $GLOBALS ) && $GLOBALS['LANGUAGE'] ? $GLOBALS['LANGUAGE'] : 'de');

            $eventObject = $database->prepare("SELECT * FROM tl_calendar_events WHERE `id` = ?")->execute($objectId)->fetchAssoc();
            $calendarObject =  $database->prepare('SELECT * FROM tl_calendar WHERE id=? AND activateEventReservation="1"')->execute($eventObject['pid'])->fetchAssoc();

            $startTime = $startTime ?: time();

            //$eventObject = \CalendarEventsModel::findByPk($objectId);
            if (($event || $calendarObject) && $eventObject && $eventObject['published'] && (($eventObject['startTime'] && ($eventObject['startTime'] > $startTime)) || (!$eventObject['startTime'] && $eventObject['startDate'] && $eventObject['startDate'] >= $startTime))) {

                $targetAudience = key_exists('targetAudience', $event) && $event['targetAudience'] ? \Contao\StringUtil::deserialize($event['targetAudience'], true) : [];
                $reservationTargetAudience = key_exists('reservationTargetAudience', $calendarObject) && $calendarObject['reservationTargetAudience'] ? \Contao\StringUtil::deserialize($calendarObject['reservationTargetAudience'], true) : [];

                $speaker = key_exists('speaker', $event) && $event['speaker'] ? \Contao\StringUtil::deserialize($event['speaker'], true) : [];
                $reservationSpeaker = key_exists('reservationSpeaker', $calendarObject) && $calendarObject['reservationSpeaker'] ? \Contao\StringUtil::deserialize($calendarObject['reservationSpeaker'], true) : [];

                $topic = key_exists('topic', $event) && $event['topic'] ? \Contao\StringUtil::deserialize($event['topic'], true) : [];
                $reservationTopic = key_exists('reservationTopic', $calendarObject) && $calendarObject['reservationTopic'] ? \Contao\StringUtil::deserialize($calendarObject['reservationTopic'], true) : [];

                $conferenceLink = key_exists('conferenceLink', $event) && $event['conferenceLink'] ? $event['conferenceLink'] : '';

                $maxParticipants = $event['maxParticipants'] ?: $calendarObject['reservationMaxParticipants'];
                $maxParticipants = self::getMaxParticipentsForObject($objectId, $maxParticipants);

                $frontendObject = new C4gReservationFrontendObject();
                $frontendObject->setType(2);
                $frontendObject->setId($eventObject['id']);
                $eventObject['price'] = $event['price'] ?: $calendarObject['reservationPrice'];
                $eventObject['priceoption'] = $event['priceoption'] ?: $calendarObject['reservationPriceOption'];
                $eventObject['taxOptions'] = $event['taxOptions'] ?: $calendarObject['taxOptions'] ?: '';

                $reservationOptionSum = '';
                if (key_exists('reservationOptionSum', $event) && $event['reservationOptionSum']) {
                    $reservationOptionSum = $event['reservationOptionSum'];
                } else if (key_exists('reservationOptionSum', $calendarObject) && $calendarObject['reservationOptionSum']) {
                    $reservationOptionSum = $calendarObject['reservationOptionSum'];
                }
                $eventObject['reservationOptionSum'] =  $reservationOptionSum;

                $participantOptionSum = '';
                if (key_exists('participantOptionSum', $event) && $event['participantOptionSum']) {
                    $participantOptionSum = $event['participantOptionSum'];
                } else if (key_exists('participantOptionSum', $calendarObject) && $calendarObject['participantOptionSum']) {
                    $participantOptionSum = $calendarObject['participantOptionSum'];
                }
                $eventObject['participantOptionSum'] = $participantOptionSum;

                $priceArray = $showPrices ? C4gReservationCalculator::calcPrices($eventObject, $type, true, 1, '', '','',$showPricesWithTaxes) : array('price' => 0, 'priceSum' => 0);
                $price = ($priceArray['price'] == 0 || $priceArray['price'] == '' || empty($priceArray['price'])) ? '' : C4gReservationHandler::formatPrice($priceArray['price']).$priceArray['priceInfo'];

                $frontendObject->setCaption($price ? $eventObject['title']." (".$price.")" : $eventObject['title']);
                $frontendObject->setDesiredCapacity([$event['minParticipants'] ?:  $calendarObject['reservationMinParticipants'], $event['maxParticipants'] ?: $maxParticipants]);
                $frontendObject->setBeginDate(C4gReservationDateChecker::mergeDateWithTime($eventObject['startDate'],$eventObject['startTime']));
                $frontendObject->setBeginTime(C4gReservationDateChecker::mergeDateWithTime($eventObject['startDate'],$eventObject['startTime']));
                $frontendObject->setEndDate(C4gReservationDateChecker::mergeDateWithTime($eventObject['endDate'],$eventObject['endTime']));
                $frontendObject->setEndTime(C4gReservationDateChecker::mergeDateWithTime($eventObject['endDate'],$eventObject['endTime']));
                $frontendObject->setMinReservationDay($event['min_reservation_day']);
                $frontendObject->setAlmostFullyBookedAt($almostFullyBookedAt);
                $frontendObject->setNumber($event['number'] ?: '');
                $frontendObject->setEventDuration('');
                $frontendObject->setAudience($targetAudience ?: $reservationTargetAudience);
                $frontendObject->setSpeaker($speaker ?: $reservationSpeaker);
                $frontendObject->setTopic($topic ?: $reservationTopic);
                $frontendObject->setLocation($event['location'] ?: $calendarObject['reservationLocation'] ?: $type['location']);
                $frontendObject->setOrganizer($event['organizer'] ?: $calendarObject['reservationOrganizer'] ?: 0);
                $frontendObject->setDescription($eventObject['teaser'] ?: '');
                $frontendObject->setImage($eventObject['singleSRC']);
                $frontendObject->setPrice($event['price'] ?: $calendarObject['reservationPrice'] ?: 0.00);
                $frontendObject->setTaxOptions($event['taxOptions'] ?: '');
//                $frontendObject->setTaxOptions($event['taxOptions']);
                $frontendObject->setPriceOption($event['priceoption'] ?: $calendarObject['reservationPriceOption']);
                $frontendObject->setConferenceLink($conferenceLink ?: '');
                $objectList[] = $frontendObject;
            }
        } else {
            $idString = "(";
            foreach ($moduleTypes as $key => $typeId) {
                $idString .= "\"$typeId\"";
                if (!(array_key_last($moduleTypes) === $key)) {
                    $idString .= ",";
                }
            }
            $idString .= ")";


            $calendarObject = $database->prepare("SELECT * FROM tl_calendar WHERE activateEventReservation='1' AND `reservationType` IN $idString")->execute()->fetchAllAssoc();
            if ($calendarObject) {
                foreach ($calendarObject as $calendar) {
                    $allEvents = $database->prepare("SELECT * FROM tl_calendar_events WHERE `id` = ?")->execute($calendar['iid'])->fetchAllAssoc();
                    foreach ($allEvents as $eventObject) {
                        $reservationEvent = $database->prepare("SELECT * FROM tl_c4g_reservation_event WHERE `id` = ? AND `reservationType` IN $idString")->execute($eventObject['id'])->fetchAssoc();

                        $targetAudience = $reservationEvent['targetAudience'] ? \Contao\StringUtil::deserialize($reservationEvent['targetAudience'], true) : [];
                        $reservationTargetAudience = $calendarObject['reservationTargetAudience'] ? \Contao\StringUtil::deserialize($calendarObject['reservationTargetAudience'], true) : [];

                        $speaker = $reservationEvent['speaker'] ? \Contao\StringUtil::deserialize($reservationEvent['speaker'], true) : [];
                        $reservationSpeaker = $calendarObject['reservationSpeaker'] ? \Contao\StringUtil::deserialize($calendarObject['reservationSpeaker'], true) : [];

                        $topic = $reservationEvent['topic'] ? \Contao\StringUtil::deserialize($reservationEvent['topic'], true) : [];
                        $reservationTopic = $calendarObject['reservationTopic'] ? \Contao\StringUtil::deserialize($calendarObject['reservationTopic'], true) : [];

                        $conferenceLink = $reservationEvent['conferenceLink'] ?: '';

                        if ($eventObject && $eventObject['published'] && (($eventObject['startTime'] && ($eventObject['startTime'] > $startTime)) || (!$eventObject['startTime'] && $eventObject['startDate'] && $eventObject['startDate'] >= $startTime))) {
                            $maxParticipants = $reservationEvent['maxParticipants'] ?: $calendarObject['reservationMaxParticipants'];
                            $maxParticipants = self::getMaxParticipentsForObject($objectId, $maxParticipants);

                            $frontendObject = new C4gReservationFrontendObject();
                            $frontendObject->setType(2);
                            $frontendObject->setId($eventObject['id']);
                            $eventObject['price'] = $reservationEvent['price'] ?: $calendarObject['reservationPrice'];
                            $eventObject['priceoption'] = $reservationEvent['priceoption'] ?: $calendarObject['reservationPriceOption'];
                            $priceArray = $showPrices ? C4gReservationCalculator::calcPrices($eventObject, $type, true, 1,'','','',$showPricesWithTaxes) : array('price' => 0, 'priceSum' => 0);
                            $price = ($priceArray['price'] == 0 || $priceArray['price'] == '' || empty($priceArray['price'])) ? '' : C4gReservationHandler::formatPrice($priceArray['price']);
                            $frontendObject->setCaption($showPrices && $price ? $eventObject['title'] . " (" . $price . ")" : $eventObject['title']);
                            $frontendObject->setDesiredCapacity([$reservationEvent['minParticipants'] ?: $calendarObject['reservationMinParticipants'], $maxParticipants]);
                            $frontendObject->setBeginDate(C4gReservationDateChecker::mergeDateWithTime($eventObject['startDate'], $eventObject['startTime']));
                            $frontendObject->setBeginTime(C4gReservationDateChecker::mergeDateWithTime($eventObject['startDate'], $eventObject['startTime']));
                            $frontendObject->setEndDate(C4gReservationDateChecker::mergeDateWithTime($eventObject['endDate'], $eventObject['endTime']));
                            $frontendObject->setEndTime(C4gReservationDateChecker::mergeDateWithTime($eventObject['endDate'], $eventObject['endTime']));
                            $frontendObject->setAlmostFullyBookedAt($almostFullyBookedAt);
                            $frontendObject->setNumber($reservationEvent['number']);
                            $frontendObject->setEventDuration('');
                            $frontendObject->setAudience($targetAudience ?: $reservationTargetAudience);
                            $frontendObject->setSpeaker($speaker ?: $reservationSpeaker);
                            $frontendObject->setTopic($topic ?: $reservationTopic);
                            $frontendObject->setLocation($reservationEvent['location'] ?: $calendarObject['reservationLocation'] ?: $reservationEvent['location']);
                            $frontendObject->setOrganizer($reservationEvent['organizer'] ?: $calendarObject['reservationOrganizer'] ?: 0);
                            $frontendObject->setDescription($eventObject['teaser'] ?: '');
                            $frontendObject->setImage($eventObject['singleSRC']);
                            $frontendObject->setPrice($reservationEvent['price'] ?: $calendarObject['reservationPrice'] ?: 0.00);
                            $frontendObject->setTaxOptions($reservationEvent['taxOptions'] ?: '');
//                            $frontendObject->setTaxOptions($reservationEvent['taxOptions']);
                            $frontendObject->setPriceOption($reservationEvent['priceoption'] ?: $calendarObject['reservationPriceOption']);
                            $frontendObject->setConferenceLink($conferenceLink ?: '');
                            $objectList[] = $frontendObject;
                        }
                    }
                }
            }
        }

        return $objectList;
    }

    /**
     * @param $opening_hours
     * @param $type
     * @return false[]
     */
    private static function checkWeekdays(&$opening_hours, $type) {
        // Build a stable, light-weight signature for caching
        $sigArray = [];
        foreach ($opening_hours as $d => $p) {
            // only take begin/end needed for decision to keep signature small
            $sigArray[$d] = [
                isset($p[0]['time_begin']) ? (int)$p[0]['time_begin'] : 0,
                isset($p[0]['time_end']) ? (int)$p[0]['time_end'] : 0,
                isset($p[1]['time_end']) ? (int)$p[1]['time_end'] : 0,
            ];
        }
        $sig = md5(json_encode([$sigArray, (string)($type['periodType'] ?? '')]));
        if (isset(self::$weekdaysCache[$sig])) {
            return self::$weekdaysCache[$sig];
        }

        $weekdays = array('0'=>false,'1'=>false,'2'=>false,'3'=>false,'4'=>false,'5'=>false,'6'=>false);
        $timeBeginKey = 0;
        $timeEndKey = ($type['periodType'] ?? '') === 'overnight' ? 1 : 0;

        foreach ($opening_hours as $day=>$period) {
            if (!empty($opening_hours[$day][$timeBeginKey]['time_begin']) && !empty($opening_hours[$day][$timeEndKey]['time_end'])) {
                if ($timeEndKey) {
                    $opening_hours[$day][$timeBeginKey]['time_end_org'] = $opening_hours[$day][$timeBeginKey]['time_end'];
                    $opening_hours[$day][$timeBeginKey]['time_end'] = $opening_hours[$day][$timeEndKey]['time_end'] < $opening_hours[$day][$timeBeginKey]['time_end']
                        ? $opening_hours[$day][$timeEndKey]['time_end'] + 86400
                        : $opening_hours[$day][$timeBeginKey]['time_end'];
                }
                $weekdays[C4gReservationDateChecker::getWeekdayNumber($day)] = true;
            }
        }

        return self::$weekdaysCache[$sig] = $weekdays;
    }

    /**
     * @param null $moduleTypes
     * @param $type
     * @param $calculator
     * @param $date
     * @param false $showPrices
     * @return array
     */
    public static function getReservationObjectDefaultList($moduleTypes = null, $objectId = 0, $type, $showPrices = false, $showPricesWithTaxes = false, $duration = 0, $date = 0, $langCookie = '')
    {
        $objectList = array();

        $database = Database::getInstance();

        if ($langCookie) {
            $GLOBALS['TL_LANGUAGE'] = $GLOBALS['TL_LANGUAGE'] ?: $langCookie;
        }
        System::loadLanguageFile('fe_c4g_reservation', $GLOBALS['TL_LANGUAGE']);

        if ($objectId && is_numeric($objectId)) {
            $sql = "SELECT * FROM tl_c4g_reservation_object WHERE published = ? AND id = ? ORDER BY caption";
            $allObjects = $database->prepare($sql)->execute('1', $objectId)->fetchAllAssoc();
        } else if ($objectId && is_string($objectId)) {
            $sql = "SELECT * FROM tl_c4g_reservation_object WHERE published = ? AND alias = ? ORDER BY caption";
            $allObjects = $database->prepare($sql)->execute('1', $objectId)->fetchAllAssoc();
        } else {
            $sql = "SELECT * FROM tl_c4g_reservation_object WHERE published = ? ORDER BY caption";
            $allObjects = $database->prepare($sql)->execute('1')->fetchAllAssoc();
        }

        $almostFullyBookedAt = $type['almostFullyBookedAt'] ?: 0;
        if ($moduleTypes) {
            $types = $moduleTypes;
            $objects = [];
            foreach ($allObjects as $object) {
                $objectTypes = \Contao\StringUtil::deserialize($object['viewableTypes'], true);
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
            $cloneObject = false;
            if ($type['cloneObject']) {
                $cloneObject = $database->prepare("SELECT * FROM tl_c4g_reservation_object WHERE id = ?")->execute($type['cloneObject'])->fetchAssoc();
            }

            foreach ($objects as $object) {
                $reservationObjectID = $object['id'];
                $reservationTypeID = $type['id'];
                $database = Database::getInstance();
                
                $sql = "SELECT desiredCapacity FROM `tl_c4g_reservation` WHERE `reservation_object` =? AND `reservation_type` =? AND NOT `cancellation`=?";
                $reservations = $database->prepare($sql)
                ->execute($reservationObjectID, $reservationTypeID,'1')->fetchAllAssoc();
                $currentReservations = C4gReservationHandler::countReservations($reservations);

                $sql = "SELECT desiredCapacityMax FROM `tl_c4g_reservation_object` WHERE `id` =?";
                $objectMaxCapacity = $database->prepare($sql)
                ->execute($reservationObjectID)->fetchAllAssoc();
                $objectMaxCapacity = $objectMaxCapacity[0]['desiredCapacityMax'];

                $sql = "SELECT severalBookings FROM `tl_c4g_reservation_type` WHERE `id`=?";
                $severalBookings = $database->prepare($sql)
                ->execute($reservationTypeID)->fetchAllAssoc();
                $severalBookings = $severalBookings[0]['severalBookings'];

                $frontendObject = new C4gReservationFrontendObject();
                $frontendObject->setType(1);
                $frontendObject->setId($object['id']);

                // Caption mit per-request Cache (
                $frontendObject->setCaption($object['caption']);

                // prepare language key once
                if (self::$langLower === null) {
                    self::$langLower = strtolower((string)($GLOBALS['TL_LANGUAGE'] ?? ''));
                }
                $captionCacheKey = ($object['id'] ?? '0') . '_' . self::$langLower;
                if (isset(self::$captionCache[$captionCacheKey])) {
                    $frontendObject->setCaption(self::$captionCache[$captionCacheKey]);
                } else {
                    $captions = StringUtil::deserialize($object['options'], true);
                    if ($captions) {
                        foreach ($captions as $caption) {
                            $capLang = strtolower((string)($caption['language'] ?? ''));
                            if (($capLang !== '' && strpos(self::$langLower, $capLang) !== false) && !empty($caption['caption'])) {
                                $frontendObject->setCaption($caption['caption']);
                                self::$captionCache[$captionCacheKey] = $caption['caption'];
                                break;
                            }
                        }
                    }
                }

                $priceArray = $showPrices ? C4gReservationCalculator::calcPrices($object, $type, false, 1, $duration, $date,'',$showPricesWithTaxes) : array('price' => 0, 'priceSum' => 0);
                $price = ($priceArray['price'] == 0 || $priceArray['price'] == '' || empty($priceArray['price'])) ? '' : C4gReservationHandler::formatPrice($priceArray['price']).$priceArray['priceInfo'];

                $frontendObject->setCaption($showPrices && $price ? $frontendObject->getCaption()." (".$price.")" : $frontendObject->getCaption());

                $frontendObject->setPeriodType($type['periodType']);
                $frontendObject->setReservationTypes(\Contao\StringUtil::deserialize($object['viewableTypes'], true));
                $frontendObject->setQuantity($object['quantity']);
                $frontendObject->setAlmostFullyBookedAt($almostFullyBookedAt);
                $frontendObject->setPriority($object['priority'] ?: 0);
                $frontendObject->setSwitchAllTypes($object['switchAllTypes']);
                $frontendObject->setDescription($object['description'] ?: '');
                $frontendObject->setImage($object['image']);
                $frontendObject->setLocation($object['location'] ?: $type['location']);
                $frontendObject->setAudience($object['targetAudience'] ? \Contao\StringUtil::deserialize($object['targetAudience'], true) : []);
                $frontendObject->setSpeaker($object['speaker'] ? \Contao\StringUtil::deserialize($object['speaker'], true) : []);
                $frontendObject->setTopic($object['topic'] ? \Contao\StringUtil::deserialize($object['topic'], true) : []);
                $frontendObject->setPrice($object['price'] ?: 0.00);
                $frontendObject->setTaxOptions($object['taxOptions'] ?: '');
                $frontendObject->setPriceOption($object['priceoption']);
                $frontendObject->setTypeOfObject($object['typeOfObject']);
                $beginDateTimeCheck = $object['dateTimeBegin'];
                $frontendObject->setDateTimeBegin($object['dateTimeBegin'] ?: 0); 
                $frontendObject->setTypeOfObjectDuration($object['typeOfObjectDuration']);
                $frontendObject->setCurrentReservations($currentReservations);
                $frontendObject->setSeveralBookings($severalBookings);

                if ($cloneObject) {
                    $frontendObject->setTimeinterval(($object['time_interval'] && $object['time_interval'] !== 1) || !$cloneObject['time_interval'] ? $object['time_interval']: $cloneObject['time_interval']);
                    $frontendObject->setDuration($object['duration'] ?: $cloneObject['duration']);
                    $frontendObject->setMinReservationDay($object['min_reservation_day'] ?: $cloneObject['min_reservation_day']);
                    $frontendObject->setMaxReservationDay($object['max_reservation_day'] ?: $cloneObject['max_reservation_day']);

                    if ($object['maxBeginTime'] || $cloneObject['maxBeginTime']) {
                        $frontendObject->setMaxBeginTime($object['maxBeginTime'] ?: $cloneObject['maxBeginTime']);
                    } else {
                        $frontendObject->setMaxBeginTime('');
                    }

                    $frontendObject->setDesiredCapacity([$object['desiredCapacityMin'] ?: $cloneObject['desiredCapacityMin'], $object['desiredCapacityMax'] ?: $cloneObject['desiredCapacityMax']]);
                    $frontendObject->setAllTypesQuantity($object['allTypesQuantity'] ?: intval($cloneObject['allTypesQuantity']));
                    $frontendObject->setAllTypesValidity($object['allTypesValidity'] ?: intval($cloneObject['allTypesValidity']));
                    //$frontendObject->setAllTypesEvents(\Contao\StringUtil::deserialize($object['allTypesEvents']) ?: \Contao\StringUtil::deserialize($cloneObject['allTypesEvents']));
                    $frontendObject->setLocation($frontendObject->getLocation() ?: $cloneObject['location']);
                } else {
                    $frontendObject->setTimeinterval($object['time_interval']);
                    $frontendObject->setDuration($object['duration']);
                    $frontendObject->setMinReservationDay($object['min_reservation_day']);
                    $frontendObject->setMaxReservationDay($object['max_reservation_day']);
                    $frontendObject->setMaxBeginTime($object['maxBeginTime'] ?: '');
                    $frontendObject->setDesiredCapacity([$object['desiredCapacityMin'], $object['desiredCapacityMax']]);
                    $frontendObject->setAllTypesQuantity($object['allTypesQuantity'] ?: 0);
                    $frontendObject->setAllTypesValidity($object['allTypesValidity'] ?: 0);
                    //$frontendObject->setAllTypesEvents(\Contao\StringUtil::deserialize($object['allTypesEvents']) ?: []);
                }

                $opening_hours = array();
                $datesExclusion = array();

                if ($frontendObject->getTypeOfObject() !== 'fixed_date') {
                    if ($cloneObject) {
                        $opening_hours['su'] = \Contao\StringUtil::deserialize($cloneObject['oh_sunday'], true);
                        $opening_hours['mo'] = \Contao\StringUtil::deserialize($cloneObject['oh_monday'], true);
                        $opening_hours['tu'] = \Contao\StringUtil::deserialize($cloneObject['oh_tuesday'], true);
                        $opening_hours['we'] = \Contao\StringUtil::deserialize($cloneObject['oh_wednesday'], true);
                        $opening_hours['th'] = \Contao\StringUtil::deserialize($cloneObject['oh_thursday'], true);
                        $opening_hours['fr'] = \Contao\StringUtil::deserialize($cloneObject['oh_friday'], true);
                        $opening_hours['sa'] = \Contao\StringUtil::deserialize($cloneObject['oh_saturday'], true);
                    } else {
                        $opening_hours['su'] = \Contao\StringUtil::deserialize($object['oh_sunday'], true);
                        $opening_hours['mo'] = \Contao\StringUtil::deserialize($object['oh_monday'], true);
                        $opening_hours['tu'] = \Contao\StringUtil::deserialize($object['oh_tuesday'], true);
                        $opening_hours['we'] = \Contao\StringUtil::deserialize($object['oh_wednesday'], true);
                        $opening_hours['th'] = \Contao\StringUtil::deserialize($object['oh_thursday'], true);
                        $opening_hours['fr'] = \Contao\StringUtil::deserialize($object['oh_friday'], true);
                        $opening_hours['sa'] = \Contao\StringUtil::deserialize($object['oh_saturday'], true);
                    }

                    $weekdays = C4gReservationHandler::checkWeekdays($opening_hours, $type);
                    $frontendObject->setWeekdayExclusion($weekdays);
                    $frontendObject->setOpeningHours($opening_hours);

                    $datesExclusion = \Contao\StringUtil::deserialize($object['days_exclusion'], true);
                    $calendars = \Contao\StringUtil::deserialize($object['allTypesEvents'], true);

                    if (is_array($calendars) || is_object($calendars)) {
                        // Reuse prepared statement for repeated calendar event fetches
                        $stmtEvents = $database->prepare("SELECT * FROM tl_calendar_events WHERE `pid` = ? AND `published` = '1'");
                        foreach ($calendars as $calendarId) {
                            if ($calendarId) {
                                $events = $stmtEvents->execute($calendarId)->fetchAllAssoc();

                                foreach ($events as $event) {
                                    if ($event){
                                        $startDate = $event['startDate'];
                                        $endDate = $event['endDate'] ?: $startDate;
                                        $startTime = $event['startTime'] ?: C4gReservationDateChecker::getBeginOfDate($event['startDate']);
                                        $endTime = $event['endTime'];
                                        if ($startTime != $endTime) {
                                            $endTime = $endTime ?: C4gReservationDateChecker::getBeginOfDate($event['startDate'])+86399;
                                        } else {
                                            $endTime = $endTime ?: C4gReservationDateChecker::getBeginOfDate($event['startDate'])+86399;
                                        }
    
                                        $startDateTime = C4gReservationDateChecker::mergeDateAndTimeStamp($startDate, $startTime); //+1 ToDo Check Why?
                                        $endDateTime = C4gReservationDateChecker::mergeDateAndTimeStamp($endDate, $endTime);
                                        $datesExclusion[] = ['date_exclusion'=>$startDateTime, 'date_exclusion_end'=>$endDateTime];
    
                                        if ($event['recurring']) {
                                            $repeatEach = StringUtil::deserialize($event['repeatEach'], true);
                                            $repeatEnd = $event['repeatEnd']; //timestamp
                                            $recurrences = $event['recurrences'];
    
                                            switch ($repeatEach['unit']) {
                                                case 'days':
                                                    $recInterval = 86400*$repeatEach['value'];
                                                    break;
                                                case 'weeks':
                                                    $recInterval = 86400*7*$repeatEach['value'];
                                                    break;
                                                case 'months': //ToDo other solution
                                                    $recInterval = 86400*7*4*$repeatEach['value'];
                                                    break;
                                                case 'years':  //ToDo other solution
                                                    $recInterval = 86400*12*4*7*$repeatEach['value'];
                                                    break;
                                                default:
                                                    $recInterval = 0;
                                                    break;
                                            }
    
                                            for ($i=0;$i<=$recurrences;$i++) {
                                                $startDateTime = $startDateTime + $recInterval;
                                                $endDateTime = $endDateTime + $recInterval;
    
                                                if ($startDateTime >= $repeatEnd) {
                                                    break;
                                                }
    
                                                $datesExclusion[] = ['date_exclusion'=>$startDateTime, 'date_exclusion_end'=>$endDateTime];
                                            }
                                        }
                                    }
    
                                }
                            }
                        }
                    }
                    
                }

                $frontendObject->setDatesExclusion($datesExclusion);

                $objectList[] = $frontendObject;
            }
        }

        return $objectList;
    }

    public static function countReservations($reservations) {
        $currentReservation = 0;
        if ($reservations && is_countable($reservations)){
            foreach ($reservations as $reservation) {
                $currentReservation = $currentReservation + intval($reservation['desiredCapacity']);
            }
        }
        return $currentReservation;
    }

    public static function preventNonCorrectPeriod($reservationType,$reservationObject,$putVars)
    {
        $result = false;
        if ($reservationObject and $putVars) {
            $objectId = $reservationObject->id;
            $typeId   = $reservationType->id;
            $periodType = $reservationType->periodType;
            $beginTime = 0;
            if ($reservationType->reservationObjectType === '3') {
                $beginDate = $putVars['beginDate_'.$typeId.'-33'.$objectId];
                foreach ($putVars as $key => $value) {
                    if (strpos($key, "beginTime_" . $typeId . '-33' . $objectId) !== false) {
                        if ($value) {
                            if (strpos($value, '#') !== false) {
                                $newValue = substr($value, 0, strpos($value, '#')); //remove frontend duration
                            } else {
                               $begindate_timestamp = strtotime($beginDate); 
                               if(date('I')){
                                    $beginTime = $value - $begindate_timestamp - 3600; 
                               } else {
                                    $beginTime = $value - $begindate_timestamp; 
                                }     
                               break;
                            }

                            $beginTime = $newValue ?: $value;
                            break;
                        }
                    }
                }
                $beginDateAsTstamp = strtotime($beginDate);
            } else {
                $beginDate = strtotime($putVars['beginDate_' . $typeId]);
                foreach ($putVars as $key => $value) {
                    if (strpos($key, "beginTime_".$typeId) !== false) {
                        if ($value) {
                            if (strpos($value, '#') !== false) {
                                $value = substr($value,0, strpos($value, '#')); //remove frontend duration
                            }

                            $beginTime = $value;
                            break;
                        }
                    }
                }
                $beginDateAsTstamp = $beginDate;
            }
            $reservationId = $putVars['reservation_id'];
            $objectType = intval($putVars['reservationObjectType']);
            $reservationDuration = intval($putVars['duration_'.$typeId]);
            $reservationDuration = $periodType == 'week' ? $reservationDuration * 7 : $reservationDuration;
                     
            $reservationPeriodType = $reservationType->periodType;
            $periodFaktor = self::getPeriodFaktor($reservationPeriodType);
            $severalBookings = $reservationType->severalBookings;
            $reservationObjectCount = $reservationType->objectCount;
            $minDuration = intval($reservationType->min_residence_time ? $reservationType->min_residence_time : $reservationObject->time_interval);
            $objectQuantity = $reservationObject->quantity;
            $reservationAllTypesQuantity = $reservationObject->allTypesQuantity;

            $database = Database::getInstance();
            $currentBookedTimes = $database->prepare("SELECT beginDate, endDate FROM `tl_c4g_reservation` WHERE `reservation_type`=? AND `reservation_object`=? AND `reservationObjectType`=? AND NOT `cancellation`=?")
            ->execute($typeId,$objectId,$objectType,'1')->fetchAllAssoc();  
            
            $otherObjectsBookedTimes = $database->prepare("SELECT beginDate,endDate,desiredCapacity FROM `tl_c4g_reservation` WHERE `reservation_type`=? AND `reservation_object`!=? AND `reservationObjectType`=? AND NOT `cancellation`=?") 
            ->execute($typeId,$objectId,$objectType,'1')->fetchAllAssoc(); 
            
            if ($currentBookedTimes || $otherObjectsBookedTimes) {
                if ($currentBookedTimes) {
                    $i = 0;
                    foreach ($currentBookedTimes as $currentBookings) { 
                        $bookedBeginDate[$i] = $currentBookings['beginDate'];
                        $bookedEndDate[$i++] = $currentBookings['endDate']; 
                    }
                }

                if (isset($bookedBeginDate)) sort($bookedBeginDate);
                if (isset($bookedEndDate)) sort($bookedEndDate);
               
                $i = 0;
                if ($otherObjectsBookedTimes) {
                    $otherDates = self::getBookedDates($minDuration,$otherObjectsBookedTimes,$periodFaktor,$periodType);
                    foreach ($otherDates as $otherDate) {
                        $allDates[$i++] = $otherDate;
                    }
                }

                if ($currentBookedTimes) {
                    $fullyBookedDate = self::getFullyBookedDates($minDuration,$currentBookedTimes,$periodFaktor, $objectQuantity, $objectId, $periodType);
                    $currentDates = self::getBookedDates($minDuration,$currentBookedTimes,$periodFaktor,$periodType);
                    foreach ($currentDates as $currentDate) {
                        $allDates[$i++] = $currentDate;
                    }
                }    

                $allDatesQuantity = array_count_values($allDates);
                $i = 0;
                foreach ($allDatesQuantity as $bookedKey => $bookedQuantity) {
                    if ($bookedQuantity >= $reservationObjectCount) {
                        $allFullyBookedDates[$i] = $bookedKey;
                        $i++;
                    }
                }

                if ($fullyBookedDate || $allFullyBookedDates) {
                    foreach ($currentBookedTimes as $currentTimes) {
                        $currentTimeBegin = $currentTimes['beginDate'];
                        
                        $periodCounterPosition = $beginDateAsTstamp;
                        for ($i = 0; $i < $reservationDuration; $i++) {
                            $y=0;
                            foreach ($fullyBookedDate as $date) {
                               if ($y !=0 && $periodType == 'overnight' && $periodCounterPosition == ($date - $periodFaktor)) {
                                    return true;
                                } else if ($periodType != 'overnight' && $periodCounterPosition == $date) {
                                    return true;
                                }
                                $y++;
                            }
                            $periodCounterPosition += $periodFaktor;
                        }
                    }

                    foreach ($otherObjectsBookedTimes as $currentTimes) {
                        $currentTimeBegin = $currentTimes['beginDate'];
                        
                        $periodCounterPosition = $beginDateAsTstamp;
                        for ($i = 0; $i < $reservationDuration; $i++) {
                            $y=0;
                            foreach ($fullyBookedDate as $date) {
                               if ($y !=0 && $periodType == 'overnight' && $periodCounterPosition == ($date - $periodFaktor)) {
                                    return true;
                                } else if ($periodType != 'overnight' && $periodCounterPosition == $date) {
                                    return true;
                                }
                                $y++;
                            }
                            $periodCounterPosition += $periodFaktor;
                        }
                    }
                }

                foreach ($currentBookedTimes as $currentTimes) {
                    $currentTimeBegin = $currentTimes['beginDate'];
                    
                    $periodCounterPosition = $beginDateAsTstamp;
                    for ($i = 0; $i < $reservationDuration; $i++) {
                        foreach ($fullyBookedDate as $date) {
                            $y=0;
                            foreach ($fullyBookedDate as $date) {
                               if ($y !=0 && $periodType == 'overnight' && $periodCounterPosition == ($date - $periodFaktor)) {
                                    return true;
                                } else if ($periodType != 'overnight' && $periodCounterPosition == $date) {
                                    return true;
                                }
                                $y++;
                            }
                        }
                        $periodCounterPosition += $periodFaktor;
                    }
                }
            }
        }
        return $result;
    }

  public static function getBookedDays($listType,$reservationObject) {
        $typeId = $listType['id'];
        $objectId = $reservationObject->getId();
        $objectQuantity = $reservationObject->getQuantity();
        
        if ($listType['objectType']) {
            $objectType = intval($listType['objectType']);
        } else if ($listType['reservationObjectType']) {
            $objectType = intval($listType['reservationObjectType']);
        }
        $minDuration = intval($listType['min_residence_time'] ? $listType['min_residence_time'] : $reservationObject->getTimeinterval());
        $maxCapacity = $reservationObject->getDesiredCapacity()[1];
        $currentReservations = $reservationObject->getCurrentReservations();
        $severalBookings = $reservationObject->getSeveralBookings();
    
        $periodFaktor = $listType['periodType'] != 'week' ? self::getPeriodFaktor($listType['periodType']) : self::getPeriodFaktor($listType['periodType']) / 7;
        $allTypesQuantity = $reservationObject->getAllTypesQuantity(); 
        
        $database = Database::getInstance();

        if ($listType['objectCount']) {
            $reservationObjectCount = $listType['objectCount'];
        } else {
            $reservationObjectCount = $database->prepare("SELECT objectCount FROM `tl_c4g_reservation_type` WHERE `id`=?")
            ->execute($typeId)->fetchAllAssoc();
            $reservationObjectCount = $reservationObjectCount[0]['objectCount']; 
        }
        $reservationObjectCount = $reservationObjectCount ? $reservationObjectCount : PHP_INT_MAX;

        $currentBookedTimes = $database->prepare("SELECT beginDate,endDate,desiredCapacity FROM `tl_c4g_reservation` WHERE `reservation_type`=? AND `reservation_object`=? AND `reservationObjectType`=? AND NOT `cancellation`=?")
        ->execute($typeId,$objectId,$objectType,'1')->fetchAllAssoc(); 

        $otherObjectsBookedTimes = $database->prepare("SELECT beginDate,endDate,desiredCapacity FROM `tl_c4g_reservation` WHERE `reservation_type`=? AND `reservation_object`!=? AND `reservationObjectType`=? AND NOT `cancellation`=?")
        ->execute($typeId,$objectId,$objectType,'1')->fetchAllAssoc(); 

        $result = [];
        $periodType = $listType['periodType'];

        if ($currentBookedTimes || $otherObjectsBookedTimes) {
            $i = 0;
            if ($otherObjectsBookedTimes) {
                $otherDates = self::getBookedDates($minDuration, $otherObjectsBookedTimes, $periodFaktor, $periodType);
                foreach ($otherDates as $otherDate) {
                    $allDates[$i++] = $otherDate;
                    if ($listType['periodType'] == 'week') {
                        
                    }
                }
            }

            if ($currentBookedTimes) {
                $fullyBookedDate = self::getFullyBookedDates($minDuration,$currentBookedTimes,$periodFaktor, $objectQuantity, $objectId, $periodType);
                $currentDates = self::getBookedDates($minDuration,$currentBookedTimes,$periodFaktor, $periodType);
                foreach ($currentDates as $currentDate) {
                    $allDates[$i++] = $currentDate;
                    if ($listType['periodType'] == 'week') {
                         $allDates[$i++] = $currentDate - 604800;
                    }
                }
            }

            $allDatesQuantity = array_count_values($allDates);
            $i = 0;
            foreach ($allDatesQuantity as $bookedKey => $bookedQuantity) {
                if ($bookedQuantity >= $reservationObjectCount) {
                    $allFullyBookedDates[$i] = $bookedKey;
                    $i++;
                }
            }

            if ($currentBookedTimes) {
                $i = 0;
                foreach ($currentBookedTimes as $currentBookings) { 
                    $bookedBeginDate[$i] = $currentBookings['beginDate'];
                    $bookedEndDate[$i++] = $currentBookings['endDate']; 
                }
            }
                 
            if (!$severalBookings || ($severalBookings && !$allTypesQuantity && $currentReservations >= $maxCapacity)) {
                if ($fullyBookedDate || $allFullyBookedDates) {
                    if (isset($bookedBeginDate)) sort($bookedBeginDate);
                    if (isset($bookedEndDate)) sort($bookedEndDate);
                    if (isset($fullyBookedDate)) sort($fullyBookedDate);
                    $i = 1;
                    foreach ($fullyBookedDate as $date) {
                        if ($date) {
                             if ($i < count($fullyBookedDate) && $listType['periodType'] == 'overnight') {
                                $isEndDate = false;
                                for ($y = 0; $y < count($bookedEndDate); $y++)
                                {
                                    if ($bookedEndDate[$y] == $date) {
                                        $isEndDate = true;
                                        $selectedEndDate = $date;
                                        break;
                                    }
                                }
                                $isBeginDate = false;
                                for ($y = 0; $y < count($bookedBeginDate); $y++)
                                {
                                    if ($bookedBeginDate[$y] == $date) {
                                        $isBeginDate = true;
                                        $selectedBeginDate = $date;
                                        break;
                                    }
                                }

                                if ($isBeginDate == true || $selectedBeginDate == $selectedEndDate || ($date != $selectedBeginDate && $date != $selectedEndDate)) {
                                    $result['dates'] = self::addComma($result['dates']) . date('d.m.Y', $date);
                                } 
                            } else if ($listType['periodType'] == 'week') {
                                $result['dates'] = self::addComma($result['dates']) . date('d.m.Y', $date - $listType['min_residence_time'] * 604800+86400);
                            } else if ($listType['periodType'] == 'day') {
                                $result['dates'] = self::addComma($result['dates']) . date('d.m.Y', $date);
                            } 
                        }
                        $i++;
                    }

                    $i = 1;
                    if (isset($bookedEndDate)) sort($bookedEndDate);
                    if (isset($allFullyBookedDates)) sort($allFullyBookedDates);
                    foreach ($allFullyBookedDates as $date) {
                        if ($date) {
                            if ($i < count($allFullyBookedDates) && $listType['periodType'] == 'overnight') {
                                for ($y = 0; $y < count($bookedEndDate); $y++)
                                {
                                    if ($bookedEndDate[$y] == $date) {
                                        break;
                                    } else {
                                        $result['dates'] = self::addComma($result['dates']) . date('d.m.Y', $date);
                                    }
                                }      
                           } else if ($listType['periodType'] == 'week') {
                               $result['dates'] = self::addComma($result['dates']) . date('d.m.Y', $date - $listType['min_residence_time'] * 604800+86400);
                           } else if ($listType['periodType'] == 'day') {
                               $result['dates'] = self::addComma($result['dates']) . date('d.m.Y', $date);
                           } 
                       }
                       $i++;
                    }
                    if ($listType['periodType'] == 'day' || $listType['periodType'] == 'week') {
                        $result['dates'] = self::addComma($result['dates']) . date('d.m.Y', $date+86400);
                    } 
                }
            } else {
                $result['dates'] = "";
            }
        }
        return $result;     
    }

    public static function getPeriodFaktor($periodType) {
        switch ($periodType) {
            case 'minute':
                $periodFaktor = 60;
                break;
            case 'hour':
                $periodFaktor = 3600;
                break;
            case 'day':
                $periodFaktor = 86400;
                break;
            case 'overnight':
                $periodFaktor = 86400;
                break;
            case 'week':
                $periodFaktor = 604800;
                break;
            default: '';
        }
        return $periodFaktor;
    }

    public static function getFullyBookedDates($minDuration, $currentBookedTimes, $periodFaktor, $objectQuantity, $objectId, $periodType) {
        $bookedDates = self::getBookedDates($minDuration, $currentBookedTimes, $periodFaktor, $periodType);
        $bookedDatesQuantity = array_count_values($bookedDates);

        $i = 0;
        foreach ($bookedDatesQuantity as $bookedKey => $bookedQuantity) {
            if ($bookedQuantity >= $objectQuantity) {
                $fullyBookedDate[$i] = $bookedKey;
                $i++;
            }
        }
        return $fullyBookedDate;
    }

    public static function getBookedDates($minDuration, $currentBookedTimes, $periodFaktor, $periodType) {
        $i = 0;
        foreach ($currentBookedTimes as $currentBookedTime) {
            $bookedBegin = $minDuration ? $currentBookedTime['beginDate'] - (($minDuration-1) * $periodFaktor) : $currentBookedTime['beginDate'];
            $bookedEnd = $currentBookedTime['endDate'];
            do {
                $bookedDates[$i] = $bookedBegin;
                $bookedBegin += $periodFaktor;
                $i++;
            } while($periodType == 'overnight' ? $bookedBegin < $bookedEnd : $bookedBegin <= $bookedEnd);  
        }
        return $bookedDates;
    }

    public static function replaceSimpleTokensWithFormValues($filename, $formValues)
    {
        $simpleTokenRegExp = "/##(\w+)##/";

        $output = preg_replace_callback($simpleTokenRegExp, function($matches) use ($formValues) {
            $simpleTokenKey = $matches[1];
            if(isset($formValues[$simpleTokenKey]))
                return $formValues[$simpleTokenKey];
        }, $filename);

        $pathinfo = pathinfo($output);
        if(!isset($pathinfo['extension']) || empty($pathinfo['extension']))
        {
            $output .= '.pdf';
        }

        return $output;
    }
}