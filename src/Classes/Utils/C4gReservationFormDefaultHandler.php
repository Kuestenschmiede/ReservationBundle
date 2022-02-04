<?php

namespace con4gis\ReservationBundle\Classes\Utils;

use con4gis\CoreBundle\Classes\Helper\StringHelper;
use con4gis\CoreBundle\Resources\contao\models\C4gLogModel;
use con4gis\ProjectsBundle\Classes\Buttons\C4GBrickButton;
use con4gis\ProjectsBundle\Classes\Common\C4GBrickCommon;
use con4gis\ProjectsBundle\Classes\Common\C4GBrickConst;
use con4gis\ProjectsBundle\Classes\Common\C4GBrickRegEx;
use con4gis\ProjectsBundle\Classes\Conditions\C4GBrickCondition;
use con4gis\ProjectsBundle\Classes\Conditions\C4GBrickConditionType;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GButtonField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GCheckboxField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GDateField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GEmailField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GForeignKeyField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GHeadlineField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GImageField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GInfoTextField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GKeyField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GLabelField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GMultiCheckboxField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GMultiLinkField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GMultiSelectField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GNumberField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GPostalField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GRadioGroupField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GSelectField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GSubDialogField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTelField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTextareaField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTextField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTimeField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTrixEditorField;
use con4gis\ReservationBundle\Classes\Models\C4gReservationEventAudienceModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationEventModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationEventSpeakerModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationEventTopicModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationLocationModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationParamsModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationSettingsModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationTypeModel;
use con4gis\ReservationBundle\Controller\C4gReservationController;
use Contao\Controller;
use Contao\Date;
use Contao\FrontendUser;
use Contao\Input;
use Contao\ModuleModel;
use Contao\StringUtil;

class C4gReservationFormDefaultHandler extends C4gReservationFormHandler
{
    public function addFields() {
        $listType = $this->typeObject;
        $module = $this->module;
        $reservationSettings = $module->getReservationSettings();
        $reservationObjects = $listType['objects'];
        $maxCapacity = $listType['maxParticipantsPerBooking'] ?: 0;
        $minCapacity = $listType['minParticipantsPerBooking'] ?: 1;
        $condition = $this->condition;
        $showDateTime = $reservationSettings->showDateTime ? "1" : "0";

        if ($reservationSettings->withCapacity) {
            $reservationDesiredCapacity = new C4GNumberField();
            $reservationDesiredCapacity->setFieldName('desiredCapacity');

            if ($minCapacity && $maxCapacity) {
                $reservationDesiredCapacity->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['desiredCapacity']. '&nbsp;('.$minCapacity.'-'.$maxCapacity.')');
            } else {
                $reservationDesiredCapacity->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['desiredCapacity']);
            }
            $reservationDesiredCapacity->setFormField(true);
            $reservationDesiredCapacity->setEditable(true);
            $reservationDesiredCapacity->setCondition(array($condition));
            $reservationDesiredCapacity->setInitialValue($minCapacity);
            $reservationDesiredCapacity->setMandatory(true);
            $reservationDesiredCapacity->setMin($minCapacity);
            if ($maxCapacity) {
                $reservationDesiredCapacity->setMax($maxCapacity);
            }
            $reservationDesiredCapacity->setPattern(C4GBrickRegEx::NUMBERS);
            $reservationDesiredCapacity->setCallOnChange(true);
            $reservationDesiredCapacity->setCallOnChangeFunction("setReservationForm(".$listType['id'] . "," . $showDateTime . ",false);");
            $reservationDesiredCapacity->setNotificationField(true);
            $reservationDesiredCapacity->setAdditionalID($listType['id']);
            $reservationDesiredCapacity->setStyleClass('desired-capacity');

