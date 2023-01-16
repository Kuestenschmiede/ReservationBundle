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

namespace con4gis\ReservationBundle\Classes\Calculator;

use con4gis\ReservationBundle\Classes\Utils\C4gReservationDateChecker;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationHandler;
use Contao\Database;

class C4gReservationCalculator
{
    private $calculatorResult = null;
    private $reservations = [];
    private $resultList = [];
    private $date = 0;
    private $objectTypeId = 1;

    private $objectListString = '';

    /**
     * @param $date
     * @param $type
     * @param int $objectTypeId
     */
    public function __construct($startDay, $endDay, $typeId, $objectTypeId, $objectList, $testResults = [])
    {
        $beginDate = C4gReservationDateChecker::getBeginOfDate($startDay);
        $endDate = C4gReservationDateChecker::getEndOfDate($endDay);

        $this->date = $beginDate;
        $this->objectTypeId = $objectTypeId;

        if ($testResults && !empty($testResults)) {
            $this->reservations[$date][$objectTypeId] = $testResults;
        } else {
            $database = Database::getInstance();


            $objStr = '';
            $all = false;
            foreach ($objectList as $object) {
                $all = $all || ($object->getAllTypesValidity() || $object->getAllTypesQuantity());
                $objStr .= $objStr ? ",".$object->getId() : strval($object->getId());
            }

            $this->objectListString = $objStr;

            if ($all) {
                $set = [$beginDate, $endDate, $beginDate, $endDate, $objectTypeId];
                $result = $database->prepare('SELECT * FROM `tl_c4g_reservation` WHERE ' .
                    "((`beginDate` BETWEEN ? AND ?) OR (`endDate` BETWEEN ? AND ?)) AND `reservationObjectType` IN(1,3) AND NOT `cancellation`='1'")
                    ->execute($set)->fetchAllAssoc();
            } else {
                $set = [$beginDate, $endDate, $beginDate, $endDate, $typeId, $objectTypeId];
                $result = $database->prepare('SELECT * FROM `tl_c4g_reservation` WHERE ' .
                    "((`beginDate` BETWEEN ? AND ?) OR (`endDate` BETWEEN ? AND ?)) AND ? <= `endDate`) AND `reservation_type` = ? AND `reservationObjectType` = ? AND `reservation_object` IN (".$objStr.") AND NOT `cancellation`='1'")
                    ->execute($set)->fetchAllAssoc();
            }

            if ($result) {
                $this->reservations[$beginDate][$objectTypeId] = $result;
            }
        }
    }

    /**
     * @param $type
     * @param $object
     * @return void
     */
    public function loadReservations($type, $object)
    {
        if (!$type || !$object || !$this->date || !count($this->reservations)) {
            return;
        }

        $date = $this->date;
        $typeId = $type['id'];
        $objectTypeId = $this->objectTypeId;
        $reservations = $this->reservations[$date][$objectTypeId];
        $objectId = $object->getId();
        $allTypesValidity = $object->getAllTypesValidity();
        $allTypesQuantity = $object->getAllTypesQuantity();
        $switchAllTypes = $object->getSwitchAllTypes();

        $this->resultList = [];

        $commaDates = C4gReservationHandler::getDateExclusionString([$object], $type,0);
        if ($commaDates) {
            $commaDates = $commaDates['dates'];
        }
        $dates = explode(',',$commaDates);
        foreach($dates as $date) {
            if ($date == $this->date) {
                return false;
            }
        }

        $switchAllTypes = \Contao\StringUtil::deserialize($switchAllTypes);
        foreach ($reservations as $reservation) {
            if ($objectId) {
              $reservation['timeInterval'] = $object->getTimeinterval();
              $reservation['duration'] = $object->getDuration() ?: $reservation['duration']; //ToDo
              $reservation['periodType'] = $object->getPeriodType();
            }

            if ($allTypesValidity) {
                if ($switchAllTypes && count($switchAllTypes) > 0) {

                    if (!in_array($typeId, $switchAllTypes)) {
                        $switchAllTypes[] = $typeId;
                    }
                    if (in_array($reservation['reservation_type'], $switchAllTypes)) {
                        $this->resultList[] = $reservation;
                    }
                } else {
                    $this->resultList[] = $reservation;
                }
            } elseif ($allTypesQuantity) {
                if ($switchAllTypes && count($switchAllTypes) > 0) {
                    if (!in_array($typeId, $switchAllTypes)) {
                        $switchAllTypes[] = $typeId;
                    }
                    if ((in_array($reservation['reservation_type'], $switchAllTypes) && ($reservation['reservation_object'] == $objectId))) {
                        $this->resultList[] = $reservation;
                    }
                } else {
                    if ($reservation['reservation_object'] == $objectId) {
                        $this->resultList[] = $reservation;
                    }
                }
            } else {
                $this->resultList[] = $reservation;
            }
        }
    }

