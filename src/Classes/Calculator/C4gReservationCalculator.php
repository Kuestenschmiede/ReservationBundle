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

use con4gis\CoreBundle\Resources\contao\models\C4gSettingsModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationParamsModel;
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
            $this->reservations[$startDay][$objectTypeId] = $testResults;
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
                    "((`beginDate` BETWEEN ? AND ?) OR (`endDate` BETWEEN ? AND ?)) AND `reservation_type` = ? AND `reservationObjectType` = ? AND `reservation_object` IN (".$objStr.") AND NOT `cancellation`='1'")
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
    public function calculate(int $date, int $endDate, int $time, int $endTime, $object, int $capacity, $timeArray)
    {
        $objectId = $object->getId();
        $reservationList = [];
        $firstDate = C4gReservationDateChecker::getBeginOfDate($date);
        $lastDate = C4gReservationDateChecker::getBeginOfDate($endDate);

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
                    $timeEnd = $lastDate+86400+$endTime;
                } else {
                    $timeEnd = $lastDate+$endTime;
                }

                $dbBeginTime = C4gReservationDateChecker::getStampAsTime($reservation['beginTime']);
                $dbEndTime = C4gReservationDateChecker::getStampAsTime($reservation['endTime']);
                if (($reservation['beginDate'] == $reservation['endDate']) && ($dbBeginTime > $dbEndTime)) {
                   $timeEndDb = $reservation['endDate']+(86400+$dbEndTime);
                } else {
                   $timeEndDb = $reservation['endDate']+$dbEndTime;
                }

                if (C4gReservationDateChecker::isStampInPeriod($timeBegin, $timeBeginDb, $timeEndDb) ||
                    C4gReservationDateChecker::isStampInPeriod($timeEnd, $timeBeginDb, $timeEndDb, 1) ||
                    C4gReservationDateChecker::isStampInPeriod($timeBeginDb, $timeBegin, $timeEnd) ||
                    C4gReservationDateChecker::isStampInPeriod($timeEndDb, $timeBegin, $timeEnd, 1)) {
                    $reservationList[] = $reservation;
                }