            $this->fieldList[] = $reservationDesiredCapacity;
        }


        //set reservationObjectType to default
        $reservationObjectTypeField = new C4GNumberField();
        $reservationObjectTypeField->setFieldName('reservationObjectType');
        $reservationObjectTypeField->setInitialValue('1');
        $reservationObjectTypeField->setDatabaseField(true);
        $reservationObjectTypeField->setFormField(false);
        $this->fieldList[] = $reservationObjectTypeField;

        $additionalDuration = $reservationSettings->additionalDuration;
        if (intval($additionalDuration) >= 1) {
            $durationField = new C4GNumberField();
            $durationField->setFieldName('duration');
            $durationField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['duration']);
            $durationField->setColumnWidth(10);
            $durationField->setFormField(true);
            $durationField->setSortColumn(true);
            $durationField->setTableColumn(true);
            $durationField->setMandatory(true);
            $durationField->setCallOnChange(true);
            $durationField->setCallOnChangeFunction("setTimeset(document.getElementById('c4g_beginDate_" . $listType['id'] . "'), " . $listType['id'] . "," . $showDateTime . ");");
            $durationField->setCondition(array($condition));
            $durationField->setNotificationField(true);
            $durationField->setStyleClass('duration');
            $durationField->setMin(1);
            $durationField->setMax($additionalDuration);
            $durationField->setMaxLength(3);
            $durationField->setStep(5);

            $this->fieldList[] = $durationField;
        } else {
            $additionalDuration = 0;
        }

        if (($listType['periodType'] === 'minute') || ($listType['periodType'] === 'hour')) {
            if (!$this->initialValues->getDate() && $listType['directBooking']) {
                $initialBookingDate = time();
            } else {
                $initialBookingDate = false;
            }

            if ($this->initialValues->getDate() || $initialBookingDate) {
                $script = $this->getDialogParams()->getOnloadScript();
                $script = $script."setTimeset(document.getElementById('c4g_beginDate_".$listType['id']."')," . $listType['id'] . "," . $showDateTime . ");";
                $this->getDialogParams()->setOnloadScript($script);
            }

            $reservationBeginDateField = new C4GDateField();
            $reservationBeginDateField->setFlipButtonPosition(false);
            $reservationBeginDateField->setMinDate(C4gReservationHandler::getMinDate($reservationObjects));
            $reservationBeginDateField->setMaxDate(C4gReservationHandler::getMaxDate($reservationObjects));
            $reservationBeginDateField->setExcludeWeekdays(C4gReservationHandler::getWeekdayExclusionString($reservationObjects));
            $reservationBeginDateField->setExcludeDates(C4gReservationHandler::getDateExclusionString($reservationObjects, $listType, $reservationSettings->removeBookedDays));
            $reservationBeginDateField->setFieldName('beginDate');
            $reservationBeginDateField->setCustomFormat($GLOBALS['TL_CONFIG']['dateFormat']);
            $reservationBeginDateField->setCustomLanguage($GLOBALS['TL_LANGUAGE']);
            $reservationBeginDateField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDate']);
            $reservationBeginDateField->setEditable(true);
            $reservationBeginDateField->setInitialValue($initialBookingDate ?: $this->initialValues->getDate());
            $reservationBeginDateField->setComparable(false);
            $reservationBeginDateField->setSortColumn(true);
            $reservationBeginDateField->setSortSequence('de_datetime');
            $reservationBeginDateField->setTableColumn(true);
            $reservationBeginDateField->setFormField(true);
            $reservationBeginDateField->setColumnWidth(10);
            $reservationBeginDateField->setMandatory(true);
            $reservationBeginDateField->setCondition(array($condition));
            $reservationBeginDateField->setCallOnChange(true);
            $reservationBeginDateField->setCallOnChangeFunction("setTimeset(this," . $listType['id'] . "," . $showDateTime . ");");
            $reservationBeginDateField->setNotificationField(true);
            $reservationBeginDateField->setAdditionalID($listType['id']);
            $reservationBeginDateField->setStyleClass('begin-date');
            $this->fieldList[] = $reservationBeginDateField;
        }

        $reservationendTimeField = new C4GTextField();
        $reservationendTimeField->setFieldName('endTime');
        $reservationendTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['endTime']);
        $reservationendTimeField->setFormField(false);
        $reservationendTimeField->setHidden(true);
        $reservationendTimeField->setEditable(true);
        $reservationendTimeField->setSort(false);
        $reservationendTimeField->setDatabaseField(true);
        $reservationendTimeField->setCallOnChange(true);
        $reservationendTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $showDateTime . ')');
        $reservationendTimeField->setNotificationField(true);
        $reservationendTimeField->setRemoveWithEmptyCondition(true);
        $reservationendTimeField->setStyleClass('reservation_time_button reservation_time_button_' . $listType['id']);
        $this->fieldList[] = $reservationendTimeField;

        if (!$this->initialValues->getTime() && $listType['directBooking']) {
            $objDate = new Date(date($GLOBALS['TL_CONFIG']['timeFormat'],time()), Date::getFormatFromRgxp('time'));

            //ToDo check valid initial time ???

            $initialBookingTime = $objDate->tstamp;
        } else {
            $initialBookingTime = false;
        }

        $objects = [];
        foreach ($reservationObjects as $reservationObject) {

            //ToDo Check Capacity
            $objects[] = array(
                'id' => $reservationObject->getId(),
                'name' => $reservationObject->getCaption(),
                'min' => $reservationObject->getDesiredCapacity()[0] ? $reservationObject->getDesiredCapacity()[0] : 1,
                'max' => $reservationObject->getDesiredCapacity()[1] ? ($reservationObject->getDesiredCapacity()[1] * $reservationObject->getQuantity()) : 0,// unbegrenzt -> $reservationObject->getQuantity(),
                'allmostFullyBookedAt' => $reservationObject->getAlmostFullyBookedAt(),
                'openingHours' => $reservationObject->getOpeningHours()
            );
        }

        if ($initialBookingDate && $initialBookingTime && $objects) {
            $reservationBeginTimeField = new C4GRadioGroupField();
            $reservationBeginTimeField->setFieldName('beginTime');
            $reservationBeginTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
            $reservationBeginTimeField->setFormField(true);
            $reservationBeginTimeField->setDatabaseField(true);
            $reservationBeginTimeField->setOptions(C4gReservationHandler::getReservationNowTime($objects[0], $reservationSettings->showEndTime, $reservationSettings->showFreeSeats));
            $reservationBeginTimeField->setCallOnChange(true);
            $reservationBeginTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $reservationSettings->showDateTime . ')');
            $reservationBeginTimeField->setMandatory(false);
            $reservationBeginTimeField->setInitialValue($initialBookingTime ?: $this->initialValues->getTime());
            $reservationBeginTimeField->setSort(false);
            $reservationBeginTimeField->setCondition(array($condition));
            $reservationBeginTimeField->setAdditionalID($listType['id'].'-00'.date('w', $this->initialValues->getDate()));
            $reservationBeginTimeField->setNotificationField(true);
            $reservationBeginTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
            $reservationBeginTimeField->setTurnButton(true);
            $reservationBeginTimeField->setShowButtons(true);
            $reservationBeginTimeField->setRemoveWithEmptyCondition(true);
            $reservationBeginTimeField->setStyleClass('reservation_time_button reservation_time_button_direct reservation_time_button_' . $listType['id']);
            $reservationBeginTimeField->setTimeButtonSpecial(true);
            $this->fieldList[] = $reservationBeginTimeField;
        } else if (($listType['periodType'] === 'hour') || ($listType['periodType'] === 'minute')) {

            for ($i=0;$i<=6;$i++) {
                if ($this->initialValues->getDate()) {
                    $wd = date('N', $this->initialValues->getDate());
                    if ($i != $wd) {
                        continue;
                    }
                }

                $we = $reservationObject->getWeekdayExclusion();
                foreach ($we as $key=>$value) {
                    if ($i == $key) {
                        if (!$value) {
                            continue(2);
                        }
                    }
                }
                $wdCondition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $listType['id'].'--'.$i);
                $wdCondition->setModel(C4gReservationHandler::class);
                $wdCondition->setFunction('isWeekday');
                $wdConditionArr = [$wdCondition, $condition];

                $reservationTimeField = new C4GRadioGroupField();
                $reservationTimeField->setFieldName('beginTime');
                $reservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                $reservationTimeField->setFormField(true);
                $reservationTimeField->setOptions(
                    C4gReservationHandler::getReservationTimes(
                        $reservationObjects,
                        $listType['id'],
                        $i,
                        -1,
                        0,
                        $reservationSettings->showEndTime,
                        $reservationSettings->showFreeSeats
                    ));
                $reservationTimeField->setMandatory(true);
                $reservationTimeField->setInitInvisible(true);
                $reservationTimeField->setSort(false);
                $reservationTimeField->setCondition($wdConditionArr);
                $reservationTimeField->setCallOnChange(true);
                $reservationTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $reservationSettings->showDateTime . ')');
                $reservationTimeField->setAdditionalID($listType['id'] . '-00'.$i);
                $reservationTimeField->setNotificationField(true);
                $reservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                $reservationTimeField->setTurnButton(true);
                $reservationTimeField->setShowButtons(true);
                $reservationTimeField->setRemoveWithEmptyCondition(true);
                $reservationTimeField->setStyleClass('reservation_time_button reservation_time_button_' . $listType['id']);
                $reservationTimeField->setInitialValue($this->initialValues->getTime());
                $reservationTimeField->setTimeButtonSpecial(true);
                $this->fieldList[] = $reservationTimeField;

                //NEXT
            }
