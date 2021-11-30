<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 8
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2021, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

namespace con4gis\ReservationBundle\Resources\contao\modules;

use con4gis\CoreBundle\Resources\contao\models\C4gLogModel;
use con4gis\ProjectsBundle\Classes\Actions\C4GSaveAndRedirectDialogAction;
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
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GKeyField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GLabelField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GMultiCheckboxField;
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
use con4gis\ProjectsBundle\Classes\Framework\C4GBrickModuleParent;
use con4gis\ProjectsBundle\Classes\Views\C4GBrickViewType;
use con4gis\ReservationBundle\Classes\C4gReservationBrickTypes;
use con4gis\ReservationBundle\Classes\C4gReservationDateChecker;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationEventAudienceModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationEventModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationEventSpeakerModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationEventTopicModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationLocationModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationObjectModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationParamsModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationTypeModel;
use Contao\Controller;
use Contao\Date;
use Contao\FrontendUser;
use Contao\StringUtil;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class C4gReservation extends C4GBrickModuleParent
{
    protected $tableName    = 'tl_c4g_reservation';
    protected $modelClass   = C4gReservationModel::class;
    protected $languageFile = 'fe_c4g_reservation';
    protected $brickKey     = C4gReservationBrickTypes::BRICK_RESERVATION;
    protected $viewType     = C4GBrickViewType::PUBLICFORM;
    protected $sendEMails   = null;
    protected $brickScript  = 'bundles/con4gisreservation/dist/js/c4g_brick_reservation.js';
    protected $brickStyle   = 'bundles/con4gisreservation/dist/css/c4g_brick_reservation.min.css';
    protected $withNotification = true;

    //Resource Params
    protected $loadDefaultResources = true;
    protected $loadDateTimePickerResources = false;
    protected $loadChosenResources = false;
    protected $loadClearBrowserUrlResources = false;
    protected $loadConditionalFieldDisplayResources = true;
    protected $loadMoreButtonResources = false;
    protected $loadFontAwesomeResources = true;
    protected $loadTriggerSearchFromOtherModuleResources = false;
    protected $loadFileUploadResources = false;
    protected $loadMultiColumnResources = false;
    protected $loadMiniSearchResources = false;
    protected $loadHistoryPushResources = false;

    //JQuery GUI Resource Params
    protected $jQueryAddCore = true;
    protected $jQueryAddJquery = true;
    protected $jQueryAddJqueryUI = true;
    protected $jQueryUseTree = false;
    protected $jQueryUseTable = false;
    protected $jQueryUseHistory = false;
    protected $jQueryUseTooltip = false;
    protected $jQueryUseMaps = false;
    protected $jQueryUseGoogleMaps = false;
    protected $jQueryUseMapsEditor = false;
    protected $jQueryUseWswgEditor = false;
    protected $jQueryUseScrollPane = false;
    protected $jQueryUsePopups = false;

    protected $withPermissionCheck = false;
    protected $useUuidCookie = false;

    public function initBrickModule($id)
    {
        self::loadLanguageFile('fe_c4g_reservation');
        $this->setBrickCaption($GLOBALS['TL_LANG']['fe_c4g_reservation']['brick_caption']);
        $this->setBrickCaptionPlural($GLOBALS['TL_LANG']['fe_c4g_reservation']['brick_caption_plural']);
        parent::initBrickModule($id);

        $this->dialogParams->setWithoutGuiHeader(true);
        $this->dialogParams->deleteButton(C4GBrickConst::BUTTON_SAVE);
        $this->dialogParams->deleteButton(C4GBrickConst::BUTTON_SAVE_AND_NEW);
        $this->dialogParams->deleteButton(C4GBrickConst::BUTTON_DELETE);
        $this->dialogParams->setRedirectSite($this->reservation_redirect_site);
        $this->dialogParams->setSaveWithoutSavingMessage(false);
    }

    public function addFields()
    {
        $fieldList = array();

        $idField = new C4GKeyField();
        $idField->setFieldName('id');
        $idField->setEditable(false);
        $idField->setFormField(false);
        $idField->setSortColumn(false);
        $fieldList[] = $idField;

        $typelist = array();
        
        $initialDate = '';
        $initialTime = '';

        $eventId = $this->Input->get('event') ? $this->Input->get('event') : 0;

        //ToDo hotfix
        //Please keep it that way. The get parameters are lost during processing in Projects and are thus preserved.
        if (!$eventId && $_COOKIE['reservationEventCookie']) {
            $eventId = $_COOKIE['reservationEventCookie'];
        } else if ($eventId) {
            setcookie('reservationEventCookie', $eventId, time()+60, '/');
        }

        $event = $eventId ? \CalendarEventsModel::findByPk($eventId) : false;
        $eventObj = $event && $event->published ? C4gReservationEventModel::findBy('pid', $event->id) : false;
        if ($eventObj && (count($eventObj) > 1)) {
            C4gLogModel::addLogEntry('reservation', 'There are more than one event connections. Check Event: ' . $event->id);
        } else {
            $date = $this->Input->get('date') ? $this->Input->get('date') : 0;
            if ($date) {
                $initialDate = $date;
            }

            //ToDo hotfix
            //Please keep it that way. The get parameters are lost during processing in Projects and are thus preserved.
            if ($eventObj && !$initialDate && $_COOKIE['reservationInitialDateCookie']) {
                $initialDate = $_COOKIE['reservationInitialDateCookie'];
            } else if ($eventObj && $initialDate) {
                setcookie('reservationInitialDateCookie', $initialDate, time()+60, '/');
            }

            $time = $this->Input->get('time') ? $this->Input->get('time') : 0;

            //ToDo hotfix
            //Please keep it that way. The get parameters are lost during processing in Projects and are thus preserved.
            if ($eventObj && !$time && $_COOKIE['reservationTimeCookie']) {
                $time = $_COOKIE['reservationTimeCookie'];
            } else if ($eventObj && $time) {
                setcookie('reservationTimeCookie', $time, time()+60, '/');
            }

            if ($time) {
                $initialTime = strtotime($time);
                $objDate = new Date(date($GLOBALS['TL_CONFIG']['timeFormat'],$initialTime), Date::getFormatFromRgxp('time'));
                $initialTime = $objDate->tstamp;
            }
        }

        $t = 'tl_c4g_reservation_type';
        $arrValues = array();
        $arrOptions = array();

        if ($eventObj && count($eventObj) == 1) {
            $typeId = $eventObj->reservationType;
            $arrColumns = array("$t.published='1' AND $t.id=$typeId");
            $types = C4gReservationTypeModel::findBy($arrColumns, $arrValues, $arrOptions);
        } else if (!$eventObj) {
            $arrColumns = array("$t.published='1'");
            $types = C4gReservationTypeModel::findBy($arrColumns, $arrValues, $arrOptions);
        }

        $specialParticipantMechanism = $this->specialParticipantMechanism;

        if ($types) {
            $memberId = 0;
            if (FE_USER_LOGGED_IN === true) {
                $member = FrontendUser::getInstance();
                if ($member) {
                    $memberId = $member->id;
                }
            }

            $moduleTypes = unserialize($this->reservation_types);
            foreach ($types as $type) {
                if ($moduleTypes && (count($moduleTypes) > 0)) {
                    $arrModuleTypes = $moduleTypes;
                    if (!in_array($type->id, $arrModuleTypes)) {
                        continue;
                    }
                }

                $objects = C4gReservationObjectModel::getReservationObjectList(array($type->id), intval($eventId), $this->showPrices);
                if (!$objects || (count($objects) <= 0)) {
                    continue;
                }

                $captions = unserialize($type->options);
                if ($captions && (count($captions) > 0)) {
                    foreach ($captions as $caption) {
                        if (strpos($GLOBALS['TL_LANGUAGE'], $caption['language']) >= 0) {
                            $typelist[$type->id] = array(
                                'id' => $type->id,
                                'name' => $caption['caption'] ?: $type->caption,
                                'periodType' => $type->periodType,
                                'includedParams' => unserialize($type->included_params),
                                'additionalParams' => unserialize($type->additional_params),
                                'participantParams' => unserialize($type->participant_params),
                                'minParticipantsPerBooking' => $type->minParticipantsPerBooking,
                                'maxParticipantsPerBooking' => $type->maxParticipantsPerBooking,
                                'objects' => $objects,
                                'isEvent' => $type->reservationObjectType && $type->reservationObjectType === '2' ? true : false,
                                'memberId' => $type->member_id ?: $memberId,
                                'groupId' => $type->group_id,
                                'type' => $type->reservationObjectType,
                                'directBooking' => $type->directBooking
                            );
                        }
                    }
                } else {
                    $typelist[$type->id] = array(
                        'id' => $type->id,
                        'name' => $type->caption,
                        'periodType' => $type->periodType,
                        'includedParams' => unserialize($type->included_params),
                        'additionalParams' => unserialize($type->additional_params),
                        'participantParams' => unserialize($type->participant_params),
                        'minParticipantsPerBooking' => $type->minParticipantsPerBooking,
                        'maxParticipantsPerBooking' => $type->maxParticipantsPerBooking,
                        'objects' => $objects,
                        'isEvent' => $type->reservationObjectType && $type->reservationObjectType === '2' ? true : false,
                        'memberId' => $type->member_id ?: $memberId,
                        'groupId' => $type->group_id,
                        'type' => $type->reservationObjectType,
                        'directBooking' => $type->directBooking
                    );
                }
            }
        }

        $showDateTime = $this->showDateTime ? "1" : "0";

        if (count($typelist) > 0) {
            $firstType = array_key_first($typelist);

            $onLoadScript = $this->getDialogParams()->getOnloadScript();
            $onLoadScript .= " jQuery('#c4g_reservation_type').trigger('change');";
            $this->getDialogParams()->setOnloadScript(trim($onLoadScript));

            $reservationTypeField = new C4GSelectField();
            $reservationTypeField->setChosen(false);
            $reservationTypeField->setFieldName('reservation_type');
            $reservationTypeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_type']);
            $reservationTypeField->setSortColumn(false);
            $reservationTypeField->setTableColumn(false);
            $reservationTypeField->setColumnWidth(20);
            $reservationTypeField->setSize(1);
            $reservationTypeField->setOptions($typelist);
            $reservationTypeField->setMandatory(true);
            $reservationTypeField->setCallOnChange(true);
            $reservationTypeField->setCallOnChangeFunction("setReservationForm(" . $this->id . ", -1 ,'getCurrentTimeset'," . $showDateTime . ",false)");
            $reservationTypeField->setInitialValue($firstType);
            $reservationTypeField->setStyleClass('reservation-type');
            $reservationTypeField->setHidden(count($typelist) == 1);
            $reservationTypeField->setNotificationField(true);
            $reservationTypeField->setWithOptionType(true);
            //$reservationTypeField->setInitialCallOnChange($typelist[$firstType]['isEvent']);
            $fieldList[] = $reservationTypeField;
        }

        foreach ($typelist as $listType) {
            $isEvent = $listType['isEvent'];
            $reservationObjects = $listType['objects'];

            $condition = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'reservation_type', $listType['id']);
            $minCapacity = $listType['minParticipantsPerBooking'] ? $listType['minParticipantsPerBooking'] : 1;
            $maxCapacity = $listType['maxParticipantsPerBooking'] ? $listType['maxParticipantsPerBooking'] : 0;
            if ($this->withCapacity) {
                $conditionCapacity = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'desiredCapacity_' . $listType['id']);

                $reservationDesiredCapacity = new C4GNumberField();
                $reservationDesiredCapacity->setFieldName('desiredCapacity');

                if ($minCapacity && $maxCapacity) {
                    $reservationDesiredCapacity->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['desiredCapacity']);/*. ' ('.$minCapacity.'-'.$maxCapacity.')')*/; //ToDo default solution
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
                if (!$isEvent) {
                    $reservationDesiredCapacity->setCallOnChangeFunction("setReservationForm(".$this->id . "," . $listType['id'] . ",'getCurrentTimeset'," . $showDateTime . ",false);");
                } else {
                    $reservationDesiredCapacity->setCallOnChangeFunction("setReservationForm(".$this->id . "," . $listType['id'] . ",'getCurrentTimeset'," . $showDateTime . ",true);");
                    //$reservationDesiredCapacity->setInitialCallOnChange(true);
                }
                $reservationDesiredCapacity->setNotificationField(true);
                $reservationDesiredCapacity->setAdditionalID($listType['id']);
                $reservationDesiredCapacity->setStyleClass('desired-capacity');

                $fieldList[] = $reservationDesiredCapacity;
            }


            //Default fields
            if (!$isEvent) {
                //set reservationObjectType to default
                $reservationObjectTypeField = new C4GNumberField();
                $reservationObjectTypeField->setFieldName('reservationObjectType');
                $reservationObjectTypeField->setInitialValue('1');
                $reservationObjectTypeField->setDatabaseField(true);
                $reservationObjectTypeField->setFormField(false);
                $fieldList[] = $reservationObjectTypeField;

                $additionalDuration = $this->additionalDuration;
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
                    $durationField->setCallOnChangeFunction("setTimeset(document.getElementById('c4g_beginDate_" . $listType['id'] . "'), " . $this->id . "," . $listType['id'] . ",'getCurrentTimeset'," . $this->showDateTime . ");");
                    $durationField->setCondition(array($condition));
                    $durationField->setNotificationField(true);
                    $durationField->setStyleClass('duration');
                    $durationField->setMin(1);
                    $durationField->setMax($additionalDuration);
                    $durationField->setMaxLength(3);
                    $durationField->setStep(5);

                    $fieldList[] = $durationField;
                } else {
                    $additionalDuration = 0;
                }

                if (($listType['periodType'] === 'minute') || ($listType['periodType'] === 'hour')) {
                    //$conditionDate = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'beginDate_'.$listType['id']);

                    if (!$initialDate && $listType['directBooking']) {
                        $initialBookingDate = time();
                    } else {
                        $initialBookingDate = false;
                    }

                    if ($initialDate || $initialBookingDate) {
                        $script = "setTimeset(document.getElementById('c4g_beginDate_".$listType['id']."'), " . $this->id . "," . $listType['id'] . ",'getCurrentTimeset'," . $this->showDateTime . ");";
                        $this->getDialogParams()->setOnloadScript($script);
                    }

                    $reservationBeginDateField = new C4GDateField();
                    $reservationBeginDateField->setFlipButtonPosition(true);
                    $reservationBeginDateField->setMinDate(C4gReservationObjectModel::getMinDate($reservationObjects));
                    $reservationBeginDateField->setMaxDate(C4gReservationObjectModel::getMaxDate($reservationObjects));
                    $reservationBeginDateField->setExcludeWeekdays(C4gReservationObjectModel::getWeekdayExclusionString($reservationObjects));
                    $reservationBeginDateField->setExcludeDates(C4gReservationObjectModel::getDateExclusionString($reservationObjects, $listType, $this->removeBookedDays));
                    $reservationBeginDateField->setFieldName('beginDate');
                    $reservationBeginDateField->setCustomFormat($GLOBALS['TL_CONFIG']['dateFormat']);
                    $reservationBeginDateField->setCustomLanguage($GLOBALS['TL_LANGUAGE']);
                    $reservationBeginDateField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDate']);
                    $reservationBeginDateField->setEditable(true);
                    $reservationBeginDateField->setInitialValue($initialBookingDate ?: strtotime($initialDate));
                    $reservationBeginDateField->setComparable(false);
                    $reservationBeginDateField->setSortColumn(true);
                    $reservationBeginDateField->setSortSequence('de_datetime');
                    $reservationBeginDateField->setTableColumn(true);
                    $reservationBeginDateField->setFormField(true);
                    $reservationBeginDateField->setColumnWidth(10);
                    $reservationBeginDateField->setMandatory(true);
                    $reservationBeginDateField->setCondition(array($condition));
                    $reservationBeginDateField->setCallOnChange(true);
                    $reservationBeginDateField->setCallOnChangeFunction("setTimeset(this, " . $this->id . "," . $listType['id'] . ",'getCurrentTimeset'," . $this->showDateTime . ");");
                    $reservationBeginDateField->setNotificationField(true);
                    $reservationBeginDateField->setAdditionalID($listType['id']);
                    $reservationBeginDateField->setStyleClass('begin-date');
                    $fieldList[] = $reservationBeginDateField;
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
                $reservationendTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $this->showDateTime . ')');
                $reservationendTimeField->setNotificationField(true);
                $reservationendTimeField->setRemoveWithEmptyCondition(true);
                $reservationendTimeField->setStyleClass('reservation_time_button reservation_time_button_' . $listType['id']);
                $fieldList[] = $reservationendTimeField;

                if (!$initialTime && $listType['directBooking']) {
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
                        'max' => $reservationObject->getDesiredCapacity()[1] ? ($reservationObject->getDesiredCapacity()[1] * $reservationObject->getQuantity()) : $reservationObject->getQuantity(),
                        'allmostFullyBookedAt' => $reservationObject->getAlmostFullyBookedAt(),
                        'openingHours' => $reservationObject->getOpeningHours()
                    );
                }

                if ($initialBookingDate && $initialBookingTime && $objects) {
//                    if ($initialDate) {
//                        $script = "setObjectId(this," . $listType['id'] . ",' . $this->showDateTime . ');";
//                        $this->getDialogParams()->setOnloadScript($script);
//                    }

                    $reservationBeginTimeField = new C4GRadioGroupField();
                    $reservationBeginTimeField->setFieldName('beginTime');
                    $reservationBeginTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                    $reservationBeginTimeField->setFormField(true);
                    $reservationBeginTimeField->setDatabaseField(true);
                    $reservationBeginTimeField->setOptions(C4gReservationObjectModel::getReservationNowTime($objects[0], $this->showEndTime, $this->showFreeSeats));
                    $reservationBeginTimeField->setCallOnChange(true);
                    $reservationBeginTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $this->showDateTime . ')');
                    $reservationBeginTimeField->setMandatory(false);
                    $reservationBeginTimeField->setInitialValue($initialBookingTime ?: $initialTime);
                    $reservationBeginTimeField->setSort(false);
                    $reservationBeginTimeField->setCondition(array($condition));
                    $reservationBeginTimeField->setAdditionalID($listType['id'].'-00'.date('w', strtotime($initialDate)));
                    $reservationBeginTimeField->setNotificationField(true);
                    $reservationBeginTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $reservationBeginTimeField->setTurnButton(true);
                    $reservationBeginTimeField->setRemoveWithEmptyCondition(true);
                    $reservationBeginTimeField->setStyleClass('reservation_time_button reservation_time_button_direct reservation_time_button_' . $listType['id']);
                    $reservationBeginTimeField->setTimeButtonSpecial(true);
                    $fieldList[] = $reservationBeginTimeField;
                } else if (($listType['periodType'] === 'hour') || ($listType['periodType'] === 'minute')) {
                    $su_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $listType['id']);
                    $su_condition->setModel(C4gReservationObjectModel::class);
                    $su_condition->setFunction('isSunday');
                    $suConditionArr = [$su_condition, $condition];

                    $suReservationTimeField = new C4GRadioGroupField();
                    $suReservationTimeField->setFieldName('beginTime');
                    $suReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                    $suReservationTimeField->setFormField(true);
                    $suReservationTimeField->setOptions(
                        C4gReservationObjectModel::getReservationTimes(
                            $reservationObjects,
                            $listType['id'],
                            '0',
                            date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getNextWeekday($reservationObjects, 0)),
                            0,
                            $this->showEndTime,
                            $this->showFreeSeats
                        ));
                    $suReservationTimeField->setMandatory(true);
                    $suReservationTimeField->setInitInvisible(true);
                    $suReservationTimeField->setSort(false);
                    $suReservationTimeField->setCondition($suConditionArr);
                    $suReservationTimeField->setCallOnChange(true);
                    $suReservationTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $this->showDateTime . ')');
                    $suReservationTimeField->setAdditionalID($listType['id'] . '-000');
                    $suReservationTimeField->setNotificationField(true);
                    $suReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $suReservationTimeField->setTurnButton(true);
                    $suReservationTimeField->setRemoveWithEmptyCondition(true);
                    $suReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_' . $listType['id']);
                    $suReservationTimeField->setInitialValue($initialTime);
                    $suReservationTimeField->setTimeButtonSpecial(true);
                    $fieldList[] = $suReservationTimeField;

                    $mo_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $listType['id']);
                    $mo_condition->setModel(C4gReservationObjectModel::class);
                    $mo_condition->setFunction('isMonday');
                    $moConditionArr = [$mo_condition, $condition];

                    $moReservationTimeField = new C4GRadioGroupField();
                    $moReservationTimeField->setFieldName('beginTime');
                    $moReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                    $moReservationTimeField->setFormField(true);
                    $moReservationTimeField->setOptions(
                        C4gReservationObjectModel::getReservationTimes(
                            $reservationObjects,
                            $listType['id'],
                            '1',
                            date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getNextWeekday($reservationObjects, 1)),
                            0,
                            $this->showEndTime,
                            $this->showFreeSeats
                        ));
                    $moReservationTimeField->setMandatory(true);
                    $moReservationTimeField->setInitInvisible(true);
                    $moReservationTimeField->setSort(false);
                    $moReservationTimeField->setCondition($moConditionArr);
                    $moReservationTimeField->setCallOnChange(true);
                    $moReservationTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $this->showDateTime . ')');
                    $moReservationTimeField->setAdditionalID($listType['id'] . '-001');
                    $moReservationTimeField->setNotificationField(true);
                    $moReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $moReservationTimeField->setTurnButton(true);
                    $moReservationTimeField->setRemoveWithEmptyCondition(true);
                    $moReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_' . $listType['id']);
                    $moReservationTimeField->setInitialValue($initialTime);
                    $moReservationTimeField->setTimeButtonSpecial(true);
                    $fieldList[] = $moReservationTimeField;

                    $tu_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $listType['id']);
                    $tu_condition->setModel(C4gReservationObjectModel::class);
                    $tu_condition->setFunction('isTuesday');
                    $tuConditionArr = [$tu_condition, $condition];

                    $tuReservationTimeField = new C4GRadioGroupField();
                    $tuReservationTimeField->setFieldName('beginTime');
                    $tuReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                    $tuReservationTimeField->setFormField(true);
                    $tuReservationTimeField->setOptions(
                        C4gReservationObjectModel::getReservationTimes(
                            $reservationObjects,
                            $listType['id'],
                            '2',
                            date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getNextWeekday($reservationObjects, 2)),
                            0,
                            $this->showEndTime,
                            $this->showFreeSeats
                        ));
                    $tuReservationTimeField->setMandatory(true);
                    $tuReservationTimeField->setInitInvisible(true);
                    $tuReservationTimeField->setSort(false);
                    $tuReservationTimeField->setCondition($tuConditionArr);
                    $tuReservationTimeField->setCallOnChange(true);
                    $tuReservationTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $this->showDateTime . ')');
                    $tuReservationTimeField->setAdditionalID($listType['id'] . '-002');
                    $tuReservationTimeField->setNotificationField(true);
                    $tuReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $tuReservationTimeField->setTurnButton(true);
                    $tuReservationTimeField->setRemoveWithEmptyCondition(true);
                    $tuReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_' . $listType['id']);
                    $tuReservationTimeField->setInitialValue($initialTime);
                    $tuReservationTimeField->setTimeButtonSpecial(true);
                    $fieldList[] = $tuReservationTimeField;

                    $we_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $listType['id']);
                    $we_condition->setModel(C4gReservationObjectModel::class);
                    $we_condition->setFunction('isWednesday');
                    $weConditionArr = [$we_condition, $condition];

                    $weReservationTimeField = new C4GRadioGroupField();
                    $weReservationTimeField->setFieldName('beginTime');
                    $weReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                    $weReservationTimeField->setFormField(true);
                    $weReservationTimeField->setOptions(
                        C4gReservationObjectModel::getReservationTimes(
                            $reservationObjects,
                            $listType['id'],
                            '3',
                            date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getNextWeekday($reservationObjects, 3)),
                            0,
                            $this->showEndTime,
                            $this->showFreeSeats
                        ));
                    $weReservationTimeField->setMandatory(true);
                    $weReservationTimeField->setInitInvisible(true);
                    $weReservationTimeField->setSort(false);
                    $weReservationTimeField->setCondition($weConditionArr);
                    $weReservationTimeField->setCallOnChange(true);
                    $weReservationTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $this->showDateTime . ')');
                    $weReservationTimeField->setAdditionalID($listType['id'] . '-003');
                    $weReservationTimeField->setNotificationField(true);
                    $weReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $weReservationTimeField->setTurnButton(true);
                    $weReservationTimeField->setRemoveWithEmptyCondition(true);
                    $weReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_' . $listType['id']);
                    $weReservationTimeField->setInitialValue($initialTime);
                    $weReservationTimeField->setTimeButtonSpecial(true);
                    $fieldList[] = $weReservationTimeField;

                    $th_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $listType['id']);
                    $th_condition->setModel(C4gReservationObjectModel::class);
                    $th_condition->setFunction('isThursday');
                    $thConditionArr = [$th_condition, $condition];

                    $thReservationTimeField = new C4GRadioGroupField();
                    $thReservationTimeField->setFieldName('beginTime');
                    $thReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                    $thReservationTimeField->setFormField(true);
                    $thReservationTimeField->setOptions(
                        C4gReservationObjectModel::getReservationTimes(
                            $reservationObjects,
                            $listType['id'],
                            '4',
                            date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getNextWeekday($reservationObjects, 4)),
                            0,
                            $this->showEndTime,
                            $this->showFreeSeats
                        ));
                    $thReservationTimeField->setMandatory(true);
                    $thReservationTimeField->setInitInvisible(true);
                    $thReservationTimeField->setSort(false);
                    $thReservationTimeField->setCondition($thConditionArr);
                    $thReservationTimeField->setCallOnChange(true);
                    $thReservationTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $this->showDateTime . ')');
                    $thReservationTimeField->setAdditionalID($listType['id'] . '-004');
                    $thReservationTimeField->setNotificationField(true);
                    $thReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $thReservationTimeField->setTurnButton(true);
                    $thReservationTimeField->setRemoveWithEmptyCondition(true);
                    $thReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_' . $listType['id']);
                    $thReservationTimeField->setInitialValue($initialTime);
                    $thReservationTimeField->setTimeButtonSpecial(true);
                    $fieldList[] = $thReservationTimeField;

                    $fr_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $listType['id']);
                    $fr_condition->setModel(C4gReservationObjectModel::class);
                    $fr_condition->setFunction('isFriday');
                    $frConditionArr = [$fr_condition, $condition];

                    $frReservationTimeField = new C4GRadioGroupField();
                    $frReservationTimeField->setFieldName('beginTime');
                    $frReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                    $frReservationTimeField->setFormField(true);
                    $frReservationTimeField->setOptions(
                        C4gReservationObjectModel::getReservationTimes(
                            $reservationObjects,
                            $listType['id'],
                            '5',
                            date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getNextWeekday($reservationObjects, 5)),
                            0,
                            $this->showEndTime,
                            $this->showFreeSeats
                        ));
                    $frReservationTimeField->setMandatory(true);
                    $frReservationTimeField->setInitInvisible(true);
                    $frReservationTimeField->setSort(false);
                    $frReservationTimeField->setCondition($frConditionArr);
                    $frReservationTimeField->setCallOnChange(true);
                    $frReservationTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $this->showDateTime . ')');
                    $frReservationTimeField->setAdditionalID($listType['id'] . '-005');
                    $frReservationTimeField->setNotificationField(true);
                    $frReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $frReservationTimeField->setTurnButton(true);
                    $frReservationTimeField->setRemoveWithEmptyCondition(true);
                    $frReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_' . $listType['id']);
                    $frReservationTimeField->setInitialValue($initialTime);
                    $frReservationTimeField->setTimeButtonSpecial(true);
                    $fieldList[] = $frReservationTimeField;

                    $sa_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $listType['id']);
                    $sa_condition->setModel(C4gReservationObjectModel::class);
                    $sa_condition->setFunction('isSaturday');
                    $saConditionArr = [$sa_condition, $condition];

                    $saReservationTimeField = new C4GRadioGroupField();
                    $saReservationTimeField->setFieldName('beginTime');
                    $saReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                    $saReservationTimeField->setFormField(true);
                    $saReservationTimeField->setEditable(false);
                    $saReservationTimeField->setOptions(
                        C4gReservationObjectModel::getReservationTimes(
                            $reservationObjects,
                            $listType['id'],
                            '6',
                            date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getNextWeekday($reservationObjects, 6)),
                            0,
                            $this->showEndTime,
                            $this->showFreeSeats
                        ));
                    $saReservationTimeField->setMandatory(true);
                    $saReservationTimeField->setInitInvisible(true);
                    $saReservationTimeField->setSort(false);
                    $saReservationTimeField->setCondition($saConditionArr);
                    $saReservationTimeField->setCallOnChange(true);
                    $saReservationTimeField->setCallOnChangeFunction('setObjectId(this,' . $listType['id'] . ',' . $this->showDateTime . ')');
                    $saReservationTimeField->setAdditionalID($listType['id'] . '-006');
                    $saReservationTimeField->setNotificationField(true);
                    $saReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $saReservationTimeField->setTurnButton(true);
                    $saReservationTimeField->setRemoveWithEmptyCondition(true);
                    $saReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_' . $listType['id']);
                    $saReservationTimeField->setInitialValue($initialTime);
                    $saReservationTimeField->setTimeButtonSpecial(true);
                    $fieldList[] = $saReservationTimeField;

                }
            } else { //event
                //set reservationObjectType to event
                $reservationObjectTypeDBField = new C4GNumberField();
                $reservationObjectTypeDBField->setFieldName('reservationObjectType');
                $reservationObjectTypeDBField->setInitialValue('2');
                $reservationObjectTypeDBField->setDatabaseField(true);
                $reservationObjectTypeDBField->setFormField(false);
                $fieldList[] = $reservationObjectTypeDBField;

                $objects = [];
                foreach ($reservationObjects as $reservationObject) {

                    //ToDo Check Capacity
                    $objects[] = array(
                        'id' => $reservationObject->getId(),
                        'name' => $reservationObject->getNumber() ? '[' . $reservationObject->getNumber() . ']&nbsp;' . $reservationObject->getCaption() : $reservationObject->getCaption(),
                        'min' => $reservationObject->getDesiredCapacity()[0] ? $reservationObject->getDesiredCapacity()[0] : 1,
                        'max' => $reservationObject->getDesiredCapacity()[1] ? $reservationObject->getDesiredCapacity()[1] : 1
                    );
                }

                //save event id as reservation object
                $reservationObjectDBField = new C4GSelectField();
                $reservationObjectDBField->setFieldName('reservation_object');
                $reservationObjectDBField->setDatabaseField(true);
                $reservationObjectDBField->setFormField(false);
                $reservationObjectDBField->setOptions($objects);
                $reservationObjectDBField->setNotificationField(true);
                $fieldList[] = $reservationObjectDBField;

                //save beginDate
                $reservationBeginDateDBField = new C4GDateField();
                $reservationBeginDateDBField->setFieldName('beginDate');
                $reservationBeginDateDBField->setInitialValue(0);
                $reservationBeginDateDBField->setDatabaseField(true);
                $reservationBeginDateDBField->setFormField(false);
                $reservationBeginDateDBField->setMax(999999999999);
                $reservationBeginDateDBField->setNotificationField(true);
                $fieldList[] = $reservationBeginDateDBField;

                //save beginTime
                $reservationBeginTimeDBField = new C4GTimeField();
                $reservationBeginTimeDBField->setFieldName('beginTime');
                $reservationBeginTimeDBField->setInitialValue(0);
                $reservationBeginTimeDBField->setDatabaseField(true);
                $reservationBeginTimeDBField->setFormField(false);
                $reservationBeginTimeDBField->setMax(999999999999);
                $reservationBeginTimeDBField->setNotificationField(true);
                $fieldList[] = $reservationBeginTimeDBField;
            }

            //save endDate
            $reservationEndDateDBField = new C4GDateField();
            $reservationEndDateDBField->setFieldName('endDate');
            $reservationEndDateDBField->setInitialValue(0);
            $reservationEndDateDBField->setDatabaseField(true);
            $reservationEndDateDBField->setFormField(false);
            $reservationEndDateDBField->setMax(9999999999999);
            $reservationEndDateDBField->setNotificationField(true);
            $fieldList[] = $reservationEndDateDBField;

            //save endTime
            $reservationEndTimeDBField = new C4GTimeField();
            $reservationEndTimeDBField->setFieldName('endTime');
            $reservationEndTimeDBField->setInitialValue(0);
            $reservationEndTimeDBField->setDatabaseField(true);
            $reservationEndTimeDBField->setFormField(false);
            $reservationEndTimeDBField->setMax(9999999999999);
            $reservationEndTimeDBField->setNotificationField(true);
            $fieldList[] = $reservationEndTimeDBField;

            $reservationObjectField = new C4GSelectField();
            $reservationObjectField->setChosen(false);
            $reservationObjectField->setFieldName($isEvent ? 'reservation_object_event' : 'reservation_object');
            $reservationObjectField->setTitle($isEvent ? $GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object_event'] : $GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object']);
            $reservationObjectField->setDescription($isEvent ? '' : $GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_reservation_object']);
            $reservationObjectField->setFormField(true);
            $reservationObjectField->setEditable($isEvent && !$eventId);
            $reservationObjectField->setOptions($objects);
            $reservationObjectField->setMandatory(true);
            $reservationObjectField->setNotificationField(true);
            $reservationObjectField->setRangeField('desiredCapacity_' . $listType['id']);
            $reservationObjectField->setStyleClass($isEvent ? 'reservation-event-object displayReservationObjects' : 'reservation-object displayReservationObjects');
            $reservationObjectField->setWithEmptyOption(!$isEvent);
            $reservationObjectField->setInitialValue(-1);
            $reservationObjectField->setShowIfEmpty(true);
            $reservationObjectField->setDatabaseField(!$isEvent);
            $reservationObjectField->setEmptyOptionLabel($this->emptyOptionLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object_none']);
            $reservationObjectField->setCondition([$condition]);
            $reservationObjectField->setRemoveWithEmptyCondition(true);
            $reservationObjectField->setCallOnChange($isEvent);
            $reservationObjectField->setCallOnChangeFunction("checkEventFields(this)");
            $reservationObjectField->setAdditionalID($listType["id"]);
            $fieldList[] = $reservationObjectField;

            if ($isEvent) {
                foreach ($reservationObjects as $reservationObject) {
                    //$type_condition = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'reservation_type', $listType['id']);
                    $val_condition = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'reservation_object_event_' . $listType['id']. '-22' . $reservationObject->getId(), $reservationObject->getId());
                    $obj_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'reservation_object_event_' . $listType['id']. '-22' . $reservationObject->getId());
                    $obj_condition->setModel(C4gReservationObjectModel::class);
                    $obj_condition->setFunction('isEventObject');
                    $objConditionArr = [$obj_condition,$val_condition];

                    $reservationBeginDateField = new C4gDateField();
                    $reservationBeginDateField->setFlipButtonPosition(true);
                    $reservationBeginDateField->setFieldName('beginDateEvent');
                    $reservationBeginDateField->setCustomFormat($GLOBALS['TL_CONFIG']['dateFormat']);
                    $reservationBeginDateField->setCustomLanguage($GLOBALS['TL_LANGUAGE']);
                    $reservationBeginDateField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDateEvent']);
                    $reservationBeginDateField->setEditable(false);
                    $reservationBeginDateField->setComparable(false);
                    $reservationBeginDateField->setDatabaseField(false);
                    $reservationBeginDateField->setSortColumn(true);
                    $reservationBeginDateField->setSortSequence('de_datetime');
                    $reservationBeginDateField->setTableColumn(false);
                    $reservationBeginDateField->setFormField(true);
                    $reservationBeginDateField->setColumnWidth(10);
                    $reservationBeginDateField->setMandatory(false);
                    $reservationBeginDateField->setCondition($objConditionArr);
                    $reservationBeginDateField->setRemoveWithEmptyCondition(true);
                    $reservationBeginDateField->setInitialValue($reservationObject->getBeginDate());
                    $reservationBeginDateField->setNotificationField(true);
                    $reservationBeginDateField->setAdditionalID($listType['id'] . '-22' . $reservationObject->getId());
                    $reservationBeginDateField->setStyleClass('begindate-event');
                    $fieldList[] = $reservationBeginDateField;

                    $reservationEndDateField = new C4GDateField();
                    $reservationEndDateField->setFlipButtonPosition(true);
                    $reservationEndDateField->setFieldName('endDateEvent');
                    $reservationEndDateField->setCustomFormat($GLOBALS['TL_CONFIG']['dateFormat']);
                    $reservationEndDateField->setCustomLanguage($GLOBALS['TL_LANGUAGE']);
                    $reservationEndDateField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['endDateEvent']);
                    $reservationEndDateField->setEditable(false);
                    $reservationEndDateField->setComparable(false);
                    $reservationEndDateField->setSortColumn(true);
                    $reservationEndDateField->setSortSequence('de_datetime');
                    $reservationEndDateField->setDatabaseField(false);
                    $reservationEndDateField->setTableColumn(false);
                    $reservationEndDateField->setFormField(true);
                    $reservationEndDateField->setColumnWidth(10);
                    $reservationEndDateField->setMandatory(false);
                    $reservationEndDateField->setCondition($objConditionArr);
                    $reservationEndDateField->setRemoveWithEmptyCondition(true);
                    $reservationEndDateField->setInitialValue($reservationObject->getEndDate());
                    $reservationEndDateField->setNotificationField(true);
                    $reservationEndDateField->setAdditionalID($listType['id'] . '-22' . $reservationObject->getId());
                    $reservationEndDateField->setShowIfEmpty(false);
                    $reservationEndDateField->setStyleClass('enddate-event');
                    $fieldList[] = $reservationEndDateField;

                    //ToDo find better solution for empty beginTime
                    if ($reservationObject->getBeginTime() && date('H', $reservationObject->getBeginTime()) != '00') {
                        $reservationBeginTimeField = new C4GRadioGroupField();
                        $reservationBeginTimeField->setFieldName('beginTimeEvent');
                        $reservationBeginTimeField->setTitle($isEvent ? $GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeEvent'] : $GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                        $reservationBeginTimeField->setFormField(true);
                        $reservationBeginTimeField->setOptions(C4gReservationObjectModel::getReservationEventTime($reservationObject, $this->showEndTime, $this->showFreeSeats));
                        $reservationBeginTimeField->setMandatory(false);
                        $reservationBeginTimeField->setInitialValue($reservationObject->getBeginTime());
                        $reservationBeginTimeField->setDatabaseField(false);
                        $reservationBeginTimeField->setSort(false);
                        $reservationBeginTimeField->setCondition($objConditionArr);
                        $reservationBeginTimeField->setAdditionalID($listType['id'] . '-22' . $reservationObject->getId());
                        $reservationBeginTimeField->setNotificationField(true);
                        $reservationBeginTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                        $reservationBeginTimeField->setTurnButton(true);
                        $reservationBeginTimeField->setRemoveWithEmptyCondition(true);
                        $reservationBeginTimeField->setStyleClass('reservation_time_event_button reservation_time_event_button_' . $listType['id'] . '-22' . $reservationObject->getId() . C4gReservationObjectModel::getButtonStateClass($reservationObject));
                        $fieldList[] = $reservationBeginTimeField;
                    }

                    $locationId = $reservationObject->getLocation();
                    if ($locationId) {
                        $location = C4gReservationLocationModel::findByPk($locationId);
                        if ($location) {
                            $locationName = $location->name;
                            $street = $location->contact_street;
                            $postal = $location->contact_postal;
                            $city = $location->contact_city;
                            if ($street && $postal && $city) {
                                $locationName .= "&nbsp;(" . $street . ",&nbsp;" . $postal . "&nbsp;" . $city . ")";
                            }
                            $reservationLocationField = new C4GTextField();
                            $reservationLocationField->setFieldName('location');
                            $reservationLocationField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['eventlocation']);
                            $reservationLocationField->setFormField(true);
                            $reservationLocationField->setEditable(false);
                            $reservationLocationField->setDatabaseField(false);
                            $reservationLocationField->setCondition($objConditionArr);
                            $reservationLocationField->setInitialValue($locationName);
                            $reservationLocationField->setMandatory(false);
                            $reservationLocationField->setShowIfEmpty(false);
                            $reservationLocationField->setAdditionalID($listType['id'] . '-22' . $reservationObject->getId());
                            $reservationLocationField->setRemoveWithEmptyCondition(true);
                            $reservationLocationField->setNotificationField(true);
                            $reservationLocationField->setSimpleTextWithoutEditing(true);
                            $reservationLocationField->setStyleClass('eventdata eventdata_' . $listType['id'] . '-22' . $reservationObject->getId() . ' event-location');
                            $fieldList[] = $reservationLocationField;
                        }
                    }

                    $speakerIds = $reservationObject->getSpeaker();
                    $speakerStr = '';
                    if ($speakerIds && count($speakerIds) > 0) {
                        foreach ($speakerIds as $speakerId) {
                            $speaker = C4gReservationEventSpeakerModel::findByPk($speakerId);
                            if ($speaker) {
                                $speakerName = $speaker->title ? $speaker->title . '&nbsp;' . $speaker->firstname . '&nbsp;' . $speaker->lastname : $speaker->firstname . '&nbsp;' . $speaker->lastname;
                                $speakerStr = $speakerStr ? $speakerStr . ',&nbsp;' . $speakerName : $speakerName;
                            }
                        }

                        $speakerField = new C4GTextField();
                        $speakerField->setFieldName('speaker');
                        $speakerField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['speaker']);
                        $speakerField->setFormField(true);
                        $speakerField->setEditable(false);
                        $speakerField->setDatabaseField(false);
                        $speakerField->setCondition($objConditionArr);
                        $speakerField->setInitialValue($speakerStr);
                        $speakerField->setMandatory(false);
                        $speakerField->setShowIfEmpty(false);
                        $speakerField->setAdditionalID($listType['id'] . '-22' . $reservationObject->getId());
                        $speakerField->setRemoveWithEmptyCondition(true);
                        $speakerField->setNotificationField(true);
                        $speakerField->setSimpleTextWithoutEditing(true);
                        $speakerField->setStyleClass('eventdata eventdata_' . $listType['id'] . '-22' . $reservationObject->getId() . ' event-speaker');
                        $fieldList[] = $speakerField;
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
                        $fieldList[] = $topicField;

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
                        $fieldList[] = $audienceField;
                    }
                }
            }

            $includedParams = $listType['includedParams'];
            $includedParamsArr = [];

            if ($includedParams) {
                foreach ($includedParams as $paramId) {
                    $includedParam = C4gReservationParamsModel::findByPk($paramId);
                    if ($includedParam && $includedParam->caption && ($includedParam->price && $this->showPrices)) {
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
                $fieldList[] = $includedParams;
            }

            $params = $listType['additionalParams'];
            $additionalParamsArr = [];

            if ($params) {
                foreach ($params as $paramId) {
                    if ($paramId) {
                        $additionalParam = C4gReservationParamsModel::findByPk($paramId);
                        if ($additionalParam && $additionalParam->caption && ($additionalParam->price && $this->showPrices)) {
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
                $fieldList[] = $additionalParams;
            }
        }
        //end foreach type

        if (!$typelist || count($typelist) <= 0) {
            $reservationNoneTypeField = new C4GLabelField();
            $reservationNoneTypeField->setDatabaseField(false);
            $reservationNoneTypeField->setInitialValue($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_none']);
            $fieldList[] = $reservationNoneTypeField;
        }

        $bookerHeadline = new C4GHeadlineField();
        $bookerHeadline->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['headline_data']);
        $fieldList[] = $bookerHeadline;

        $salutation = [
            ['id' => 'man', 'name' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['man']],
            ['id' => 'woman', 'name' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['woman']],
            ['id' => 'various', 'name' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['various']],
        ];

        $additionaldatas = StringUtil::deserialize($this->hide_selection);

        //check mandatory fields
        $mandatoryFields = ['firstname' => true, 'lastname' => true, 'email' => true];
        foreach ($additionaldatas as $rowdata) {
            $rowField = $rowdata['additionaldatas'];
            if ($rowField == 'firstname') {
                $mandatoryFields['firstname'] = false;
            } else if ($rowField == 'lastname') {
                $mandatoryFields['lastname'] = false;
            } else if ($rowField == 'email') {
                $mandatoryFields['email'] = false;
            }
        }

        $addMandatoryFields = [];
        foreach ($mandatoryFields as $mandatoryField => $value) {
            if ($value) {
                $addMandatoryFields[] = ['additionaldatas' => $mandatoryField, 'initialValue' => '', 'mandatory' => true];
            }
        }

        $additionaldatas = array_merge($addMandatoryFields, $additionaldatas);

        $memberArr = [];
        $memberArr['company'] = '';
        $memberArr['firstname'] = '';
        $memberArr['lastname'] = '';
        $memberArr['email'] = '';
        $memberArr['street'] = '';
        $memberArr['postal'] = '';
        $memberArr['city'] = '';
        $memberArr['country'] = '';
        $memberArr['phone'] = '';
        $memberArr['dateOfBirth'] = '';

        if ($this->showMemberData && FE_USER_LOGGED_IN === true) {
            $member = FrontendUser::getInstance();
            if ($member) {
                $memberArr['id'] = $member->id ? $member->id : '';
                $memberArr['company'] = $member->company ? $member->company : '';
                $memberArr['firstname'] = $member->firstname ? $member->firstname : '';
                $memberArr['lastname'] = $member->lastname ? $member->lastname : '';
                $memberArr['email'] = $member->email ? $member->email : '';
                $memberArr['street'] = $member->street ? $member->street : '';
                $memberArr['postal'] = $member->postal ? $member->postal : '';
                $memberArr['city'] = $member->city ? $member->city : '';
                $memberArr['country'] = $member->country ? $member->country : '';
                $memberArr['phone'] = $member->phone ? $member->phone : '';
                $memberArr['dateOfBirth'] = $member->dateOfBirth ? $member->dateOfBirth : '';
            }
        }

        foreach ($additionaldatas as $rowdata) {
            $rowField = $rowdata['additionaldatas'];
            $initialValue = $rowdata['initialValue'];
            $rowMandatory = $rowdata['binding'];

            if ($rowField == "organisation") {
                $organisationField = new C4GTextField();
                $organisationField->setFieldName('organisation');
                $organisationField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['organisation']);
                $organisationField->setColumnWidth(10);
                $organisationField->setSortColumn(false);
                $organisationField->setTableColumn(true);
                $organisationField->setMandatory($rowMandatory);
                $organisationField->setNotificationField(true);
                $organisationField->setStyleClass('organisation');
                $organisationField->setInitialValue($initialValue);
                $fieldList[] = $organisationField;
            } else if ($rowField == "title") {
                $titleField = new C4GTextField();
                $titleField->setFieldName('title');
                $titleField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['title']);
                $titleField->setSortColumn(false);
                $titleField->setTableColumn(false);
                $titleField->setMandatory(false);
                $titleField->setNotificationField(true);
                $titleField->setStyleClass('title');
                $titleField->setInitialValue($initialValue);
                $fieldList[] = $titleField;
            } else if ($rowField == "salutation") {
                $salutationField = new C4GSelectField();
                $salutationField->setFieldName('salutation');
                $salutationField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['salutation']);
                $salutationField->setSortColumn(false);
                $salutationField->setTableColumn(false);
                $salutationField->setOptions($salutation);
                $salutationField->setMandatory($rowMandatory);
                $salutationField->setNotificationField(true);
                $salutationField->setStyleClass('salutation');
                $salutationField->setInitialValue($initialValue);
                $fieldList[] = $salutationField;
            } else if ($rowField == "firstname") {
                $firstnameField = new C4GTextField();
                $firstnameField->setFieldName('firstname');
                $firstnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname']);
                $firstnameField->setColumnWidth(10);
                $firstnameField->setSortColumn(false);
                $firstnameField->setTableColumn(true);
                $firstnameField->setMandatory(true);
                $firstnameField->setNotificationField(true);
                $firstnameField->setStyleClass('firstname');
                $firstnameField->setInitialValue($initialValue ? $initialValue : $memberArr['firstname']);
                $fieldList[] = $firstnameField;
            } else if ($rowField == "lastname") {
                $lastnameField = new C4GTextField();
                $lastnameField->setFieldName('lastname');
                $lastnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname']);
                $lastnameField->setColumnWidth(10);
                $lastnameField->setSortColumn(false);
                $lastnameField->setTableColumn(true);
                $lastnameField->setMandatory(true);
                $lastnameField->setNotificationField(true);
                $lastnameField->setStyleClass('lastname');
                $lastnameField->setInitialValue($initialValue ? $initialValue : $memberArr['lastname']);
                $fieldList[] = $lastnameField;
            } else if ($rowField == "email") {
                $emailField = new C4GEmailField();
                $emailField->setFieldName('email');
                $emailField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['email']);
                $emailField->setColumnWidth(10);
                $emailField->setSortColumn(false);
                $emailField->setTableColumn(false);
                $emailField->setMandatory(true);
                $emailField->setNotificationField(true);
                $emailField->setStyleClass('email');
                $emailField->setInitialValue($initialValue ? $initialValue : $memberArr['email']);
                $fieldList[] = $emailField;
            } else if ($rowField == "phone") {
                $phoneField = new C4GTelField();
                $phoneField->setFieldName('phone');
                $phoneField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['phone']);
                $phoneField->setColumnWidth(10);
                $phoneField->setSortColumn(false);
                $phoneField->setMandatory($rowMandatory);
                $phoneField->setTableColumn(false);
                $phoneField->setNotificationField(true);
                $phoneField->setStyleClass('phone');
                $phoneField->setInitialValue($initialValue ? $initialValue : $memberArr['phone']);
                $fieldList[] = $phoneField;
            } else if ($rowField == "address") {
                $addressField = new C4GTextField();
                $addressField->setFieldName('address');
                $addressField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['address']);
                $addressField->setColumnWidth(60);
                $addressField->setSortColumn(false);
                $addressField->setTableColumn(false);
                $addressField->setMandatory($rowMandatory);
                $addressField->setNotificationField(true);
                $addressField->setStyleClass('address');
                $addressField->setInitialValue($initialValue ? $initialValue : $memberArr['street']);
                $fieldList[] = $addressField;
            } else if ($rowField == "postal") {
                $postalField = new C4GPostalField();
                $postalField->setFieldName('postal');
                $postalField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['postal']);
                $postalField->setColumnWidth(60);
                $postalField->setSize(5); //international 32
                $postalField->setSortColumn(false);
                $postalField->setTableColumn(false);
                $postalField->setMandatory($rowMandatory);
                $postalField->setNotificationField(true);
                $postalField->setStyleClass('postal');
                $postalField->setInitialValue($initialValue ? $initialValue : $memberArr['postal']);
                $fieldList[] = $postalField;
            } else if ($rowField == "city") {
                $cityField = new C4GTextField();
                $cityField->setFieldName('city');
                $cityField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['city']);
                $cityField->setColumnWidth(60);
                $cityField->setSortColumn(false);
                $cityField->setTableColumn(false);
                $cityField->setMandatory($rowMandatory);
                $cityField->setNotificationField(true);
                $cityField->setStyleClass('city');
                $cityField->setInitialValue($initialValue ? $initialValue : $memberArr['city']);
                $fieldList[] = $cityField;
            } else if ($rowField == "dateOfBirth") {
                $birthDateField = new C4GDateField();
                $birthDateField->setFlipButtonPosition(true);
                $birthDateField->setFieldName('dateOfBirth');
                $birthDateField->setMinDate(strtotime('-120 year'));

                $year = date('Y');
                $birthDateField->setMaxDate(strtotime($year . '-12-31'));
                $birthDateField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['dateOfBirth']);
                $birthDateField->setColumnWidth(60);
                $birthDateField->setSortColumn(false);
                $birthDateField->setTableColumn(false);
                $birthDateField->setSortSequence('de_datetime');
                $birthDateField->setMandatory($rowMandatory);
                $birthDateField->setNotificationField(true);
                $birthDateField->setStyleClass('dateOfBirth');
                $birthDateField->setInitialValue($initialValue ? $initialValue : $memberArr['dateOfBirth']);
                $birthDateField->setDatePickerByBrowser(true);
                $fieldList[] = $birthDateField;
            } else if ($rowField == "salutation2") {
                $salutationField2 = new C4GSelectField();
                $salutationField2->setFieldName('salutation2');
                $salutationField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['salutation2']);
                $salutationField2->setSortColumn(false);
                $salutationField2->setTableColumn(false);
                $salutationField2->setOptions($salutation);
                $salutationField2->setMandatory($rowMandatory);
                $salutationField2->setNotificationField(true);
                $salutationField2->setStyleClass('salutation');
                $salutationField2->setInitialValue($initialValue);
                $fieldList[] = $salutationField2;
            } else if ($rowField == "title2") {
                $titleField2 = new C4GTextField();
                $titleField2->setFieldName('title2');
                $titleField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['title2']);
                $titleField2->setSortColumn(false);
                $titleField2->setTableColumn(false);
                $titleField2->setMandatory(false);
                $titleField2->setNotificationField(true);
                $titleField2->setStyleClass('title');
                $titleField2->setInitialValue($initialValue);
                $fieldList[] = $titleField2;
            } else if ($rowField == "firstname2") {
                $firstnameField2 = new C4GTextField();
                $firstnameField2->setFieldName('firstname2');
                $firstnameField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname2']);
                $firstnameField2->setColumnWidth(10);
                $firstnameField2->setSortColumn(false);
                $firstnameField2->setTableColumn(true);
                $firstnameField2->setMandatory($rowMandatory);
                $firstnameField2->setNotificationField(true);
                $firstnameField2->setStyleClass('firstname');
                $firstnameField2->setInitialValue($initialValue);
                $fieldList[] = $firstnameField2;
            } else if ($rowField == "lastname2") {
                $lastnameField2 = new C4GTextField();
                $lastnameField2->setFieldName('lastname2');
                $lastnameField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname2']);
                $lastnameField2->setColumnWidth(10);
                $lastnameField2->setSortColumn(false);
                $lastnameField2->setTableColumn(true);
                $lastnameField2->setMandatory($rowMandatory);
                $lastnameField2->setNotificationField(true);
                $lastnameField2->setStyleClass('lastname');
                $lastnameField2->setInitialValue($initialValue);
                $fieldList[] = $lastnameField2;
            } else if ($rowField == "email2") {
                $emailField2 = new C4GEmailField();
                $emailField2->setFieldName('email2');
                $emailField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['email2']);
                $emailField2->setColumnWidth(10);
                $emailField2->setSortColumn(false);
                $emailField2->setTableColumn(false);
                $emailField2->setMandatory($rowMandatory);
                $emailField2->setNotificationField(true);
                $emailField2->setStyleClass('email');
                $emailField2->setInitialValue($initialValue);
                $fieldList[] = $emailField2;
            } else if ($rowField == "organisation2") {
                $organisationField2 = new C4GTextField();
                $organisationField2->setFieldName('organisation2');
                $organisationField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['organisation2']);
                $organisationField2->setColumnWidth(10);
                $organisationField2->setSortColumn(false);
                $organisationField2->setTableColumn(true);
                $organisationField2->setMandatory($rowMandatory);
                $organisationField2->setNotificationField(true);
                $organisationField2->setStyleClass('organisation');
                $organisationField2->setInitialValue($initialValue);
                $fieldList[] = $organisationField2;
            } else if ($rowField == "phone2") {
                $phoneField2 = new C4GTelField();
                $phoneField2->setFieldName('phone2');
                $phoneField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['phone2']);
                $phoneField2->setColumnWidth(10);
                $phoneField2->setSortColumn(false);
                $phoneField2->setMandatory($rowMandatory);
                $phoneField2->setTableColumn(false);
                $phoneField2->setNotificationField(true);
                $phoneField2->setStyleClass('phone');
                $phoneField2->setInitialValue($initialValue);
                $fieldList[] = $phoneField2;
            } else if ($rowField == "address2") {
                $addressField2 = new C4GTextField();
                $addressField2->setFieldName('address2');
                $addressField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['address2']);
                $addressField2->setColumnWidth(60);
                $addressField2->setSortColumn(false);
                $addressField2->setTableColumn(false);
                $addressField2->setMandatory($rowMandatory);
                $addressField2->setNotificationField(true);
                $addressField2->setStyleClass('address');
                $addressField2->setInitialValue($initialValue);
                $fieldList[] = $addressField2;
            } else if ($rowField == "postal2") {
                $postalField2 = new C4GPostalField();
                $postalField2->setFieldName('postal2');
                $postalField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['postal2']);
                $postalField2->setColumnWidth(60);
                $postalField2->setSize(5); //international 32
                $postalField2->setSortColumn(false);
                $postalField2->setTableColumn(false);
                $postalField2->setMandatory($rowMandatory);
                $postalField2->setNotificationField(true);
                $postalField2->setStyleClass('postal');
                $postalField2->setInitialValue($initialValue);
                $fieldList[] = $postalField2;
            } else if ($rowField == "city2") {
                $cityField2 = new C4GTextField();
                $cityField2->setFieldName('city2');
                $cityField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['city2']);
                $cityField2->setColumnWidth(60);
                $cityField2->setSortColumn(false);
                $cityField2->setTableColumn(false);
                $cityField2->setMandatory($rowMandatory);
                $cityField2->setNotificationField(true);
                $cityField2->setStyleClass('city');
                $cityField2->setInitialValue($initialValue);
                $fieldList[] = $cityField2;
            } else if ($rowField == "comment") {
                $commentField = new C4GTextareaField();
                $commentField->setFieldName('comment');
                $commentField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['comment']);
                $commentField->setColumnWidth(60);
                $commentField->setSortColumn(false);
                $commentField->setTableColumn(false);
                $commentField->setMandatory($rowMandatory);
                $commentField->setNotificationField(true);
                $commentField->setStyleClass('comment');
                $commentField->setInitialValue($initialValue);
                $fieldList[] = $commentField;
            } else if ($rowField == "additionalHeadline") {
                $headlineField = new C4GHeadlineField();
                $headlineField->setTitle($initialValue);
                $fieldList[] = $headlineField;
            } else if ($rowField == "participants") {
                $participantsKey = new C4GKeyField();
                $participantsKey->setFieldName('id');
                $participantsKey->setComparable(false);
                $participantsKey->setEditable(false);
                $participantsKey->setHidden(true);
                $participantsKey->setFormField(true);
                $participantsForeign = new C4GForeignKeyField();
                $participantsForeign->setFieldName('pid');
                $participantsForeign->setHidden(true);
                $participantsForeign->setFormField(true);
                $participants = [];

                $firstnameField = new C4GTextField();
                $firstnameField->setFieldName('firstname');
                $firstnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname']);
                $firstnameField->setColumnWidth(10);
                $firstnameField->setSortColumn(false);
                $firstnameField->setTableColumn(true);
                $firstnameField->setMandatory($specialParticipantMechanism ? $rowMandatory : true);
                $firstnameField->setNotificationField(false);
                $participants[] = $firstnameField;

                $lastnameField = new C4GTextField();
                $lastnameField->setFieldName('lastname');
                $lastnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname']);
                $lastnameField->setColumnWidth(10);
                $lastnameField->setSortColumn(false);
                $lastnameField->setTableColumn(true);
                $lastnameField->setMandatory($specialParticipantMechanism ? $rowMandatory : true);
                $lastnameField->setNotificationField(false);
                $participants[] = $lastnameField;

                $emailField = new C4GEmailField();
                $emailField->setFieldName('email');
                $emailField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['email']);
                $emailField->setColumnWidth(10);
                $emailField->setSortColumn(false);
                $emailField->setTableColumn(false);
                $emailField->setMandatory($rowMandatory);
                $emailField->setNotificationField(false);
                $participants[] = $emailField;

                foreach ($typelist as $type) {
                    $condition = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'reservation_type', $type['id']);

                    $params = $type['participantParams'];
                    $participantParamsArr = [];

                    if ($params) {
                        foreach ($params as $paramId) {
                            if ($paramId) {
                                $participantParam = C4gReservationParamsModel::findByPk($paramId);
                                if ($participantParam && $participantParam->caption && ($participantParam->price && $this->showPrices)) {
                                    $participantParamsArr[] = ['id' => $paramId, 'name' => $participantParam->caption . "<span class='price'>&nbsp;(+" . number_format($participantParam->price, 2, ',', '.') . " €)</span>"];
                                } else if ($participantParam && $participantParam->caption) {
                                    $participantParamsArr[] = ['id' => $paramId, 'name' => $participantParam->caption];
                                }
                            }
                        }
                    }

                    if (count($participantParamsArr) > 0) {
                        $participantParamField = new C4GMultiCheckboxField();
                        $participantParamField->setFieldName('participant_params');
                        $participantParamField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['participant_params']);
                        $participantParamField->setFormField(true);
                        $participantParamField->setEditable(true);
                        $participantParamField->setOptions($participantParamsArr);
                        $participantParamField->setMandatory(false);
                        $participantParamField->setModernStyle(false);
                        $participantParamField->setStyleClass('participant-params');
                        $participantParamField->setNotificationField(false);

                        $participants[] = $participantParamField;
                    }

                    if (!$specialParticipantMechanism) {
                        $reservationParticipants = new C4GSubDialogField();
                        $reservationParticipants->setFieldName('participants');
                        $reservationParticipants->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['participants']);
                        $reservationParticipants->setAddButton($GLOBALS['TL_LANG']['fe_c4g_reservation']['addParticipant']);
                        $reservationParticipants->setRemoveButton($GLOBALS['TL_LANG']['fe_c4g_reservation']['removeParticipant']);
                        $reservationParticipants->setTable('tl_c4g_reservation_participants');
                        $reservationParticipants->addFields($participants);
                        $reservationParticipants->setKeyField($participantsKey);
                        $reservationParticipants->setForeignKeyField($participantsForeign);
                        $reservationParticipants->setMandatory($rowMandatory);
                        $reservationParticipants->setRemoveButtonMessage($GLOBALS['TL_LANG']['fe_c4g_reservation']['removeParticipantMessage']);

                        $reservationParticipants->setMin($minCapacity);
                        if ($maxCapacity) {
                            $reservationParticipants->setMax($maxCapacity);
                        }

                        $reservationParticipants->setNotificationField(false);
                        $reservationParticipants->setShowFirstDataSet(true);
                        $reservationParticipants->setParentFieldList($fieldList);
                        $reservationParticipants->setDelimiter('§');
                        $reservationParticipants->setCondition(array($condition));
                        $reservationParticipants->setRemoveWithEmptyCondition(true);
                        $reservationParticipants->setAdditionalID($listType['id']);

                        $fieldList[] = $reservationParticipants;
                    } else {
                        for ($i = $minCapacity; $i <= $maxCapacity; $i++) {
                            $newCondition = [];

                            //$newCondition[] = $condition;
                            $newCondition[] = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'desiredCapacity_'.$listType['id'], $i);

                            $reservationParticipants = new C4GSubDialogField();
                            $reservationParticipants->setFieldName('participants');
                            $reservationParticipants->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['additionalParticipants']);
                            $reservationParticipants->setShowButtons(false);
                            $reservationParticipants->setTable('tl_c4g_reservation_participants');
                            $reservationParticipants->addFields($participants);
                            $reservationParticipants->setKeyField($participantsKey);
                            $reservationParticipants->setForeignKeyField($participantsForeign);
                            $reservationParticipants->setMandatory($rowMandatory);

                            $reservationParticipants->setMin($minCapacity);
                            if ($maxCapacity) {
                                $reservationParticipants->setMax($maxCapacity);
                            }

                            $reservationParticipants->setNotificationField(false);

                            $reservationParticipants->setShowDataSetsByCount($i-1);
                            $reservationParticipants->setParentFieldList($fieldList);
                            $reservationParticipants->setDelimiter('§');
                            $reservationParticipants->setCondition($newCondition);
                            $reservationParticipants->setRemoveWithEmptyCondition(true);
                            $reservationParticipants->setAdditionalID($listType['id'] . '-' . ($i-1));

                            $fieldList[] = $reservationParticipants;
                        }
                    }
                }

                $reservationParticipantList = new C4GMultiSelectField();
                $reservationParticipantList->setFieldName('participantList');
                $reservationParticipantList->setDatabaseField(false);
                $reservationParticipantList->setNotificationField(true);
                $reservationParticipantList->setFormField(false);
                $fieldList[] = $reservationParticipantList;
            }
        }

        $reservationIdField = new C4GTextField();
        $reservationIdField->setFieldName('reservation_id');
        $reservationIdField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_id']);
        $reservationIdField->setDescription($GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_reservation_id']);
        $reservationIdField->setColumnWidth(10);
        $reservationIdField->setSortColumn(false);
        $reservationIdField->setTableColumn(true);
        $reservationIdField->setMandatory(false);
        $reservationIdField->setInitialValue(C4GBrickCommon::getUUID());
        //$reservationIdField->setRandomValue(C4GBrickCommon::getUUID());
        $reservationIdField->setTableRow(false);
        $reservationIdField->setEditable(false);
        $reservationIdField->setUnique(true);
        $reservationIdField->setNotificationField(true);
        $reservationIdField->setDbUnique(true);
        $reservationIdField->setSimpleTextWithoutEditing(false); //!!!
        $reservationIdField->setDatabaseField(true);
        $reservationIdField->setDbUniqueResult($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_id_exists']);
        //$reservationIdField->setDbUniqueAdditionalCondition("tl_c4g_reservation.cancellation <> '1' AND tl_c4g_reservation.beginDate > UNIX_TIMESTAMP(NOW())");
        $reservationIdField->setStyleClass('reservation-id');
        $fieldList[] = $reservationIdField;

        if ($this->privacy_policy_text) {
            $privacyPolicyText = new C4GTextField();
            $privacyPolicyText->setSimpleTextWithoutEditing(true);
            $privacyPolicyText->setFieldName('privacy_policy_text');
            $privacyPolicyText->setInitialValue(\Contao\Controller::replaceInsertTags($this->privacy_policy_text));
            $privacyPolicyText->setSize(4);
            $privacyPolicyText->setTableColumn(false);
            $privacyPolicyText->setEditable(false);
            $privacyPolicyText->setDatabaseField(false);
            $privacyPolicyText->setMandatory(false);
            $privacyPolicyText->setNotificationField(false);
            $privacyPolicyText->setStyleClass('privacy-policy-text');
            $fieldList[] = $privacyPolicyText;
        }

        $agreedField = new C4GCheckboxField();
        $agreedField->setFieldName('agreed');
        $agreedField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['agreed']);
        if ($this->privacy_policy_site) {
            $href = \Contao\Controller::replaceInsertTags('{{link_url::' . $this->privacy_policy_site . '}}');
            $agreedField->setDescription('<span class="c4g_field_description_text">' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed'] . '</span> <a href="' . $href . '" target="_blank" rel="noopener">' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed_link_text'] . '</a>');
        } else {
            $agreedField->setDescription($GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed_without_link']);
        }
        $agreedField->setTableRow(false);
        $agreedField->setColumnWidth(5);
        $agreedField->setSortColumn(false);
        $agreedField->setTableColumn(false);
        $agreedField->setMandatory(true);
        $agreedField->setNotificationField(true);
        $agreedField->setStyleClass('agreed');
        $agreedField->setWithoutDescriptionLineBreak(true);
        $fieldList[] = $agreedField;

        $clickButton = new C4GBrickButton(
            C4GBrickConst::BUTTON_CLICK,
            $this->reservationButtonCaption ? \Contao\Controller::replaceInsertTags($this->reservationButtonCaption) : $GLOBALS['TL_LANG']['fe_c4g_reservation']['button_reservation'],
            $visible = true,
            $enabled = true,
            $action = '',
            $accesskey = '',
            $defaultByEnter = true);

        $buttonField = new C4GButtonField($clickButton);
        $buttonField->setOnClickType(C4GBrickConst::ONCLICK_TYPE_SERVER);
        $buttonField->setOnClick('clickReservation');
        $buttonField->setWithoutLabel(true);
        $fieldList[] = $buttonField;

        if (!$isEvent) {
            $location_name = new C4GTextField();
            $location_name->setFieldName('location');
            $location_name->setSortColumn(false);
            $location_name->setFormField(false);
            $location_name->setTableColumn(true);
            $location_name->setNotificationField(true);
            $fieldList[] = $location_name;
        }

        $contact_name = new C4GTextField();
        $contact_name->setFieldName('contact_name');
        $contact_name->setSortColumn(false);
        $contact_name->setFormField(false);
        $contact_name->setTableColumn(true);
        $contact_name->setNotificationField(true);
        $fieldList[] = $contact_name;

        $contact_phone = new C4GTelField();
        $contact_phone->setFieldName('contact_phone');
        $contact_phone->setFormField(false);
        $contact_phone->setTableColumn(false);
        $contact_phone->setNotificationField(true);
        $fieldList[] = $contact_phone;

        $contact_email = new C4GEmailField();
        $contact_email->setFieldName('contact_email');
        $contact_email->setTableColumn(false);
        $contact_email->setFormField(false);
        $contact_email->setNotificationField(true);
        $fieldList[] = $contact_email;


        $contact_street = new C4GTextField();
        $contact_street->setFieldName('contact_street');
        $contact_street->setTableColumn(false);
        $contact_street->setFormField(false);
        $contact_street->setNotificationField(true);
        $fieldList[] = $contact_street;


        $contact_postal = new C4GTextField();
        $contact_postal->setFieldName('contact_postal');
        $contact_postal->setFormField(false);
        $contact_postal->setTableColumn(false);
        $contact_postal->setNotificationField(true);
        $fieldList[] = $contact_postal;


        $contact_city = new C4GTextField();
        $contact_city->setFieldName('contact_city');
        $contact_city->setTableColumn(false);
        $contact_city->setFormField(false);
        $contact_city->setNotificationField(true);
        $fieldList[] = $contact_city;

        $contact_city = new C4GTextField();
        $contact_city->setFieldName('icsFilename');
        $contact_city->setTableColumn(false);
        $contact_city->setFormField(false);
        $contact_city->setNotificationField(true);
        $fieldList[] = $contact_city;

        $memberId = new C4GTextField();
        $memberId->setFieldName('member_id');
        $memberId->setTableColumn(true);
        $memberId->setFormField(false);
        $memberId->setNotificationField(false);
        $fieldList[] = $memberId;

        $groupId = new C4GTextField();
        $groupId->setFieldName('group_id');
        $groupId->setTableColumn(true);
        $groupId->setFormField(false);
        $groupId->setNotificationField(false);
        $fieldList[] = $groupId;

        $this->fieldList = $fieldList;

        //return $fieldList;
    }

    /**
     * @param $values
     * @param $putVars
     * @return array|false|mixed|string|string[]|void
     *
     */
    public function clickReservation($values, $putVars)
    {
        $type = $putVars['reservation_type'];
        $reservationType = $this->Database->prepare("SELECT * FROM tl_c4g_reservation_type WHERE id=? AND published='1'")
            ->execute($type);

        if ($reservationType && $reservationType->notification_type) {
            $this->getDialogParams()->setNotificationType($reservationType->notification_type);
            $this->notification_type = $reservationType->notification_type;
        }

        $newFieldList = [];
        $removedFromList = [];

        $reservationIdKey = 0;
        foreach ($this->getFieldList() as $key=>$field) {
            if ($field->getFieldName() === 'reservation_id') {
                $reservationIdKey = $key;
            }
            $additionalId = $field->getAdditionalID();
            if ($additionalId && (($additionalId != $type) && (strpos($additionalId, strval($type.'-')) !== 0))) {
                unset($putVars[$field->getFieldName()."_".$additionalId]);
                continue;
            } else if ($additionalId) {
                $removedFromList[$field->getFieldName()] = $additionalId;
                unset($putVars[$field->getFieldName()]);
            }

            $isEvent = $reservationType->reservationObjectType && $reservationType->reservationObjectType === '2' ? true : false;

            if (!$isEvent && ($field->getFieldName() == "beginTime")) {
                foreach ($putVars as $key => $value) {
                    if (strpos($key, "beginTime_".$type) !== false) {
                        $additionalIdPostParam = substr($key, (strlen("beginTime_".$type)));
                    }
                }
                if ($additionalId != $type.$additionalIdPostParam) {
                    continue;
                }
            }

            if ($isEvent) {
                $key = "reservation_object_event_" . $type;
                $resObject = $putVars[$key];
                $reservationObject = $this->Database->prepare("SELECT * FROM tl_calendar_events WHERE id=? AND published='1'")
                    ->execute($resObject);

                foreach ($putVars as $key => $value) {
                    if (strpos($key, strval($type.'-22'))) {
                        if (!strpos($key, strval($type.'-22'.$resObject))) {
                            unset($putVars[$key]);
                        }
                    }
                }

                if ($additionalId && (($additionalId != $type) && (strpos($additionalId, strval($type.'-22')) !== 0))) {
                    if (strpos($additionalId, strval($type.'-22'.$resObject)) === 0) {
                        unset($putVars[$field->getFieldName()."_".$additionalId]);
                        continue;
                    }
                }
            } else {
                $key = "reservation_object_" . $type;
                $resObject = $putVars[$key];
                $reservationObject = $this->Database->prepare("SELECT * FROM tl_c4g_reservation_object WHERE id=? AND published='1'")
                    ->execute($resObject);
            }

            if ($field->getFieldName() && (!$removedFromList[$field->getFieldName()] || ($removedFromList[$field->getFieldName()] == $field->getAdditionalId()))) {
                $newFieldList[] = $field;
            }

            if (!$field->isEditable() && !$field->isDatabaseField() && $field->getInitialValue() && $field->isNotificationField()) {
                if ($field->getAdditionalId()) {
                    $putVars[$field->getFieldName().'_'.$field->getAdditionalId()] = $field->getInitialValue();
                } else {
                    $putVars[$field->getFieldName()] = $field->getInitialValue();
                }
            }
        }

        $reservationId = $putVars['reservation_id'];

        if ($isEvent) {
            $putVars['reservationObjectType'] = '2';
            $objectId = $reservationObject->id; //$putVars['reservation_object_event_' . $type];
            $t = 'tl_c4g_reservation';
            $arrColumns = array("$t.reservation_object=$objectId AND $t.reservationObjectType='2' AND NOT $t.cancellation='1'");
            $arrValues = array();
            $arrOptions = array();
            $reservations = C4gReservationModel::findBy($arrColumns, $arrValues, $arrOptions);

            $reservationCount = count($reservations);

            $reservationEventObjects = C4gReservationEventModel::findBy('pid', $objectId);

            if ($reservationEventObjects && (count($reservationEventObjects) > 1)) {
                C4gLogModel::addLogEntry('reservation', 'There are more than one event connections. Check Event: '.$objectId);
                return ['usermessage' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['error']];
            }

            $reservationEventObject = is_array($reservationEventObjects) && count($reservationEventObjects) > 0 ? $reservationEventObjects[0] : $reservationEventObjects;

            $factor = 1;
            $desiredCapacity =  $reservationEventObject && $reservationEventObject->maxParticipants ? ($reservationEventObject->maxParticipants * $factor) : 0;

            if ($desiredCapacity && ($reservationCount >= $desiredCapacity)) {
                return ['usermessage' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['fully_booked']];
            }

            $putVars['reservation_object'] = $objectId;

            //ToDo implement all event possibilities
            $beginDate = $reservationObject->startDate ? intval($reservationObject->startDate) : 0;
            $beginTime = $reservationObject->startTime ? intval($reservationObject->startTime) : 0;
            $endDate   = $reservationObject->endDate ? intval($reservationObject->endDate) : 0;
            $endTime   = $reservationObject->endTime ? intval($reservationObject->endTime) : 0;

            $putVars['beginDate'] = $beginDate ? date($GLOBALS['TL_CONFIG']['dateFormat'], $beginDate) : $beginDate;
            $putVars['beginTime'] = $beginTime ? date($GLOBALS['TL_CONFIG']['timeFormat'], $beginTime) : $beginTime;
            $putVars['endDate'] = $endDate ? date($GLOBALS['TL_CONFIG']['dateFormat'], $endDate) : $endDate;
            $putVars['endTime'] = $endTime ? date($GLOBALS['TL_CONFIG']['timeFormat'], $endTime) : $endTime;
       } else {
            $putVars['reservationObjectType'] = '1';

            //check duplicate reservation id
            $reservations = C4gReservationModel::findBy("reservation_id", $reservationId);
            $reservationCount = is_array($reservations) ? count($reservations) : 0;
            if ($reservationCount >= 1) {
                C4gLogModel::addLogEntry('reservation', 'Duplicate reservation ID detected.');
                return ['usermessage' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['duplicate_reservation_id']];
            }

            //check duplicate bookings
            if ($reservationObject && $reservationObject->id && C4gReservationObjectModel::preventDublicateBookings($reservationType,$reservationObject,$putVars)) {
                C4gLogModel::addLogEntry('reservation', 'Duplicate booking detected.');
                return ['usermessage' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['duplicate_booking']];
            }

            if (!$reservationObject || !$reservationObject->id) {
                return ['usermessage' => $GLOBALS['TL_LANG']['FE_C4G_DIALOG']['USERMESSAGE_MANDATORY']];
            }

            $beginDate = $putVars['beginDate_'.$type];

            $beginTime = 0;
            $timeKey = false;
            foreach ($putVars as $key => $value) {
                if (strpos($key, "beginTime_".$type) !== false) {
                    if ($value) {
                        if (strpos($value, '#') !== false) {
                            $newValue = substr($value,0, strpos($value, '#')); //remove frontend duration
                        }

                        $beginTime = $newValue ?: $value;
                        $timeKey = $key;
                        break;
                    }
                }
            }

            $time_interval = $reservationObject->time_interval;
            $min_residence_time = $reservationObject->min_residence_time;
            $max_residence_time = $reservationObject->max_residence_time;

            switch ($reservationType->periodType) {
                case 'minute':
                    $interval = 60;
                    break;
                case 'hour':
                    $interval = 3600;
                    break;
                default: '';
            }

            $duration = $putVars['duration'];
            if ($duration && ((!$min_residence_time || ($duration >= $min_residence_time) && (!$max_residence_time || ($duration <= $max_residence_time))))) {
                //$duration = $duration;
            } else {
                $duration = $time_interval;
            }

            $duration = $duration * $interval;
            $endTime = $beginTime + $duration;

            $putVars['endDate'] = $putVars['beginDate_'.$type]; //ToDo multiple days
            $putVars['endTime'] = date($GLOBALS['TL_CONFIG']['timeFormat'],$endTime);

            //check nxt day times
            $bday = $putVars['beginDate_'.$type];
            $nextDay = strtotime("+1 day", strtotime($bday));
            if (!$reservationType->directBooking && $beginTime >= 86400) {
                $putVars['beginDate_'.$type] = date($GLOBALS['TL_CONFIG']['dateFormat'], $nextDay);
                $putVars[$timeKey] = ($beginTime-86400);
            } else {
                $putVars[$timeKey] = $beginTime;
            }

            if (!$reservationType->directBooking && ($endTime >= 86400)) {
                $putVars['endDate'] = date($GLOBALS['TL_CONFIG']['dateFormat'], $nextDay);
                $putVars['endTime'] = date($GLOBALS['TL_CONFIG']['timeFormat'], ($endTime-86400));
            }

            if ($reservationType->directBooking) {
                $objDate = new Date(date($GLOBALS['TL_CONFIG']['timeFormat'],$beginTime), Date::getFormatFromRgxp('time'));
                $directTime = $objDate->tstamp;
                $putVars[$timeKey] = $directTime;
                //$putVars['beginDate_'.$type] = date($GLOBALS['TL_CONFIG']['dateFormat'], $nextDay);
            }
        }

        $locationId = 0;
        if ($isEvent && $reservationEventObject->location) {
            $locationId = $reservationEventObject->location;
        } else If (!$isEvent && $reservationObject->location) {
            $locationId = $reservationObject->location;
        } else {
            $locationId = $reservationType->location;
        }

        $location = null;
        if ($locationId > 0) {
            $location = C4gReservationLocationModel::findByPk($locationId);
            if ($location) {
                $locationName = $location->name;
                $contact_name = $location->contact_name;
                $contact_email = $location->contact_email;
                $vcard = $location->vcard_show;
                if ($vcard) {
                    $contact_street = $location->contact_street;
                    $contact_phone = $location->contact_phone;
                    $contact_postal = $location->contact_postal;
                    $contact_city = $location->contact_city;
                } else {
                    $contact_street = $location->contact_street;
                    $contact_phone = $location->contact_phone;
                    $contact_postal = $location->contact_postal;
                    $contact_city = $location->contact_city;
                }

                if (!$isEvent) {
                    $putVars['location'] = $locationName;
                }

                $putVars['contact_name'] = $contact_name;
                $putVars['contact_phone'] = $contact_phone;
                $putVars['contact_email'] = $contact_email;
                $putVars['contact_street'] = $contact_street;
                $putVars['contact_postal'] = $contact_postal;
                $putVars['contact_city'] = $contact_city;
            }
        }

        $memberId = 0;
        if (FE_USER_LOGGED_IN === true) {
            $member = FrontendUser::getInstance();
            if ($member) {
                $memberId = $member->id;
            }
        }

        $putVars['member_id'] = $reservationType->member_id ? $reservationType->member_id : $memberId;
        $putVars['group_id'] = $reservationType->group_id;

        $participantsArr = [];
        foreach ($putVars as $key => $value) {
            if ($this->specialParticipantMechanism) {
                $desiredCapacity = $putVars['desiredCapacity_'.$reservationType->id];
                if ($desiredCapacity) {
                    $extId = $desiredCapacity-1;
                    if (strpos($key,"participants_".$type."-".$extId."§") !== false) {
                        $keyArr = explode("§", $key);
                        if (trim($keyArr[0]) && trim($keyArr[1]) && trim($value)) {
                            $keyPos = strpos(trim($keyArr[0]), "-".$extId);
                            if ($keyPos) {
                                $keyArr[0] = substr(trim($keyArr[0]),0, $keyPos);
                                //$putVars[$keyArr[0].'~'.$keyArr[1].'~'.$keyArr[2]] = $value;
                            }
                            $pos = strpos($keyArr[1],'|');
                            if ($pos) {
                                $keyValue = $keyArr[1];
                                $keyArr[1] = substr($keyValue,0, $pos);
                                $paramId = substr($keyValue,$pos+1);
                                $paramCaption = C4gReservationParamsModel::findByPk($paramId)->caption;
                                if ($value && $value !== 'false' && $participantsArr[$keyArr[1]][$keyArr[0]]) {
                                    $value = $participantsArr[$keyArr[1]][$keyArr[0]].', '.$paramCaption;
                                } else if ($value && $value !== 'false') {
                                    $value = $paramCaption;
                                }
                            }

                            if ($value && $value !== 'false') {
                                $participantsArr[$keyArr[2]][$keyArr[1]] = $value;
                            }

                        }
                    }
                }

                foreach ($putVars as $key => $value) {
                    $desiredCapacity = $putVars['desiredCapacity_'.$reservationType->id];
                    if ($desiredCapacity) {
                        $extId = ($desiredCapacity - 1);
                        for ($i = 0; $i <= 100; $i++) {
                            if ((strpos($key, "participants_" . $type . "-" . $i . "§") !== false) && ($i !== $extId)) {
                                unset($putVars[$key]);
                            }
                        }
                    }
                }


            } else {
                if (strpos($key,"participants_".$type."§") !== false) {
                    $keyArr = explode("§", $key);
                    if (trim($keyArr[0]) && trim($keyArr[1]) && trim($value)) {
                        $pos = strpos($keyArr[1],'|');
                        if ($pos) {
                            $keyValue = $keyArr[1];
                            $keyArr[1] = substr($keyValue,0, $pos);
                            $paramId = substr($keyValue,$pos+1);
                            $paramCaption = C4gReservationParamsModel::findByPk($paramId)->caption;
                            if ($value && $value !== 'false' && $participantsArr[$keyArr[1]][$keyArr[0]]) {
                                $value = $participantsArr[$keyArr[1]][$keyArr[0]].', '.$paramCaption;
                            } else if ($value && $value !== 'false') {
                                $value = $paramCaption;
                            }
                        }

                        if ($value && $value !== 'false') {
                            $participantsArr[$keyArr[2]][$keyArr[1]] = $value;
                        }

                    }
                }
            }
        }

        $factor = 1;

        if ($reservationType && $reservationType->severalBookings) {
            $factor = $reservationType->objectCount && ($reservationType->objectCount < $reservationObject->quantity) ? $reservationType->objectCount : $reservationObject->quantity;
        }

        $desiredCapacity =  $reservationObject && $reservationObject->maxParticipants ? ($reservationObject->maxParticipants * $factor) : 0;

        $participants = '';
        if ($participantsArr && count($participantsArr) > 0) {
            $possible = $desiredCapacity - $reservationCount;
            if ($desiredCapacity && $possible < count($participantsArr)) {
                return ['usermessage' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['too_many_participants'].$possible];
            }

            if ($reservationType->maxParticipantsPerBooking && (count($participantsArr) > $reservationType->maxParticipantsPerBooking)) {
                return ['usermessage' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['too_many_participants_per_booking'].$reservationType->maxParticipantsPerBooking];
            }

            $number = 0;
            foreach ($participantsArr as $key => $valueArray) {
                $number++;
                $participants .= $participants ? '; '.$number.': '.trim(implode(', ',$valueArray)) : $number.': '.trim(implode(', ',$valueArray));
            }
            $putVars['participantList'] = $participants;
        }

        $icsObject = $reservationEventObject ?: $reservationObject;

        $beginDateTime = C4gReservationDateChecker::mergeDateWithTimeForIcs(strtotime($beginDate), $beginTime);
        $endDateTime = C4gReservationDateChecker::mergeDateWithTimeForIcs($endDate ?: strtotime($beginDate), $endTime);
        $putVars['icsFilename'] = $this->createIcs($beginDateTime, $endDateTime, $icsObject, $reservationType, $location, $reservationId);

        $rawData = '';
        foreach ($putVars as $key => $value) {
            $rawData .= (isset($putVars[$key]) ? $putVars[$key] : ucfirst($key)) . ': ' . (is_array($value) ? implode(', ', $value) : $value) . "\n";
        }

        $action = new C4GSaveAndRedirectDialogAction($this->getDialogParams(), $this->getListParams(), $newFieldList, $putVars, $this->getBrickDatabase());
        $action->setModule($this);
        $result = $action->run();
        //$this->fieldList[$reservationIdKey]->setInitialValue(C4GBrickCommon::getUUID());
        return $result;
    }

    /**
     * @param $beginDateTime
     * @param $endDateTime
     * @param $object
     * @param $type
     * @param $location
     * @param $reservationId
     * @return string
     */
    public function createIcs($beginDateTime, $endDateTime, $object, $type, $location, $reservationId)
    {

        if ($beginDateTime && $endDateTime && $object && $type && $location && $location->ics && $location->icsPath && $reservationId) {
            $icsprodid = $reservationId;
            $icsuid = $reservationId;
            $contact_street = $location->contact_street;
            $contact_postal = $location->contact_postal;
            $contact_city = $location->contact_city;
            $contact_name = $location->contact_name;
            $icslocation = $contact_name ." (". $contact_street .", ". $contact_postal." ". $contact_city . ")";
            $icssummary = $object->caption;
            $icsdescription = strip_tags($object->description);
            $timezone   = $GLOBALS['TL_CONFIG']['timeZone'];
            $icstimezone = 'TZID='.$timezone;
            $dstart = $icstimezone.':'.$beginDateTime;
            $dend = $icstimezone.':'.$endDateTime;
            $dstamp = C4gReservationDateChecker::mergeDateWithTimeForIcs(time(),time()).'Z';
            $icsalert = $location->icsAlert;
            $icsalert = $icsalert * 60;
            $icsalert = '-PT'.$icsalert.'M';

            $fileId = $reservationId;//sprintf("%05d", $type->id).sprintf("%05d",$object->id);
            $pathUuid = $location->icsPath;
            if ($pathUuid) {
                $pathUuid = StringUtil::binToUuid($pathUuid);
                $path = Controller::replaceInsertTags("{{file::$pathUuid}}");

                $filename = $path.'/'.$fileId.'/'.'reservation.ics';
                try {
                    mkdir($path.'/'.$fileId.'/');
                    $ics = new File($filename);
                } catch (\Exception $exception) {
                    $fs = new Filesystem();
                    $fs->touch($filename);
                    $ics = new File($filename);
                }

                $ics->openFile("w")->fwrite(
                    "BEGIN:VCALENDAR\nVERSION:2.0\nCALSCALE:GREGORIAN\nMETHOD:PUBLISH\nPRODID:$icsprodid\n".
                    "X-WR-TIMEZONE:$icstimezone\nBEGIN:VEVENT\nUID:$icsuid\nLOCATION:$icslocation\nSUMMARY:$icssummary\nCLASS:PUBLIC\nDESCRIPTION:$icsdescription\n".
                    "DTSTART;$dstart\nDTEND;$dend\nDTSTAMP:$dstamp\nBEGIN:VALARM\nTRIGGER:$icsalert\nACTION:DISPLAY\nEND:VALARM\nEND:VEVENT\nEND:VCALENDAR\n");
                return $filename;
            }
        }

        return '';
    }

    /**
     * @param $values
     * @param $putVars
     * @return array
     */
    public function getCurrentTimeset($values, $putVars)
    {
        $date = $values[2];
        $type = $values[3];
        $duration = $values[4];
        $wd = -1;
        $times = [];

        //hotfix dates with slashes
        $date = str_replace("~", "/", $date);
        if ($date)  {
            $format = $GLOBALS['TL_CONFIG']['dateFormat'];

            $tsdate = \DateTime::createFromFormat($format, $date);
            if ($tsdate) {
                $tsdate->Format($format);
                $tsdate->setTime(0,0,0);
                $tsdate = $tsdate->getTimestamp();
            } else {
                $format = "d/m/Y";
                $tsdate = \DateTime::createFromFormat($format, $date);
                if ($tsdate) {
                    $tsdate->Format($format);
                    $tsdate->setTime(0,0,0);
                    $tsdate = $tsdate->getTimestamp();
                } else {
                    $tsdate = strtotime($date);
                }
            }

            $datetime = $tsdate;//strtotime($date);
            $wd = date("w", $datetime);
        }

        $eventId  = $this->Input->get('event') ? $this->Input->get('event') : 0;

        //ToDo hotfix
        //Please keep it that way. The get parameters are lost during processing in Projects and are thus preserved.
        if (!$eventId && $_COOKIE['reservationEventCookie']) {
            $eventId = $_COOKIE['reservationEventCookie'];
        } else if ($eventId) {
            setcookie('reservationEventCookie', $eventId, time()+60, '/');
        }

        if ($date) {
            $objects = C4gReservationObjectModel::getReservationObjectList(array($type), intval($eventId), $this->showPrices);
            $withEndTimes = $this->showEndTime;
            $withFreeSeats = $this->showFreeSeats;

            $times = C4gReservationObjectModel::getReservationTimes($objects, $type, $wd, $date, $duration, $withEndTimes, $withFreeSeats, true);

            if ($type) {
                if ($this->fieldList) {
                    foreach ($this->fieldList as $key => $field) {
                        if (($field->getFieldName() == 'beginTime') && ($field->getAdditionalId() == $type . '00' . $wd)) {
                            $this->fieldList[$key]->setOptions($times);
                            break;
                        }
                    }
                }
            }
        }


        return array(
            'reservationId' => C4GBrickCommon::getUUID(),
            'times' => $times
        );
    }

}

