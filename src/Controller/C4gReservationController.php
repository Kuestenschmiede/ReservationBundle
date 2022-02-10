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

namespace con4gis\ReservationBundle\Controller;

use con4gis\CoreBundle\Classes\Helper\StringHelper;
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
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GInfoTextField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GKeyField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GLabelField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GMultiCheckboxField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GMultiSelectField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GNumberField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GPostalField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GSelectField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GSubDialogField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTelField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTextareaField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTextField;
use con4gis\ProjectsBundle\Classes\Framework\C4GBaseController;
use con4gis\ProjectsBundle\Classes\Framework\C4GController;
use con4gis\ProjectsBundle\Classes\Views\C4GBrickViewType;
use con4gis\ReservationBundle\Classes\Models\C4gReservationEventModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationLocationModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationParamsModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationSettingsModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationTypeModel;
use con4gis\ReservationBundle\Classes\Projects\C4gReservationBrickTypes;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationDateChecker;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationFormDefaultHandler;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationFormEventHandler;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationFormObjectFirstHandler;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationHandler;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationInitialValues;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Date;
use Contao\FrontendUser;
use Contao\Input;
use Contao\ModuleModel;
use Contao\StringUtil;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Reservation form (Contao frontend module)
 */
class C4gReservationController extends C4GBaseController
{
    public const TYPE = 'C4gReservation';

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
    protected $loadChosenResources = true;
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
    protected $jQueryAddJqueryUI = false;
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

    protected $reservationSettings = null;

    /**
     * @param string $rootDir
     * @param Session $session
     * @param ContaoFramework $framework
     */
    public function __construct(string $rootDir, Session $session, ContaoFramework $framework, ModuleModel $model = null)
    {
        parent::__construct($rootDir, $session, $framework, $model);

        if (!$this->reservationSettings && $this->reservation_settings) {
            $this->reservationSettings = C4gReservationSettingsModel::findByPk($this->reservation_settings);
        }
        $doIt = false;
        $moduleTypes = StringUtil::deserialize($this->reservationSettings->reservation_types);
        if ($moduleTypes) {
            $doIt = true;
            $t = 'tl_c4g_reservation_type';
            $arrValues = array();
            $arrOptions = array();
            foreach ($moduleTypes as $moduleType) {
                //$moduleType = intval($moduleType);
                $arrColumns = array("$t.published='1' AND $t.reservationObjectType='2' AND $t.id = $moduleType"); //no event type selection - use get params
                $types = C4gReservationTypeModel::findBy($arrColumns, $arrValues, $arrOptions);
                if ($types) {
                    $doIt = false;
                    break;
                }
            }
        }

        $eventId  = Input::get('event') ? Input::get('event') : 0;
        if (!$eventId && $doIt && ($oldEventId = $this->session->getSessionValue('reservationEventCookie'))) {
            $this->session->remove('reservationEventCookie');
            $this->session->remove('reservationInitialDateCookie_'.$oldEventId);
            $this->session->remove('reservationTimeCookie_'.$oldEventId);
        }
    }

    public function initBrickModule($id)
    {
        \System::loadLanguageFile('fe_c4g_reservation');
        $this->setBrickCaption($GLOBALS['TL_LANG']['fe_c4g_reservation']['brick_caption']);
        $this->setBrickCaptionPlural($GLOBALS['TL_LANG']['fe_c4g_reservation']['brick_caption_plural']);
        parent::initBrickModule($id);

        if (!$this->reservationSettings && $this->reservation_settings) {
            $this->reservationSettings = C4gReservationSettingsModel::findByPk($this->reservation_settings);
        }

        $this->dialogParams->setWithoutGuiHeader(true);
        $this->dialogParams->deleteButton(C4GBrickConst::BUTTON_SAVE);
        $this->dialogParams->deleteButton(C4GBrickConst::BUTTON_SAVE_AND_NEW);
        $this->dialogParams->deleteButton(C4GBrickConst::BUTTON_DELETE);
        $this->dialogParams->setRedirectSite($this->reservationSettings->reservation_redirect_site);
        $this->dialogParams->setSaveWithoutSavingMessage(false);
    }