//            $su_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $listType['id']);
//            $su_condition->setModel(C4gReservationHandler::class);
//            $su_condition->setFunction('isSunday');
//            $suConditionArr = [$su_condition, $condition];
//
//            $suReservationTimeField = new C4GRadioGroupField();
//            $suReservationTimeField->setFieldName('beginTime');
//            $suReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
//            $suReservationTimeField->setFormField(true);
//            $suReservationTimeField->setOptions(
//                C4gReservationHandler::getReservationTimes(
//                    $reservationObjects,
//                    $listType['id'],
//                    '0',
//                    -1,
//                    0,
//                    $reservationSettings->showEndTime,
//                    $reservationSettings->showFreeSeats
//                ));
//            $suReservationTimeField->setMandatory(true);
//            $suReservationTimeField->setInitInvisible(true);
//            $suReservationTimeField->setSort(false);
//            $suReservationTimeField->setCondition($suConditionArr);
//            $suReservationTimeField->setCallOnChange(true);
//            $suReservationTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $reservationSettings->showDateTime . ')');
//            $suReservationTimeField->setAdditionalID($listType['id'] . '-000');
//            $suReservationTimeField->setNotificationField(true);
//            $suReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
//            $suReservationTimeField->setTurnButton(true);
//            $suReservationTimeField->setShowButtons(true);
//            $suReservationTimeField->setRemoveWithEmptyCondition(true);
//            $suReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_' . $listType['id']);
//            $suReservationTimeField->setInitialValue($this->initialValues->getTime());
//            $suReservationTimeField->setTimeButtonSpecial(true);
//            $this->fieldList[] = $suReservationTimeField;
//
//            $mo_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $listType['id']);
//            $mo_condition->setModel(C4gReservationHandler::class);
//            $mo_condition->setFunction('isMonday');
//            $moConditionArr = [$mo_condition, $condition];
//
//            $moReservationTimeField = new C4GRadioGroupField();
//            $moReservationTimeField->setFieldName('beginTime');
//            $moReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
//            $moReservationTimeField->setFormField(true);
//            $moReservationTimeField->setOptions(
//                C4gReservationHandler::getReservationTimes(
//                    $reservationObjects,
//                    $listType['id'],
//                    '1',
//                    -1,
//                    0,
//                    $reservationSettings->showEndTime,
//                    $reservationSettings->showFreeSeats
//                ));
//            $moReservationTimeField->setMandatory(true);
//            $moReservationTimeField->setInitInvisible(true);
//            $moReservationTimeField->setSort(false);
//            $moReservationTimeField->setCondition($moConditionArr);
//            $moReservationTimeField->setCallOnChange(true);
//            $moReservationTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $reservationSettings->showDateTime . ')');
//            $moReservationTimeField->setAdditionalID($listType['id'] . '-001');
//            $moReservationTimeField->setNotificationField(true);
//            $moReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
//            $moReservationTimeField->setTurnButton(true);
//            $moReservationTimeField->setShowButtons(true);
//            $moReservationTimeField->setRemoveWithEmptyCondition(true);
//            $moReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_' . $listType['id']);
//            $moReservationTimeField->setInitialValue($this->initialValues->getTime());
//            $moReservationTimeField->setTimeButtonSpecial(true);
//            $this->fieldList[] = $moReservationTimeField;
//
//            $tu_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $listType['id']);
//            $tu_condition->setModel(C4gReservationHandler::class);
//            $tu_condition->setFunction('isTuesday');
//            $tuConditionArr = [$tu_condition, $condition];
//
//            $tuReservationTimeField = new C4GRadioGroupField();
//            $tuReservationTimeField->setFieldName('beginTime');
//            $tuReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
//            $tuReservationTimeField->setFormField(true);
//            $tuReservationTimeField->setOptions(
//                C4gReservationHandler::getReservationTimes(
//                    $reservationObjects,
//                    $listType['id'],
//                    '2',
//                    -1,
//                    0,
//                    $reservationSettings->showEndTime,
//                    $reservationSettings->showFreeSeats
//                ));
//            $tuReservationTimeField->setMandatory(true);
//            $tuReservationTimeField->setInitInvisible(true);
//            $tuReservationTimeField->setSort(false);
//            $tuReservationTimeField->setCondition($tuConditionArr);
//            $tuReservationTimeField->setCallOnChange(true);
//            $tuReservationTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $reservationSettings->showDateTime . ')');
//            $tuReservationTimeField->setAdditionalID($listType['id'] . '-002');
//            $tuReservationTimeField->setNotificationField(true);
//            $tuReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
//            $tuReservationTimeField->setTurnButton(true);
//            $tuReservationTimeField->setShowButtons(true);
//            $tuReservationTimeField->setRemoveWithEmptyCondition(true);
//            $tuReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_' . $listType['id']);
//            $tuReservationTimeField->setInitialValue($this->initialValues->getTime());
//            $tuReservationTimeField->setTimeButtonSpecial(true);
//            $this->fieldList[] = $tuReservationTimeField;
//
//            $we_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $listType['id']);
//            $we_condition->setModel(C4gReservationHandler::class);
//            $we_condition->setFunction('isWednesday');
//            $weConditionArr = [$we_condition, $condition];
//
//            $weReservationTimeField = new C4GRadioGroupField();
//            $weReservationTimeField->setFieldName('beginTime');
//            $weReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
//            $weReservationTimeField->setFormField(true);
//            $weReservationTimeField->setOptions(
//                C4gReservationHandler::getReservationTimes(
//                    $reservationObjects,
//                    $listType['id'],
//                    '3',
//                    -1,
//                    0,
//                    $reservationSettings->showEndTime,
//                    $reservationSettings->showFreeSeats
//                ));
//            $weReservationTimeField->setMandatory(true);
//            $weReservationTimeField->setInitInvisible(true);
//            $weReservationTimeField->setSort(false);
//            $weReservationTimeField->setCondition($weConditionArr);
//            $weReservationTimeField->setCallOnChange(true);
//            $weReservationTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $reservationSettings->showDateTime . ')');
//            $weReservationTimeField->setAdditionalID($listType['id'] . '-003');
//            $weReservationTimeField->setNotificationField(true);
//            $weReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
//            $weReservationTimeField->setTurnButton(true);
//            $weReservationTimeField->setShowButtons(true);
//            $weReservationTimeField->setRemoveWithEmptyCondition(true);
//            $weReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_' . $listType['id']);
//            $weReservationTimeField->setInitialValue($this->initialValues->getTime());
//            $weReservationTimeField->setTimeButtonSpecial(true);
//            $this->fieldList[] = $weReservationTimeField;
//
//            $th_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $listType['id']);
//            $th_condition->setModel(C4gReservationHandler::class);
//            $th_condition->setFunction('isThursday');
//            $thConditionArr = [$th_condition, $condition];
//
//            $thReservationTimeField = new C4GRadioGroupField();
//            $thReservationTimeField->setFieldName('beginTime');
//            $thReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
//            $thReservationTimeField->setFormField(true);
//            $thReservationTimeField->setOptions(
//                C4gReservationHandler::getReservationTimes(
//                    $reservationObjects,
//                    $listType['id'],
//                    '4',
//                    -1,
//                    0,
//                    $reservationSettings->showEndTime,
//                    $reservationSettings->showFreeSeats
//                ));
//            $thReservationTimeField->setMandatory(true);
//            $thReservationTimeField->setInitInvisible(true);
//            $thReservationTimeField->setSort(false);
//            $thReservationTimeField->setCondition($thConditionArr);
//            $thReservationTimeField->setCallOnChange(true);
//            $thReservationTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $reservationSettings->showDateTime . ')');
//            $thReservationTimeField->setAdditionalID($listType['id'] . '-004');
//            $thReservationTimeField->setNotificationField(true);
//            $thReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
//            $thReservationTimeField->setTurnButton(true);
//            $thReservationTimeField->setShowButtons(true);
//            $thReservationTimeField->setRemoveWithEmptyCondition(true);
//            $thReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_' . $listType['id']);
//            $thReservationTimeField->setInitialValue($this->initialValues->getTime());
//            $thReservationTimeField->setTimeButtonSpecial(true);
//            $this->fieldList[] = $thReservationTimeField;
//
//            $fr_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $listType['id']);
//            $fr_condition->setModel(C4gReservationHandler::class);
//            $fr_condition->setFunction('isFriday');
//            $frConditionArr = [$fr_condition, $condition];
//
//            $frReservationTimeField = new C4GRadioGroupField();
//            $frReservationTimeField->setFieldName('beginTime');
//            $frReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
//            $frReservationTimeField->setFormField(true);
//            $frReservationTimeField->setOptions(
//                C4gReservationHandler::getReservationTimes(
//                    $reservationObjects,
//                    $listType['id'],
//                    '5',
//                    -1,
//                    0,
//                    $reservationSettings->showEndTime,
//                    $reservationSettings->showFreeSeats
//                ));
//            $frReservationTimeField->setMandatory(true);
//            $frReservationTimeField->setInitInvisible(true);
//            $frReservationTimeField->setSort(false);
//            $frReservationTimeField->setCondition($frConditionArr);
//            $frReservationTimeField->setCallOnChange(true);
//            $frReservationTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $reservationSettings->showDateTime . ')');
//            $frReservationTimeField->setAdditionalID($listType['id'] . '-005');
//            $frReservationTimeField->setNotificationField(true);
//            $frReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
//            $frReservationTimeField->setTurnButton(true);
//            $frReservationTimeField->setShowButtons(true);
//            $frReservationTimeField->setRemoveWithEmptyCondition(true);
//            $frReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_' . $listType['id']);
//            $frReservationTimeField->setInitialValue($this->initialValues->getTime());
//            $frReservationTimeField->setTimeButtonSpecial(true);
//            $this->fieldList[] = $frReservationTimeField;
//
//            $sa_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $listType['id']);
//            $sa_condition->setModel(C4gReservationHandler::class);
//            $sa_condition->setFunction('isSaturday');
//            $saConditionArr = [$sa_condition, $condition];
//
//            $saReservationTimeField = new C4GRadioGroupField();
//            $saReservationTimeField->setFieldName('beginTime');
//            $saReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
//            $saReservationTimeField->setFormField(true);
//            $saReservationTimeField->setEditable(false);
//            $saReservationTimeField->setOptions(
//                C4gReservationHandler::getReservationTimes(
//                    $reservationObjects,
//                    $listType['id'],
//                    '6',
//                    -1,
//                    0,
//                    $reservationSettings->showEndTime,
//                    $reservationSettings->showFreeSeats
//                ));
//            $saReservationTimeField->setMandatory(true);
//            $saReservationTimeField->setInitInvisible(true);
//            $saReservationTimeField->setSort(false);
//            $saReservationTimeField->setCondition($saConditionArr);
//            $saReservationTimeField->setCallOnChange(true);
//            $saReservationTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $reservationSettings->showDateTime . ')');
//            $saReservationTimeField->setAdditionalID($listType['id'] . '-006');
//            $saReservationTimeField->setNotificationField(true);
//            $saReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
//            $saReservationTimeField->setTurnButton(true);
//            $saReservationTimeField->setShowButtons(true);
//            $saReservationTimeField->setRemoveWithEmptyCondition(true);
//            $saReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_' . $listType['id']);
//            $saReservationTimeField->setInitialValue($this->initialValues->getTime());
//            $saReservationTimeField->setTimeButtonSpecial(true);
//            $this->fieldList[] = $saReservationTimeField;

        }

        //save endDate
        $reservationEndDateDBField = new C4GDateField();
        $reservationEndDateDBField->setFieldName('endDate');
        $reservationEndDateDBField->setInitialValue(0);
        $reservationEndDateDBField->setDatabaseField(true);
        $reservationEndDateDBField->setFormField(false);
        $reservationEndDateDBField->setMax(9999999999999);
        $reservationEndDateDBField->setNotificationField(true);
        $this->fieldList[] = $reservationEndDateDBField;

        //save endTime
        $reservationEndTimeDBField = new C4GTimeField();
        $reservationEndTimeDBField->setFieldName('endTime');
        $reservationEndTimeDBField->setInitialValue(0);
        $reservationEndTimeDBField->setDatabaseField(true);
        $reservationEndTimeDBField->setFormField(false);
        $reservationEndTimeDBField->setMax(9999999999999);
        $reservationEndTimeDBField->setNotificationField(true);
        $this->fieldList[] = $reservationEndTimeDBField;

        $reservationObjectField = new C4GSelectField();
        $reservationObjectField->setChosen(false);
        $reservationObjectField->setFieldName('reservation_object');
        $reservationObjectField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object']);
        $reservationObjectField->setDescription($GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_reservation_object']);
        $reservationObjectField->setFormField(true);
        $reservationObjectField->setEditable(false);
        $reservationObjectField->setOptions($objects);
        $reservationObjectField->setMandatory(true);
        $reservationObjectField->setNotificationField(true);
        $reservationObjectField->setRangeField('desiredCapacity_' . $listType['id']);
        $reservationObjectField->setStyleClass('reservation-object displayReservationObjects');
        $reservationObjectField->setWithEmptyOption(true);
        $reservationObjectField->setInitialValue(-1);
        $reservationObjectField->setShowIfEmpty(true);
        $reservationObjectField->setDatabaseField(true);
        $reservationObjectField->setEmptyOptionLabel($this->reservationSettings->emptyOptionLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object_none']);
        $reservationObjectField->setCondition([$condition]);
        $reservationObjectField->setRemoveWithEmptyCondition(true);
        $reservationObjectField->setCallOnChange(true);
        $reservationObjectField->setAdditionalID($listType["id"]);
        $this->fieldList[] = $reservationObjectField;

        if ($reservationSettings->showDetails) {
            foreach ($reservationObjects as $reservationObject) {
                $object_condition = [
                    new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'reservation_object_' . $listType['id'], $reservationObject->getId()),
                    new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'reservation_type', $listType['id'])
                ];
                if ($reservationObject->getDescription()) {
                    $descriptionField = new C4GTrixEditorField();
                    $descriptionField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['description']);
                    $descriptionField->setFieldName('description');
                    $descriptionField->setInitialValue($reservationObject->getDescription());
                    $descriptionField->setCondition($object_condition);
                    $descriptionField->setFormField(true);
                    $descriptionField->setShowIfEmpty(false);
                    $descriptionField->setAdditionalID($listType['id'] . '-' . $reservationObject->getId());
                    $descriptionField->setRemoveWithEmptyCondition(true);
                    $descriptionField->setDatabaseField(false);
                    $descriptionField->setEditable(false);
                    $descriptionField->setNotificationField(true);
                    $this->fieldList[] = $descriptionField;
                }

                if ($reservationObject->getImage()) {
                    $imageField = new C4GImageField();
                    $imageField->setFieldName('image');
                    $imageField->setInitialValue($reservationObject->getImage());
                    $imageField->setCondition($object_condition);
                    $imageField->setFormField(true);
                    $imageField->setShowIfEmpty(false);
                    $imageField->setAdditionalID($listType['id'] . '-' . $reservationObject->getId());
                    $imageField->setRemoveWithEmptyCondition(true);
                    $imageField->setDatabaseField(false);
                    $imageField->setLightBoxField(true);
                    $imageField->setInitInvisible(true);
                    $this->fieldList[] = $imageField;
                }
            }
        }

        $includedParams = $listType['includedParams'];
        $includedParamsArr = [];

        if ($includedParams) {
            foreach ($includedParams as $paramId) {
                $includedParam = C4gReservationParamsModel::findByPk($paramId);
                if ($includedParam && $includedParam->caption && ($includedParam->price && $reservationSettings->showPrices)) {
                    $includedParamsArr[] = ['id' => $paramId, 'name' => $includedParam->caption . "<span class='price'>&nbsp;(+" . number_format($includedParam->price, 2, ',', '.') . " €)</span>"];
                } else if ($includedParam && $includedParam->caption) {
                    $includedParamsArr[] = ['id' => $paramId, 'name' => $includedParam->caption];
                }
            }
        }

        if (count($includedParamsArr) > 0) {
            $includedParams = new C4GMultiCheckboxField();
            $includedParams->setFieldName('included_params');
            $includedParams->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['included_params']);
            $includedParams->setFormField(true);
            $includedParams->setEditable(false);
            $includedParams->setOptions($includedParamsArr);
            $includedParams->setMandatory(false);
            $includedParams->setModernStyle(false);
            $includedParams->setCondition(array($condition));
            $includedParams->setRemoveWithEmptyCondition(true);
            $includedParams->setAdditionalID($listType['id'] . '-00' . $reservationObject->getId());
            $includedParams->setStyleClass('included-params');
            $includedParams->setAllChecked(true);
            $includedParams->setNotificationField(true);
            $this->fieldList[] = $includedParams;
        }

        $params = $listType['additionalParams'];
        $additionalParamsArr = [];

        if ($params) {
            foreach ($params as $paramId) {
                if ($paramId) {
                    $additionalParam = C4gReservationParamsModel::findByPk($paramId);
                    if ($additionalParam && $additionalParam->caption && ($additionalParam->price && $reservationSettings->showPrices)) {
                        $additionalParamsArr[] = ['id' => $paramId, 'name' => $additionalParam->caption . "<span class='price'>&nbsp;(+" . number_format($additionalParam->price, 2, ',', '.') . " €)</span>"];
                    } else if ($additionalParam && $additionalParam->caption) {
                        $additionalParamsArr[] = ['id' => $paramId, 'name' => $additionalParam->caption];
                    }
                }
            }
        }

        if (count($additionalParamsArr) > 0) {
            $additionalParams = new C4GMultiCheckboxField();
            $additionalParams->setFieldName('additional_params');
            $additionalParams->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['additional_params']);
            $additionalParams->setFormField(true);
            $additionalParams->setEditable(true);
            $additionalParams->setOptions($additionalParamsArr);
            $additionalParams->setMandatory(false);
            $additionalParams->setModernStyle(false);
            $additionalParams->setCondition(array($condition));
            $additionalParams->setRemoveWithEmptyCondition(true);
            $additionalParams->setAdditionalID($listType['id'] . '-00' . $reservationObject->getId());
            $additionalParams->setStyleClass('additional-params');
            $additionalParams->setNotificationField(true);
            $this->fieldList[] = $additionalParams;
        }


        return $this->fieldList;
    }
}