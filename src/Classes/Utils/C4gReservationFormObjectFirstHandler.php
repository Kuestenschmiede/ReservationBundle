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

class C4gReservationFormObjectFirstHandler extends C4gReservationFormHandler
{
    public function addFields() {
        $listType = $this->typeObject;
        $module = $this->module;
        $reservationSettings = $module->getReservationSettings();
        $reservationObjects = $listType['objects'];
        $condition = $this->condition;
        $showDateTime = $reservationSettings->showDateTime ? "1" : "0";

        $objects = [];
        foreach ($reservationObjects as $reservationObject) {
            $objects[] = array(
                'id' => $reservationObject->getId(),
                'name' => $reservationObject->getCaption(),
                'min' => $reservationObject->getDesiredCapacity()[0] ?: 1,
                'max' => $reservationObject->getDesiredCapacity()[1] ?: 0,
                'allmostFullyBookedAt' => $reservationObject->getAlmostFullyBookedAt(),
                'openingHours' => $reservationObject->getOpeningHours()
            );
        }

        $reservationObjectField = new C4GSelectField();
        $reservationObjectField->setChosen(true);
        $reservationObjectField->setFieldName('reservation_object');
        $reservationObjectField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object']);
        $reservationObjectField->setDescription($GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_reservation_object']);
        $reservationObjectField->setFormField(true);
        $reservationObjectField->setEditable(true);
        $reservationObjectField->setOptions($objects);
        $reservationObjectField->setMandatory(true);
        $reservationObjectField->setNotificationField(true);
        $reservationObjectField->setRangeField('desiredCapacity_' . $listType['id']);
        $reservationObjectField->setStyleClass('reservation-object'); //displayReservationObjects
        $reservationObjectField->setWithEmptyOption(false);
        $reservationObjectField->setInitialValue($objects[0]['id']);
        $reservationObjectField->setShowIfEmpty(false);
        $reservationObjectField->setDatabaseField(true);
        //$reservationObjectField->setEmptyOptionLabel($this->reservationSettings->emptyOptionLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_objectsfirst_none']);
        $reservationObjectField->setCondition([$condition]);
        $reservationObjectField->setRemoveWithEmptyCondition(true);
        //$reservationObjectField->setCallOnChangeFunction('C4GCallOnChange(this)');
        $reservationObjectField->setInitialCallOnChange(true);
        $reservationObjectField->setCallOnChange(true);
        $reservationObjectField->setAdditionalID($listType["id"]);
        $this->fieldList[] = $reservationObjectField;

        foreach ($reservationObjects as $reservationObject) {
            $object_condition = [
                new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'reservation_object_' . $listType['id'], $reservationObject->getId()),
                $condition
            ];

            if ($reservationSettings->showDetails) {
                if ($reservationObject->getDescription()) {
                    $descriptionField = new C4GTrixEditorField();
                    $descriptionField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['description']);
                    $descriptionField->setFieldName('description');
                    $descriptionField->setInitialValue($reservationObject->getDescription());
                    $descriptionField->setCondition($object_condition);
                    $descriptionField->setFormField(true);
                    $descriptionField->setShowIfEmpty(false);
                    $descriptionField->setAdditionalID($listType['id'] . '-33' . $reservationObject->getId());
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
                    $imageField->setAdditionalID($listType['id'] . '-33' . $reservationObject->getId());
                    $imageField->setRemoveWithEmptyCondition(true);
                    $imageField->setDatabaseField(false);
                    $imageField->setLightBoxField(true);
                    $imageField->setInitInvisible(true);
                    $this->fieldList[] = $imageField;
                }
            }
            if (!$this->initialValues->getDate() && $listType['directBooking']) {
                $initialBookingDate = time();
            } else {
                $initialBookingDate = false;
            }

            if ($this->initialValues->getDate() || $initialBookingDate) {
                $script = "setTimeset(document.getElementById('c4g_beginDate_".$listType['id'] . '-33' . $reservationObject->getId()."')," . $listType['id'] . "," . $showDateTime . ");";
                $this->getDialogParams()->setOnloadScript($script);
            }

            switch($listType['periodType']) {
                case 'minute':
                    $titleDate = $GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDate'];
                    $titleBeginTime = $GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime'];
                    break;
                case 'hour':
                    $titleDate = $GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDate'];
                    $titleBeginTime = $GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime'];
                    break;
                case 'day':
                    $titleDate = $GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDateMultipleDays'];
                    $titleBeginTime = $GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeMultipleDays'];
                    break;
                case 'week':
                    $titleDate = $GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDateMultipleDays'];
                    $titleBeginTime = $GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeMultipleDays'];
                    break;
            }

            $reservationBeginDateField = new C4GDateField();
            $reservationBeginDateField->setFlipButtonPosition(false);
            $reservationBeginDateField->setMinDate(C4gReservationHandler::getMinDate([$reservationObject]));
            $reservationBeginDateField->setMaxDate(C4gReservationHandler::getMaxDate([$reservationObject]));
            $reservationBeginDateField->setExcludeWeekdays(C4gReservationHandler::getWeekdayExclusionString([$reservationObject]));
            $reservationBeginDateField->setExcludeDates(C4gReservationHandler::getDateExclusionString([$reservationObject], $listType, $reservationSettings->removeBookedDays));
            $reservationBeginDateField->setFieldName('beginDate');
            $reservationBeginDateField->setCustomFormat($GLOBALS['TL_CONFIG']['dateFormat']);
            $reservationBeginDateField->setCustomLanguage($GLOBALS['TL_LANGUAGE']);
            $reservationBeginDateField->setTitle($titleDate);
            $reservationBeginDateField->setEditable(true);
            $reservationBeginDateField->setInitialValue($initialBookingDate ?: $this->initialValues->getDate());
            $reservationBeginDateField->setComparable(false);
            $reservationBeginDateField->setSortColumn(true);
            $reservationBeginDateField->setSortSequence('de_datetime');
            $reservationBeginDateField->setTableColumn(false);
            $reservationBeginDateField->setDatabaseField(true);
            $reservationBeginDateField->setFormField(true);
            $reservationBeginDateField->setColumnWidth(10);
            $reservationBeginDateField->setMandatory(true);
            $reservationBeginDateField->setCondition($object_condition);
            $reservationBeginDateField->setRemoveWithEmptyCondition(true);
            $reservationBeginDateField->setCallOnChange(true);
            $reservationBeginDateField->setCallOnChangeFunction("setTimeset(this," . $listType['id'] . "," . $showDateTime . ");");
            $reservationBeginDateField->setNotificationField(true);
            $reservationBeginDateField->setAdditionalID($listType['id'] . '-33' . $reservationObject->getId());
            $reservationBeginDateField->setStyleClass('begin-date');
            $reservationBeginDateField->setShowInlinePicker($reservationSettings->showInlineDatepicker ? true : false);
            $reservationBeginDateField->setInitInvisible(true);
            $this->fieldList[] = $reservationBeginDateField;

            if (!$this->initialValues->getTime() && $listType['directBooking']) {
                $objDate = new Date(date($GLOBALS['TL_CONFIG']['timeFormat'],time()), Date::getFormatFromRgxp('time'));

                //ToDo check valid initial time ???

                $initialBookingTime = $objDate->tstamp;
            } else {
                $initialBookingTime = false;
            }

            if ($initialBookingDate && $initialBookingTime && $objects) {
                $reservationBeginTimeField = new C4GRadioGroupField();
                $reservationBeginTimeField->setFieldName('beginTime');
                $reservationBeginTimeField->setTitle($titleBeginTime);
                $reservationBeginTimeField->setFormField(true);
                $reservationBeginTimeField->setDatabaseField(true);
                $reservationBeginTimeField->setOptions(C4gReservationHandler::getReservationNowTime($objects[0], $reservationSettings->showEndTime, $reservationSettings->showFreeSeats));
                $reservationBeginTimeField->setCallOnChange(false);
                //$reservationBeginTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $reservationSettings->showDateTime . ')');
                $reservationBeginTimeField->setMandatory(false);
                $reservationBeginTimeField->setInitialValue($initialBookingTime ?: $this->initialValues->getTime());
                $reservationBeginTimeField->setSort(false);
                $reservationBeginTimeField->setCondition($object_condition);
                $reservationBeginTimeField->setAdditionalID($listType['id'] . '-33' . $reservationObject->getId().'-00'.date('w', $initialBookingDate));
                $reservationBeginTimeField->setNotificationField(true);
                $reservationBeginTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                $reservationBeginTimeField->setTurnButton(true);
                $reservationBeginTimeField->setShowButtons(true);
                $reservationBeginTimeField->setRemoveWithEmptyCondition(true);
                $reservationBeginTimeField->setStyleClass('reservation_time_button reservation_time_button_direct reservation_time_button_' . $listType['id']);
                $reservationBeginTimeField->setTimeButtonSpecial(true);
                $reservationBeginTimeField->setInitInvisible(true);
                $this->fieldList[] = $reservationBeginTimeField;
            } else if (($listType['periodType'] === 'hour') || ($listType['periodType'] === 'minute') || ($listType['periodType'] === 'day') || ($listType['periodType'] === 'week')) {

                for ($i=0;$i<=6;$i++) {
                    if ($this->initialValues->getDate()) {
                        $wd = date('N', intval($this->initialValues->getDate()));
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
                    $wdCondition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $listType['id'] . '-33' . $reservationObject->getId().'--'.$i);
                    $wdCondition->setModel(C4gReservationHandler::class);
                    $wdCondition->setFunction('isWeekday');
                    $wdConditionArr = [
                        new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'reservation_object_' . $listType['id'], $reservationObject->getId()),
                        //new C4GBrickCondition(C4GBrickConditionType::BOOLSWITCH, 'beginDate_' . $listType['id'] . '-33' . $reservationObject->getId()),
                        $condition,
                        $wdCondition
                    ];

                    $reservationTimeField = new C4GRadioGroupField();
                    $reservationTimeField->setFieldName('beginTime');
                    $reservationTimeField->setTitle($titleBeginTime);
                    $reservationTimeField->setFormField(true);
                    $reservationTimeField->setOptions(
                        C4gReservationHandler::getReservationTimes(
                            [$reservationObject],
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
                    $reservationTimeField->setCallOnChange(false);
                    //$reservationTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $reservationSettings->showDateTime . ')');
                    $reservationTimeField->setAdditionalID($listType['id'] . '-33' . $reservationObject->getId() . '-00'.$i);
                    $reservationTimeField->setNotificationField(true);
                    $reservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $reservationTimeField->setTurnButton(true);
                    $reservationTimeField->setShowButtons(true);
                    $reservationTimeField->setRemoveWithEmptyCondition(true);
                    $reservationTimeField->setStyleClass('reservation_time_button reservation_time_button_' . $listType['id']);
                    $reservationTimeField->setInitialValue($this->initialValues->getTime());
                    $reservationTimeField->setTimeButtonSpecial(true);
                    $reservationTimeField->setInitInvisible(true);
                    $this->fieldList[] = $reservationTimeField;
                }
            }
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