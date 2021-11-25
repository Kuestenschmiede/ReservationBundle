<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 8
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2021, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

namespace con4gis\ReservationBundle\Classes;

use Contao\Database;

class C4gReservationCalculator
{
    private $calculatorResult = null;

    /**
     * C4gReservationCalculator constructor.
     * @param int $date
     * @param int $time
     * @param int $interval
     * @param $type
     * @param $object
     * @param int $capacity
     */
    public function __construct(int $date, int $time, int $endTime, $object, $type, int $capacity, $timeArray)
    {
        $objectId = $object->getId();
        $typeId = $type->id;
        $objectType = $type->reservationObjectType;
        $reservationList = [];

        $database = Database::getInstance();

        if ($endTime >= 86400) { //nxt day

            if ($object && $object->getAllTypesValidity()) {
                if ($time >= 86400) {
                    $set = [strtotime('+1 day', $date), $objectId, $objectType];
                } else {
                    $set = [$date, $objectType];
                }
                $reservations = $database->prepare('SELECT * FROM `tl_c4g_reservation` WHERE ' .
                    "`beginDate`=? AND `reservationObjectType`=? AND NOT `cancellation`='1'")
                    ->execute($set)->fetchAllAssoc();
            } else if ($object && $object->getAllTypesQuantity()) {
                if ($time >= 86400) {
                    $set = [strtotime('+1 day', $date), $objectId, $objectType];
                } else {
                    $set = [$date, $objectId, $objectType];
                }
                $reservations = $database->prepare('SELECT * FROM `tl_c4g_reservation` WHERE ' .
                    "`beginDate`=? AND `reservation_object`=? AND `reservationObjectType`=? AND NOT `cancellation`='1'")
                    ->execute($set)->fetchAllAssoc();
            } else {
                if ($time >= 86400) {
                    $set = [strtotime('+1 day', $date), $typeId, $objectType];
                } else {
                    $set = [$date, $typeId, $objectType];
                }
                $reservations = $database->prepare('SELECT * FROM `tl_c4g_reservation` WHERE ' .
                    "`beginDate`=? AND `reservation_type`=? AND `reservationObjectType`=? AND NOT `cancellation`='1'")
                    ->execute($set)->fetchAllAssoc();
            }

            if ($reservations) {
                foreach ($reservations as $reservation) {
                    $tbdb = date($GLOBALS['TL_CONFIG']['timeFormat'], $reservation['beginTime']);
                    $tedb = date($GLOBALS['TL_CONFIG']['timeFormat'], $reservation['endTime']);

                    $tb = $time ? $time >= 86400 : ($time - 86400);
                    $tb = date($GLOBALS['TL_CONFIG']['timeFormat'], $tb);

                    $te = $endTime ? $endTime >= 86400 : ($endTime - 86400);
                    $te = date($GLOBALS['TL_CONFIG']['timeFormat'], $te);
                    $timeBegin = strtotime($tb);
                    $timeEnd = strtotime($te);
                    $timeBeginDb = strtotime($tbdb);
                    $timeEndDb = strtotime($tedb);
                    if (
                        (($timeBegin >= $timeBeginDb) && ($timeBegin < $timeEndDb)) ||
                        (($timeEnd > $timeBeginDb) && ($timeEnd <= $timeEndDb))) {
                        $reservationList[] = $reservation;
                    }
                }
            }
        } else {
            if ($object && $object->getAllTypesValidity()) {
                $set = [$date, $objectType];
                $reservations = $database->prepare('SELECT * FROM `tl_c4g_reservation` WHERE ' .
                    "`beginDate`=? AND `reservationObjectType`=? AND NOT `cancellation`='1'")
                    ->execute($set)->fetchAllAssoc();
            } else if ($object && $object->getAllTypesQuantity()) {
                $set = [$date, $objectId, $objectType];
                $reservations = $database->prepare('SELECT * FROM `tl_c4g_reservation` WHERE ' .
                    "`beginDate`=? AND `reservation_object`=? AND `reservationObjectType`=? AND NOT `cancellation`='1'")
                    ->execute($set)->fetchAllAssoc();
            } else {
                $set = [$date, $typeId, $objectType];
                $reservations = $database->prepare('SELECT * FROM `tl_c4g_reservation` WHERE ' .
                    "`beginDate`=? AND `reservation_type`=? AND `reservationObjectType`=? AND NOT `cancellation`='1'")
                    ->execute($set)->fetchAllAssoc();
            }

            if ($reservations) {
                foreach ($reservations as $reservation) {
                    $tbdb = date($GLOBALS['TL_CONFIG']['timeFormat'], $reservation['beginTime']);
                    $tedb = date($GLOBALS['TL_CONFIG']['timeFormat'], $reservation['endTime']);
                    $tb = date($GLOBALS['TL_CONFIG']['timeFormat'], $time);
                    $te = date($GLOBALS['TL_CONFIG']['timeFormat'], $endTime);
                    $timeBegin = strtotime($tb);
                    $timeEnd = strtotime($te);
                    $timeBeginDb = strtotime($tbdb);
                    $timeEndDb = strtotime($tedb);
                    if (
                        (($timeBegin >= $timeBeginDb) && ($timeBegin < $timeEndDb)) ||
                        (($timeEnd > $timeBeginDb) && ($timeEnd <= $timeEndDb))) {
                        $reservationList[] = $reservation;
                    }
                }
            }
        }

        $calculatorResult = new C4gReservationCalculatorResult();
        $calculatorResult->setDbBookings($this->calculateDbBookingsPerType($reservationList));
        $calculatorResult->setDbBookedObjects($this->calculateDbObjectsPerType($reservationList));
        $calculatorResult->setDbPersons($this->calculateDbPersons($reservationList, $objectId));
        $calculatorResult->setDbPercent($this->calculateDbPercent($object, $calculatorResult->getDbPersons(), $capacity));
        $calculatorResult->setTimeArray($this->calculateTimeArray($reservationList, $timeArray, $date, $time, $objectId));

        $this->calculatorResult = $calculatorResult;
    }

