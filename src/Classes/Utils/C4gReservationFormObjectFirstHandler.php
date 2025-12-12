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
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GLinkField;
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
use Contao\System;

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
        $initialIndex = 0;
        $index = 0;

        $initialBookingDate = '';
        $initialBookingTime = '';
        foreach ($reservationObjects as $reservationObject) {
            $typeOfObject = $reservationObject->getTypeOfObject();
//            if ($typeOfObject !== 'fixed_date') {
                $objects[] = array(
                    'id' => $reservationObject->getId(),
                    'name' => str_replace(' ', '&nbsp;&#x200B;', $reservationObject->getCaption()),
                    'min' => $reservationObject->getDesiredCapacity()[0] ?: 1,
                    'max' => $reservationObject->getDesiredCapacity()[1] ?: 0,
                    'currentReservations' => $reservationObject->getCurrentReservations(),
                    'allmostFullyBookedAt' => $reservationObject->getAlmostFullyBookedAt(),
                    'openingHours' => $reservationObject->getOpeningHours()
                );
//            } else {
//                $objects[] = array(
//                    'id' => $reservationObject->getId(),
//                    'name' => $reservationObject->getCaption(),
//                    'min' => $reservationObject->getDesiredCapacity()[0] ?: 1,
//                    'max' => $reservationObject->getDesiredCapacity()[1] ?: 0,
//                    'allmostFullyBookedAt' => $reservationObject->getAlmostFullyBookedAt(),
//                );
//            }

            if ($reservationObject->getPriority()) {
                $initialIndex = $index;
            }

            

       
        $index++;
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
        $reservationObjectField->setInitialValue($objects[$initialIndex]['id']);
        $reservationObjectField->setShowIfEmpty(false);
        $reservationObjectField->setDatabaseField(true);
        //$reservationObjectField->setEmptyOptionLabel($this->reservationSettings->emptyOptionLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_objectsfirst_none']);
        $reservationObjectField->setCondition([$condition]);
        $reservationObjectField->setRemoveWithEmptyCondition(true);
        $reservationObjectField->setCallOnChangeFunction("setTimeset(document.getElementById('c4g_beginDate_".$listType['id']."-33'+document.getElementById('c4g_reservation_object_".$listType['id']."').value).value,".$listType['id'].",".$showDateTime.",document.getElementById('c4g_reservation_object_".$listType['id']."').value);handleBrickConditions();");
        $reservationObjectField->setInitialCallOnChange(true);
        $reservationObjectField->setCallOnChange(true);
        $reservationObjectField->setAdditionalID($listType["id"]);
        $reservationObjectField->setHidden($reservationSettings->objectHide);
        $reservationObjectField->setPrintable($this->module->isWithDefaultPDFContent());
        $this->fieldList[] = $reservationObjectField;

        
        $arrayCounter = 0;
        foreach ($reservationObjects as $reservationObject) {
            $typeOfObject = $reservationObject->getTypeOfObject();
            $object_condition = [
                new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'reservation_object_' . $listType['id'], $reservationObject->getId()),
                $condition
            ]; 

            if ($typeOfObject == 'fixed_date') {
                $showMinMax = $reservationSettings->showMinMaxWithCapacity ? "1" : "0";
                $currentReservations = $reservationObject->getCurrentReservations();
                $severalBookings = $reservationObject->getSeveralBookings();
                $maxParticiPantsPerBooking = $listType['maxParticipantsPerBooking']; 
                $objectMaxCapacity = $objects[$arrayCounter]['max'];  
                $minCapacity = $objects[$arrayCounter]['min'];
                
                $freeObjects = 1;
                if ($objectMaxCapacity) {
                    $freeObjects = $objectMaxCapacity - $currentReservations;
                    if ($maxParticiPantsPerBooking > $freeObjects) {
                        $maxParticiPantsPerBooking = $freeObjects;
                    }
                }
    
                if ((!$severalBookings && !$currentReservations) || $severalBookings) {
                    if ($objectMaxCapacity) {
                        $maxCapacity = $objectMaxCapacity - $currentReservations;
                    } else {
                        $maxCapacity = $maxParticiPantsPerBooking;
                    }
                     if ($maxParticiPantsPerBooking && $maxCapacity > $maxParticiPantsPerBooking) {
                        $maxCapacity = $maxParticiPantsPerBooking;
                    } 
                    
                    if ($minCapacity > $maxCapacity) {
                        $minCapacity = $maxCapacity;
                    }
                   
                } else {
                    $minCapacity = 0;
                    $maxCapacity = 0;
                }
                
                $arrayCounter++;
            
                if ($reservationSettings->withCapacity) { // && !$onlyParticipant
                    $showDateTime = $reservationSettings->showDateTime ? "1" : "0";
                    $reservationDesiredCapacity = new C4GNumberField();
                    $reservationDesiredCapacity->setFieldName('desiredCapacity');
                    $reservationDesiredCapacity->setFormField(true);
                    $reservationDesiredCapacity->setCondition($object_condition);
                    $reservationDesiredCapacity->setInitialValue($minCapacity);
                    $reservationDesiredCapacity->setMandatory(true);
                    $reservationDesiredCapacity->setEditable($maxCapacity > 0);
                    if ($objectMaxCapacity) {
                        if ($maxCapacity > 0) {
                            $reservationDesiredCapacity->setTitle(C4gReservationController::withDesiredCapacityTitle($minCapacity,$maxCapacity,$showMinMax)); 
                        } else {
                            $reservationDesiredCapacity->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['fully_booked']);
                        } 
                        $reservationDesiredCapacity->setMax($maxCapacity);
                        $reservationDesiredCapacity->setMin($minCapacity);
                        
                    } else if ($objectMaxCapacity <= 0) {
                        if ($maxParticiPantsPerBooking > 0) {
                            $reservationDesiredCapacity->setTitle(C4gReservationController::withDesiredCapacityTitle($minCapacity,$maxCapacity,$showMinMax)); 
                            $reservationDesiredCapacity->setMax($maxCapacity);
                            $reservationDesiredCapacity->setMin($minCapacity);  
                        } else {
                            $reservationDesiredCapacity->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['desiredCapacity']); 
                        } 
                    
                    }                                                    
                    $reservationDesiredCapacity->setPattern(C4GBrickRegEx::NUMBERS);
                    $reservationDesiredCapacity->setCallOnChange(true);
                    $reservationDesiredCapacity->setCallOnChangeFunction("setReservationForm(".$listType['id'] . '-33' . $reservationObject->getId(). "," . $showDateTime . ");");
                    $reservationDesiredCapacity->setNotificationField(true);
                    $reservationDesiredCapacity->setAdditionalID($listType['id'] . '-33' . $reservationObject->getId());
                    $reservationDesiredCapacity->setStyleClass('desired-capacity');
                    $reservationDesiredCapacity->setPrintable($this->module->isWithDefaultPDFContent());
            
                    $this->fieldList[] = $reservationDesiredCapacity; 
                }
            }    
          
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
                    $descriptionField->setPrintable($this->module->isWithDefaultPDFContent());
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
                    $imageField->setPrintable($this->module->isWithDefaultPDFContent());
                    $this->fieldList[] = $imageField;
                }

                $locationId = $reservationObject->getLocation();
                if ($locationId) {
                    $location = C4gReservationLocationModel::findByPk($locationId);
                    if ($location) {
                        $href = '';
                        if ($reservationSettings->location_redirect_site) {
                            $jumpTo = \Contao\PageModel::findByPk($reservationSettings->location_redirect_site);
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
                        //$reservationLocationField->setStyleClass('eventdata eventdata_' . $listType['id'] . '-' . $reservationObject->getId() . ' event-location');
                        $reservationLocationField->setPrintable($this->module->isWithDefaultPDFContent());
                        $reservationLocationField->setHidden($reservationSettings->hideLocation);
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
                                $jumpTo = \Contao\PageModel::findByPk($reservationSettings->speaker_redirect_site);
                                if ($jumpTo) {
                                    $speakerAlias = $speaker->alias ?: $speakerId;
                                    $href = C4GUtils::replaceInsertTags("{{env::url}}").'/'.$jumpTo->getFrontendUrl().'?speaker='.$speakerAlias;
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
                        $speakerLinks->setPrintable($this->module->isWithDefaultPDFContent());
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
                            $topicStr = $topicStr ? $topicStr . ', ' . $topicName : $topicName;
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
                    $topicField->setPrintable($this->module->isWithDefaultPDFContent());
                    //$topicField->setStyleClass('eventdata eventdata_' . $listType['id'] . '-' . $reservationObject->getId() . ' event-topic');
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
                    $audienceField->setPrintable($this->module->isWithDefaultPDFContent());
                    //$audienceField->setStyleClass('eventdata eventdata_' . $listType['id'] . '-' . $reservationObject->getId() . ' event-audience');
                    $this->fieldList[] = $audienceField;
                }
            }
            if (!$this->initialValues->getDate() && $listType['directBooking']) {
                $objDate = new Date(date($GLOBALS['TL_CONFIG']['dateFormat'],time()), Date::getFormatFromRgxp('date'));
                $initialBookingDate = $objDate->tstamp;
            } else {
                $initialBookingDate = false;
            }

            if ($this->initialValues->getDate() || $initialBookingDate/* || $typeOfObject == 'fixed_date'*/) {
                $script = "setTimeset(document.getElementById('c4g_beginDate_".$listType['id']."-33'+document.getElementById('c4g_reservation_object_".$listType['id']."').value).value,".$listType['id'].",".$showDateTime.",document.getElementById('c4g_reservation_object_".$listType['id']."').value);handleBrickConditions();)";
                $this->getDialogParams()->setOnloadScript($script);
            }
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
            //$reservationBeginDateField->setFlipButtonPosition(false);
            $reservationBeginDateField->setMinDate(C4gReservationHandler::getBookableMinDate([$reservationObject], $listType));
            $reservationBeginDateField->setMaxDate(C4gReservationHandler::getMaxDate([$reservationObject]));
            $reservationBeginDateField->setExcludeWeekdays(C4gReservationHandler::getWeekdayExclusionString([$reservationObject]));

            $periodType = $listType['periodType'];
            $bookedDays = "";
            if ($periodType == 'day' || $periodType  == 'overnight' || $periodType  == 'week') {            
                $bookedDays = C4gReservationHandler::getBookedDays($listType,$reservationObject);
                if ($bookedDays) {
                    $bookedDays = $bookedDays['dates'];
                } 
                $reservationBeginDateField->setExcludeDates($bookedDays);
                $initialValue = $initialBookingDate ? $this->initialValues->getDate() : C4gReservationHandler::getBookableMinDate([$reservationObject], $listType);
            } else {
                $commaDates = C4gReservationHandler::getDateExclusionString($reservationObjects, $listType, $reservationSettings->removeBookedDays);
                if ($commaDates) {
                    $commaDates = isset($commaDates['dates']) ? $commaDates['dates'] : null;
                }
                $reservationBeginDateField->setExcludeDates($commaDates);
            }
            
            $reservationBeginDateField->setFieldName('beginDate');
            $reservationBeginDateField->setCustomFormat($GLOBALS['TL_CONFIG']['dateFormat']);
            $reservationBeginDateField->setCustomLanguage($GLOBALS['TL_LANGUAGE']);
            $reservationBeginDateField->setTitle($titleDate);
            $reservationBeginDateField->setStyleClass('begin-date');
            $reservationBeginDateField->setMandatory(true);
            $reservationBeginDateField->setEditable($typeOfObject !== 'fixed_date');

            if ($typeOfObject == 'fixed_date') {
                $reservationBeginDateField->setInitialValue($reservationObject->getBeginDate());
                $reservationBeginDateField->setShowInlinePicker($reservationSettings->showInlineDatepicker && !$typeOfObject == 'fixed_date' ? true : false);
                $reservationBeginDateField->setEditable(false);
                $reservationBeginDateField->setMandatory(false);
            } else {
                $reservationBeginDateField->setInitialValue($initialBookingDate ? $this->initialValues->getDate() : C4gReservationHandler::getBookableMinDate([$reservationObject], $listType));
                $reservationBeginDateField->setShowInlinePicker($reservationSettings->showInlineDatepicker ? true : false);
                $reservationBeginDateField->setMandatory(true);
                $reservationBeginDateField->setEditable(true);
            }

            $reservationBeginDateField->setComparable(false);
            $reservationBeginDateField->setSortColumn(true);
            $reservationBeginDateField->setSortSequence('de_datetime');
            $reservationBeginDateField->setTableColumn(false);
            $reservationBeginDateField->setDatabaseField(true);
            $reservationBeginDateField->setFormField(true);
            $reservationBeginDateField->setColumnWidth(10);
            $reservationBeginDateField->setCondition($object_condition);
            $reservationBeginDateField->setRemoveWithEmptyCondition(true);
            $reservationBeginDateField->setCallOnChange(true);
            //"var actValue = document.getElementById('c4g_reservation_object_".$listType['id']."').value;setTimeset(document.getElementById('c4g_beginDate_".$listType['id']."-33'+actValue).value,".$listType['id'].",".$showDateTime.",actValue);handleBrickConditions();"
            $reservationBeginDateField->setCallOnChangeFunction("setTimeset(this.value," . $listType['id'] . "," . $showDateTime . ",". $reservationObject->getId().",true);");
            $reservationBeginDateField->setNotificationField(true);
            $reservationBeginDateField->setAdditionalID($listType['id'] . '-33' . $reservationObject->getId());
            $reservationBeginDateField->setInitInvisible(false);
            $reservationBeginDateField->setPrintable($this->module->isWithDefaultPDFContent());

            $this->fieldList[] = $reservationBeginDateField;

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
                
                if ($typeOfObject == 'fixed_date') {
                    $classes = 'reservation_time_button reservation_time_button_' . $listType['id'] . '-33' . $reservationObject->getId() . C4gReservationHandler::getButtonStateClass($reservationObject,$listType['objectType']);
                } else {
                    $classes = 'reservation_time_button reservation_time_button_' . $listType['id'];
                }
                
            }

            $reservationBeginTimeField = new C4GRadioGroupField();
            $reservationBeginTimeField->setFieldName('beginTime');
            $reservationBeginTimeField->setTitle($titleBeginTime);
            $reservationBeginTimeField->setDescription('');
            $reservationBeginTimeField->setFormField(true);
            $reservationBeginTimeField->setDatabaseField(true);
            $reservationBeginTimeField->setOptions($initialBookingTime || $options ? $options : []);
            $reservationBeginTimeField->setCallOnChange(false);
//            $reservationBeginTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $reservationSettings->showDateTime . ')');
            if ($typeOfObject == 'fixed_date'){
                $reservationBeginTimeField->setInitialValue($reservationObject->getDateTimeBegin());
            } else {
                $reservationBeginTimeField->setInitialValue($initialBookingTime);
            }
            $reservationBeginTimeField->setMandatory(false);
//            $reservationBeginTimeField->setInitialValue($initialBookingTime ?: $this->initialValues->getTime());
            $reservationBeginTimeField->setSort(false);
            $reservationBeginTimeField->setCondition($object_condition);
            $reservationBeginTimeField->setAdditionalID($listType['id'] . '-33' . $reservationObject->getId());
            $reservationBeginTimeField->setNotificationField(true);
            $reservationBeginTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
            $reservationBeginTimeField->setTurnButton(true);
            $reservationBeginTimeField->setShowButtons(true);
            $reservationBeginTimeField->setRemoveWithEmptyCondition(true);
            $reservationBeginTimeField->setStyleClass($classes);
            $reservationBeginTimeField->setTimeButtonSpecial(true);
            $reservationBeginTimeField->setInitInvisible(true);
            $reservationBeginTimeField->setWithoutScripts(true);
            $reservationBeginTimeField->setPrintable($this->module->isWithDefaultPDFContent());

            $this->fieldList[] = $reservationBeginTimeField;
        }

        //save endDate
        $reservationEndDateDBField = new C4GDateField();
        $reservationEndDateDBField->setFieldName('endDate');
        $reservationEndDateDBField->setInitialValue(0);
        $reservationEndDateDBField->setDatabaseField(true);
        $reservationEndDateDBField->setFormField(false);
        $reservationEndDateDBField->setMax(9999999999999);
        $reservationEndDateDBField->setNotificationField(true);
        $reservationEndDateDBField->setPrintable($this->module->isWithDefaultPDFContent());
        $this->fieldList[] = $reservationEndDateDBField;

        //save endTime
        $reservationEndTimeDBField = new C4GTimeField();
        $reservationEndTimeDBField->setFieldName('endTime');
        $reservationEndTimeDBField->setInitialValue(0);
        $reservationEndTimeDBField->setDatabaseField(true);
        $reservationEndTimeDBField->setFormField(false);
        $reservationEndTimeDBField->setMax(9999999999999);
        $reservationEndTimeDBField->setNotificationField(true);
        $reservationEndTimeDBField->setPrintable($this->module->isWithDefaultPDFContent());
        $this->fieldList[] = $reservationEndTimeDBField;

