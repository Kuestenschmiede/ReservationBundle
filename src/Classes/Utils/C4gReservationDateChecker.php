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

class C4gReservationDateChecker
{
    public static function mergeDateWithTime($date, $time)
    {
        $result = $date;
        if ($date && $time) {
            $dateTimeObject = new \DateTime();
            $dateTimeObject->setTimezone(new \DateTimeZone($GLOBALS['TL_CONFIG']['timeZone']));
            $dateTimeObject->setTimestamp($date+$time+3600); //ToDo lost hour - calc time diff
            $mergedDateTime = $dateTimeObject->format($GLOBALS['TL_CONFIG']['datimFormat']);
            $mergedDateTime = \DateTime::createFromFormat($GLOBALS['TL_CONFIG']['datimFormat'], $mergedDateTime);
            $result = $mergedDateTime->getTimestamp();
        }

        return $result;
    }

    public static function  getBeginOfDate($time)
    {
        if ($time) {
            $dateTimeObject = new \DateTime();
            $dateTimeObject->setTimezone(new \DateTimeZone($GLOBALS['TL_CONFIG']['timeZone']));
            $dateTimeObject->setTimestamp($time);
            $beginOfDayString = $dateTimeObject->format('Y-m-d 00:00:00');
            $beginOfDayObject = \DateTime::createFromFormat('Y-m-d H:i:s', $beginOfDayString);
            $beginOfDay = $beginOfDayObject->getTimestamp();

            return $beginOfDay;
        }

        return $time;
    }

    public static function getEndOfDate($time)
    {
        if ($time) {
            $dateTimeObject = new \DateTime();
            $dateTimeObject->setTimezone(new \DateTimeZone($GLOBALS['TL_CONFIG']['timeZone']));
            $dateTimeObject->setTimestamp($time);
            $endOfDayString = $dateTimeObject->format('Y-m-d 23:59:59');
            $endOfDayObject = \DateTime::createFromFormat('Y-m-d H:i:s', $endOfDayString);
            $endOfDay = $endOfDayObject->getTimestamp();

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
            switch ($weekday) {
                case 'su':
                    $weekday = 0;

                    break;
                case 'mo':
                    $weekday = 1;

                    break;
                case 'tu':
                    $weekday = 2;

                    break;
                case 'we':
                    $weekday = 3;

                    break;
                case 'th':
                    $weekday = 4;

                    break;
                case 'fr':
                    $weekday = 5;

                    break;
                case 'sa':
                    $weekday = 6;

                    break;
            }
        }

        return $weekday;
    }

    public static function isSunday($date)
    {
        if ($date && (date('w', $date) == 0)) {
            return true;
        }

        return false;
    }

    public static function isMonday($date)
    {
        if ($date && (date('w', $date) == 1)) {
            return true;
        }

        return false;
    }

    public static function isTuesday($date)
    {
        if ($date && (date('w', $date) == 2)) {
            return true;
        }

        return false;
    }

    public static function isWednesday($date)
    {
        if ($date && (date('w', $date) == 3)) {
            return true;
        }

        return false;
    }

    public static function isThursday($date)
    {
        if ($date && (date('w', $date) == 4)) {
            return true;
        }

        return false;
    }

    public static function isFriday($date)
    {
        if ($date && (date('w', $date) == 5)) {
            return true;
        }

        return false;
    }

    public static function isSaturday($date)
    {
        if ($date && (date('w', $date) == 6)) {
            return true;
        }

        return false;
    }
}