    /**
     * @return int
     */
    private function calculateDbBookingsPerType($reservations)
    {
        return $reservations ? count($reservations) : 0;
    }

    /**
     * @return int
     */
    private function calculateDbObjectsPerType($reservations)
    {
        $result = [];
        foreach ($reservations as $reservation) {
            if ($reservation['reservation_object']) {
                $result[intval($reservation['reservation_object'])] = $reservation;
            }
        }

        return $result ? count($result) : 0;
    }

    /**
     * @param $objectId
     * @return int|mixed
     */
    private function calculateDbPersons($reservations, $objectId)
    {
        $actPersons = 0;
        if ($reservations) {
            foreach ($reservations as $reservation) {
                if ($reservation['reservation_object']) {
                    if ($reservation['reservation_object'] === $objectId) {
                        $actPersons = $actPersons + intval($reservation['desiredCapacity']);
                    }
                }
            }
        }

        return $actPersons;
    }

    /**
     * @param $object
     * @param $actPersons
     * @param $capacity
     * @return float|int
     */
    private function calculateDbPercent($object, $actPersons, $capacity)
    {
        $actPercent = 0;
        if ($object && $object->getAlmostFullyBookedAt()) {
            $percent = ($actPersons / $capacity) * 100;
            if ($percent >= $object->getAlmostFullyBookedAt()) {
                $actPercent = $percent;
            }
        }

        return $actPercent;
    }

    /**
     * @param $tsdate
     * @param $time
     * @param $objectId
     * @return array
     */
    private function calculateTimeArray($reservations, $timeArray, $tsdate, $time, $objectId)
    {
        if ($reservations) {
            foreach ($reservations as $reservation) {
                if ($reservation['reservation_object']) {
                    if ($reservation['reservation_object'] === $objectId) {
                        $timeArray[$tsdate][$time] = $timeArray[$tsdate][$time] ? $timeArray[$tsdate][$time] + 1 : 1;
                    }
                }
            }
        }

        return $timeArray;
    }

    /**
     * @return null
     */
    public function getCalculatorResult()
    {
        return $this->calculatorResult;
    }
}