//        $captions = StringUtil::deserialize($object['options']);
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
            $includedParams->setMandatory(/*$listType['includedParamsMandatory']*/false);
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

        // Append static tail fields from cached blueprint (privacy text, consent, submit, contact meta)
        foreach ($this->getStaticFieldBlueprint() as $bpField) {
            $this->fieldList[] = clone $bpField;
        }

        return $this->fieldList;
    }

    /**
     * Build and cache the request-invariant tail of the form for the ObjectFirst handler
     * (privacy text, consent checkbox, submit button, and contact info fields).
     */
    protected function getStaticFieldBlueprint(): array
    {
        $settings = $this->module ? $this->module->getReservationSettings() : null;
        $withPdf = $this->module ? $this->module->isWithDefaultPDFContent() : false;

        // Defaults
        $useCache = true;
        $ttl = 43200; // 12h
        $settingsId = '0';
        try {
            if ($settings && property_exists($settings, 'id')) {
                $settingsId = (string) $settings->id;
            }
            if ($settings && property_exists($settings, 'reservation_enable_cache')) {
                $flag = (string) $settings->reservation_enable_cache;
                $useCache = ($flag === '1' || $flag === 1 || $flag === true);
            }
            if ($settings && property_exists($settings, 'reservation_cache_ttl')) {
                $ttlCandidate = (int) $settings->reservation_cache_ttl;
                if ($ttlCandidate > 0) { $ttl = $ttlCandidate; }
                if ($ttlCandidate === 0) { $ttl = 43200; }
            }
        } catch (\Throwable $t) { /* ignore */ }

        $lang = strtolower((string) ($GLOBALS["TL_LANGUAGE"] ?? ''));
        $buttonCaption = '';
        try {
            $btn = $settings ? ($settings->reservationButtonCaption ?? '') : '';
            if ($btn) { $buttonCaption = C4GUtils::replaceInsertTags($btn); }
        } catch (\Throwable $t) { $buttonCaption = ''; }
        $hasPrivacyText = $settings ? !empty($settings->privacy_policy_text) : false;
        $privacySite = (string) ($settings->privacy_policy_site ?? '');
        $printableFlag = $withPdf ? '1' : '0';

        $blueprint = null;
        $cache = null;
        $cacheKey = 'c4g_reservation_blueprint_objectfirst_' . md5(implode('|', [
            $settingsId, $lang, (string) $privacySite, $hasPrivacyText ? '1' : '0',
            (string) $buttonCaption, $printableFlag,
        ]));

        if ($useCache) {
            try {
                $container = System::getContainer();
                $cache = $container && $container->has('cache.app') ? $container->get('cache.app') : null;
                if ($cache) {
                    $item = $cache->getItem($cacheKey);
                    if ($item->isHit()) {
                        $stored = $item->get();
                        if (is_array($stored)) { $blueprint = $stored; }
                    }
                }
            } catch (\Throwable $t) { $cache = null; }
        }

        if ($blueprint === null) {
            $fields = [];

            // Optional privacy policy text
            if ($settings && !empty($settings->privacy_policy_text)) {
                $privacyPolicyText = new C4GTextField();
                $privacyPolicyText->setSimpleTextWithoutEditing(true);
                $privacyPolicyText->setFieldName('privacy_policy_text');
                $privacyPolicyText->setInitialValue(str_replace(' ', '&nbsp;&#x200B;',
                    C4GUtils::replaceInsertTags($settings->privacy_policy_text)));
                $privacyPolicyText->setSize(4);
                $privacyPolicyText->setTableColumn(false);
                $privacyPolicyText->setEditable(false);
                $privacyPolicyText->setDatabaseField(false);
                $privacyPolicyText->setMandatory(false);
                $privacyPolicyText->setNotificationField(false);
                $privacyPolicyText->setStyleClass('privacy-policy-text');
                $privacyPolicyText->setPrintable($withPdf);
                $fields[] = $privacyPolicyText;
            }

            // Consent checkbox with optional link
            if ($settings && $settings->privacy_policy_site) {
                $href = C4GUtils::replaceInsertTags('{{link_url::' . $settings->privacy_policy_site . '}}');
                $desc = '<span class="c4g_field_description_text">' . str_replace(' ', '&nbsp;&#x200B;', $GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed']) . '&nbsp;&#x200B;</span><a href="' . $href . '" target="_blank" rel="noopener">' . str_replace(' ', '&nbsp;&#x200B;', $GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed_link_text']) . '</a>.';
            } else {
                $desc = str_replace(' ', '&nbsp;&#x200B;', $GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed_without_link']);
            }

            $agreedField = new C4GCheckboxField();
            $agreedField->setFieldName('agreed');
            $agreedField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['agreed'].'&nbsp;&#x200B;'.$desc);
            $agreedField->setTableRow(false);
            $agreedField->setColumnWidth(5);
            $agreedField->setSortColumn(false);
            $agreedField->setTableColumn(false);
            $agreedField->setMandatory(true);
            $agreedField->setNotificationField(true);
            $agreedField->setStyleClass('agreed');
            $agreedField->setWithoutDescriptionLineBreak(true);
            $agreedField->setPrintable($withPdf);
            $fields[] = $agreedField;

            // Submit button
            $clickButton = new C4GBrickButton(
                C4GBrickConst::BUTTON_CLICK,
                $buttonCaption ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['button_reservation'],
                $visible = true,
                $enabled = true,
                $action = '',
                $accesskey = '',
                $defaultByEnter = true
            );
            $buttonField = new C4GButtonField($clickButton);
            $buttonField->setOnClickType(C4GBrickConst::ONCLICK_TYPE_SERVER);
            $buttonField->setOnClick('clickReservation');
            $buttonField->setWithoutLabel(true);
            $fields[] = $buttonField;

            // Contact/location fields (static meta)
            $location_name = new C4GTextField();
            $location_name->setFieldName('location');
            $location_name->setSortColumn(false);
            $location_name->setFormField(false);
            $location_name->setTableColumn(true);
            $location_name->setNotificationField(true);
            $location_name->setPrintable($withPdf);
            $fields[] = $location_name;

            $contact_name = new C4GTextField();
            $contact_name->setFieldName('contact_name');
            $contact_name->setSortColumn(false);
            $contact_name->setFormField(false);
            $contact_name->setTableColumn(true);
            $contact_name->setNotificationField(true);
            $contact_name->setPrintable($withPdf);
            $fields[] = $contact_name;

            $contact_phone = new C4GTelField();
            $contact_phone->setFieldName('contact_phone');
            $contact_phone->setFormField(false);
            $contact_phone->setTableColumn(false);
            $contact_phone->setNotificationField(true);
            $contact_phone->setPrintable($withPdf);
            $fields[] = $contact_phone;

            $contact_email = new C4GEmailField();
            $contact_email->setFieldName('contact_email');
            $contact_email->setTableColumn(false);
            $contact_email->setFormField(false);
            $contact_email->setNotificationField(true);
            $contact_email->setPrintable($withPdf);
            $fields[] = $contact_email;

            $contact_website = new C4GUrlField();
            $contact_website->setFieldName('contact_website');
            $contact_website->setTableColumn(false);
            $contact_website->setFormField(false);
            $contact_website->setNotificationField(true);
            $contact_website->setPrintable($withPdf);
            $fields[] = $contact_website;

            $contact_street = new C4GTextField();
            $contact_street->setFieldName('contact_street');
            $contact_street->setTableColumn(false);
            $contact_street->setFormField(false);
            $contact_street->setNotificationField(true);
            $contact_street->setPrintable($withPdf);
            $fields[] = $contact_street;

            $contact_postal = new C4GTextField();
            $contact_postal->setFieldName('contact_postal');
            $contact_postal->setFormField(false);
            $contact_postal->setTableColumn(false);
            $contact_postal->setNotificationField(true);
            $contact_postal->setPrintable($withPdf);
            $fields[] = $contact_postal;

            $contact_city = new C4GTextField();
            $contact_city->setFieldName('contact_city');
            $contact_city->setTableColumn(false);
            $contact_city->setFormField(false);
            $contact_city->setNotificationField(true);
            $contact_city->setPrintable($withPdf);
            $fields[] = $contact_city;

            $blueprint = $fields;

            if ($useCache && $cache) {
                try {
                    $item = $cache->getItem($cacheKey);
                    $item->set($blueprint);
                    if (method_exists($item, 'expiresAfter')) {
                        $item->expiresAfter($ttl);
                    }
                    $cache->save($item);
                } catch (\Throwable $t) { /* ignore */ }
            }
        }

        return is_array($blueprint) ? $blueprint : [];
    }
}