//TODO checking max quantity / all types validity (for all objects of the same type)
                if ($allTypesValidity && $object->getQuantity() == 1) {
                    if ($firstDate == $reservation['beginDate']) {
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

    /**
     * @param $object
     * @param $type
     * @param $isEvent
     * @param $countPersons
     * @param $duration
     * @param $date
     * @param $langCookie
     * @return int|array
     */
    public static function calcPrices($object, $type, $isEvent = false, $countPersons = 1, $duration = 0, $date = 0, $langCookie = '', $calcTaxes) {
        $price = intval($object['price']) ?? 0;
        $priceSum = 0;
        $priceInfo = '';
        $countPersons = intval($countPersons);

        if ($object) {

            if (!$duration) {
                $duration = $type['min_residence_time'];
            }

            if ($langCookie) {
                \System::loadLanguageFile('fe_c4g_reservation', $langCookie);
            }

            $priceOption = key_exists('priceoption',$object) ? $object['priceoption'] : '';
            $interval = $object['time_interval'];
            $timeSpan = max(intval($duration), intval($interval));
            switch ($priceOption) {
                case 'pMin':
                    $minutes = 0;
                    if ($isEvent && $object['startTime'] && $object['endTime']) {
                        $diff = $object['endTime'] - $object['startTime'];
                        if ($diff > 0) {
                            $minutes = $diff / 60;
                        }
                    } else if (!$isEvent && $type['periodType'] && $interval) {
                        switch ($type['periodType']) {
                            case 'minute':
                                $minutes = $timeSpan;
                                break;
                            case 'hour':
                                $minutes = $timeSpan * 60;
                                break;
                            case 'overnight':
                            case 'day':
                                $minutes = $timeSpan * 60 * 24;
                                break;
                            case 'week':
                                $minutes = $timeSpan * 60 * 24 * 7;
                                break;
                            default:
                                '';
                        }
                    }
                    $priceSum = intval($price * $minutes);
                    $priceInfo = $GLOBALS['TL_LANG']['fe_c4g_reservation']['pMin'];
                    break;
                case 'pHour':
                    $hours = 0;
                    if ($isEvent && $object['startTime'] && $object['endTime']) {
                        $diff = $object['endTime'] - $object['startTime'];
                        if ($diff > 0) {
                            $hours = $diff / 3600;
                        }
                    } else if (!$isEvent && $type['periodType'] && $timeSpan) {
                        switch ($type['periodType']) {
                            case 'minute':
                                $hours = $timeSpan / 60;
                                break;
                            case 'hour':
                                $hours = $timeSpan;
                                break;
                            case 'overnight':
                            case 'day':
                                $hours = $timeSpan * 24;
                                break;
                            case 'week':
                                $hours = $timeSpan * 24 * 7;
                                break;
                            default:
                                '';
                        }
                    }
                    $priceSum = intval($price * $hours);
                    $priceInfo = $GLOBALS['TL_LANG']['fe_c4g_reservation']['pHour'];
                    break;
                case 'pNight':
                case 'pDay':
                    $days = $duration ?: 0;
                    if ($isEvent && $object['startDate'] && $object['endDate']) {
                        $days = round(abs($object['endDate'] - $object['startDate']) / (60 * 60 * 24));
                    } else if (!$days && !$isEvent && key_exists('beginDate', $object) && $object['beginDate'] && key_exists('endDate', $object) && $object['endDate']) {
                        $days = round(abs($object['endDate'] - $object['beginDate']) / (60 * 60 * 24));
                    }
                    $priceSum = intval($price * $days);
                    $priceInfo = ($type['periodType'] === 'day') ? $GLOBALS['TL_LANG']['fe_c4g_reservation']['pDay'] : $GLOBALS['TL_LANG']['fe_c4g_reservation']['pNight'];
                    break;
                case 'pNightPerson':
                    $days = $duration ?: 0;
                    if ($isEvent && $object['startDate'] && $object['endDate']) {
                        $days = round(abs($object['endDate'] - $object['startDate']) / (60 * 60 * 24));
                    } else if (!$days && !$isEvent && key_exists('beginDate', $object) && $object['beginDate'] && key_exists('endDate', $object) && $object['endDate']) {
                        $days = round(abs($object['endDate'] - $object['beginDate']) / (60 * 60 * 24));
                    }
                    $priceSum = intval($price * $days * $countPersons);
                    $priceInfo = $GLOBALS['TL_LANG']['fe_c4g_reservation']['pNightPerson'];
                    break;
                case 'pWeek':
                    $weeks = $duration ?: 0;
                    if ($isEvent && $object['startDate'] && $object['endDate']) {
                        $weeks = round(abs($object['endDate'] - $object['startDate']) / (60 * 60 * 24 * 7));
                    } else if (!$weeks && !$isEvent && key_exists('beginDate', $object) && $object['beginDate'] && key_exists('endDate', $object) && $object['endDate']) {
                        $weeks = round(abs($object['endDate'] - $object['beginDate']) / (60 * 60 * 24 * 7));
                    }
                    $priceSum = intval($price * $weeks);
                    $priceInfo = $GLOBALS['TL_LANG']['fe_c4g_reservation']['pWeek'];
                    break;
                case 'pReservation':
                    $price = intval($price);
                    $priceInfo = $GLOBALS['TL_LANG']['fe_c4g_reservation']['pEvent'];
                    break;
                case 'pPerson':
                    $priceSum = intval($price * $countPersons);
                    $priceInfo = $GLOBALS['TL_LANG']['fe_c4g_reservation']['pPerson'];
                    break;

                case 'pAmount':
                    $priceSum = $price;
                    break;
            }
        }
        if ($price) {
            $priceInfo = $priceInfo ? "&nbsp;" . $priceInfo : '';
        }

        if ($calcTaxes) {
            $taxOption = $object['taxOptions'];
            $reservationTaxRate = self::setTaxRates($taxOption);

            if ($taxOption !== 'tNone') {
                $priceNet = $priceSum / (1 + $reservationTaxRate/100);
                $priceTax = $priceSum - $priceNet;
            } else {
                $priceNet = $priceSum;
                $priceTax = 0;
            }

            return array('price' => $price, 'priceSum' => $priceSum, 'priceInfo' => $priceInfo, 'priceNet' => $priceNet, 'priceTax' => $priceTax , 'reservationTaxRate' => $reservationTaxRate);
        } else {
            return array('price' => $price, 'priceSum' => $priceSum, 'priceInfo' => $priceInfo);
        }
    }

    /**
     * @param $putVars
     * @param $object
     * @param $type
     * @param $calcTaxes
     * @return int|array
     */
  public static function calcOptionPrices ($putVars, $object, $type, $calcTaxes) {

    $incParamSum = 0;
    $addParamSum = 0;


    if ($calcTaxes){
        $incParamSumTax = 0;
        $incParamSumNet = 0;

        $addParamSumTax = 0;
        $addParamSumNet = 0;
    }

      //Reservation included options
      $includedParams = $type['included_params'] ?: false;

      if ($includedParams) {

          $optionList = unserialize($includedParams);
          $incParamArr = isset($optionList) ? self::getReservationOptions($optionList, [], $calcTaxes) : false;
          foreach ($incParamArr as $key => $value) {
              $incParamSum += $value['price'];
              if ($calcTaxes) {
                  //individual included option tax
                  $incParamSumNet += $value['priceOptionNet'];
                  $incParamSumTax += $value['price'] - $value['priceOptionNet'];
              }
          }
      }

        // Additional reservation options
      $additionalParams = $type['additional_params'] ?: false;
      if ($additionalParams) {
          $optionList = unserialize($additionalParams);
          $additionalParamArr = isset($optionList) ? self::getReservationOptions($optionList, [], $calcTaxes) : false;
          $objectPid = $object['pid'];
          foreach ($additionalParamArr as $key => $value) {

              if ($type['additionalParamsFieldType'] == 'radio') {
                  $chosenAdditionalOptions = $putVars['additional_params_' . $type['id'] . '-00' . $objectPid];
                  if ($value['id'] == $chosenAdditionalOptions) {
                      $addParamSum += $value['price'];
                  }

              } else {

                  $chosenAdditionalOptions = $putVars['additional_params_' . $type['id'] . '-00' . $objectPid . '|' . $value['id']];
                  if ($chosenAdditionalOptions === 'true') {
                      $chosenAdditionalOptions = true;
                  } else {
                      $chosenAdditionalOptions = false;
                  }

                  if ($chosenAdditionalOptions) {
                      $addParamSum += $value['price'];
                      if ($calcTaxes) {
                          //individual additional option tax
                          $addParamSumNet += $value['priceOptionNet'];
                          $addParamSumTax += $value['price'] - $value['priceOptionNet'];
                      }
                  }
              }
          }
      }

    $priceOptionSum = $incParamSum + $addParamSum;

    if ($calcTaxes) {

        $priceOptionTax = $incParamSumTax + $addParamSumTax;
        $priceOptionNet = $incParamSumNet + $addParamSumNet;

        return array(   'priceOptionSum' => $priceOptionSum,
                        'priceOptionNet' => $priceOptionNet,
                        'priceOptionTax' => $priceOptionTax);
    } else {

        return array('priceOptionSum' => $priceOptionSum);

    }
  }

    /**
     * @param $desiredCapacity
     * @param $putVars
     * @param $object
     * @param $type
     * @param $calcTaxes
     * @param $onlyParticipants
     * @return array
     */
  public static function calcParticipantOptionPrices ($desiredCapacity, $putVars, $object, $type, $calcTaxes, $onlyParticipants) {
      $priceParticipantOptionSum = 0;
      $priceParticipantOptionSumNet = 0;
      $priceParticipantOptionSumTax = 0;

      $participantParams = $object['participant_params'] ?: ($type['participant_params'] ?: false);

      if ($participantParams) {
          $optionList = unserialize($participantParams);
          $participantParamArr = isset($optionList) ? self::getReservationOptions($optionList, [], $calcTaxes) : false;

          $counter = $onlyParticipants ? $desiredCapacity : $desiredCapacity - 1;
          $priceParticipantOptionSum = 0;
          if ($calcTaxes) {
              $priceParticipantOptionSumNet = 0;
              $priceParticipantOptionSumTax = 0;
          }

          for ($i = 0; $i < ($counter); $i++) {

              foreach ($participantParamArr as $key => $value){

                  if ($object['participantParamsFieldType'] == 'radio'){
                      $chosenParticipantOptions = $putVars['participants_' . $type['id'] . '-' . ($counter) . '§participant_params§' . $i];
                      if ($chosenParticipantOptions === $value['id']){
                          $priceParticipantOptionSum += $value['price'];
                          $chosenParticipantOptions = true;
                      } else {
                          $chosenParticipantOptions = false;
                      }

                  } else {

                      $chosenParticipantOptions = $putVars['participants_' . $type['id']  . '-' . ($counter) . '§participant_params§' . $i . '|' . $value['id']];
                      if ($chosenParticipantOptions === 'true') {
                          $chosenParticipantOptions = true;
                      } else {
                          $chosenParticipantOptions = false;
                      }
                      if ($chosenParticipantOptions){
                          $priceParticipantOptionSum += $value['price'];
                      }
                  }
                  //individual participant option tax
                  if ($calcTaxes && $chosenParticipantOptions) {
                      $priceParticipantOptionSumNet += $value['priceOptionNet'];
                      if ($value['taxOptions'] == 'tNone') {
                          $value['priceOptionNet'] = $value['price'];
                      }
                      $priceParticipantOptionSumTax += $value['price'] - $value['priceOptionNet'];
                  }
              }
          }
      }

      if ($calcTaxes) {

          return array( 'priceParticipantOptionSum' => $priceParticipantOptionSum,
                        'priceParticipantOptionSumNet' => $priceParticipantOptionSumNet,
                        'priceParticipantOptionSumTax' => $priceParticipantOptionSumTax);
      } else {
          return array('priceParticipantOptionSum' => $priceParticipantOptionSum);
      }
  }

    /**
     * @param $optionsId
     * @param $paramArr
     * @param $calcTaxes
     * @return mixed
     */
    public static function getReservationOptions($optionsId, $paramArr, $calcTaxes): mixed {
        if ($optionsId) {
            foreach ($optionsId as $paramId) {
                if ($paramId) {
                    $param = C4gReservationParamsModel::findByPk($paramId);
                    if ($param && $param->caption && $param->published && ($param->price && $calcTaxes)) {
                        $taxOption = $param->taxOptions;
                        $optionTaxRate = self::setTaxRates($taxOption);
                        $paramData = [
                            'id' => $paramId,
                            'name' => $param->caption,
                            'price' => $param->price,
                            'taxOptions' => $param->taxOptions,
                        ];

                        if ($taxOption !== 'tNone') {
                            $priceOptionNet = $param->price / (1 + $optionTaxRate/100);
                            $paramData['priceOptionNet'] = $priceOptionNet;
                        }

                        $paramArr[] = $paramData;

                    } else if ($param && $param->caption && $param->published) {
                        $paramArr[] = ['id' => $paramId, 'name' => $param->caption];
                    }
                }
            }
        }
        return $paramArr;
    }

    /**
     * @param $taxOption
     * @return int
     */
    public static function setTaxRates($taxOption) {
//this should be called when calc taxs is true butt only once
        $settings = C4gSettingsModel::findSettings();
        //Dashboard taxrates
        $taxRateStandard = ($settings->taxRateStandard ?? 0);
        $taxRateReduced = ($settings->taxRateReduced ?? 0);
//            $taxRateStandard = $taxRateStandardToken / 100;
//            $taxRateReduced = $taxRateReducedToken / 100;

        if ($taxOption === 'tStandard') {
            return $taxRateStandard;
        } elseif ($taxOption === 'tReduced') {
            return $taxRateReduced;
        } else {
            return 0;
        }
    }
}
