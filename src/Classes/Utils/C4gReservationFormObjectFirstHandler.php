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
                    'openingHours' => $reservationObject->getOpeningHours(),
                    'tags' => $reservationObject->getTags()
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
        $initialDateFromValues = '';
        if ($this->initialValues->getDate()) {
            $objDate = new Date($this->initialValues->getDate(), Date::getFormatFromRgxp('date'));
            $initialDateFromValues = $objDate->date;
        }
        $reservationObjectField->setInitialCallOnChange(true);
        $reservationObjectField->setCallOnChange(true);
        $jsOnChange = "if(typeof onObjectChangeFirst==='function'){onObjectChangeFirst(".json_encode((string)$listType['id']).",".json_encode((int)$showDateTime).",".json_encode((string)$initialDateFromValues).");}";
        $reservationObjectField->setCallOnChangeFunction($jsOnChange);
        $reservationObjectField->setAdditionalID($listType["id"]);
        $reservationObjectField->setHidden($reservationSettings->objectHide);
        $reservationObjectField->setPrintable($this->module->isWithDefaultPDFContent());
        $this->fieldList[] = $reservationObjectField;

        if ($reservationSettings->showTagsInForm) {
            $tagIndex = 0;
            foreach ($reservationObjects as $reservationObject) {
                $tags = $reservationObject->getTags();
                if (!empty($tags)) {
                    foreach ($tags as $tag) {
                        $tagField = new C4GImageField();
                        $tagField->setTitle($tag['name']);
                        $tagField->setFieldName('tags_' . $listType['id'] . '_' . $tagIndex);
                        $tagField->setInitialValue($tag['icon']);
                        $tagField->setWidth(64); //ToDo config
                        $tagField->setHeight(64); //ToDo config
                        $tagField->setCondition([
                            new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'reservation_object_' . $listType['id'], $reservationObject->getId()),
                            $condition
                        ]);
                        $this->fieldList[] = $tagField;
                    }
                }
                $tagIndex++;
            }
        }

        
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
                    $jsOnChange = "if(typeof setReservationForm==='function'){setReservationForm(".json_encode($listType['id'] . '-33' . $reservationObject->getId()).",".json_encode((int)$showDateTime).");}";
                    $reservationDesiredCapacity->setCallOnChangeFunction($jsOnChange);
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

        $jsInitialDate = "";
        if ($this->initialValues->getDate()) {
            $objDate = new Date($this->initialValues->getDate(), Date::getFormatFromRgxp('date'));
            $jsInitialDate = $objDate->date;
        }

        $jsListId = json_encode((string)$listType['id']);
        $jsDateValue = json_encode((string)$jsInitialDate);
        $jsShowDateTimeVal = json_encode((int)$showDateTime);
        $jsHasUrlDate = json_encode(!!\Contao\Input::get('date'));
        $jsHideTime = json_encode($reservationSettings->hideTime ? "1" : "0");
        $script = "(function(){var lid=".$jsListId.";var sdt=".$jsShowDateTimeVal.";var ht=".$jsHideTime.";var up=new URLSearchParams(window.location.search);var ud=up.get('date');var dv=".$jsDateValue.";if(ud){dv=ud}if(!window.con4gis_reservation_initialized){window.con4gis_reservation_initialized={}}if(ud&&!window.con4gis_reservation_initialized[lid]){window.con4gis_reservation_initialized[lid]=true;document.cookie='reservationInitialDateCookie='+encodeURIComponent(ud)+'; path=/; SameSite=Lax'}var rd=function(){var url=new URL(window.location.href);if(url.searchParams.has('date')){url.searchParams.delete('date');window.history.replaceState({},'',url.toString());ud=null;dv=null}};var sd=function(){var af=document.getElementById('c4g_reservation_object_'+lid);var av=af?af.value:'';var df=document.getElementById('c4g_beginDate_'+lid+'-33'+av);var p=document.getElementById('c4g_beginDate_'+lid+'-33'+av+'_picker');if(ud&&p&&p.datepicker&&typeof p.datepicker.setDate==='function'){try{var tv=ud;if(ud.indexOf('.')!==-1){var pts=ud.split('.');tv=new Date(pts[2],pts[1]-1,pts[0])}p.datepicker.setDate(tv);if(df){df.value=ud}if(p.datepicker.options){var os=p.datepicker.options.onSelect;p.datepicker.options.onSelect=function(dt,inst){rd();if(typeof os==='function'){os(dt,inst)}}}ud=null}catch(e){console.error(e)}}else if(ud&&df){df.value=ud;ud=null}if(p&&!p.datepicker&&ud){setTimeout(sd,100)}};setTimeout(sd,100);var afi=document.getElementById('c4g_reservation_object_'+lid);var avi=afi?afi.value:'';if(typeof setTimeset==='function'){setTimeset(dv,lid,sdt,avi)}if(ht==='1'){var style=document.createElement('style');style.innerHTML='.c4g-hide-field { display: none !important; }';(document.head||document.getElementsByTagName('head')[0]).appendChild(style)}if(typeof handleBrickConditions==='function'){handleBrickConditions()}})();";
        $this->getDialogParams()->setOnloadScript($script);
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

            $suspensionDates = C4gReservationHandler::getSuspensionDates($reservationSettings);
            $periodType = $listType['periodType'];
            $bookedDays = "";
            if ($periodType == 'day' || $periodType  == 'overnight' || $periodType  == 'week') {            
                $bookedDays = C4gReservationHandler::getBookedDays($listType, $reservationObject);
                if ($bookedDays) {
                    $bookedDays = $bookedDays['dates'];
                } 
                if ($suspensionDates) {
                    $bookedDays = $bookedDays ? $bookedDays . ',' . $suspensionDates : $suspensionDates;
                }
                $reservationBeginDateField->setExcludeDates($bookedDays);
                $initialValue = $initialBookingDate ? $this->initialValues->getDate() : C4gReservationHandler::getBookableMinDate([$reservationObject], $listType);
            } else {
                $commaDates = C4gReservationHandler::getDateExclusionString($reservationObjects, $listType, $reservationSettings->removeBookedDays);
                if ($commaDates) {
                    $commaDates = isset($commaDates['dates']) ? $commaDates['dates'] : null;
                }
                if ($suspensionDates) {
                    $commaDates = $commaDates ? $commaDates . ',' . $suspensionDates : $suspensionDates;
                }
                $reservationBeginDateField->setExcludeDates($commaDates);
            }
            
            $reservationBeginDateField->setFieldName('beginDate');
            $reservationBeginDateField->setCustomFormat($GLOBALS['TL_CONFIG']['dateFormat']);
            $reservationBeginDateField->setCustomLanguage($GLOBALS['TL_LANGUAGE']);
            $reservationBeginDateField->setTitle($titleDate);
            $reservationBeginDateField->setStyleClass('begin-date c4g_beginDate_'.$listType['id']);
            $reservationBeginDateField->setMandatory(true);
            $reservationBeginDateField->setEditable($typeOfObject !== 'fixed_date');

            if ($this->initialValues->getDate()) {
                $objDate = new Date($this->initialValues->getDate(), Date::getFormatFromRgxp('date'));
                $initialBookingDateValue = $objDate->date;
            } else if ($listType['directBooking']) {
                $objDate = new Date(time(), Date::getFormatFromRgxp('date'));
                $initialBookingDateValue = $objDate->date;
            } else {
                $initialBookingDateValue = C4gReservationHandler::getBookableMinDate([$reservationObject], $listType);
            }

            if ($typeOfObject == 'fixed_date') {
                $reservationBeginDateField->setInitialValue($reservationObject->getBeginDate());
                $reservationBeginDateField->setShowInlinePicker($reservationSettings->showInlineDatepicker && $typeOfObject !== 'fixed_date');
                $reservationBeginDateField->setEditable(false);
                $reservationBeginDateField->setMandatory(false);
            } else {
                $reservationBeginDateField->setInitialValue($initialBookingDateValue);
                $reservationBeginDateField->setShowInlinePicker(!!$reservationSettings->showInlineDatepicker);
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
            $jsListId = json_encode((string)$listType['id']);
            $jsShowDT = json_encode((int)$showDateTime);
            $jsObjectId = json_encode((string)$reservationObject->getId());
            $jsOnChange = "if(typeof setTimeset==='function'){setTimeset(String(this.value),$jsListId,$jsShowDT,$jsObjectId)}";
            $reservationBeginDateField->setCallOnChangeFunction($jsOnChange);
            $reservationBeginDateField->setNotificationField(true);
            $reservationBeginDateField->setAdditionalID($listType['id'] . '-33' . $reservationObject->getId());
            $reservationBeginDateField->setPattern('');
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
                if ($reservationSettings->hideTime && $options) {
                    $firstOption = reset($options);
                    $initialBookingTime = $firstOption['id'];
                }
                $classes = 'reservation_time_button reservation_time_button_direct reservation_time_button_' . $listType['id'];
            } else {
                $options = C4gReservationHandler::getReservationTimes(
                    $reservationObjects,
                    $listType['id'],
                    -1,
                    $initialBookingDateValue ?: $initialBookingDate,
                    0,
                    0,
                    $reservationSettings->showEndTime,
                    $reservationSettings->showFreeSeats
                );
                if ($reservationSettings->hideTime && $options) {
                    $firstOption = reset($options);
                    $initialBookingTime = $firstOption['id'];
                }
                
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
            if ($reservationSettings->hideTime) {
                $classes .= ' c4g-hide-field';
            }
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
        $reservationEndDateDBField->setPattern('');
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

        return $this->fieldList;
    }
}