<?php

namespace con4gis\ReservationBundle\Classes\Utils;

use con4gis\CoreBundle\Classes\Helper\ArrayHelper;
use con4gis\CoreBundle\Classes\Helper\StringHelper;
use con4gis\CoreBundle\Resources\contao\models\C4gLogModel;
use con4gis\ReservationBundle\Classes\Calculator\C4gReservationCalculator;
use con4gis\ReservationBundle\Classes\Models\C4gReservationEventModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationObjectModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationObjectPricesModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationTypeModel;
use con4gis\ReservationBundle\Classes\Objects\C4gReservationFrontendObject;
use Contao\Database;
use Contao\Date;
use Contao\StringUtil;
use Contao\System;

/**
 * Main class for reservation handling
 */
class C4gReservationHandler
{

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
                $array = \Contao\StringUtil::deserialize($objectData['oh_sunday']);
            }
            if($weekday == 1){
                $array = \Contao\StringUtil::deserialize($objectData['oh_monday']);
            }
            if($weekday == 2){
                $array = \Contao\StringUtil::deserialize($objectData['oh_tuesday']);
            }
            if($weekday == 3){
                $array = \Contao\StringUtil::deserialize($objectData['oh_wednesday']);
            }
            if($weekday == 4){
                $array = \Contao\StringUtil::deserialize($objectData['oh_thursday']);
            }
            if($weekday == 5){
                $array = \Contao\StringUtil::deserialize($objectData['oh_friday']);
            }
            if($weekday == 6){
                $array = \Contao\StringUtil::deserialize($objectData['oh_saturday']);
            }

            $possibleBookings = 0;
            foreach ($array as $timeset) {

                if (intval($timeset['time_end']) < intval($timeset['time_begin'])) { //nxtday
                    $possibleSeconds = intval(86400+$timeset['time_end']) + intval($timeset['time_begin']);
                } else {
                    $possibleSeconds = intval($timeset['time_end']) - intval($timeset['time_begin']);
                }

                switch ($periodType) {
                    case 'minute':
                        $toSecond = 60;
                        break;
                    case 'hour':
                        $toSecond = 3600;
                        break;
                    case 'day':
                        $toSecond = 86400;
                        break;
                    case 'week':
                        $toSecond = 604800;
                        break;
                    default: '';
                }

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
    public static function getDateExclusionString($list, $type, $removeBookedDays=1)
    {
        $result = '';
        if ($list) {
            $alldates = array();
            $minDate = 0;
            $maxDate = 7;
            //remove configured date exclusion
            foreach ($list as $object) {
                if(!$object instanceof C4gReservationFrontendObject){
                    break;
                }
                $exclusionPeriods = $object->getDatesExclusion();
                if ($exclusionPeriods) {
                    foreach ($exclusionPeriods as $period) {
                        if ($period && $period['date_exclusion'] && $period['date_exclusion_end']) {
                            $exclusionBegin = C4gReservationDateChecker::getBeginOfDate($period['date_exclusion']);
                            $exclusionEnd = C4gReservationDateChecker::getEndOfDate($period['date_exclusion_end']);

                            $current = $exclusionBegin;
                            while($current <= $exclusionEnd) {
                                $alldates[] = C4gReservationDateChecker::getBeginOfDate($current);
                                $current = intval($current) + 86400;
                            }
                        }
                    }
                }

                if ($minDate && ($minDate > 1) && ($minDate > $object->getMinReservationDay())) {
                    $minDate = $object->getMinReservationDay();
                }

                if ($maxDate && ($maxDate < 365) && ($maxDate < $object->getMaxReservationDay())) {
                    $maxDate = $object->getMaxReservationDay();
                }
            }

            //remove dates without possible times
            if ($removeBookedDays) {
                $begin = time()+($minDate*86400);
                $end   = time()+($maxDate*86400);

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

                        $nextDays = ($nextDays > 0) ? $nexdays-- : 0;
                    }

                    $i = $i+86400;
                }
            }

            foreach ($alldates as $date) {
                if ($date) {
                    $result = self::addComma($result) . date('d.m.Y'/*$GLOBALS['TL_CONFIG']['dateFormat']*/, $date);
                }
            }
        }

        return $result;
    }

    /**
     * @param $object
     * @return bool
     */
    public static function isEventObject($object) {
        return ($object && (intval($object) > 0)) ? true : false;
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
    private static function addTime($list, $time, $obj, $interval, $langCookie, $endTime = 0, $mergedTime = 0, $mergedEndTime = 0, $timeFormatCookie = '', $datimFormatCookie = '')
    {
        $clock = '';

        if ($timeFormatCookie && !$GLOBALS['TL_CONFIG']['timeFormat']) {
            $GLOBALS['TL_CONFIG']['timeFormat'] = $timeFormatCookie;
        }

        if ($datimFormatCookie && !$GLOBALS['TL_CONFIG']['datimFormat']) {
            $GLOBALS['TL_CONFIG']['datimFormat'] = $datimFormatCookie;
        }

        if (!strpos($GLOBALS['TL_CONFIG']['timeFormat'],'A')) {
            if ($GLOBALS['TL_LANG']['fe_c4g_reservation']['clock']) {
                $clock = '&nbsp;'.$GLOBALS['TL_LANG']['fe_c4g_reservation']['clock'];
            } else if ($langCookie) {
                $clock = '&nbsp;'.$langCookie;
            }
        }

        foreach ($list as $key => $item) {
            if ($key === $time || ($interval && ($key === ($time.'#'.$interval))) || ($endTime && ($key === ($time.'#'.($endTime-$time))))) {
                $list[$key]['objects'][] = $obj;
                return $list;
            }
        }

        $begin = date($GLOBALS['TL_CONFIG']['timeFormat'], $time).$clock;

        $datetim = false;
        if ($mergedTime && $mergedEndTime/* && ((($mergedTime+$interval) - $mergedTime) >= 80640)*/) {
            $begin = date($GLOBALS['TL_CONFIG']['datimFormat'], $mergedTime).$clock;
            $end = date($GLOBALS['TL_CONFIG']['datimFormat'], $mergedEndTime).$clock;
            $datetim = true;
        }

        if ($obj && ($obj['id'] == -1)) {
            if ($interval) {
                $key = $time.'#'.$interval;
                if (!$datetim) {
                    $end = date($GLOBALS['TL_CONFIG']['timeFormat'], $time+$interval).$clock;
                }
                $list[$key] = array('id' => $key, 'time' => $time, 'interval' => $interval, 'name' => $begin.'&nbsp;-&nbsp;'.$end, 'objects' => [$obj]);
            } else if ($endTime && ($endTime != $time)) {
                $key = $time.'#'.($endTime-$time);
                if (!$datetim) {
                    $end = date($GLOBALS['TL_CONFIG']['timeFormat'], $endTime).$clock;
                }
                $list[$key] = array('id' => $key, 'time' => $time, 'interval' => ($endTime-$time), 'name' => $begin.'&nbsp;-&nbsp;'.$end, 'objects' => [$obj]);
            } else {
                $key = $time;
                $list[$key] = array('id' => $key, 'time' => $time, 'interval' => 0, 'name' => $begin, 'objects' => [$obj]);
            }
        } else {
            if ($interval) {
                $key = $time.'#'.$interval;
                if (!$datetim) {
                    $end = date($GLOBALS['TL_CONFIG']['timeFormat'], $time + $interval).$clock;
                }
                $list[$key] = array('id' => $key, 'time' => $time, 'interval' => $interval, 'name' => $begin.'&nbsp;-&nbsp;'.$end, 'objects' => [$obj]);
            } else if ($endTime && ($endTime != $time)) {
                $key = $time.'#'.($endTime-$time);
                if (!$datetim) {
                    $end = date($GLOBALS['TL_CONFIG']['timeFormat'], $endTime).$clock;
                }
                $list[$key] = array('id' => $key, 'time' => $time, 'interval' => ($endTime-$time), 'name' => $begin.'&nbsp;-&nbsp;'.$end, 'objects' => [$obj]);
            } else {
                $key = $time;
                $list[$key] = array('id' => $key, 'time' => $time, 'interval' => 0, 'name' => $begin, 'objects' => [$obj]);
            }
        }

        return $list;
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
        $tstamp = intval($tstamp); //C4gReservationDateChecker::getBeginOfDate(intval($tstamp));

        $date_from = intval($period['date_from']); //C4gReservationDateChecker::getBeginOfDate(intval($period['date_from']));
        $date_to = intval($period['date_to']); //C4gReservationDateChecker::getEndOfDate(intval($period['date_to']));

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
    public static function getEndTimeForMultipleDays($object, $weekday) {

        $weekdayStr = C4gReservationDateChecker::getWeekdayStr($weekday);
        $ohString = "oh_".C4gReservationDateChecker::getWeekdayFullStr($weekday);
        $oh = StringUtil::deserialize($object->$ohString);

        foreach ($oh as $key => $period) {
            return $period['time_end'];
        }

        return false;
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
    public static function getReservationTimes($list, $type, $weekday = -1, $date = null, $duration=0, $withEndTimes=false, $showFreeSeats=false, $checkToday=false, $langCookie = '', $timeFormatCookie = '', $datimFormatCookie = '')
    {
        $result = array();
        $tsdate = 0;
        if ($list) {
            shuffle($list);

            if ($date !== -1) {
                if (is_numeric($date) && (int)$date == $date) {
                    $tsdate = (int)$date;
                } else {
                    $format = $GLOBALS['TL_CONFIG']['dateFormat'];

                    $tsdate = \DateTime::createFromFormat($format, $date);
                    if ($tsdate) {
                        $tsdate->Format($format);
                        $tsdate->setTime(0, 0, 0);
                        $tsdate = $tsdate->getTimestamp();
                    } else {
                        $format = "d/m/Y";
                        $tsdate = \DateTime::createFromFormat($format, $date);
                        if ($tsdate) {
                            $tsdate->Format($format);
                            $tsdate->setTime(0, 0, 0);
                            $tsdate = $tsdate->getTimestamp();
                        } else {
                            $tsdate = strtotime($date);
                        }
                    }
                }

                $nowDate = new \DateTime();
                if ($nowDate) {
                    $format = $GLOBALS['TL_CONFIG']['dateFormat'];
                    $nowDate->Format($format);
                    $nowDate->setTime(0,0,0);
                    $nowDate = $nowDate->getTimestamp();
                }

                $objDate = new Date(date($GLOBALS['TL_CONFIG']['timeFormat'], time()), Date::getFormatFromRgxp('time'));
                $nowTime = $objDate->tstamp;
            }

            if (!$type) {
                return [];
            }
            $database = Database::getInstance();
            $typeObject = $database->prepare("SELECT * FROM `tl_c4g_reservation_type` WHERE `id`=?")
                ->execute($type)->fetchAssoc();
            //$typeObject = C4gReservationTypeModel::findByPk($type);
            if (!$typeObject) {
                return [];
            }
            $periodType = $typeObject['periodType'];

            $maxCount = intval($typeObject['objectCount']);

            $objectType = $typeObject['reservationObjectType'];

            if (($date !== -1) && $tsdate) {
                $tsdate = C4gReservationDateChecker::getBeginOfDate($tsdate);
            }

            $calculator = new C4gReservationCalculator($tsdate, $objectType);
            foreach ($list as $object) {
                $found = false;
                $timeArray = []; //count for one object
                $objectQuantity = $object->getQuantity() ?  $object->getQuantity() : 1;
                $desiredCapacity = $object->getDesiredCapacity()[1] ? $object->getDesiredCapacity()[1] : 0; //max persons
                $maxCount = $objectQuantity;
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

                $calculator->loadReservations($typeObject, $object);

                if ($duration >= 1) //duration from client can be -1 (no input)
                {
                    switch ($periodType) {
                        case 'minute':
                            $object->setDuration($duration);
                            break;
                        case 'hour':
                            $object->setDuration($duration);
                            break;
                        case 'day':
                            $object->setDuration($duration);
                            break;
                        case 'week':
                            $object->setDuration($duration);
                            break;
                        default: '';
                    }
                }


                $oh = $object->getOpeningHours();
                switch ($periodType) {
                    case 'minute':
                        $interval = $object->getTimeinterval() * 60;
                        $durationInterval = $object->getDuration() ? $object->getDuration() * 60 : $interval;
                        break;
                    case 'hour':
                        $interval = $object->getTimeinterval() * 3600;
                        $durationInterval = $object->getDuration() ? $object->getDuration() * 3600 : $interval;
                        break;
                    case 'day':
                        $interval = $object->getTimeinterval() * 86400;
                        $durationInterval = $object->getDuration() ? $object->getDuration() * 86400 : $interval;

                        break;
                    case 'week':
                        $interval = $object->getTimeinterval() * 604800;
                        $durationInterval = $object->getDuration() ? $object->getDuration() * 604800 : $interval;
                        break;
                    default: '';
                }

                $severalBookingCount = !$typeObject['severalBookings'] ? 1 : 0;
                $maxObjects = $maxCount ?: $severalBookingCount;

                //object count * max persons
                $capacity = $objectQuantity * intval($desiredCapacity);
                $weekdayStr = C4gReservationDateChecker::getWeekdayStr($weekday);
                $calculatorResult = 0;

                if ($durationInterval && ($durationInterval > 0)) {
                    foreach ($oh as $key => $day) {
                        if (($day != -1) && ($key == $weekdayStr)) {
                            foreach ($day as $period) {
                                if ($date !== -1) {
                                    $periodValid = C4gReservationHandler::checkValidPeriod($tsdate, $period);
                                    if (!$periodValid) {
                                        continue;
                                    }
                                }

                                $time_begin = intval($period['time_begin']);

                                if (($periodType == 'day') || ($periodType == 'week')) {
                                    $time_end = intval($period['time_end']);

                                    if ($time_begin && $time_end) {
                                        $time = $time_begin;
                                        $periodEnd = $time_end;

                                        while ($time <= $periodEnd) {
                                            $id = $object->getId();
                                            if ($time && $typeObject) {
                                                $endTime = $time + $interval;

                                                if ($date && $tsdate) {
                                                    $calculator->calculateAll(
                                                        $tsdate, $time, $endTime, $object, $typeObject, $capacity, $timeArray
                                                    );
                                                    $calculatorResult = $calculator->getCalculatorResult();
                                                    $timeArray = $calculatorResult->getTimeArray();
                                                }

                                                $endTimeInterval = $durationInterval;
                                                if (!$withEndTimes) {
                                                    $endTimeInterval = 0;
                                                }

                                                //$max = $capacity;

                                                $timeObj = [
                                                    'id'=>-1,
                                                    'act'=> $calculatorResult ? $calculatorResult->getDbPersons() : 0,
                                                    'percent'=> $calculatorResult ? $calculatorResult->getDbPercent() : 0,
                                                    'max'=> intval($desiredCapacity),
                                                    'showSeats'=> $showFreeSeats,
                                                    'priority'=> intval($object->getPriority())
                                                ];

                                                $checkTime = $time;
                                                if ($typeObject['bookRunning']) {
                                                    $checkTime = $endTime;
                                                }

                                                if ($time_end >= $time_begin) {
                                                    $durationInterval = $durationInterval - 86400; //first day counts
                                                }

                                                $mergedTime = 0;
                                                if ($tsdate) {
                                                    $mergedTime = C4gReservationDateChecker::mergeDateWithTime($tsdate,$time);

                                                    $wd = date('N', $tsdate+$durationInterval);
                                                    if ($wd == 7) {
                                                        $wd = 0;
                                                    }
                                                    $periodEndWeekday = C4gReservationDateChecker::getWeekdayStr($wd);
                                                    if ($periodEndWeekday) {
                                                        $endTimes = $oh[$periodEndWeekday];
                                                        $endTime = 0;
                                                        foreach ($endTimes as $endTimeSet) {
                                                            $et = intval($endTimeSet['time_end']);
                                                            if ($et > $endTime) {
                                                                $endTime = $et;
                                                            }
                                                        }

//                                                        if ($endTime) {
                                                            $periodEnd = $endTime;
                                                            //ToDo take next day without times?
//                                                        }
                                                    }

                                                    $mergedEndTime = C4gReservationDateChecker::mergeDateWithTime($tsdate+$durationInterval,$periodEnd);
                                                }


                                                $reasionLog = '';
                                                if ($tsdate && $nowDate && (!$checkToday || ($nowDate < $tsdate) || (($nowDate == $tsdate) && ($nowTime < $checkTime)))) {
                                                    if ($capacity && ($calculatorResult->getDbPersons() >= $capacity)) {
                                                        $reasonLog = 'too many persons';
                                                    } else if ($maxObjects && ($calculatorResult->getDbBookings() >= intval($maxObjects)) && (!$typeObject['severalBookings'] || $object->getAllTypesQuantity() || $object->getAllTypesValidity())) {
                                                        $reasonLog = 'too many bookings';
                                                    } else if ($desiredCapacity && ($timeArray && !empty($timeArray)) && ($timeArray[$tsdate][$time] >= intval($desiredCapacity))) {
                                                        $reasionLog = 'too many bookings per object';
                                                    } else {
                                                        $timeObj['id'] = $id;
                                                    }
                                                    $result = self::addTime($result, $time, $timeObj, $endTimeInterval, $langCookie, 0, $mergedTime, $mergedEndTime, $timeFormatCookie, $datimFormatCookie);
                                                } else if ($date === -1) {
                                                    $result = self::addTime($result, $time, $timeObj, $endTimeInterval, $langCookie, $timeFormatCookie, $datimFormatCookie);
                                                }
                                            }

                                            $time = $time + $interval; //Hier immer 1 Tag?
                                        }
                                    }
                                } else {
                                    $time_end = intval($period['time_end']);
                                    if ($time_end < $time_begin) { //nxt day
                                        if ($time_begin && $time_end) {
                                            $time = $time_begin;

                                            $endOfDate = 86400 + $time_end; //24h + nxt day time
                                            $periodEnd = $endOfDate - $durationInterval;

                                            while ($time <= $periodEnd) {
                                                $id = $object->getId();
                                                if ($time && $typeObject) {
                                                    $endTime = $time + $interval;

                                                    if ($date && $tsdate) {
                                                        $calculator->calculateAll(
                                                            $tsdate, $time, $endTime, $object, $typeObject, $capacity, $timeArray
                                                        );
                                                        $calculatorResult = $calculator->getCalculatorResult();
                                                        $timeArray = $calculatorResult->getTimeArray();
                                                    }


                                                    $endTimeInterval = $durationInterval;
                                                    if (!$withEndTimes) {
                                                        $endTimeInterval = 0;
                                                    }

                                                    //$max = $capacity;

                                                    $timeObj = [
                                                        'id'=>-1,
                                                        'act'=>$calculatorResult ? $calculatorResult->getDbPersons() : 0,
                                                        'percent'=>$calculatorResult ? $calculatorResult->getDbPercent() : 0,
                                                        'max'=> intval($desiredCapacity),
                                                        'showSeats'=>$showFreeSeats,
                                                        'priority'=>intval($object->getPriority())
                                                    ];

                                                    $checkTime = $time;
                                                    if ($typeObject['bookRunning']) {
                                                        $checkTime = $endTime;
                                                    }

                                                    $reasionLog = '';
                                                    if ($tsdate && $nowDate && (!$checkToday || ($nowDate < $tsdate) || (($nowDate == $tsdate) && ($nowTime < $checkTime))/* || ($typeObject->directBooking)*/)) {
                                                        if ($capacity && ($calculatorResult->getDbPersons() >= $capacity)) {
                                                            $reasonLog = 'too many persons';
                                                        } else if ($maxObjects && ($calculatorResult->getDbBookings() >= intval($maxObjects)) && (!$typeObject['severalBookings'] || $object->getAllTypesQuantity() || $object->getAllTypesValidity())) {
                                                            $reasonLog = 'too many bookings';
                                                        } else if ($desiredCapacity && ($timeArray && !empty($timeArray)) && ($timeArray[$tsdate][$time] >= intval($desiredCapacity))) {
                                                            $reasionLog = 'too many bookings per object';
                                                        } else {
                                                            $timeObj['id'] = $id;
                                                        }

                                                        $result = self::addTime($result, $time, $timeObj, $endTimeInterval, $langCookie);
                                                    } else if ($date === -1) {
                                                        $result = self::addTime($result, $time, $timeObj, $endTimeInterval, $langCookie);
                                                    }
                                                }

                                                $time = $time + $interval;
                                            }
                                        }
                                    } else {
                                        if ($time_begin && $time_end) {
                                            $time = $time_begin;
                                            $periodEnd = $time_end - $durationInterval;

                                            while ($time <= $periodEnd) {
                                                $id = $object->getId();
                                                if ($time && $typeObject) {
                                                    $endTime = $time + $interval;

                                                    if ($date && $tsdate) {
                                                        $calculator->calculateAll(
                                                            $tsdate, $time, $endTime, $object, $typeObject, $capacity, $timeArray
                                                        );
                                                        $calculatorResult = $calculator->getCalculatorResult();
                                                        $timeArray = $calculatorResult->getTimeArray();
                                                    }

                                                    $endTimeInterval = $durationInterval;
                                                    if (!$withEndTimes) {
                                                        $endTimeInterval = 0;
                                                    }

                                                    $timeObj = [
                                                        'id'=>-1,
                                                        'act'=> $calculatorResult ? $calculatorResult->getDbPersons() : 0,
                                                        'percent'=> $calculatorResult ? $calculatorResult->getDbPercent() : 0,
                                                        'max'=> intval($desiredCapacity),
                                                        'showSeats'=>$showFreeSeats,
                                                        'priority'=>intval($object->getPriority())
                                                    ];

                                                    $checkTime = $time;
                                                    if ($typeObject['bookRunning']) {
                                                        $checkTime = $endTime;
                                                    }

                                                    $reasionLog = '';
                                                    if ($tsdate && $nowDate && (!$checkToday || ($nowDate < $tsdate) || (($nowDate == $tsdate) && ($nowTime < $checkTime)))) {
                                                        if ($capacity && ($calculatorResult->getDbPersons() >= $capacity)) {
                                                            $reasonLog = 'too many persons';
                                                        } else if ($maxObjects && ($calculatorResult->getDbBookings() >= intval($maxObjects)) && (!$typeObject['severalBookings'] || $object->getAllTypesQuantity() || $object->getAllTypesValidity())) {
                                                            $reasonLog = 'too many bookings';
                                                        } else if ($desiredCapacity && ($timeArray && !empty($timeArray)) && ($timeArray[$tsdate][$time] >= intval($desiredCapacity))) {
                                                            $reasionLog = 'too many bookings per object';
                                                        } else {
                                                            $timeObj['id'] = $id;
                                                        }

                                                        $result = self::addTime($result, $time, $timeObj, $endTimeInterval, $langCookie);
                                                    } else if ($date === -1) {
                                                        $result = self::addTime($result, $time, $timeObj, $endTimeInterval, $langCookie);
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
            }

            if ($result && is_array($result) && (count($result) > 0)) {
                return ArrayHelper::sortArrayByFields($result,['name' => SORT_ASC]);
            } else {
                return [];
            }

        }
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
                            }

                            $beginTime = $newValue ?: $value;
                            break;
                        }
                    }
                }
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
            }

            $reservationId = $putVars['reservation_id'];
            $database = Database::getInstance();
            $reservations = $database->prepare("SELECT desiredCapacity FROM `tl_c4g_reservation` WHERE `reservation_type`=? AND `reservation_object`=? AND `reservationObjectType` IN (1,3) AND `beginDate`=? AND `beginTime`=? AND NOT `cancellation`=?")
                ->execute($typeId,$objectId,$beginDate,$beginTime,'1')->fetchAllAssoc();

            $reservationCount = count($reservations);

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
                if ($maxCount && ($reservationObject->desiredCapacityMax && ($reservationCount >= $maxCount))) {
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
    public static function getButtonStateClass($object) {
        $result = '';

        if ($object && $object->getAlmostFullyBookedAt() &&  $object->getDesiredCapacity() &&  $object->getDesiredCapacity()[1]) {  //orange state
            $id = $object->getId();
            $database = Database::getInstance();
            $reservations = $database->prepare("SELECT desiredCapacity FROM `tl_c4g_reservation` WHERE `reservation_object`=? AND `reservationObjectType`=? AND NOT `cancellation`=?")
                ->execute($id,'2','1')->fetchAllAssoc();

            $percent = $object->getAlmostFullyBookedAt();
            $reservationCount = count($reservations);

            $desiredCapacity = $object->getDesiredCapacity()[1];

            if ((($reservationCount / $desiredCapacity) * 100) >= 100) {
                $result = ' c4g_radio_object_fully_booked';
            } else if ((($reservationCount / $desiredCapacity) * 100) >= $percent) {
                $result = ' c4g_radio_object_hurry_up';
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
        return self::addTime([], $object->getBeginTime(), $timeObj, false, 0, $endTime);
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
            return self::addTime([], $time, $timeObj, false, $endTime);
        } else {
            return [];
        }
    }


    /**
     * @param null $moduleTypes
     * @param int $objectId
     * @return array
     */
    public static function getReservationObjectList($moduleTypes = null, $objectId = 0, $showPrices = false, $getAllTypes = false, $duration = 0, $date = 0)
    {
        $objectlist = array();
        $allTypesList = array();
        foreach ($moduleTypes as $moduleType) {
            if ($moduleType) {
                $typeArr = [];
                //$type = C4gReservationTypeModel::findByPk($moduleType);
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
                    $objectlist = C4gReservationHandler::getReservationObjectEventList($typeArr, $objectId, $type, $showPrices);

                    if ($getAllTypes) {
                        foreach($objectlist as $key=>$object) {
                            $allTypesList['events'][$key] = $object;
                        }
                    } else {
                        break;
                    }

                } else {
                    $objectlist = C4gReservationHandler::getReservationObjectDefaultList($typeArr, $objectId, $type, $showPrices, $duration, $date);

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

    /**
     * @param $object
     */
    private static function calcPrices($object, $type, $isEvent = false, $countPersons = 1, $duration = 0, $date = 0) {
        $price = 0;
        if ($object) {
            \System::loadLanguageFile('fe_c4g_reservation');
            $priceOption = key_exists('priceoption',$object) ? $object['priceoption'] : '';
            $priceInfo = '';
            switch ($priceOption) {
                case 'pMin':
                    if ($isEvent && $object['startTime'] && $object['endTime']) {
                        $diff = $object['endTime'] - $object['startTime'];
                        if ($diff > 0) {
                            $minutes = $diff / 60;
                        }
                    } else if (!$isEvent && $type['periodType'] && $object['time_interval']) {
                        switch ($type['periodType']) {
                            case 'minute':
                                $minutes = $object['time_interval'];
                                break;
                            case 'hour':
                                $minutes = $object['time_interval'] * 60;
                                break;
                            case 'day':
                                $minutes = $object['time_interval'] * 60 * 24;
                                break;
                            case 'week':
                                $minutes = $object['time_interval'] * 60 * 24 * 7;
                                break;
                            default:
                                '';
                        }
                    }
                    $price = $price + (intval($object['price']) * $minutes);
                    break;
                case 'pHour':
                    if ($isEvent && $object['startTime'] && $object['endTime']) {
                        $diff = $object['endTime'] - $object['startTime'];
                        if ($diff > 0) {
                            $hours = $diff / 3600;
                        }
                    } else if (!$isEvent && $type['periodType'] && $object['time_interval']) {
                        switch ($type['periodType']) {
                            case 'minute':
                                $hours = $object['time_interval'] / 60;
                                break;
                            case 'hour':
                                $hours = $object['time_interval'];
                                break;
                            case 'day':
                                $hours = $object['time_interval'] * 24;
                                break;
                            case 'week':
                                $hours = $object['time_interval'] * 24 * 7;
                                break;
                            default:
                                '';
                        }
                    }
                    $price = $price + (intval($object['price']) * $hours);
                    break;
                case 'pDay':
                    $days = $duration ?: 0;
                    if ($isEvent && $object['startDate'] && $object['endDate']) {
                        $days = round(abs($object['endDate'] - $object['startDate']) / (60 * 60 * 24));
                    } else if (!$days && !$isEvent && key_exists('beginDate', $object) && $object['beginDate'] && key_exists('endDate', $object) && $object['endDate']) {
                        $days = round(abs($object['endDate'] - $object['beginDate']) / (60 * 60 * 24));
                    }
                    $price = $price + (intval($object['price']) * $days);
                    break;
                case 'pWeek':
                    $weeks = $duration ?: 0;
                    if ($isEvent && $object['startDate'] && $object['endDate']) {
                        $weeks = round(abs($object['endDate'] - $object['startDate']) / (60 * 60 * 24 * 7));
                    } else if (!$weeks && !$isEvent && key_exists('beginDate', $object) && $object['beginDate'] && key_exists('endDate', $object) && $object['endDate']) {
                        $weeks = round(abs($object['endDate'] - $object['beginDate']) / (60 * 60 * 24 * 7));
                    }
                    $price = $price + (intval($object['price']) * $weeks);
                    break;
                case 'pReservation':
                    $price = $price + intval($object['price']);
                    break;
                case 'pPerson':
                    $price = ($price + intval($object['price'])) . $GLOBALS['TL_LANG']['fe_c4g_reservation']['pPerson'];
                    break;
                case 'pAmount':
                    $price = $price + intval($object['price']);
                    $priceInfo = $GLOBALS['TL_LANG']['fe_c4g_reservation']['pAmount'];
                    break;
            }

        }

        if ($price) {
            $priceInfo ? "&nbsp;".$priceInfo : '';
            $price = number_format(floatval($price),2,$GLOBALS['TL_LANG']['fe_c4g_reservation']['decimal_seperator'],$GLOBALS['TL_LANG']['fe_c4g_reservation']['thousands_seperator'])."&nbsp;".$GLOBALS['TL_LANG']['fe_c4g_reservation']['currency'].$priceInfo;
        }
        return $price;
    }

    /**
     * @param null $moduleTypes
     * @param int $objectId
     * @return array
     */
    public static function getReservationObjectEventList($moduleTypes = null, $objectId = 0, $type, $showPrices = false)
    {
        $objectList = array();
        $database = Database::getInstance();
        $almostFullyBookedAt = $type['almostFullyBookedAt'];
        if ($objectId) {
            $events = $database->prepare("SELECT * FROM tl_c4g_reservation_event WHERE `pid` = ?")->execute($objectId)->fetchAllAssoc();
            //$events = C4gReservationEventModel::findBy('pid',$objectId);
            if ($events) {
                if (count($events) > 1) {
                    C4gLogModel::addLogEntry('reservation', 'There are more than one event connections. Check Event: '.$objectId);
                } else if (count($events) > 0) {
                    $event = $events[0];
                }
            }

            $eventObject = $database->prepare("SELECT * FROM tl_calendar_events WHERE `id` = ?")->execute($objectId)->fetchAssoc();
            //$eventObject = \CalendarEventsModel::findByPk($objectId);
            if ($event && $eventObject && $eventObject['published'] && (($eventObject['startTime'] && ($eventObject['startTime'] > time())) || (!$eventObject['startTime'] && $eventObject['startDate'] && $eventObject['startDate'] >= time()))) {
                $frontendObject = new C4gReservationFrontendObject();
                $frontendObject->setType(2);
                $frontendObject->setId($eventObject['id']);
                $eventObject['price'] = $event['price'];
                $eventObject['priceoption'] = $event['priceoption'];
                $price = $showPrices ? static::calcPrices($eventObject, $type, true, 1) : 0;
                $frontendObject->setCaption($showPrices && $price ? StringHelper::spaceToNbsp($eventObject['title'])."<span class='price'>&nbsp;(".$price.")</span>" : StringHelper::spaceToNbsp($eventObject['title']));
                $frontendObject->setDesiredCapacity([$event['minParticipants'],$event['maxParticipants']]);
                $frontendObject->setBeginDate($eventObject['startDate'] ?: 0);
                $frontendObject->setBeginTime($eventObject['startTime'] ?: 0);
                $frontendObject->setEndDate($eventObject['endDate'] ?: 0);
                $frontendObject->setEndTime($eventObject['endTime'] ?: 0);
                $frontendObject->setAlmostFullyBookedAt($almostFullyBookedAt);
                $frontendObject->setNumber($event['number'] ?: '');
                $frontendObject->setEventDuration('');
                $frontendObject->setAudience($event['targetAudience'] ? \Contao\StringUtil::deserialize($event['targetAudience']) : []);
                $frontendObject->setSpeaker($event['speaker'] ? \Contao\StringUtil::deserialize($event['speaker']) : []);
                $frontendObject->setTopic($event['topic'] ? \Contao\StringUtil::deserialize($event['topic']) : []);
                $frontendObject->setLocation($event['location'] ?: $type['location']);
                $frontendObject->setDescription($eventObject['teaser'] ?: '');
                $frontendObject->setImage($eventObject['singleSRC']);
                $frontendObject->setPrice($event['price'] ?: 0.00);
                $frontendObject->setPriceOption($event['priceoption']);
                $objectList[] = $frontendObject;
            }
        } else {
            $idString = "(" ;
            foreach ($moduleTypes as $key => $typeId) {
                $idString .= "\"$typeId\"";
                if (!(array_key_last($moduleTypes) === $key)) {
                    $idString .= ",";
                }
            }
            $idString .= ")";

            $allEvents = $database->prepare("SELECT * FROM tl_c4g_reservation_event WHERE `reservationType` IN $idString")->execute()->fetchAllAssoc();

            if ($allEvents) {
                foreach ($allEvents as $event) {
                    //$eventObject = \CalendarEventsModel::findByPk($event['pid']);
                    $eventObject = $database->prepare("SELECT * FROM tl_calendar_events WHERE `id` = ?")->execute($event['pid'])->fetchAssoc();
                    if ($eventObject && $eventObject['published'] && (($eventObject['startTime'] && ($eventObject['startTime'] > time())) || (!$eventObject['startTime'] && $eventObject['startDate'] && $eventObject['startDate'] >= time()))) {
                        $frontendObject = new C4gReservationFrontendObject();
                        $frontendObject->setType(2);
                        $frontendObject->setId($eventObject['id']);
                        $eventObject['price'] = $event['price'];
                        $eventObject['priceoption'] = $event['priceoption'];
                        $price = $showPrices ? static::calcPrices($eventObject, $type, true, 1) : 0;
                        $frontendObject->setCaption($showPrices && $price ? StringHelper::spaceToNbsp($eventObject['title'])."<span class='price'>&nbsp;(".$price.")</span>" : StringHelper::spaceToNbsp($eventObject['title']));
                        $frontendObject->setDesiredCapacity([$event['minParticipants'],$event['maxParticipants']]);
                        $frontendObject->setBeginDate($eventObject['startDate'] ?: 0);
                        $frontendObject->setBeginTime($eventObject['startTime'] ?: 0);
                        $frontendObject->setEndDate($eventObject['endDate'] ?: 0);
                        $frontendObject->setEndTime($eventObject['endTime'] ?: 0);
                        $frontendObject->setAlmostFullyBookedAt($almostFullyBookedAt);
                        $frontendObject->setNumber($event['number']);
                        $frontendObject->setAudience($event['targetAudience'] ? \Contao\StringUtil::deserialize($event['targetAudience']) : []);
                        $frontendObject->setEventDuration('');
                        $frontendObject->setSpeaker($event['speaker'] ? \Contao\StringUtil::deserialize($event['speaker']) : []);
                        $frontendObject->setTopic($event['topic'] ? \Contao\StringUtil::deserialize($event['topic']) : []);
                        $frontendObject->setLocation($event['location'] ?: $type['location']);
                        $frontendObject->setDescription($eventObject['teaser'] ?: '');
                        $frontendObject->setImage($eventObject['singleSRC']);
                        $frontendObject->setPrice($event['price'] ?: 0.00);
                        $frontendObject->setPriceOption($event['priceoption']);
                        $objectList[] = $frontendObject;
                    }
                }
            }
        }

        return $objectList;
    }

    /**
     * @param null $moduleTypes
     * @param $type
     * @param $calculator
     * @param $date
     * @param false $showPrices
     * @return array
     */
    public static function getReservationObjectDefaultList($moduleTypes = null, $objectId = 0, $type, $showPrices = false, $duration = 0, $date = 0)
    {
        $objectList = array();
//        $t = static::$strTable;
//        $arrOptions = array();
//        $allObjects = C4gReservationObjectModel::findBy('published','1');
        $database = Database::getInstance();

        if ($objectId && is_numeric($objectId)) {
            $allObjects = $database->prepare("SELECT * FROM tl_c4g_reservation_object WHERE published = ? AND id = ? ORDER BY caption")->execute('1', $objectId)->fetchAllAssoc();
        } else if ($objectId && is_string($objectId)) {
            $allObjects = $database->prepare("SELECT * FROM tl_c4g_reservation_object WHERE published = ? AND alias = ? ORDER BY caption")->execute('1', $objectId)->fetchAllAssoc();
        } else {
            $allObjects = $database->prepare("SELECT * FROM tl_c4g_reservation_object WHERE published = ? ORDER BY caption")->execute('1')->fetchAllAssoc();
        }

        $almostFullyBookedAt = $type['almostFullyBookedAt'] ?: 0;
        if ($moduleTypes) {
            $types = $moduleTypes;
            $objects = [];
            foreach ($allObjects as $object) {
                $objectTypes = \Contao\StringUtil::deserialize($object['viewableTypes']);
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
                $frontendObject = new C4gReservationFrontendObject();
                $frontendObject->setType(1);
                $frontendObject->setId($object['id']);

                $frontendObject->setCaption(StringHelper::spaceToNbsp($object['caption']));

                $captions = StringUtil::deserialize($object['options']);
                if ($captions) {
                    foreach ($captions as $caption) {
                        if ((strpos($GLOBALS['TL_LANGUAGE'],$caption['language']) >= 0) && $caption['caption']) {
                            $frontendObject->setCaption(StringHelper::spaceToNbsp($caption['caption']));
                            break;
                        }
                    }
                }

                $price = $showPrices ? static::calcPrices($object, $type, false, 1, $duration, $date) : 0;
                $frontendObject->setCaption($showPrices && $price ? StringHelper::spaceToNbsp($frontendObject->getCaption())."<span class='price'>&nbsp;(".$price.")</span>" : StringHelper::spaceToNbsp($frontendObject->getCaption()));

                $frontendObject->setPeriodType($type['periodType']);
                $frontendObject->setReservationTypes(\Contao\StringUtil::deserialize($object['viewableTypes']));
                $frontendObject->setQuantity($object['quantity']);
                $frontendObject->setAlmostFullyBookedAt($almostFullyBookedAt);
                $frontendObject->setPriority($object['priority'] ?: 0);
                $frontendObject->setSwitchAllTypes($object['switchAllTypes']);
                $frontendObject->setDescription($object['description'] ?: '');
                $frontendObject->setImage($object['image']);
                $frontendObject->setLocation($object['location'] ?: $type['location']);
                $frontendObject->setAudience($object['targetAudience'] ? \Contao\StringUtil::deserialize($object['targetAudience']) : []);
                $frontendObject->setSpeaker($object['speaker'] ? \Contao\StringUtil::deserialize($object['speaker']) : []);
                $frontendObject->setTopic($object['topic'] ? \Contao\StringUtil::deserialize($object['topic']) : []);
                $frontendObject->setPrice($object['price'] ?: 0.00);
                $frontendObject->setPriceOption($object['priceoption']);

                if ($cloneObject) {
                    $frontendObject->setTimeinterval($object['time_interval'] ?: $cloneObject['time_interval']);
                    $frontendObject->setDuration($object['duration'] ?: $cloneObject['duration']);
                    $frontendObject->setMinReservationDay($object['min_reservation_day'] ?: $cloneObject['min_reservation_day']);
                    $frontendObject->setMaxReservationDay($object['max_reservation_day'] ?: $cloneObject['max_reservation_day']);
                    $frontendObject->setDesiredCapacity([$object['desiredCapacityMin'] ?: $cloneObject['desiredCapacityMin'], $object['desiredCapacityMax'] ?: $cloneObject['desiredCapacityMax']]);
                    $frontendObject->setAllTypesQuantity($object['allTypesQuantity'] ?: intval($cloneObject['allTypesQuantity']));
                    $frontendObject->setAllTypesValidity($object['allTypesValidity'] ?: intval($cloneObject['allTypesValidity']));

                    $frontendObject->setLocation($frontendObject->getLocation() ?: $cloneObject['location']);
//                    $frontendObject->setAudience($frontendObject->getAudience() ?: \Contao\StringUtil::deserialize($cloneObject['targetAudience']));
//                    $frontendObject->setSpeaker($frontendObject->getSpeaker() ?: \Contao\StringUtil::deserialize($cloneObject['speaker']));
//                    $frontendObject->setTopic($frontendObject->getTopic() ?: \Contao\StringUtil::deserialize($cloneObject['topic']));

                } else {
                    $frontendObject->setTimeinterval($object['time_interval']);
                    $frontendObject->setDuration($object['duration']);
                    $frontendObject->setMinReservationDay($object['min_reservation_day']);
                    $frontendObject->setMaxReservationDay($object['max_reservation_day']);
                    $frontendObject->setDesiredCapacity([$object['desiredCapacityMin'], $object['desiredCapacityMax']]);
                    $frontendObject->setAllTypesQuantity($object['allTypesQuantity'] ?: 0);
                    $frontendObject->setAllTypesValidity($object['allTypesValidity'] ?: 0);
                }

                $opening_hours = array();
                $weekdays = array('0'=>false,'1'=>false,'2'=>false,'3'=>false,'4'=>false,'5'=>false,'6'=>false);

                if ($cloneObject) {
                    $opening_hours['su'] = \Contao\StringUtil::deserialize($cloneObject['oh_sunday']);
                    $opening_hours['mo'] = \Contao\StringUtil::deserialize($cloneObject['oh_monday']);
                    $opening_hours['tu'] = \Contao\StringUtil::deserialize($cloneObject['oh_tuesday']);
                    $opening_hours['we'] = \Contao\StringUtil::deserialize($cloneObject['oh_wednesday']);
                    $opening_hours['th'] = \Contao\StringUtil::deserialize($cloneObject['oh_thursday']);
                    $opening_hours['fr'] = \Contao\StringUtil::deserialize($cloneObject['oh_friday']);
                    $opening_hours['sa'] = \Contao\StringUtil::deserialize($cloneObject['oh_saturday']);
                } else {
                    $opening_hours['su'] = \Contao\StringUtil::deserialize($object['oh_sunday']);
                    $opening_hours['mo'] = \Contao\StringUtil::deserialize($object['oh_monday']);
                    $opening_hours['tu'] = \Contao\StringUtil::deserialize($object['oh_tuesday']);
                    $opening_hours['we'] = \Contao\StringUtil::deserialize($object['oh_wednesday']);
                    $opening_hours['th'] = \Contao\StringUtil::deserialize($object['oh_thursday']);
                    $opening_hours['fr'] = \Contao\StringUtil::deserialize($object['oh_friday']);
                    $opening_hours['sa'] = \Contao\StringUtil::deserialize($object['oh_saturday']);
                }


                //ToDo check if only the first record is empty.
                if (key_exists('su', $opening_hours) && $opening_hours['su'] !== false) {
                    if ($opening_hours['su'][0]['time_begin'] && $opening_hours['su'][0]['time_end']) {
                        $weekdays['0'] = true;
                    }
                }
                if (key_exists('mo', $opening_hours) && $opening_hours['mo'] !== false) {
                    if ($opening_hours['mo'][0]['time_begin'] && $opening_hours['mo'][0]['time_end']) {
                        $weekdays['1'] = true;
                    }
                }
                if (key_exists('tu', $opening_hours) && $opening_hours['tu'] !== false) {
                    if ($opening_hours['tu'][0]['time_begin'] && $opening_hours['tu'][0]['time_end']) {
                        $weekdays['2'] = true;
                    }
                }
                if (key_exists('we', $opening_hours) && $opening_hours['we'] !== false) {
                    if ($opening_hours['we'][0]['time_begin'] && $opening_hours['we'][0]['time_end']) {
                        $weekdays['3'] = true;
                    }
                }
                if (key_exists('th', $opening_hours) && $opening_hours['th'] !== false) {
                    if ($opening_hours['th'][0]['time_begin'] && $opening_hours['th'][0]['time_end']) {
                        $weekdays['4'] = true;
                    }
                }
                if (key_exists('fr', $opening_hours) && $opening_hours['fr'] !== false) {
                    if ($opening_hours['fr'][0]['time_begin'] && $opening_hours['fr'][0]['time_end']) {
                        $weekdays['5'] = true;
                    }
                }
                if (key_exists('sa', $opening_hours) && $opening_hours['sa'] !== false) {
                    if ($opening_hours['sa'][0]['time_begin'] && $opening_hours['sa'][0]['time_end']) {
                        $weekdays['6'] = true;
                    }
                }

                $frontendObject->setWeekdayExclusion($weekdays);
                $frontendObject->setOpeningHours($opening_hours);
                $frontendObject->setDatesExclusion(\Contao\StringUtil::deserialize($object['days_exclusion']));

                $objectList[] = $frontendObject;
            }
        }

        return $objectList;
    }
}