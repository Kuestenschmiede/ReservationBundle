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

    /**
     * @param $date
     * @param $type
     * @param int $objectTypeId
     */
    public function __construct($date, $objectTypeId, $testResults = [])
    {
        $beginDate = C4gReservationDateChecker::getBeginOfDate($date);
        $endDate = C4gReservationDateChecker::getEndOfDate($date);
        $this->date = $beginDate;
        $this->objectTypeId = $objectTypeId;

        if ($testResults) {
            $this->reservations[$date][$objectTypeId] = $testResults;
        } else {
            $database = Database::getInstance();
            $set = [$beginDate, $endDate, $objectTypeId];
//            $set = [$date, $date, $objectTypeId];
            $result = $database->prepare('SELECT * FROM `tl_c4g_reservation` WHERE ' .
                "? <= `beginDate` AND ? >= `endDate` AND `reservationObjectType` = ? AND NOT `cancellation`='1'")
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
    public function calculateAll(int $date, int $time, int $endTime, $object, $type, int $capacity, $timeArray, $actDuration = 0)
    {
        $objectId = $object->getId();
        $typeId = $type['id'];
        $objectType = $type['reservationObjectType'];
        $reservationList = [];
        $date = C4gReservationDateChecker::getBeginOfDate($date);

        if (($endTime >= 86400) && ($type['periodType'] !== 'day') && ($type['periodType'] !== 'week')) { //nxt day
            $database = Database::getInstance();
            $objectId = $object->getId();
            $allTypesValidity = $object->getAllTypesValidity();
            $allTypesQuantity = $object->getAllTypesQuantity();
            $switchAllTypes = $object->getSwitchAllTypes();
            $switchAllTypes = \Contao\StringUtil::deserialize($switchAllTypes);
            if ($object && $allTypesValidity) {
                if ($switchAllTypes && count($switchAllTypes) > 0) {
                    if (!in_array($typeId, $switchAllTypes)) {
                        $switchAllTypes[] = $typeId;
                    }
                    $allTypes = implode(',', $switchAllTypes);

                    if ($time >= 86400) {
                        $set = [strtotime('+1 day', $date)];
                    } else {
                        $set = [$date];
                    }
                    $reservations = $database->prepare('SELECT * FROM `tl_c4g_reservation` WHERE ' .
                        '`beginDate`=? AND `reservation_type` IN (' . $allTypes . ") AND `reservationObjectType` IN(1,3) AND NOT `cancellation`='1'")
                        ->execute($set)->fetchAllAssoc();
                } else {
                    if ($time >= 86400) {
                        $set = [strtotime('+1 day', $date)];
                    } else {
                        $set = [$date];
                    }
                    $reservations = $database->prepare('SELECT * FROM `tl_c4g_reservation` WHERE ' .
                        "`beginDate`=? AND `reservationObjectType` IN(1,3) AND NOT `cancellation`='1'")
                        ->execute($set)->fetchAllAssoc();
                }
            } elseif ($object && $allTypesQuantity) {
                if ($switchAllTypes && count($switchAllTypes) > 0) {
                    if (!in_array($typeId, $switchAllTypes)) {
                        $switchAllTypes[] = $typeId;
                    }
                    $allTypes = implode(',', $switchAllTypes);
                    if ($time >= 86400) {
                        $set = [strtotime('+1 day', $date), $objectId, $objectType];
                    } else {
                        $set = [$date, $objectId, $objectType];
                    }
                    $reservations = $database->prepare('SELECT * FROM `tl_c4g_reservation` WHERE ' .
                        '`beginDate`=? AND `reservation_type` IN (' . $allTypes . ") AND `reservation_object`=? AND `reservationObjectType` IN (1,3) AND NOT `cancellation`='1'")
                        ->execute($set)->fetchAllAssoc();
                } else {
                    if ($time >= 86400) {
                        $set = [strtotime('+1 day', $date), $objectId, $objectType];
                    } else {
                        $set = [$date, $objectId, $objectType];
                    }
                    $reservations = $database->prepare('SELECT * FROM `tl_c4g_reservation` WHERE ' .
                        "`beginDate`=? AND `reservation_object`=? AND `reservationObjectType` IN (1,3) AND NOT `cancellation`='1'")
                        ->execute($set)->fetchAllAssoc();
                }
            } else {
                if ($time >= 86400) {
                    $set = [strtotime('+1 day', $date), $typeId];
                } else {
                    $set = [$date, $typeId];
                }
                $reservations = $database->prepare('SELECT * FROM `tl_c4g_reservation` WHERE ' .
                    "`beginDate`=? AND `reservation_type`=? AND `reservationObjectType` IN(1,3) AND NOT `cancellation`='1'")
                    ->execute($set)->fetchAllAssoc();
            }

            if ($reservations) {
                foreach ($reservations as $reservation) {
                    $tb = $time && ($time >= 86400) ? $time - 86400 : $time;
                    $te = $endTime && ($endTime >= 86400) ? $endTime - 86400 : $endTime;

                    $timeBegin = C4gReservationDateChecker::mergeDateWithTime($date,$tb);
                    $timeEnd = C4gReservationDateChecker::mergeDateWithTime($date,$te);
                    $timeBeginDb = C4gReservationDateChecker::mergeDateWithTime($date,$reservation['beginTime']);
                    $timeEndDb = C4gReservationDateChecker::mergeDateWithTime($date,$reservation['endTime']);

                    if ($timeBegin >= $timeBeginDb) {
                        $reservationList[] = $reservation;
                    }
                }
            }
        } elseif ($this->resultList) {
            foreach ($this->resultList as $reservation) {

                if ($object) {
                    $allTypesValidity = $object->getAllTypesValidity();
//                    $allTypesQuantity = $object->getAllTypesQuantity();
//                    $switchAllTypes = $object->getSwitchAllTypes();
//                    $switchAllTypes = \Contao\StringUtil::deserialize($switchAllTypes);
                }

                if (!$allTypesValidity && $reservation['reservation_object'] != $objectId) {
                    continue;
                }

                $timeBegin = C4gReservationDateChecker::mergeDateWithTime($date,$time);
                $timeEnd = C4gReservationDateChecker::mergeDateWithTime($date,$endTime);
                $timeBeginDb = C4gReservationDateChecker::mergeDateWithTime($date,$reservation['beginTime']);
                $timeEndDb = C4gReservationDateChecker::mergeDateWithTime($date,$reservation['endTime']);

                $actDuration = intval($actDuration) && (intval($actDuration) > 0) ? intval($actDuration) : intval($reservation['duration']); //ToDo object
                $reservationInterval = intval($reservation['timeInterval']);

                if ($actDuration && $reservationInterval && ($actDuration != $reservationInterval)) {
                    switch ($reservation['periodType']) {
                        case 'minute':
                            $timeEnd = $timeEnd - ($reservationInterval * 60) + ($actDuration * 60);
                            break;
                        case 'hour':
                            $timeEnd = $timeEnd - ($reservationInterval * 3600) + ($actDuration * 3600);
                            break;
                        case 'day':
                            $timeEnd = $timeEnd - ($reservationInterval * 86400) + ($actDuration * 86400);
                            break;
                        case 'week':
                            $timeEnd = $timeEnd - ($reservationInterval * 604800) + ($actDuration * 604800);
                            break;
                    }
                }
                /* Todo Nur zum Testen */
//                date_default_timezone_set('Europe/Berlin');
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