    public function addFields() : array
    {
        if (!$this->reservationSettings && $this->reservation_settings) {
            $this->reservationSettings = C4gReservationSettingsModel::findByPk($this->reservation_settings);
        }

        $fieldList = array();
        $typelist = array();

        $initialDate = '';
        $initialTime = '';

        $eventId = Input::get('event') ? Input::get('event') : 0;

        if (!$eventId && $this->session->getSessionValue('reservationEventCookie')) {
            $eventId = $this->session->getSessionValue('reservationEventCookie');
        } else if ($eventId) {
            $this->session->setSessionValue('reservationEventCookie', $eventId);
        }

        if ($eventId) {
            $this->permalink_name = 'event';
        }

        $event = $eventId ? \CalendarEventsModel::findByPk($eventId) : false;
        $eventObj = $event && $event->published ? C4gReservationEventModel::findBy('pid', $event->id) : false;
        if ($eventObj && is_countable($eventObj) && (count($eventObj) > 1)) {
            C4gLogModel::addLogEntry('reservation', 'There are more than one event connections. Check Event: ' . $event->id);
        } else {
            $date = Input::get('date') ? Input::get('date') : 0;
            if ($date) {
                $initialDate = $date;
                if (!is_numeric($initialDate)) {
                    $initialDate = strtotime($initialDate);
                }
            }

            if ($eventObj && !$initialDate &&  $this->session->getSessionValue('reservationInitialDateCookie_'.$eventId)) {
                $initialDate = $this->session->getSessionValue('reservationInitialDateCookie_'.$eventId);
            } else if ($eventObj && $initialDate) {
                $recurring = $event->recurring;
                $startDate = C4gReservationDateChecker::getBeginOfDate($event->startDate);
                $actDate = C4gReservationDateChecker::getBeginOfDate($initialDate);
                if ($recurring && !($startDate == $actDate)) {
                    $repeatEach = StringUtil::deserialize($event->repeatEach);
                    $goodDay = false;
                    if ($repeatEach) {
                        $unit = $repeatEach['unit'];
                        $value = $repeatEach['value']; //intervall
                        $repeatEnd  = $event->repeatEnd; //0=unebfristet

                        if ($unit && $value && ($actDate > $startDate) && (!$repeatEnd || ($repeatEnd >= $actDate))) {
                            if (!$repeatEnd) {
                                $repeatEnd = $actDate;
                            }

                            $factor = 0;
                            switch($unit) {
                                case 'days':
                                    $factor = $value * 86400;
                                    break;
                                case 'weeks':
                                    $factor = $value * 86400 * 7;
                                    break;
                                case 'months':
                                    $factor = $value * 86400 * 30; //ToDo
                                    break;
                                case 'years':
                                    $factor = $value * 31557600; //ToDo
                                    break;
                            }

                            $i = $startDate;
                            while($i<=$repeatEnd) {
                                $i = $i+$factor;
                                if ($actDate == $i) {
                                    $goodDay = true;
                                    break;
                                }
                            }
                        }
                    }
                    if (!$goodDay) {
                        $info = new C4GInfoTextField();
                        $info->setFieldName('info');
                        $info->setEditable(false);
                        $info->setInitialValue($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_none_date']);
                        return [$info];
                    }
                }

                $this->session->setSessionValue('reservationInitialDateCookie_'.$eventId, $initialDate);

            }

            $time = Input::get('time') ? Input::get('time') : 0;

            if ($eventObj && !$time &&  $this->session->getSessionValue('reservationTimeCookie_'.$eventId)) {
                $time = $this->session->getSessionValue('reservationTimeCookie_'.$eventId);
            } else if ($eventObj && $time) {
                $this->session->setSessionValue('reservationTimeCookie_'.$eventId, $time);
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

        if ($eventObj) {
            $typeId = $eventObj->reservationType;
            $arrColumns = array("$t.published='1' AND $t.id=$typeId");
            $types = C4gReservationTypeModel::findBy($arrColumns, $arrValues, $arrOptions);
        } else if (!$eventObj && !$eventId) {
            $arrColumns = array("$t.published='1' AND NOT $t.reservationObjectType='2'"); //no event type selection - use get params
            $types = C4gReservationTypeModel::findBy($arrColumns, $arrValues, $arrOptions);
        }

        if ($types) {
            $memberId = 0;
            if (FE_USER_LOGGED_IN === true) {
                $member = FrontendUser::getInstance();
                if ($member) {
                    $memberId = $member->id;
                }
            }

            $moduleTypes = StringUtil::deserialize($this->reservationSettings->reservation_types);

            $safetyCount = 0;
            foreach ($types as $type) {
                if ($moduleTypes && is_array($moduleTypes) && (count($moduleTypes) > 0)) {
                    $arrModuleTypes = $moduleTypes;
                    if (!in_array($type->id, $arrModuleTypes)) {
                        continue;
                    }
                }

                $objects = C4gReservationHandler::getReservationObjectList(array($type->id), intval($eventId), $this->reservationSettings->showPrices);
                if (!$objects || (count($objects) <= 0)) {
                    continue;
                }

                if ($safetyCount == 10) {
                    break;
                }

                $captions = \Contao\StringUtil::deserialize($type->options);
                if ($captions && (count($captions) > 0)) {
                    foreach ($captions as $caption) {
                        if (strpos($GLOBALS['TL_LANGUAGE'], $caption['language']) >= 0) {
                            $typelist[$type->id] = array(
                                'id' => $type->id,
                                'name' => $caption['caption'] ?  StringHelper::spaceToNbsp($caption['caption']) : StringHelper::spaceToNbsp($type->caption),
                                'periodType' => $type->periodType,
                                'includedParams' => \Contao\StringUtil::deserialize($type->included_params),
                                'additionalParams' => \Contao\StringUtil::deserialize($type->additional_params),
                                'participantParams' => \Contao\StringUtil::deserialize($type->participant_params),
                                'minParticipantsPerBooking' => $type->minParticipantsPerBooking,
                                'maxParticipantsPerBooking' => $type->maxParticipantsPerBooking,
                                'objects' => $objects,
                                'objectType' => $type->reservationObjectType,
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
                        'name' => StringHelper::spaceToNbsp($type->caption),
                        'periodType' => $type->periodType,
                        'includedParams' => \Contao\StringUtil::deserialize($type->included_params),
                        'additionalParams' => \Contao\StringUtil::deserialize($type->additional_params),
                        'participantParams' => \Contao\StringUtil::deserialize($type->participant_params),
                        'minParticipantsPerBooking' => $type->minParticipantsPerBooking,
                        'maxParticipantsPerBooking' => $type->maxParticipantsPerBooking,
                        'objects' => $objects,
                        'objectType' => $type->reservationObjectType,
                        'memberId' => $type->member_id ?: $memberId,
                        'groupId' => $type->group_id,
                        'type' => $type->reservationObjectType,
                        'directBooking' => $type->directBooking
                    );
                }
                $safetyCount++;
            }
        }

        $idField = new C4GKeyField();
        $idField->setFieldName('id');
        $idField->setEditable(false);
        $idField->setFormField(false);
        $idField->setSortColumn(false);
        $fieldList[] = $idField;

        $showDateTime = $this->reservationSettings->showDateTime ? "1" : "0";

        if (count($typelist) > 0) {
            $firstType = array_key_first($typelist);

            $onLoadScript = "jQuery('#c4g_reservation_type').trigger('change');";
            $this->getDialogParams()->setOnloadScript(trim($onLoadScript));

            $reservationTypeField = new C4GSelectField();
            $reservationTypeField->setChosen(false);
            $reservationTypeField->setFieldName('reservation_type');
            $reservationTypeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_type']);
            $reservationTypeField->setSortColumn(false);
            $reservationTypeField->setTableColumn(false);
            $reservationTypeField->setColumnWidth(20);
            $reservationTypeField->setSize(1); //count($typelist)
            $reservationTypeField->setOptions($typelist);
            $reservationTypeField->setMandatory(true);
            $reservationTypeField->setCallOnChange(true);
            $reservationTypeField->setCallOnChangeFunction("setReservationForm(-1 ," . $showDateTime . ")");
            $reservationTypeField->setInitialValue($firstType);
            $reservationTypeField->setStyleClass('reservation-type');
            $reservationTypeField->setEditable(count($typelist) > 1);
            $reservationTypeField->setNotificationField(true);
            $reservationTypeField->setWithOptionType(true);
            $reservationTypeField->setHidden((count($typelist) == 1) && $this->reservationSettings->typeHide);
            //$reservationTypeField->setInitialCallOnChange(true);
            $fieldList[] = $reservationTypeField;
        } else {
            $info = new C4GInfoTextField();
            $info->setFieldName('info');
            $info->setEditable(false);
            $info->setInitialValue($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_none']);
            return [$info];
        }

        $initialValues = new C4gReservationInitialValues();
        $initialValues->setDate($initialDate);
        $initialValues->setTime($initialTime);

        foreach ($typelist as $listType) {
            $condition = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'reservation_type', $listType['id']);

            $maxCapacity = $listType['maxParticipantsPerBooking'] ?: 0;
            $minCapacity = $listType['minParticipantsPerBooking'] ?: 1;
            $showDateTime = $this->reservationSettings->showDateTime ? "1" : "0";

            if ($this->reservationSettings->withCapacity) {
                $reservationDesiredCapacity = new C4GNumberField();
                $reservationDesiredCapacity->setFieldName('desiredCapacity');

                if ($minCapacity && $maxCapacity && ($minCapacity != $maxCapacity)) {
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
                $reservationDesiredCapacity->setCallOnChangeFunction("setReservationForm(".$listType['id'] . "," . $showDateTime . ");");
                $reservationDesiredCapacity->setNotificationField(true);
                $reservationDesiredCapacity->setAdditionalID($listType['id']);
                $reservationDesiredCapacity->setStyleClass('desired-capacity');

                $fieldList[] = $reservationDesiredCapacity;
            }

            //($listType['periodType'] === 'hour') || ($listType['periodType'] === 'minute')

            $additionalDuration = $this->reservationSettings->additionalDuration;
            if (intval($additionalDuration) >= 1) {
                $durationField = new C4GNumberField();
                $durationField->setFieldName('duration');
                $durationField->setTitle($listType['periodType'] === 'hour' ? $GLOBALS['TL_LANG']['fe_c4g_reservation']['duration_hourly'] : $GLOBALS['TL_LANG']['fe_c4g_reservation']['duration_minutely']);
                $durationField->setColumnWidth(10);
                $durationField->setFormField(true);
                $durationField->setSortColumn(true);
                $durationField->setTableColumn(true);
                $durationField->setMandatory(true);
                $durationField->setCallOnChange(true);
                $durationField->setCallOnChangeFunction("setReservationForm(".$listType['id'] . "," . $showDateTime . ");");
                //$durationField->setCallOnChangeFunction("setTimeset(document.getElementById('c4g_beginDate_" . $listType['id'] . "'), " . $listType['id'] . "," . $showDateTime . ");");
                $durationField->setCondition(array($condition));
                $durationField->setNotificationField(true);
                $durationField->setStyleClass('duration');
                $durationField->setMin(1);
                $durationField->setMax($additionalDuration);
                $durationField->setInitialValue($additionalDuration);
                $durationField->setMaxLength(3);
                $durationField->setStep(5);
                $durationField->setAdditionalID($listType['id']);
                $durationField->setDatabaseField(true);
                $fieldList[] = $durationField;
            } else {
                $additionalDuration = 0;
            }

            //set reservationObjectType to default
            $reservationObjectTypeField = new C4GNumberField();
            $reservationObjectTypeField->setFieldName('reservationObjectType');
            $reservationObjectTypeField->setInitialValue($listType['objectType']);
            $reservationObjectTypeField->setDatabaseField(true);
            $reservationObjectTypeField->setFormField(false);
            $this->fieldList[] = $reservationObjectTypeField;

            switch($listType['objectType']) {
                case '1':
                    $formHandler = new C4gReservationFormDefaultHandler($this,$fieldList,$listType,$this->getDialogParams(), $initialValues);
                    $fieldList = $formHandler->addFields();
                    break;
                case '2':
                    $formHandler = new C4gReservationFormEventHandler($this,$fieldList,$listType,$this->getDialogParams(), $initialValues);
                    $fieldList = $formHandler->addFields();
                    break;
                case '3':
                    $formHandler = new C4gReservationFormObjectFirstHandler($this,$fieldList,$listType,$this->getDialogParams(), $initialValues);
                    $fieldList = $formHandler->addFields();
                    break;
                default:
                    //ToDo andere Meldung
                    $info = new C4GInfoTextField();
                    $info->setFieldName('info');
                    $info->setEditable(false);
                    $info->setInitialValue($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_none']);
                    return [$info];
            }
        }

        if (!$typelist || count($typelist) <= 0) {
            $reservationNoneTypeField = new C4GLabelField();
            $reservationNoneTypeField->setDatabaseField(false);
            $reservationNoneTypeField->setInitialValue($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_none']);
            $fieldList[] = $reservationNoneTypeField;
        }

        $salutation = [
            ['id' => 'man', 'name' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['man']],
            ['id' => 'woman', 'name' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['woman']],
            ['id' => 'various', 'name' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['various']],
        ];

        $additionaldatas = StringUtil::deserialize($this->reservationSettings->fieldSelection);
        if (!$additionaldatas) {
            $additionaldatas = [];
        }

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

        if ($this->reservationSettings->showMemberData && FE_USER_LOGGED_IN === true) {
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
        $specialParticipantMechanism = $this->reservationSettings->specialParticipantMechanism;
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
                    $maxParticipants = $type['maxParticipantsPerBooking'];
                    $minParticipants = $type['minParticipantsPerBooking'];
                    $maxCapacity = $maxParticipants ?: 0;
                    $minCapacity = $minParticipants ?: 1;
                    $params = $type['participantParams'];
                    $participantParamsArr = [];

                    if (!$specialParticipantMechanism ||
                        ($specialParticipantMechanism && $this->reservationSettings->withCapacity) || ($specialParticipantMechanism && $maxCapacity > 1)) {
                        if ($params) {
                            foreach ($params as $paramId) {
                                if ($paramId) {
                                    $participantParam = C4gReservationParamsModel::findByPk($paramId);
                                    if ($participantParam && $participantParam->caption && ($participantParam->price && $this->reservationSettings->showPrices)) {
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
                            if ($this->reservationSettings->withCapacity) {
                                $participantCapacity = $maxCapacity && ($maxCapacity < 10) ? $maxCapacity : 10;
                                if ($maxCapacity > 1) {
                                    $start = ($minCapacity > 2) ? $minCapacity : 2;
                                    for ($i = $minCapacity; $i <= $participantCapacity; $i++) {
                                        $newCondition = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'desiredCapacity_' . $listType['id'], $i);

                                        //$newCondition[] = $condition;
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
                                        $reservationParticipants->setMax($participantCapacity);
                                        $reservationParticipants->setNotificationField(false);
                                        $reservationParticipants->setShowDataSetsByCount($i - 1);
                                        $reservationParticipants->setParentFieldList($fieldList);
                                        $reservationParticipants->setDelimiter('§');
                                        $reservationParticipants->setCondition(array($newCondition));
                                        $reservationParticipants->setRemoveWithEmptyCondition(true);
                                        $reservationParticipants->setAdditionalID($listType['id'] . '-' . ($i - 1));

                                        $fieldList[] = $reservationParticipants;
                                    }
                                }
                            } else {
                                $maxCapacity = $maxCapacity ?: 1;
                                $newCondition = $condition;

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

                                $reservationParticipants->setShowDataSetsByCount($maxCapacity <= 10 ? $maxCapacity : 10);
                                $reservationParticipants->setParentFieldList($fieldList);
                                $reservationParticipants->setDelimiter('§');
                                $reservationParticipants->setCondition(array($newCondition));
                                $reservationParticipants->setRemoveWithEmptyCondition(true);
                                $reservationParticipants->setAdditionalID($listType['id']);

                                $fieldList[] = $reservationParticipants;

                            }
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
        $reservationIdField->setTableRow(false);
        $reservationIdField->setEditable(false);
        $reservationIdField->setUnique(true);
        $reservationIdField->setNotificationField(true);
        $reservationIdField->setDbUnique(true);
        $reservationIdField->setSimpleTextWithoutEditing(false);
        $reservationIdField->setDatabaseField(true);
        $reservationIdField->setDbUniqueResult($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_id_exists']);
        $reservationIdField->setStyleClass('reservation-id');
        $fieldList[] = $reservationIdField;

        if ($this->reservationSettings->privacy_policy_text) {
            $privacyPolicyText = new C4GTextField();
            $privacyPolicyText->setSimpleTextWithoutEditing(true);
            $privacyPolicyText->setFieldName('privacy_policy_text');
            $privacyPolicyText->setInitialValue(\Contao\Controller::replaceInsertTags($this->reservationSettings->privacy_policy_text));
            $privacyPolicyText->setSize(4);
            $privacyPolicyText->setTableColumn(false);
            $privacyPolicyText->setEditable(false);
            $privacyPolicyText->setDatabaseField(false);
            $privacyPolicyText->setMandatory(false);
            $privacyPolicyText->setNotificationField(false);
            $privacyPolicyText->setStyleClass('privacy-policy-text');
            $fieldList[] = $privacyPolicyText;
        }

        if ($this->reservationSettings->privacy_policy_site) {
            $href = \Contao\Controller::replaceInsertTags('{{link_url::' . $this->reservationSettings->privacy_policy_site . '}}');
            $desc = '<span class="c4g_field_description_text">' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed'] . '</span> <a href="' . $href . '" target="_blank" rel="noopener">' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed_link_text'] . '</a>';
        } else {
            $desc = $GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed_without_link'];
        }

        $agreedField = new C4GCheckboxField();
        $agreedField->setFieldName('agreed');
        $agreedField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['agreed'].' '.$desc);
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
            $this->reservationSettings->reservationButtonCaption ? \Contao\Controller::replaceInsertTags($this->reservationSettings->reservationButtonCaption) : $GLOBALS['TL_LANG']['fe_c4g_reservation']['button_reservation'],
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

        $location_name = new C4GTextField();
        $location_name->setFieldName('location');
        $location_name->setSortColumn(false);
        $location_name->setFormField(false);
        $location_name->setTableColumn(true);
        $location_name->setNotificationField(true);
        $fieldList[] = $location_name;

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

        $bookedAt = new C4GTextField();
        $bookedAt->setFieldName('bookedAt');
        $bookedAt->setTableColumn(true);
        $bookedAt->setFormField(false);
        $bookedAt->setNotificationField(false);
        $fieldList[] = $bookedAt;
        return $fieldList;
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
        $database = \Database::getInstance();
        $reservationType = $database->prepare("SELECT * FROM tl_c4g_reservation_type WHERE id=? AND published='1'")
            ->execute($type);

        $this->notification_type = $this->reservationSettings->notification_type;
        $this->getDialogParams()->setNotificationType($this->reservationSettings->notification_type);

        if ($reservationType->notification_type) {
            $this->getDialogParams()->setNotificationType($reservationType->notification_type);
            $this->notification_type = $reservationType->notification_type;
        }

        $isEvent = $reservationType->reservationObjectType && $reservationType->reservationObjectType === '2' ? true : false;

        if ($isEvent) {
            $key = "reservation_object_event_" . $type;
            $resObject = $putVars[$key];

            if ($resObject) {
                $reservationObject = $database->prepare("SELECT * FROM tl_calendar_events WHERE id=? AND published='1'")
                    ->execute($resObject);

                foreach ($putVars as $key => $value) {
                    if (strpos($key, strval($type.'-22'))) {
                        if (strpos($key, strval($type.'-22'.$resObject)) === false) {
                            unset($putVars[$key]);
                        }
                    }
                }
            }
        } else {
            $key = "reservation_object_" . $type;
            $resObject = $putVars[$key];
            $reservationObject = $database->prepare("SELECT * FROM tl_c4g_reservation_object WHERE id=? AND published='1'")
                ->execute($resObject);

            if ($reservationObject && $reservationObject->notification_type) {
                $this->getDialogParams()->setNotificationType($reservationObject->notification_type);
                $this->notification_type = $reservationObject->notification_type;
            }

            if ($reservationType->reservationObjectType === '3') {
                foreach ($putVars as $key => $value) {
                    if (strpos($key, strval($type.'-33'))) {
                        if (strpos($key, strval($type.'-33'.$resObject)) === false) {
                            unset($putVars[$key]);
                        }
                    } else if (
                        (strpos($key, 'beginDate') !== false) ||
                        (strpos($key, 'beginTime') !== false) ||
                        (strpos($key, 'description') !== false) ||
                        (strpos($key, 'image') !== false) ||
                        (strpos($key, 'undefined') !== false) ||
                        (!trim($key))) {
                        unset($putVars[$key]);
                    }
                }
            }
        }

        $newFieldList = [];
        $removedFromList = [];

        foreach ($this->getFieldList() as $key=>$field) {
            $additionalId = $field->getAdditionalID();
            if ($additionalId && (($additionalId != $type) && (strpos($additionalId, strval($type.'-'))))) {
                unset($putVars[$field->getFieldName()."_".$additionalId]);
                continue;
            } else if ($additionalId) {
                $removedFromList[$field->getFieldName()] = $additionalId;
                unset($putVars[$field->getFieldName()]);
            }

            if ($additionalId && ($additionalId != $type)) {
                if (($field->getFieldName() == 'desiredCapacity') || ($field->getFieldName() == 'duration')) {
                    unset($putVars[$field->getFieldName()."_".$additionalId]);
                    continue;
                }
            }

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
                if ($additionalId && (($additionalId != $type) && (strpos($additionalId, strval($type.'-22')) !== false))) {
                    if (strpos($additionalId, strval($type.'-22'.$reservationObject->id)) === false) {
                        unset($putVars[$field->getFieldName()."_".$additionalId]);
                        continue;
                    }
                }
            }

            if ($reservationType->reservationObjectType === '3') {
                if ($additionalId && (($additionalId != $type) && (strpos($additionalId, strval($type.'-33')) !== false))) {
                    if (strpos($additionalId, strval($type.'-33'.$reservationObject->id)) === false) {
                        unset($putVars[$field->getFieldName()."_".$additionalId]);
                        continue;
                    }
                }
            }

            if (!$field->isEditable() && !$field->isDatabaseField() && $field->getInitialValue() && $field->isNotificationField()) {
                if ($field->getAdditionalId()) {
                    if ($isEvent) {
                        if (($field->getAdditionalId() != $type) && (strpos($field->getAdditionalId(), strval($type.'-22')) !== false)) {
                            if (strpos($field->getAdditionalId(), strval($type.'-22'.$reservationObject->id)) === false) {
                                continue;
                            }
                        }
                    }

                    if ($reservationType->reservationObjectType === '3') {
                        if (($field->getAdditionalId() != $type) && (strpos($field->getAdditionalId(), strval($type.'-33')) !== false)) {
                            if (strpos($field->getAdditionalId(), strval($type.'-33'.$reservationObject->id)) === false) {
                                continue;
                            }
                        }
                    }

                    $putVars[$field->getFieldName().'_'.$field->getAdditionalId()] = $field->getInitialValue();
                } else {
                    if ($field->getAdditionalId()) {
                        if ($isEvent) {
                            if (($field->getAdditionalId() != $type) && (strpos($field->getAdditionalId(), strval($type . '-22')) !== false)) {
                                if (strpos($field->getAdditionalId(), strval($type . '-22' . $reservationObject->id)) === false) {
                                    continue;
                                }
                            }
                        }

                        if ($reservationType->reservationObjectType === '3') {
                            if (($field->getAdditionalId() != $type) && (strpos($field->getAdditionalId(), strval($type . '-33')) !== false)) {
                                if (strpos($field->getAdditionalId(), strval($type . '-33' . $reservationObject->id)) === false) {
                                    continue;
                                }
                            }
                        }
                    }

                    $putVars[$field->getFieldName()] = $field->getInitialValue();
                }
            }

            if ($reservationType->reservationObjectType === '3') {
                if ((strpos($field->getAdditionalId(), '-33') === false) && (
                        (strpos($field->getFieldName(), 'beginDate') !== false) ||
                        (strpos($field->getFieldName(), 'beginTime') !== false) ||
                        (strpos($field->getFieldName(), 'description') !== false) ||
                        (strpos($field->getFieldName(), 'image') !== false))) {
                    continue;
                }
            }
            if ($field->getFieldName() && (!$removedFromList[$field->getFieldName()] || ($removedFromList[$field->getFieldName()] == $field->getAdditionalId()))) {
                $newFieldList[] = $field;
            }
        }

        $reservationId = $putVars['reservation_id'];

        if ($isEvent) {
            $putVars['reservationObjectType'] = '2';
            $objectId = $reservationObject ? $reservationObject->id : 0;
            $t = 'tl_c4g_reservation';
            $arrValues = array();
            $arrOptions = array();

            if ($objectId) {
                $arrColumns = array("$t.reservation_object=$objectId AND $t.reservationObjectType='2' AND NOT $t.cancellation='1'");
                $reservations = C4gReservationModel::findBy($arrColumns, $arrValues, $arrOptions);
            } else {
                C4gLogModel::addLogEntry('reservation', 'Event reservation called without event');
                return ['usermessage' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['error']];
            }

            $reservationCount = $reservations && is_countable($reservations) ? count($reservations) : 0; //ToDo check

            $reservationEventObjects = C4gReservationEventModel::findBy('pid', $objectId);

            if ($reservationEventObjects && is_countable($reservationEventObjects) && count($reservationEventObjects) > 1) {
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
            $putVars['endDate'] = $endDate ? date($GLOBALS['TL_CONFIG']['dateFormat'], $endDate) : $putVars['beginDate']; //ToDO Check
            $putVars['endTime'] = $endTime ? date($GLOBALS['TL_CONFIG']['timeFormat'], $endTime) : $endTime;
       } else {
            $putVars['reservationObjectType'] = $reservationType->reservationObjectType;
            $objectId = $reservationObject ? $reservationObject->id : 0;
            //check duplicate reservation id
            $reservations = C4gReservationModel::findBy("reservation_id", $reservationId);
            $reservationCount = is_array($reservations) ? count($reservations) : 0;
            if ($reservationCount >= 1) {
                C4gLogModel::addLogEntry('reservation', 'Duplicate reservation ID detected.');
                return ['usermessage' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['duplicate_reservation_id']];
            }

            //check duplicate bookings
            if ($reservationObject && $reservationObject->id && C4gReservationHandler::preventDublicateBookings($reservationType,$reservationObject,$putVars)) {
                C4gLogModel::addLogEntry('reservation', 'Duplicate booking detected.');
                return ['usermessage' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['duplicate_booking']];
            }

            if (!$reservationObject || !$reservationObject->id) {
                return ['usermessage' => $GLOBALS['TL_LANG']['FE_C4G_DIALOG']['USERMESSAGE_MANDATORY']];
            }

            $beginTime = 0;
            $timeKey = false;
            foreach ($putVars as $key => $value) {
                if ($reservationType->reservationObjectType === '3') {
                    $beginDate = $putVars['beginDate_'.$type.'-33'.$objectId];
                    if (strpos($key, "beginTime_".$type.'-33'.$objectId) !== false) {
                        if ($value) {
                            if (strpos($value, '#') !== false) {
                                $newValue = substr($value,0, strpos($value, '#')); //remove frontend duration
                            }

                            $beginTime = $newValue ?: $value;
                            $timeKey = $key;
                            break;
                        }
                    }
                } else {
                    $beginDate = $putVars['beginDate_'.$type];
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
            }

            $time_interval = $reservationObject->time_interval;
//            $min_residence_time = $reservationObject->min_residence_time;
//            $max_residence_time = $reservationObject->max_residence_time;

            switch ($reservationType->periodType) {
                case 'minute':
                    $interval = 60;
                    break;
                case 'hour':
                    $interval = 3600;
                    break;
                default: '';
            }

            $duration = $putVars['duration_'.$type];
            if (!$duration) {
                $duration = $time_interval;
                $putVars['duration_'.$type] = $reservationObject->duration ?: $duration;
            }

            //$putVars['duration_'.$type] = intval($putVars['duration_'.$type]);

            $duration = $duration * $interval;
            $endTime = $beginTime + $duration;
            $putVars['endTime'] = date($GLOBALS['TL_CONFIG']['timeFormat'],$endTime);

            //check nxt day times
            if ($reservationType->reservationObjectType === '3') {
                $putVars['endDate'] = $putVars['beginDate_'.$type.'-33'.$objectId]; //ToDo multiple days
                $bday = $putVars['beginDate_'.$type.'-33'.$objectId];
                $nextDay = strtotime("+1 day", strtotime($bday));
                if (!$reservationType->directBooking && $beginTime >= 86400) {
                    $putVars['beginDate_'.$type.'-33'.$objectId] = date($GLOBALS['TL_CONFIG']['dateFormat'], $nextDay);
                    $putVars[$timeKey] = ($beginTime-86400);
                } else {
                    $putVars[$timeKey] = $beginTime;
                }
            } else {
                $putVars['endDate'] = $putVars['beginDate_'.$type]; //ToDo multiple days
                $bday = $putVars['beginDate_'.$type];
                $nextDay = strtotime("+1 day", strtotime($bday));
                if (!$reservationType->directBooking && $beginTime >= 86400) {
                    $putVars['beginDate_'.$type] = date($GLOBALS['TL_CONFIG']['dateFormat'], $nextDay);
                    $putVars[$timeKey] = ($beginTime-86400);
                } else {
                    $putVars[$timeKey] = $beginTime;
                }
            }

            if (!$reservationType->directBooking && ($endTime >= 86400)) {
                $putVars['endDate'] = date($GLOBALS['TL_CONFIG']['dateFormat'], $nextDay);
                $putVars['endTime'] = date($GLOBALS['TL_CONFIG']['timeFormat'], ($endTime-86400));
            }

            if ($reservationType->directBooking) {
                $objDate = new Date(date($GLOBALS['TL_CONFIG']['timeFormat'],$beginTime), Date::getFormatFromRgxp('time'));
                $directTime = $objDate->tstamp;
                $putVars[$timeKey] = $directTime;
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
            if ($this->reservationSettings->specialParticipantMechanism) {
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

        $putVars['bookedAt'] = time();

        $action = new C4GSaveAndRedirectDialogAction($this->getDialogParams(), $this->getListParams(), $newFieldList, $putVars, $this->getBrickDatabase());
        $action->setModule($this);
        $result = $action->run();

        if (!$result['usermessage']) {
            if ($oldEventId = $this->session->getSessionValue('reservationEventCookie')) {
                $this->session->remove('reservationEventCookie');
                $this->session->remove('reservationInitialDateCookie_'.$oldEventId);
                $this->session->remove('reservationTimeCookie_'.$oldEventId);
            }
        }

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
     * @Route("/reservation-api/currentTimeset/{date}/{type}/{duration}/{objectId}", methods={"GET"})
     */
    public function getCurrentTimesetAction(Request $request, $date, $type, $duration, $objectId)
    {
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

            $datetime = C4gReservationDateChecker::getBeginOfDate($tsdate);
            $wd = date("w", $datetime);
        }

        if (!$objectId) {
            $objectId  = Input::get('event') ? Input::get('event') : 0;

            if (!$objectId && $this->session->getSessionValue('reservationEventCookie')) {
                $eventId = $this->session->getSessionValue('reservationEventCookie');
            } else if ($objectId) {
                $this->session->setSessionValue('reservationEventCookie', $objectId);
            }
        }

        if ($date) {
            $objects = C4gReservationHandler::getReservationObjectList(array($type), intval($objectId), $this->reservationSettings->showPrices);
            $withEndTimes = $this->reservationSettings->showEndTime;
            $withFreeSeats = $this->reservationSettings->showFreeSeats;

            $times = C4gReservationHandler::getReservationTimes($objects, $type, $wd, $date, $duration, $withEndTimes, $withFreeSeats, true);


            //ToDo the following lines are necessary obsolete
            $reservationId = C4GBrickCommon::getUUID();
            if ($type) {
                if ($this->fieldList) {
                    foreach ($this->fieldList as $key => $field) {
                        if (($field->getFieldName() == 'beginTime') && ($field->getAdditionalId() == $type . '00' . $wd)) {
                            $this->fieldList[$key]->setOptions($times);
                        }

                        if (($field->getFieldName() == 'reservation_id') && ($field->getInitialValue())) {
                            $reservationId = $field->getInitialValue();
                        }
                    }
                }
            }
        }

        return new JsonResponse(array(
            'reservationId' => $reservationId,
            'times' => $times
        ));
    }

    /**
     * @return C4gReservationSettingsModel|\Contao\Model|\Contao\Model[]|\Contao\Model\Collection|null
     */
    public function getReservationSettings()
    {
        return $this->reservationSettings;
    }

    /**
     * @param C4gReservationSettingsModel|\Contao\Model|\Contao\Model[]|\Contao\Model\Collection|null $reservationSettings
     */
    public function setReservationSettings($reservationSettings): void
    {
        $this->reservationSettings = $reservationSettings;
    }

}

