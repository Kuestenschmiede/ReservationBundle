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
namespace con4gis\ReservationBundle\Classes\Utils;

use con4gis\ProjectsBundle\Classes\Common\C4GBrickCommon;
use Contao\Date;

class C4gReservationDateChecker
{
    /**
     * @param $date
     * @return void
     */
    public static function getDateAsStamp($date) {
        $tsdate = 0;

        if ($date !== -1) {
            if (is_numeric($date) && (int)$date == $date) {
                $tsdate = (int)$date;
            } else {
                //date_default_timezone_set($GLOBALS['TL_CONFIG']['timeZone'] && ($GLOBALS['TL_CONFIG']['timeZone'] != 'UTC') ?: 'Europe/Berlin');
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
                        $tsdate = strtotime(C4GBrickCommon::getLongDateToConvert($format, $date).' '.$GLOBALS['TL_CONFIG']['timeZone']);
                    }
                }
            }
        }

        return $tsdate;
    }

    public static function mergeDateWithTime($date, $time, $timeZone = false) //ToDo check
    {
        $result = $date;
        if ($date && $time) {
            $beginOfDate = self::getBeginOfDate($date,$timeZone ?: $GLOBALS['TL_CONFIG']['timeZone']);
            if ($time > 86400) {
                $result = $time;
            } else {
                $result = $beginOfDate+$time;
            }
//            $timeZone = $timeZone ? new \DateTimeZone($timeZone) : new \DateTimeZone($GLOBALS['TL_CONFIG']['timeZone']);
//            if ($timeZone && ($time > 86400)) {
//                $dateStamp = self::getBeginOfDate($date,$GLOBALS['TL_CONFIG']['timeZone']);
//                $dateTime = \DateTime::createFromFormat($GLOBALS['TL_CONFIG']['timeFormat'], $time, $timeZone);
//                if ($dateTime) {
//                    $objDate = new Date(date($GLOBALS['TL_CONFIG']['timeFormat'], $dateTime->getTimestamp()), Date::getFormatFromRgxp('time'));
//                    $timeStamp = $objDate->tstamp;
//                    $result = $dateStamp+$timeStamp;
//                }
//            } else if ($timeZone) {
//                $dateStamp = self::getBeginOfDate($date,$GLOBALS['TL_CONFIG']['timeZone']);
//                $dateTime = \DateTime::createFromFormat($GLOBALS['TL_CONFIG']['timeFormat'], $dateStamp+$time, $timeZone);
//                if ($dateTime) {
//                    $objDate = new Date(date($GLOBALS['TL_CONFIG']['timeFormat'], $dateTime->getTimestamp()), Date::getFormatFromRgxp('time'));
//                    $timeStamp = $objDate->tstamp;
//                    $result = $dateStamp+$timeStamp;
//                }
//            }
        }

        return $result;
    }

    public static function mergeDateAndTimeStamp($date, $time)
    {
        return self::mergeDateWithTime($date, $time);
    }

    public static function getBeginOfDate($time, $timeZone = false)
    {
        if ($time) {
            $timeZone = $timeZone ?: $GLOBALS['TL_CONFIG']['timeZone'];
            $beginOfDay = strtotime("today ".$timeZone, $time);
            return $beginOfDay;
        }

        return $time;
    }

    public static function getEndOfDate($time, $timeZone = false)
    {
        if ($time) {
            $timeZone = $timeZone ?: $GLOBALS['TL_CONFIG']['timeZone'];
            $beginOfDay = strtotime("today ".$timeZone, $time);
            $endOfDay   = strtotime("tomorrow ".$timeZone, $beginOfDay) - 1;
            return $endOfDay;
        }

        return $time;
    }

    public static function mergeDateWithTimeForIcs($date, $time)
    {
        $result = $date;

        if ($date) {
            $date = date('Ymd', intval($date));
        }

        if ($time) {
            $time = date('His', intval($time));
        }

        if ($date && $time) {
            $result = $date . 'T' . $time;
        }

        return $result;
    }

    public static function getWeekdayStr($weekday)
    {
        if (is_numeric($weekday)) {
            switch (intval($weekday)) {
                case 0:
                    $weekday = 'su';

                    break;
                case 1:
                    $weekday = 'mo';

                    break;
                case 2:
                    $weekday = 'tu';

                    break;
                case 3:
                    $weekday = 'we';

                    break;
                case 4:
                    $weekday = 'th';

                    break;
                case 5:
                    $weekday = 'fr';

                    break;
                case 6:
                    $weekday = 'sa';

                    break;
            }
        }

        return $weekday;
    }

    public static function getWeekdayFullStr($weekday)
    {
        if (is_numeric($weekday)) {
            switch (intval($weekday)) {
                case 0:
                    $weekday = 'sunday';

                    break;
                case 1:
                    $weekday = 'monday';

                    break;
                case 2:
                    $weekday = 'tuesday';

                    break;
                case 3:
                    $weekday = 'wednesday';

                    break;
                case 4:
                    $weekday = 'thursday';

                    break;
                case 5:
                    $weekday = 'friday';

                    break;
                case 6:
                    $weekday = 'saturday';

                    break;
            }
        }

        return $weekday;
    }

    public static function getWeekdayNumber($weekday)
    {
        if (is_string($weekday)) {
            switch (strtolower($weekday)) {
                case 'su': return 0;
                case 'mo': return 1;
                case 'tu': return 2;
                case 'we': return 3;
                case 'th': return 4;
                case 'fr': return 5;
                case 'sa': return 6;
            }

        }

    }

    /**
     * @param $date
     * @param $timeZone
     * @return bool
     */
    public static function isSunday($date, $timeZone = '')
    {
        return self::checkDay(0, $date, $timeZone);
    }

    public static function isMonday($date, $timeZone = '')
    {
        return self::checkDay(1, $date, $timeZone);
    }

    public static function isTuesday($date, $timeZone = '')
    {
        return self::checkDay(2, $date, $timeZone);
    }

    public static function isWednesday($date, $timeZone = '')
    {
        return self::checkDay(3, $date, $timeZone);
    }

    public static function isThursday($date, $timeZone = '')
    {
        return self::checkDay(4, $date, $timeZone);
    }

    public static function isFriday($date, $timeZone = '')
    {
        return self::checkDay(5, $date, $timeZone);
    }

    public static function isSaturday($date, $timeZone = '')
    {
        return self::checkDay(6, $date, $timeZone);
    }

    public static function checkDay($day, $date, $timeZone = '') {
//        date_default_timezone_set($timeZone ?: $GLOBALS['TL_CONFIG']['timeZone']);
        if ($date && (date('w', $date) == $day)) {
            return true;
        }

        return false;
    }

    public static function isStampInPeriod($stamp, $begin, $end, $reverse = 0) {
        return $reverse ? (($stamp > $begin) && ($stamp <= $end)) : (($stamp >= $begin) && ($stamp < $end));
    }
}