    /**
     * @param int $date
     * @param int $time
     * @param int $endTime
     * @param $object
     * @param $type
     * @param int $capacity
     * @param $timeArray
     */
    public function calculate(int $date, int $time, int $endTime, $object, $type, int $capacity, $timeArray, $actDuration = 0)
    {
        $objectId = $object->getId();
        $reservationList = [];
        $firstDate = C4gReservationDateChecker::getBeginOfDate($date);
        $nextDate = $firstDate+86400;

        if ($this->resultList) {
            foreach ($this->resultList as $reservation) {

                if ($object) {
                    $allTypesValidity = $object->getAllTypesValidity();
                }

                if (!$allTypesValidity && $reservation['reservation_object'] != $objectId) {
                    continue;
                }

                $timeBegin = $firstDate+$time;
                $timeBeginDb = $reservation['beginDate']+C4gReservationDateChecker::getStampAsTime($reservation['beginTime']);

                if ($endTime >= 86400) {
                    $endTime = ($endTime-86400);
                }

                $endTime = C4gReservationDateChecker::getStampAsTime($endTime);

                if ($time > $endTime) {
                    $timeEnd = $nextDate+$endTime;
                } else {
                    $timeEnd = $firstDate+$endTime;
                }

                $dbBeginTime = C4gReservationDateChecker::getStampAsTime($reservation['beginTime']);
                $dbEndTime = C4gReservationDateChecker::getStampAsTime($reservation['endTime']);
                if (($reservation['beginDate'] == $reservation['endDate']) && ($dbBeginTime > $dbEndTime)) {
                   $timeEndDb = $reservation['endDate']+(86400+$dbEndTime);
                } else {
                   $timeEndDb = $reservation['endDate']+$dbEndTime;
                }

                /* for testing */
                $realBeginTimeDb = date($GLOBALS['TL_CONFIG']['datimFormat'], $reservation['beginTime']);
                $realEndTimeDb   = date($GLOBALS['TL_CONFIG']['datimFormat'], $reservation['endTime']);
                $realBeginDateDb = date($GLOBALS['TL_CONFIG']['datimFormat'], $reservation['beginDate']);
                $realEndDateDb   = date($GLOBALS['TL_CONFIG']['datimFormat'], $reservation['endDate']);

                $realBegin = date($GLOBALS['TL_CONFIG']['datimFormat'], $timeBegin);
                $realEnd   = date($GLOBALS['TL_CONFIG']['datimFormat'], $timeEnd);
                $realBeginDb = date($GLOBALS['TL_CONFIG']['datimFormat'], $timeBeginDb);
                $realEndDb   = date($GLOBALS['TL_CONFIG']['datimFormat'], $timeEndDb);

                if (C4gReservationDateChecker::isStampInPeriod($timeBegin, $timeBeginDb, $timeEndDb) ||
                    C4gReservationDateChecker::isStampInPeriod($timeEnd, $timeBeginDb, $timeEndDb, 1) ||
                    C4gReservationDateChecker::isStampInPeriod($timeBeginDb, $timeBegin, $timeEnd) ||
                    C4gReservationDateChecker::isStampInPeriod($timeEndDb, $timeBegin, $timeEnd, 1)) {
                    $reservationList[] = $reservation;
                }
            }
        }

        $calculatorResult = new C4gReservationCalculatorResult();
        $calculatorResult->setDbBookings($this->calculateDbBookingsPerType($reservationList));
        $calculatorResult->setDbBookedObjects($this->calculateDbObjectsPerType($reservationList));
        $calculatorResult->setDbPersons($this->calculateDbPersons($reservationList, $objectId));
        $calculatorResult->setDbPercent($this->calculateDbPercent($object, $calculatorResult->getDbPersons(), $capacity));
        $calculatorResult->setTimeArray($timeArray);

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
        if ($capacity && $object && $object->getAlmostFullyBookedAt()) {
            $percent = ($actPersons / $capacity) * 100;
            if ($percent >= $object->getAlmostFullyBookedAt()) {
                $actPercent = $percent;
            }
        }

        return $actPercent;
    }

    /**
     * @return null
     */
    public function getCalculatorResult()
    {
        return $this->calculatorResult;
    }
}
