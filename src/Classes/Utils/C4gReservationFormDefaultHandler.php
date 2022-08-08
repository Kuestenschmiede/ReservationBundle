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

        if (($listType['periodType'] === 'minute') || ($listType['periodType'] === 'hour') || ($listType['periodType'] === 'day') || ($listType['periodType'] === 'week')) {
            if (!$this->initialValues->getDate() && $listType['directBooking']) {
                $objDate = new Date(date($GLOBALS['TL_CONFIG']['dateFormat'],time()), Date::getFormatFromRgxp('date'));
                $initialBookingDate = $objDate->tstamp;
            } else {
                $initialBookingDate = false;
            }

            $script = "setTimeset(document.getElementById('c4g_beginDate_".$listType['id']."')," . $listType['id'] . "," . $showDateTime . ");";
            $this->getDialogParams()->setOnloadScript($script);

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
            $reservationBeginDateField->setMinDate(C4gReservationHandler::getMinDate($reservationObjects));
            $reservationBeginDateField->setMaxDate(C4gReservationHandler::getMaxDate($reservationObjects));
            $reservationBeginDateField->setExcludeWeekdays(C4gReservationHandler::getWeekdayExclusionString($reservationObjects));
            $reservationBeginDateField->setExcludeDates(C4gReservationHandler::getDateExclusionString($reservationObjects, $listType, $reservationSettings->removeBookedDays));
            $reservationBeginDateField->setFieldName('beginDate');
            $reservationBeginDateField->setCustomFormat($GLOBALS['TL_CONFIG']['dateFormat']);
            $reservationBeginDateField->setCustomLanguage($GLOBALS['TL_LANGUAGE']);
            $reservationBeginDateField->setTitle($titleDate);
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
            $reservationBeginDateField->setRemoveWithEmptyCondition(true);
            $reservationBeginDateField->setCallOnChangeFunction("setTimeset(this," . $listType['id'] . "," . $showDateTime . ");");
            $reservationBeginDateField->setNotificationField(true);
            $reservationBeginDateField->setAdditionalID($listType['id']);
            $reservationBeginDateField->setStyleClass('begin-date');
            $reservationBeginDateField->setShowInlinePicker($reservationSettings->showInlineDatepicker ? true : false);
            $reservationBeginDateField->setInitInvisible(true);
            $this->fieldList[] = $reservationBeginDateField;
        }

        if (!$this->initialValues->getTime() && $listType['directBooking']) {
            $objDate = new Date(date($GLOBALS['TL_CONFIG']['timeFormat'],time()), Date::getFormatFromRgxp('time'));
            $initialBookingTime = $objDate->tstamp;
        } else {
            $initialBookingTime = false;
        }

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

        if ($initialBookingDate && $initialBookingTime && $objects) {
            $reservationBeginTimeField = new C4GRadioGroupField();
            $reservationBeginTimeField->setFieldName('beginTime');
            $reservationBeginTimeField->setTitle($titleBeginTime);
            $reservationBeginTimeField->setFormField(true);
            $reservationBeginTimeField->setDatabaseField(true);
            $reservationBeginTimeField->setOptions(C4gReservationHandler::getReservationNowTime($objects[0], $reservationSettings->showEndTime, $reservationSettings->showFreeSeats));
            $reservationBeginTimeField->setCallOnChange(true);
            $reservationBeginTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $reservationSettings->showDateTime . ')');
            $reservationBeginTimeField->setMandatory(false);
            $reservationBeginTimeField->setInitialValue($initialBookingTime ?: $this->initialValues->getTime());
            $reservationBeginTimeField->setSort(false);
            $reservationBeginTimeField->setCondition(array($condition));
            $reservationBeginTimeField->setAdditionalID($listType['id'].'-00'.date('w', $initialBookingDate));
            $reservationBeginTimeField->setNotificationField(true);
            $reservationBeginTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
            $reservationBeginTimeField->setTurnButton(true);
            $reservationBeginTimeField->setShowButtons(true);
            $reservationBeginTimeField->setRemoveWithEmptyCondition(true);
            $reservationBeginTimeField->setStyleClass('reservation_time_button reservation_time_button_direct reservation_time_button_' . $listType['id']);
            $reservationBeginTimeField->setTimeButtonSpecial(true);
            $reservationBeginTimeField->setWithoutScripts(true);
            $this->fieldList[] = $reservationBeginTimeField;
        } else if (($listType['periodType'] === 'hour') || ($listType['periodType'] === 'minute') || ($listType['periodType'] === 'day') || ($listType['periodType'] === 'week')) {

            for ($i=0;$i<=6;$i++) {
                if ($this->initialValues->getDate()) {
                    $wd = date('N', intval($this->initialValues->getDate()));
                    if ($i != $wd) {
                        continue;
                    }
                }

//                $we = $reservationObject->getWeekdayExclusion();
//                foreach ($we as $key=>$value) {
//                    if ($i == $key) {
//                        if (!$value) {
//                            continue(2);
//                        }
//                    }
//                }
                $wdCondition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $listType['id'].'--'.$i);
                $wdCondition->setModel(C4gReservationHandler::class);
                $wdCondition->setFunction('isWeekday');
                $wdConditionArr = [$wdCondition, $condition];

                $reservationTimeField = new C4GRadioGroupField();
                $reservationTimeField->setFieldName('beginTime');
                $reservationTimeField->setTitle($titleBeginTime);
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
                $reservationTimeField->setInitialValue($initialBookingTime ?: $this->initialValues->getTime());
                $reservationTimeField->setTimeButtonSpecial(true);
                $reservationTimeField->setInitInvisible(true);
                $reservationTimeField->setWithoutScripts(true);
                $this->fieldList[] = $reservationTimeField;
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

        //price for notification
        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('price');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(9999999999999);
        $priceDBField->setNotificationField(true);
        $this->fieldList[] = $priceDBField;

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
                                $href = Controller::replaceInsertTags("{{env::url}}").'/'.$jumpTo->getFrontendUrl().'?location='.$locationAlias;
                            }
                        }

                        $locationName = $location->name;
                        $street = $location->contact_street;
                        $postal = $location->contact_postal;
                        $city = $location->contact_city;
                        if ($street && $postal && $city) {
                            $locationName .= "&nbsp;(" . $street . ",&nbsp;" . $postal . "&nbsp;" . $city . ")";
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
                            $speakerName = $speaker->title ? $speaker->title . '&nbsp;' . $speaker->firstname . '&nbsp;' . $speaker->lastname : $speaker->firstname . '&nbsp;' . $speaker->lastname;

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
                            $topicStr = $topicStr ? $topicStr . ',&nbsp;' . $topicName : $topicName;
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
                            $audienceStr = $audienceStr ? $audienceStr . ',&nbsp;' . $audienceName : $audienceName;
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
                $includedParam = C4gReservationParamsModel::findByPk($paramId);
                if ($includedParam && $includedParam->caption && $includedParam->published && ($includedParam->price && $reservationSettings->showPrices)) {
                    $includedParamsArr[] = ['id' => $paramId, 'name' => $includedParam->caption . "<span class='price'>&nbsp;(+" . number_format($includedParam->price, 2, $GLOBALS['TL_LANG']['fe_c4g_reservation']['decimal_seperator'], $GLOBALS['TL_LANG']['fe_c4g_reservation']['thousands_seperator']) . " ".$GLOBALS['TL_LANG']['fe_c4g_reservation']['currency'].")</span>"];
                } else if ($includedParam && $includedParam->caption && $includedParam->published) {
                    $includedParamsArr[] = ['id' => $paramId, 'name' => $includedParam->caption];
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
                    $additionalParam = C4gReservationParamsModel::findByPk($paramId);

                    if ($additionalParam && $additionalParam->caption && $additionalParam->published && ($additionalParam->price && $reservationSettings->showPrices)) {
                        $additionalParamsArr[] = ['id' => $paramId, 'name' => $additionalParam->caption . "<span class='price'>&nbsp;(+" . number_format($additionalParam->price, 2, $GLOBALS['TL_LANG']['fe_c4g_reservation']['decimal_seperator'], $GLOBALS['TL_LANG']['fe_c4g_reservation']['thousands_seperator']) . " ".$GLOBALS['TL_LANG']['fe_c4g_reservation']['currency'].")</span>"];
                    } else if ($additionalParam && $additionalParam->caption && $additionalParam->published) {
                        $additionalParamsArr[] = ['id' => $paramId, 'name' => $additionalParam->caption];
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