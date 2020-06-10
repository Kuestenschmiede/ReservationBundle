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

class C4gReservationDateChecker
{
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
