<?php

namespace con4gis\ReservationBundle\Classes\Utils;

use con4gis\CoreBundle\Classes\C4GUtils;
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
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GUrlField;
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
        $condition = $this->condition;
        $showDateTime = $reservationSettings->showDateTime ? "1" : "0";

        $initialBookingDate = '';
        $initialBookingTime = '';
        $typeOfObject = 'standard';
        if (($listType['periodType'] === 'minute') || ($listType['periodType'] === 'hour') || ($listType['periodType'] === 'day') || ($listType['periodType'] === 'overnight') || ($listType['periodType'] === 'week')) {

            if (!$this->initialValues->getDate() && $listType['directBooking']) {
                $objDate = new Date(date($GLOBALS['TL_CONFIG']['dateFormat'],time()), Date::getFormatFromRgxp('date'));
                $initialBookingDate = $objDate->tstamp;
            } else {
                $initialBookingDate = false;
            }

            $objects = [];
            foreach ($reservationObjects as $reservationObject) {
                $typeOfObject = $reservationObject->getTypeOfObject();
                $object = [
                    'id' => $reservationObject->getId(),
                    'name' => $reservationObject->getCaption(),
                    'min' => $reservationObject->getDesiredCapacity()[0] ?: 1,
                    'max' => $reservationObject->getDesiredCapacity()[1] ?: 0,
                    'almostFullyBookedAt' => $reservationObject->getAlmostFullyBookedAt(),
                    'openingHours' => $reservationObject->getOpeningHours()
                ];
                // setting begin Date and Time initially
                if ($typeOfObject == 'fixed_date') {
                    $timestamp = $reservationObject->getDateTimeBegin();
                    $object['typeOfObject'] = $reservationObject->getTypeOfObject();
                    $beginDate = C4gReservationDateChecker::getBeginOfDate($timestamp);
                    $summerDiff = C4gReservationDateChecker::getCESDiffToLocale($timestamp);

                    $beginTime = $timestamp - $beginDate;
                    if ($summerDiff) {
                        $beginTime -= $summerDiff;
                    }

                    $reservationObject->setBeginDate($beginDate);
                    $reservationObject->setBeginTime($beginTime);
                    $reservationObject->setDuration($reservationObject->getTypeOfObjectDuration());
                    $reservationObject->setTimeinterval($reservationObject->getTypeOfObjectDuration());

                    $periodType = $listType['periodType'];
                    $duration = $reservationObject->getTypeOfObjectDuration();

                    switch ($periodType) {
                        case 'minute':
                            $periodType = 60 * $duration;
                            break;
                        case 'hour':
                            $periodType = 3600 * $duration;
                            break;
                        case 'day':
                        case 'overnight':
                        case 'week':
                            $periodType = 86400 * $duration;
                            break;
                    }

                    $EndTime = $beginTime + $periodType;
                    $EndDate = $beginDate + $EndTime;

                    $reservationObject->setEndTime($EndTime);
                    $reservationObject->setEndDate($EndDate);

                    $initialBookingDate = $beginDate;
                    $initialBookingTime = $beginTime;

                }

                $objects[] = $object;
            }
//            $script = "setTimeset(document.getElementById('c4g_beginDate_".$listType['id']."').value," . $listType['id'] . "," . $showDateTime . ",0);";
//            $this->getDialogParams()->setOnloadScript($script);

            //changes title
            $titleDateHour = '';
            $titleBeginTimeHour = '';
            if ($typeOfObject == 'fixed_date') {
                $titleDateHour = $GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDateEvent'];
                $titleBeginTimeHour = $GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeEvent'];
            } else {
                $titleDateHour = $GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDate'];
                $titleBeginTimeHour = $GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime'];
            }

            switch($listType['periodType']) {
                case 'minute':
                case 'hour':
                    $titleDate = $titleDateHour;
                    $titleBeginTime = $titleBeginTimeHour;
                    break;
                case 'day':
                case 'overnight':
                case 'week':
                    $titleDate = $GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDateMultipleDays'];
                    $titleBeginTime = $GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeMultipleDays'];
                    break;
            }

            $reservationBeginDateField = new C4GDateField();
            $reservationBeginDateField->setMinDate(C4gReservationHandler::getBookableMinDate($reservationObjects, $listType));
            $reservationBeginDateField->setMaxDate(C4gReservationHandler::getMaxDate($reservationObjects));
            $reservationBeginDateField->setExcludeWeekdays(C4gReservationHandler::getWeekdayExclusionString($reservationObjects));

            $periodType = $listType['periodType'];
            $bookedDays = "";
            if ($periodType == 'day' || $periodType  == 'overnight' || $periodType == 'week') {
                $bookedDays = C4gReservationHandler::getBookedDays($listType, $reservationObject);
                if ($bookedDays) {
                    $bookedDays = $bookedDays['dates'];
                } 
                $reservationBeginDateField->setExcludeDates($bookedDays);
            } else {
                $commaDates = C4gReservationHandler::getDateExclusionString($reservationObjects, $listType, $reservationSettings->removeBookedDays);
                if ($commaDates) {
                    $commaDates = isset($commaDates['dates']) ? $commaDates['dates'] : "";
                }
                $reservationBeginDateField->setExcludeDates($commaDates);
            }

            $reservationBeginDateField->setFieldName('beginDate');
            $reservationBeginDateField->setCustomFormat($GLOBALS['TL_CONFIG']['dateFormat']);
            $reservationBeginDateField->setCustomLanguage($GLOBALS['TL_LANGUAGE']);
            $reservationBeginDateField->setTitle($titleDate);
            $reservationBeginDateField->setStyleClass('begin-date');
            $reservationBeginDateField->setEditable($typeOfObject !== 'fixed_date');

            if ($typeOfObject == 'fixed_date') {
                $reservationBeginDateField->setInitialValue($initialBookingDate);
                $reservationBeginDateField->setShowInlinePicker($reservationSettings->showInlineDatepicker && !$typeOfObject == 'fixed_date');
                $reservationBeginDateField->setMandatory(false);
                $reservationBeginDateField->setTableColumn(false);
            } else {
                $reservationBeginDateField->setInitialValue($initialBookingDate ? $this->initialValues->getDate() : C4gReservationHandler::getBookableMinDate($reservationObjects, $listType));
                $reservationBeginDateField->setShowInlinePicker($reservationSettings->showInlineDatepicker ? true : false);
                $reservationBeginDateField->setMandatory(true);
                $reservationBeginDateField->setTableColumn(true);
            }

            $reservationBeginDateField->setComparable(false);
            $reservationBeginDateField->setSortColumn(true);
            $reservationBeginDateField->setSortSequence('de_datetime');
            $reservationBeginDateField->setFormField(true);
            $reservationBeginDateField->setColumnWidth(10);
            $reservationBeginDateField->setCondition(array($condition));
            $reservationBeginDateField->setCallOnChange(true);
            $reservationBeginDateField->setRemoveWithEmptyCondition(true);
            $reservationBeginDateField->setCallOnChangeFunction("setTimeset(this.value," . $listType['id'] . "," . $showDateTime . ",0,true);");
            $reservationBeginDateField->setNotificationField(true);
            $reservationBeginDateField->setAdditionalID($listType['id']);

            $this->fieldList[] = $reservationBeginDateField;
        }

        if (!$this->initialValues->getTime() && $listType['directBooking']) {
            $objDate = new Date(date($GLOBALS['TL_CONFIG']['timeFormat'],time()), Date::getFormatFromRgxp('time'));
            $initialBookingTime = $objDate->tstamp;
        } else if ($this->initialValues->getTime()) {
            $initialBookingTime = $this->initialValues->getTime();
        } else {
            $initialBookingTime = false;
        }

        if ($initialBookingTime) {
            $options = C4gReservationHandler::getReservationNowTime($objects[0], $reservationSettings->showEndTime, $reservationSettings->showFreeSeats);
            $classes = 'reservation_time_button reservation_time_button_direct reservation_time_button_' . $listType['id'];
        } else {
            /* public static function getReservationTimes($objectList, $typeId, $weekday = -1, $date = null, $actDuration=0, $actCapacity=0, $withEndTimes=false, $showFreeSeats=false, $checkToday=false, $langCookie = '', $showArrivalAndDeparture=false) */
            $options = C4gReservationHandler::getReservationTimes(
                        $reservationObjects,
                        $listType['id'],
                        0,
                        -1,
                        0,
                        0,
                        $reservationSettings->showEndTime,
                        $reservationSettings->showFreeSeats

        );
            $classes = 'reservation_time_button reservation_time_button_' . $listType['id']  ;
            //$classes = 'reservation_time_button reservation_time_button_' . $listType['id'] . C4gReservationHandler::getButtonStateClass($reservationObject,$listType['objectType']) ;
        }

        $reservationBeginTimeField = new C4GRadioGroupField();
        $reservationBeginTimeField->setFieldName('beginTime');
        $reservationBeginTimeField->setTitle($titleBeginTime);
        $reservationBeginTimeField->setDescription('');
        $reservationBeginTimeField->setFormField(true);
        $reservationBeginTimeField->setDatabaseField(true);
        $reservationBeginTimeField->setOptions($initialBookingTime || $options ? $options : []);
        $reservationBeginTimeField->setCallOnChange(true);
        $reservationBeginTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $reservationSettings->showDateTime . ')');
        $reservationBeginTimeField->setMandatory(false);
        if ($typeOfObject == 'fixed_date'){
            $reservationBeginTimeField->setInitialValue($reservationObject->getDateTimeBegin());
        } else {
            $reservationBeginTimeField->setInitialValue($initialBookingTime);
        }
        $reservationBeginTimeField->setSort(false);
        $reservationBeginTimeField->setCondition(array($condition));
        $reservationBeginTimeField->setAdditionalID($listType['id']);
        $reservationBeginTimeField->setNotificationField(true);
        $reservationBeginTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
        $reservationBeginTimeField->setTurnButton(true);
        $reservationBeginTimeField->setShowButtons(true);
        $reservationBeginTimeField->setRemoveWithEmptyCondition(true);
        $reservationBeginTimeField->setStyleClass($classes);
        $reservationBeginTimeField->setTimeButtonSpecial(true);
        $reservationBeginTimeField->setWithoutScripts(true);
        $reservationBeginTimeField->setShowIfEmpty(true);
        $this->fieldList[] = $reservationBeginTimeField;

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
        $reservationObjectField->setEmptyOptionLabel($reservationSettings->emptyOptionLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object_none']);
        $reservationObjectField->setCondition([$condition]);
        $reservationObjectField->setRemoveWithEmptyCondition(true);
        $reservationObjectField->setCallOnChange(true);
        $reservationObjectField->setAdditionalID($listType["id"]);
        $reservationObjectField->setHidden($reservationSettings->objectHide);
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

                $locationId = $reservationObject->getLocation();
                if ($locationId) {
                    $location = C4gReservationLocationModel::findByPk($locationId);
                    if ($location) {
                        $href = '';
                        if ($reservationSettings->location_redirect_site) {
                            $jumpTo = \PageModel::findByPk($reservationSettings->location_redirect_site);
                            if ($jumpTo) {
                                $locationAlias = $location->alias ?: $locationId;
                                $href = C4GUtils::replaceInsertTags("{{env::url}}").'/'.$jumpTo->getFrontendUrl().'?location='.$locationAlias;
                            }
                        }

                        $locationName = $location->name;
                        $street = $location->contact_street;
                        $postal = $location->contact_postal;
                        $city = $location->contact_city;
                        if ($street && $postal && $city) {
                            $locationName .= " (" . $street . ", " . $postal . " " . $city . ")";
                        }

                        if ($href) {
                            $reservationLocationField = new C4GUrlField();
                            $reservationLocationField->setUrl($href);
                            $reservationLocationField->setInitialValue($locationName);
                            $objectLocationTitle = $GLOBALS['TL_LANG']['fe_c4g_reservation']['objectlocationUrl'];
                        } else {
                            $reservationLocationField = new C4GTextField();
                            $reservationLocationField->setInitialValue($locationName);
                            $reservationLocationField->setSimpleTextWithoutEditing(true);
                            $objectLocationTitle = $GLOBALS['TL_LANG']['fe_c4g_reservation']['objectlocation'];
                        }

                        $reservationLocationField->setFieldName('location');
                        $reservationLocationField->setTitle($objectLocationTitle);
                        $reservationLocationField->setFormField(true);
                        $reservationLocationField->setEditable(false);
                        $reservationLocationField->setDatabaseField(false);
                        $reservationLocationField->setCondition($object_condition);
                        $reservationLocationField->setMandatory(false);
                        $reservationLocationField->setShowIfEmpty(false);
                        $reservationLocationField->setAdditionalID($listType['id'] . '-' . $reservationObject->getId());
                        $reservationLocationField->setRemoveWithEmptyCondition(true);
                        $reservationLocationField->setNotificationField(true);
                        $reservationLocationField->setWithoutValidation(true);
                        //$reservationLocationField->setStyleClass('eventdata eventdata_' . $listType['id'] . '-22' . $reservationObject->getId() . ' event-location');
                        $this->fieldList[] = $reservationLocationField;
                    }
                }

                $speakerIds = $reservationObject->getSpeaker();
                $speakerStr = '';
                $speakerLinkArr = [];
                if ($speakerIds && count($speakerIds) > 0) {
                    $speakerNr = 0;
                    $speakerList = [];
                    foreach ($speakerIds as $speakerId) {
                        $speakerNr++;
                        $speaker = C4gReservationEventSpeakerModel::findByPk($speakerId);
                        if ($speaker) {
                            $speakerName = $speaker->title ? $speaker->title . ' ' . $speaker->firstname . ' ' . $speaker->lastname : $speaker->firstname . ' ' . $speaker->lastname;

                            if ($reservationSettings->speaker_redirect_site) {
                                $jumpTo = \PageModel::findByPk($reservationSettings->speaker_redirect_site);
                                if ($jumpTo) {
                                    $speakerAlias = $speaker->alias ?: $speakerId;
                                    $href = Controller::replaceInsertTags("{{env::url}}").'/'.$jumpTo->getFrontendUrl().'?speaker='.$speakerAlias;
                                }
                            }

                            $speakerLinkArr[] = ['linkHref'=>$href, 'linkTitle'=>$speakerName, 'linkNewTag'=>0];
                        }
                    }

                    if ($speakerLinkArr && (count($speakerLinkArr) > 0)) {
                        $speakerLinks = new C4GMultiLinkField();
                        $speakerLinks->setWrapper(true);
                        $speakerLinks->setFieldName('speaker');
                        $speakerLinks->setInitialValue(serialize($speakerLinkArr));
                        $speakerLinks->setFormField(true);
                        $speakerLinks->setDatabaseField(false);
                        $speakerLinks->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['objectspeaker']);
                        $speakerLinks->setCondition($object_condition);
                        $speakerLinks->setShowIfEmpty(false);
                        $speakerLinks->setAdditionalID($listType['id'] . '-' . $reservationObject->getId());
                        $speakerLinks->setRemoveWithEmptyCondition(true);
                        $speakerLinks->setNotificationField(true);
                        $this->fieldList[] = $speakerLinks;
                    }

                }

                $topicIds = $reservationObject->getTopic();
                $topicStr = '';
                if ($topicIds && count($topicIds) > 0) {
                    foreach ($topicIds as $topicId) {
                        $topic = C4gReservationEventTopicModel::findByPk($topicId);
                        if ($topic) {
                            $topicName = $topic->topic;
                            $topicStr = $topicStr ? $topicStr . ' ' . $topicName : $topicName;
                        }
                    }

                    $topicField = new C4GTextField();
                    $topicField->setFieldName('topic');
                    $topicField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['topic']);
                    $topicField->setFormField(true);
                    $topicField->setEditable(false);
                    $topicField->setDatabaseField(false);
                    $topicField->setCondition($object_condition);
                    $topicField->setInitialValue($topicStr);
                    $topicField->setMandatory(false);
                    $topicField->setShowIfEmpty(false);
                    $topicField->setAdditionalID($listType['id'] . '-' . $reservationObject->getId());
                    $topicField->setRemoveWithEmptyCondition(true);
                    $topicField->setNotificationField(true);
                    $topicField->setSimpleTextWithoutEditing(true);
                    //$topicField->setStyleClass('eventdata eventdata_' . $listType['id'] . '-22' . $reservationObject->getId() . ' event-topic');
                    $this->fieldList[] = $topicField;

                }

                $audienceIds = $reservationObject->getAudience();
                $audienceStr = '';
                if ($audienceIds && count($audienceIds) > 0) {
                    foreach ($audienceIds as $audienceId) {
                        $audience = C4gReservationEventAudienceModel::findByPk($audienceId);
                        if ($audience) {
                            $audienceName = $audience->targetAudience;
                            $audienceStr = $audienceStr ? $audienceStr . ', ' . $audienceName : $audienceName;
                        }
                    }

                    $audienceField = new C4GTextField();
                    $audienceField->setFieldName('audience');
                    $audienceField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['targetAudience']);
                    $audienceField->setFormField(true);
                    $audienceField->setEditable(false);
                    $audienceField->setDatabaseField(false);
                    $audienceField->setCondition($object_condition);
                    $audienceField->setInitialValue($audienceStr);
                    $audienceField->setMandatory(false);
                    $audienceField->setShowIfEmpty(false);
                    $audienceField->setAdditionalID($listType['id'] . '-' . $reservationObject->getId());
                    $audienceField->setRemoveWithEmptyCondition(true);
                    $audienceField->setNotificationField(true);
                    $audienceField->setSimpleTextWithoutEditing(true);
                    //$audienceField->setStyleClass('eventdata eventdata_' . $listType['id'] . '-22' . $reservationObject->getId() . ' event-audience');
                    $this->fieldList[] = $audienceField;
                }
            }
        }

        $includedParams = $listType['includedParams'];
        $includedParamsArr = [];

        if ($includedParams) {
            foreach ($includedParams as $paramId) {
                if ($paramId) {
                    $includedParam = C4gReservationParamsModel::feParamsCaptions($paramId, $reservationSettings);

                    if ($includedParam !== null) {
                        $includedParamsArr[] = $includedParam;
                    }
                }
            }
        }

        if (count($includedParamsArr) > 0) {
            $includedParams = new C4GMultiCheckboxField();
            $includedParams->setModernStyle(false);
            $includedParams->setAllChecked(true);
            $includedParams->setFieldName('included_params');
            $includedParams->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['included_params']);
            $includedParams->setFormField(true);
            $includedParams->setEditable(false);
            $includedParams->setOptions($includedParamsArr);
            $includedParams->setMandatory(false);
            $includedParams->setCondition(array($condition));
            $includedParams->setRemoveWithEmptyCondition(true);
            $includedParams->setAdditionalID($listType['id'] . '-00' . $reservationObject->getId());
            $includedParams->setStyleClass('included-params');
            $includedParams->setNotificationField(true);
            $includedParams->setSort(false);
            $this->fieldList[] = $includedParams;
        }

        $params = $listType['additionalParams'];
        $additionalParamsArr = [];
        if ($params) {
            foreach ($params as $paramId) {
                if ($paramId) {
                    $additionalParam = C4gReservationParamsModel::feParamsCaptions($paramId, $reservationSettings);

                    if ($additionalParam !== null) {
                        $additionalParamsArr[] = $additionalParam;
                    }
                }
            }
        }

        if (count($additionalParamsArr) > 0) {
            if ($listType['additionalParamsFieldType'] == 'radio') {
                $additionalParams = new C4GRadioGroupField();
                $additionalParams->setInitialValue($additionalParamsArr[0]['id']);
                $additionalParams->setSaveAsArray(true);
            } else {
                $additionalParams = new C4GMultiCheckboxField();
                $additionalParams->setModernStyle(false);
            }
            $additionalParams->setFieldName('additional_params');
            $additionalParams->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['additional_params']);
            $additionalParams->setFormField(true);
            $additionalParams->setEditable(true);
            $additionalParams->setOptions($additionalParamsArr);
            $additionalParams->setMandatory($listType['additionalParamsMandatory']);
            $additionalParams->setCondition(array($condition));
            $additionalParams->setRemoveWithEmptyCondition(true);
            $additionalParams->setAdditionalID($listType['id'] . '-00' . $reservationObject->getId());
            $additionalParams->setStyleClass('additional-params');
            $additionalParams->setNotificationField(true);
            $additionalParams->setSort(false);
            $this->fieldList[] = $additionalParams;
        }

        return $this->fieldList;
    }
}