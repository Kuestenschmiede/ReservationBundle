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

class C4gReservationFormEventHandler extends C4gReservationFormHandler
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
                'name' => $reservationObject->getNumber() ? '[' . $reservationObject->getNumber() . ']&nbsp;' . $reservationObject->getCaption() : $reservationObject->getCaption(),
                'min' => $reservationObject->getDesiredCapacity()[0] ?: 1,
                'max' => $reservationObject->getDesiredCapacity()[1] ?: 0 //ToDo check
            );
        }

        //save event id as reservation object
        $reservationObjectDBField = new C4GSelectField();
        $reservationObjectDBField->setFieldName('reservation_object');
        $reservationObjectDBField->setDatabaseField(true);
        $reservationObjectDBField->setFormField(false);
        $reservationObjectDBField->setOptions($objects);
        $reservationObjectDBField->setNotificationField(true);
        $this->fieldList[] = $reservationObjectDBField;

        //save beginDate
        $reservationBeginDateDBField = new C4GDateField();
        $reservationBeginDateDBField->setFieldName('beginDate');
        $reservationBeginDateDBField->setInitialValue(0);
        $reservationBeginDateDBField->setDatabaseField(true);
        $reservationBeginDateDBField->setFormField(false);
        $reservationBeginDateDBField->setMax(999999999999);
        $reservationBeginDateDBField->setNotificationField(true);
        $this->fieldList[] = $reservationBeginDateDBField;

        //save beginTime
        $reservationBeginTimeDBField = new C4GTimeField();
        $reservationBeginTimeDBField->setFieldName('beginTime');
        $reservationBeginTimeDBField->setInitialValue(0);
        $reservationBeginTimeDBField->setDatabaseField(true);
        $reservationBeginTimeDBField->setFormField(false);
        $reservationBeginTimeDBField->setMax(999999999999);
        $reservationBeginTimeDBField->setNotificationField(true);
        $this->fieldList[] = $reservationBeginTimeDBField;

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

        //$dateCondition = new C4GBrickCondition(C4GBrickConditionType::BOOLSWITCH, 'beginDate_'.$listType['id']);

        $reservationObjectField = new C4GSelectField();
        $reservationObjectField->setChosen(false);
        $reservationObjectField->setFieldName('reservation_object_event');
        $reservationObjectField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object_event']);
        $reservationObjectField->setDescription('');
        $reservationObjectField->setFormField(true);
        $reservationObjectField->setEditable(false);
        $reservationObjectField->setOptions($objects);
        $reservationObjectField->setMandatory(true);
        $reservationObjectField->setNotificationField(true);
        $reservationObjectField->setRangeField('desiredCapacity_' . $listType['id']);
        $reservationObjectField->setStyleClass('reservation-event-object displayReservationObjects');
        $reservationObjectField->setWithEmptyOption(false);
        $reservationObjectField->setInitialValue(-1);
        $reservationObjectField->setShowIfEmpty(true);
        $reservationObjectField->setDatabaseField(false);
        $reservationObjectField->setEmptyOptionLabel($reservationSettings->emptyOptionLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object_none']);
        $reservationObjectField->setCondition([$condition]);
        $reservationObjectField->setRemoveWithEmptyCondition(true);
        $reservationObjectField->setCallOnChange(true);
        $reservationObjectField->setCallOnChangeFunction("checkEventFields(this)");
        $reservationObjectField->setAdditionalID($listType["id"]);
        $this->fieldList[] = $reservationObjectField;

        foreach ($reservationObjects as $reservationObject) {
            $type_condition = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'reservation_type', $listType['id']);
            $obj_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'reservation_object_event_' . $listType['id']. '-22' . $reservationObject->getId());
            $obj_condition->setModel(C4gReservationHandler::class);
            $obj_condition->setFunction('isEventObject');
            $objConditionArr = $type_condition;//[$obj_condition,$val_condition]; //ToDo check

            $reservationBeginDateField = new C4gDateField();
            $reservationBeginDateField->setFlipButtonPosition(true);
            $reservationBeginDateField->setFieldName('beginDateEvent');
            $reservationBeginDateField->setCustomFormat($GLOBALS['TL_CONFIG']['dateFormat']);
            $reservationBeginDateField->setCustomLanguage($GLOBALS['TL_LANGUAGE']);
            $reservationBeginDateField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDateEvent']);
            $reservationBeginDateField->setEditable(false);
            $reservationBeginDateField->setComparable(false);
            $reservationBeginDateField->setWithoutValidation(true);
            $reservationBeginDateField->setDatabaseField(false);
            $reservationBeginDateField->setSortColumn(true);
            $reservationBeginDateField->setSortSequence('de_datetime');
            $reservationBeginDateField->setTableColumn(false);
            $reservationBeginDateField->setFormField(true);
            $reservationBeginDateField->setColumnWidth(10);
            $reservationBeginDateField->setMandatory(false);
            $reservationBeginDateField->setCondition($objConditionArr);
            $reservationBeginDateField->setRemoveWithEmptyCondition(true);
            $reservationBeginDateField->setInitialValue($this->initialValues->getDate() ? $this->initialValues->getDate() : $reservationObject->getBeginDate());
            $reservationBeginDateField->setNotificationField(true);
            $reservationBeginDateField->setAdditionalID($listType['id'] . '-22' . $reservationObject->getId());
            $reservationBeginDateField->setStyleClass('begindate-event');
            $this->fieldList[] = $reservationBeginDateField;

            $reservationEndDateField = new C4GDateField();
            $reservationEndDateField->setFlipButtonPosition(true);
            $reservationEndDateField->setFieldName('endDateEvent');
            $reservationEndDateField->setCustomFormat($GLOBALS['TL_CONFIG']['dateFormat']);
            $reservationEndDateField->setCustomLanguage($GLOBALS['TL_LANGUAGE']);
            $reservationEndDateField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['endDateEvent']);
            $reservationEndDateField->setEditable(false);
            $reservationEndDateField->setComparable(false);
            $reservationEndDateField->setWithoutValidation(true);
            $reservationEndDateField->setSortColumn(true);
            $reservationEndDateField->setSortSequence('de_datetime');
            $reservationEndDateField->setDatabaseField(false);
            $reservationEndDateField->setTableColumn(false);
            $reservationEndDateField->setFormField(true);
            $reservationEndDateField->setColumnWidth(10);
            $reservationEndDateField->setMandatory(false);
            $reservationEndDateField->setCondition($objConditionArr);
            $reservationEndDateField->setRemoveWithEmptyCondition(true);
            $reservationEndDateField->setInitialValue($this->initialValues->getDate() ? $this->initialValues->getDate() : $reservationObject->getEndDate()); //ToDo mehrtägige Termnine
            $reservationEndDateField->setNotificationField(true);
            $reservationEndDateField->setAdditionalID($listType['id'] . '-22' . $reservationObject->getId());
            $reservationEndDateField->setShowIfEmpty(false);
            $reservationEndDateField->setStyleClass('enddate-event');
            $this->fieldList[] = $reservationEndDateField;

            //ToDo find better solution for empty beginTime
            if ($reservationObject->getBeginTime() && date('H', $reservationObject->getBeginTime()) != '00') {
                $reservationBeginTimeField = new C4GRadioGroupField();
                $reservationBeginTimeField->setFieldName('beginTimeEvent');
                $reservationBeginTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeEvent']);
                $reservationBeginTimeField->setFormField(true);
                $reservationBeginTimeField->setOptions(C4gReservationHandler::getReservationEventTime($reservationObject, $reservationSettings->showEndTime, $reservationSettings->showFreeSeats));
                $reservationBeginTimeField->setMandatory(false);
                $reservationBeginTimeField->setInitialValue($reservationObject->getBeginTime());
                $reservationBeginTimeField->setDatabaseField(false);
                $reservationBeginTimeField->setSort(false);
                $reservationBeginTimeField->setCondition($objConditionArr);
                $reservationBeginTimeField->setAdditionalID($listType['id'] . '-22' . $reservationObject->getId());
                $reservationBeginTimeField->setNotificationField(true);
                $reservationBeginTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                $reservationBeginTimeField->setTurnButton(true);
                $reservationBeginTimeField->setShowButtons(true);
                $reservationBeginTimeField->setRemoveWithEmptyCondition(true);
                $reservationBeginTimeField->setStyleClass('reservation_time_event_button reservation_time_event_button_' . $listType['id'] . '-22' . $reservationObject->getId() . C4gReservationHandler::getButtonStateClass($reservationObject));
                $this->fieldList[] = $reservationBeginTimeField;
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
                    } else {
                        $reservationLocationField = new C4GTextField();
                        $reservationLocationField->setInitialValue($locationName);
                        $reservationLocationField->setSimpleTextWithoutEditing(true);
                    }

                    $reservationLocationField->setFieldName('location');
                    $reservationLocationField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['eventlocation']);
                    $reservationLocationField->setFormField(true);
                    $reservationLocationField->setEditable(false);
                    $reservationLocationField->setDatabaseField(false);
                    $reservationLocationField->setCondition($objConditionArr);
                    $reservationLocationField->setMandatory(false);
                    $reservationLocationField->setShowIfEmpty(false);
                    $reservationLocationField->setAdditionalID($listType['id'] . '-22' . $reservationObject->getId());
                    $reservationLocationField->setRemoveWithEmptyCondition(true);
                    $reservationLocationField->setNotificationField(true);
                    $reservationLocationField->setWithoutValidation(true);
                    $reservationLocationField->setStyleClass('eventdata eventdata_' . $listType['id'] . '-22' . $reservationObject->getId() . ' event-location');
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
                    $speakerLinks->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['speaker']);
                    $speakerLinks->setCondition($objConditionArr);
                    $speakerLinks->setShowIfEmpty(false);
                    $speakerLinks->setAdditionalID($listType['id'] . '-22' . $reservationObject->getId());
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
                $topicField->setCondition($objConditionArr);
                $topicField->setInitialValue($topicStr);
                $topicField->setMandatory(false);
                $topicField->setShowIfEmpty(false);
                $topicField->setAdditionalID($listType['id'] . '-22' . $reservationObject->getId());
                $topicField->setRemoveWithEmptyCondition(true);
                $topicField->setNotificationField(true);
                $topicField->setSimpleTextWithoutEditing(true);
                $topicField->setStyleClass('eventdata eventdata_' . $listType['id'] . '-22' . $reservationObject->getId() . ' event-topic');
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
                $audienceField->setCondition($objConditionArr);
                $audienceField->setInitialValue($audienceStr);
                $audienceField->setMandatory(false);
                $audienceField->setShowIfEmpty(false);
                $audienceField->setAdditionalID($listType['id'] . '-22' . $reservationObject->getId());
                $audienceField->setRemoveWithEmptyCondition(true);
                $audienceField->setNotificationField(true);
                $audienceField->setSimpleTextWithoutEditing(true);
                $audienceField->setStyleClass('eventdata eventdata_' . $listType['id'] . '-22' . $reservationObject->getId() . ' event-audience');
                $this->fieldList[] = $audienceField;
            }

            if ($reservationSettings->showDetails) {
                if ($reservationObject->getDescription()) {
                    $descriptionField = new C4GTrixEditorField();
                    $descriptionField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['description']);
                    $descriptionField->setFieldName('description');
                    $descriptionField->setInitialValue($reservationObject->getDescription());
                    $descriptionField->setCondition($objConditionArr);
                    $descriptionField->setFormField(true);
                    $descriptionField->setShowIfEmpty(false);
                    $descriptionField->setAdditionalID($listType['id'] . '-22' . $reservationObject->getId());
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
                    $imageField->setCondition($objConditionArr);
                    $imageField->setFormField(true);
                    $imageField->setShowIfEmpty(false);
                    $imageField->setAdditionalID($listType['id'] . '-22' . $reservationObject->getId());
                    $imageField->setRemoveWithEmptyCondition(true);
                    $imageField->setDatabaseField(false);
                    $imageField->setLightBoxField(true);
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