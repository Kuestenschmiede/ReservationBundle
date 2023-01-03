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
    public function __construct($date, $endDate, $typeId, $objectTypeId, $objectList, $testResults = [])
    {
        $beginDate = $date;
        $endDate = $endDate;
        $this->date = $beginDate;
        $this->objectTypeId = $objectTypeId;

        if ($testResults && !empty($testResults)) {
            $this->reservations[$date][$objectTypeId] = $testResults;
        } else {
            $database = Database::getInstance();
            $set = [$beginDate, $beginDate, $endDate, $endDate, $typeId, $objectTypeId];

            $objStr = '';
            foreach ($objectList as $object) {
                $objStr .= $objStr ? ",".$object->getId() : strval($object->getId());
            }

            $this->objectListString = $objStr;

            //ToDo check OR vs. AND on begin and end date
            $result = $database->prepare('SELECT * FROM `tl_c4g_reservation` WHERE ' .
                "((? >= `beginDate` AND ? <= `endDate`) OR (? >= `beginDate` AND ? <= `endDate`)) AND `reservation_type` = ? AND `reservationObjectType` = ? AND `reservation_object` IN (".$objStr.") AND NOT `cancellation`='1'")
                ->execute($set)->fetchAllAssoc();
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
    public function calculateDefault(int $date, int $time, int $endTime, $object, $type, int $capacity, $timeArray, $actDuration = 0)
    {
        $objectId = $object->getId();
        $reservationList = [];
        $date = C4gReservationDateChecker::getBeginOfDate($date);

        if ($this->resultList) {
            foreach ($this->resultList as $reservation) {

                if ($object) {
                    $allTypesValidity = $object->getAllTypesValidity();
                }

                if (!$allTypesValidity && $reservation['reservation_object'] != $objectId) {
                    continue;
                }

                $timeBegin = C4gReservationDateChecker::mergeDateWithTime($date,$time,'GMT');
                $timeEnd = C4gReservationDateChecker::mergeDateWithTime($date,$endTime,'GMT');
                $timeBeginDb = C4gReservationDateChecker::mergeDateWithTime($date,$reservation['beginTime'],'GMT');
                $timeEndDb = C4gReservationDateChecker::mergeDateWithTime($date,$reservation['endTime'],'GMT');

                /* Todo Nur zum Testen */
                $realBegin = date($GLOBALS['TL_CONFIG']['timeFormat'], $timeBegin);
                $realEnd   = date($GLOBALS['TL_CONFIG']['timeFormat'], $timeEnd);
                $realBeginDb = date($GLOBALS['TL_CONFIG']['timeFormat'], $timeBeginDb);
                $realEndDb   = date($GLOBALS['TL_CONFIG']['timeFormat'], $timeEndDb);

                if (C4gReservationDateChecker::isStampInPeriod($timeBegin, $timeBeginDb, $timeEndDb) ||
                    C4gReservationDateChecker::isStampInPeriod($timeEnd, $timeBeginDb, $timeEndDb, 1) ||
                    C4gReservationDateChecker::isStampInPeriod($timeBeginDb, $timeBegin, $timeEnd) ||
                    C4gReservationDateChecker::isStampInPeriod($timeEndDb, $timeBegin, $timeEnd,1)) {
                    $reservationList[] = $reservation;
                }
            }
        }

        $calculatorResult = new C4gReservationCalculatorResult();
        $calculatorResult->setDbBookings($this->calculateDbBookingsPerType($reservationList));
        $calculatorResult->setDbBookedObjects($this->calculateDbObjectsPerType($reservationList));
        $calculatorResult->setDbPersons($this->calculateDbPersons($reservationList, $objectId));
        $calculatorResult->setDbPercent($this->calculateDbPercent($object, $calculatorResult->getDbPersons(), $capacity));
        $calculatorResult->setTimeArray($timeArray);//$this->calculateTimeArray($reservationList, $timeArray, $date, $time, $objectId));

        $this->calculatorResult = $calculatorResult;
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
    public function calculateNextDay(int $date, int $time, int $endTime, $object, $type, int $capacity, $timeArray, $actDuration = 0)
    {
        $this->calculateMultipleDays($date,$time,$endTime,$object,$type,$capacity,$timeArray,$actDuration);
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
    public function calculateMultipleDays(int $date, int $time, int $endTime, $object, $type, int $capacity, $timeArray, $actDuration = 0)
    {
        $objectId = $object->getId();
        $typeId = $type['id'];
        $objectType = $type['reservationObjectType'];
        $reservationList = [];
        $date = C4gReservationDateChecker::getBeginOfDate($date);

        if ($this->resultList) {
            foreach ($this->resultList as $reservation) {

                if ($object) {
                    $allTypesValidity = $object->getAllTypesValidity();
                }

                if (!$allTypesValidity && $reservation['reservation_object'] != $objectId) {
                    continue;
                }

                $timeBegin = C4gReservationDateChecker::mergeDateWithTime($date,$time,'GMT');
                $timeEnd = $endTime > 86400 ? $endTime : C4gReservationDateChecker::mergeDateWithTime($date,$endTime, 'GMT');
                $timeBeginDb = C4gReservationDateChecker::mergeDateWithTime($reservation['beginDate'],$reservation['beginTime'], 'GMT');
                $timeEndDb = C4gReservationDateChecker::mergeDateWithTime($reservation['endDate'],$reservation['endTime'], 'GMT');

                /* Todo Nur zum Testen */
                $realBegin = date($GLOBALS['TL_CONFIG']['timeFormat'], $timeBegin);
                $realEnd   = date($GLOBALS['TL_CONFIG']['timeFormat'], $timeEnd);
                $realBeginDb = date($GLOBALS['TL_CONFIG']['timeFormat'], $timeBeginDb);
                $realEndDb   = date($GLOBALS['TL_CONFIG']['timeFormat'], $timeEndDb);

                if (C4gReservationDateChecker::isStampInPeriod($timeBegin, $timeBeginDb, $timeEndDb) ||
                    C4gReservationDateChecker::isStampInPeriod($timeEnd, $timeBeginDb, $timeEndDb, 1) ||
                    C4gReservationDateChecker::isStampInPeriod($timeBeginDb, $timeBegin, $timeEnd) ||
                    C4gReservationDateChecker::isStampInPeriod($timeEndDb, $timeBegin, $timeEnd,1)) {
                    $reservationList[] = $reservation;
                }
            }
        }

        $calculatorResult = new C4gReservationCalculatorResult();
        $calculatorResult->setDbBookings($this->calculateDbBookingsPerType($reservationList));
        $calculatorResult->setDbBookedObjects($this->calculateDbObjectsPerType($reservationList));
        $calculatorResult->setDbPersons($this->calculateDbPersons($reservationList, $objectId));
        $calculatorResult->setDbPercent($this->calculateDbPercent($object, $calculatorResult->getDbPersons(), $capacity));
        $calculatorResult->setTimeArray($timeArray ?: []);//$this->calculateTimeArray($reservationList, $timeArray, $date, $time, $objectId));

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
