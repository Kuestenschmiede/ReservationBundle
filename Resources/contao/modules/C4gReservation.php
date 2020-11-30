<?php
/**
 * This file is part of con4gis,
 * the gis-kit for Contao CMS.
 *
 * @package    con4gis
 * @version    7
 * @author     con4gis contributors (see "authors.txt")
 * @license    LGPL-3.0-or-later
 * @copyright  Küstenschmiede GmbH Software & Design
 * @link       https://www.con4gis.org
 */

namespace con4gis\ReservationBundle\Resources\contao\modules;

use con4gis\CoreBundle\Classes\Helper\InputHelper;
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
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GNumberField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GPostalField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GRadioGroupField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GSelectField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GSubDialogField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTelField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTextareaField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTextField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTimeField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTimepickerField;
use con4gis\ProjectsBundle\Classes\Framework\C4GBrickModuleParent;
use con4gis\ProjectsBundle\Classes\Views\C4GBrickViewType;
use con4gis\ReservationBundle\Classes\C4gReservationBrickTypes;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationEventModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationObjectModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationParamsModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationTypeModel;
use Contao\Date;
use Contao\StringUtil;
use Contao\System;
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
    protected $brickScript  = 'bundles/con4gisreservation/js/c4g_brick_reservation.js';
    protected $brickStyle   = 'bundles/con4gisreservation/css/c4g_brick_reservation.css';
    protected $strTemplate  = 'mod_c4g_brick_simple';
    protected $withNotification = true;

    protected $jQueryUseTable = false;
    protected $jQueryUseScrollPane = false;
    protected $jQueryUsePopups = false;
    protected $loadChosenResources = false;

    public function initBrickModule($id)
    {
        parent::initBrickModule($id);

        $this->dialogParams->setWithoutGuiHeader(true);

        $this->dialogParams->deleteButton(C4GBrickConst::BUTTON_SAVE);
        $this->dialogParams->deleteButton(C4GBrickConst::BUTTON_SAVE_AND_NEW);
        $this->dialogParams->deleteButton(C4GBrickConst::BUTTON_DELETE);
        $this->dialogParams->setWithoutGuiHeader(true);
        $this->dialogParams->setRedirectSite($this->reservation_redirect_site);
        $this->dialogParams->setSaveWithoutSavingMessage(true);
        $this->brickCaption = $GLOBALS['TL_LANG']['fe_c4g_reservation']['brick_caption'];
        $this->brickCaptionPlural = $GLOBALS['TL_LANG']['fe_c4g_reservation']['brick_caption_plural'];
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

        $eventId  = $this->Input->get('event') ? $this->Input->get('event') : 0;
        $event    = $eventId ? \CalendarEventsModel::findByPk($eventId) : false;
        $eventObj = $event && $event->published ? C4gReservationEventModel::findOneBy('pid', $event->id) : false;
        if ($eventObj) {
            $typeId = $eventObj->reservationType;
            $types[] = C4gReservationTypeModel::findByPk($typeId); //ToDo check published
        } else {
            $types = C4gReservationTypeModel::findBy('published', '1');
        }

        if ($types) {
            $moduleTypes = unserialize($this->reservation_types);
            foreach ($types as $type) {
                if ($moduleTypes && (count($moduleTypes) > 0)) {
                    $arrModuleTypes = $moduleTypes;
                    if (!in_array($type->id, $arrModuleTypes)) {
                        continue;
                    }
                }

                $objects = C4gReservationObjectModel::getReservationObjectList(array($type->id), $eventId);
                if (!$objects || (count($objects) <= 0)) {
                    continue;
                }

                $captions = unserialize($type->options);
                if ($captions) {
                    foreach ($captions as $caption) {
                        if ($caption['language'] == $GLOBALS['TL_LANGUAGE']) {
                            $typelist[$type->id] = array(
                                'id' => $type->id,
                                'name' => $caption['caption'] ? $caption['caption'] : $type->caption,
                                'periodType' => $type->periodType,
                                'additionalParams' => unserialize($type->additional_params),
                                'objects' => $objects,
                                'isEvent' => $type->reservationObjectType && $type->reservationObjectType === '2' ? true : false
                            );
                        }
                    }
                }
            }
        }

        if (count($typelist) > 0) {
            $firstType = array_key_first($typelist);

            $onLoadScript = $this->getDialogParams()->getOnloadScript();
            $onLoadScript .= ' jQuery("#c4g_reservation_type").trigger("change");';
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
            $reservationTypeField->setCallOnChangeFunction("setReservationForm(this, " . $this->id . ", -1 ,'getCurrentTimeset')");
            $reservationTypeField->setInitialValue($firstType);
            $reservationTypeField->setStyleClass('reservation-type');
            $reservationTypeField->setHidden(count($typelist) == 1);
            $fieldList[] = $reservationTypeField;
        }

        foreach ($typelist as $type) {
            $isEvent = $type['isEvent'];
            $reservationObjects = $type['objects'];

            $condition = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'reservation_type', $type['id']);

            if ($this->withCapacity) {
                $conditionCapacity = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'desiredCapacity_'.$type['id']);
                $reservationDesiredCapacity = new C4GNumberField();
                $reservationDesiredCapacity->setFieldName('desiredCapacity');
                $reservationDesiredCapacity->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['desiredCapacity']);
                $reservationDesiredCapacity->setFormField(true);
                $reservationDesiredCapacity->setEditable(true);
                $reservationDesiredCapacity->setCondition(array($condition));
                $reservationDesiredCapacity->setInitialValue(1);
                $reservationDesiredCapacity->setMandatory(true);
                $reservationDesiredCapacity->setMin(1);
                $reservationDesiredCapacity->setPattern(C4GBrickRegEx::NUMBERS);
                $reservationDesiredCapacity->setCallOnChange(true);
                if ($isEvent) {
                    //ToDo
                    //$reservationDesiredCapacity->setCallOnChangeFunction("setTimeset(document.getElementById('c4g_beginDate_".$type['id']."')," . $this->id . "," . $type['id'] . ",'getCurrentTimeset');");
                } else {
                    $reservationDesiredCapacity->setCallOnChangeFunction("setTimeset(document.getElementById('c4g_beginDate_".$type['id']."')," . $this->id . "," . $type['id'] . ",'getCurrentTimeset');");
                }
                $reservationDesiredCapacity->setNotificationField(true);
                $reservationDesiredCapacity->setAdditionalID($type['id']);
                $reservationDesiredCapacity->setStyleClass('desired-capacity');
                //$reservationDesiredCapacity->setHidden(!$this->withCapacity);
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

                $additionalDuration = StringUtil::deserialize($this->additionalDuration);
                if ($additionalDuration == "1") {
                    $durationField = new C4GNumberField();
                    $durationField->setFieldName('duration');
                    $durationField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['duration']);
                    $durationField->setColumnWidth(10);
                    $durationField->setFormField(true);
                    $durationField->setSortColumn(true);
                    $durationField->setTableColumn(true);
                    $durationField->setMandatory(true);
                    $durationField->setCallOnChange(true);
                    $durationField->setCallOnChangeFunction("setTimeset(this, " . $this->id . "," . $type['id'] . ",'getCurrentTimeset');");
                    $durationField->setCondition(array($condition));
                    $durationField->setNotificationField(true);
                    $durationField->setStyleClass('duration');
                    $fieldList[] = $durationField;
                } else {
                    $additionalDuration = 0;
                }

                if (($type['periodType'] === 'minute') || ($type['periodType'] === 'hour')) {
                    //$conditionDate = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'beginDate_'.$type['id']);

                    $reservationBeginDateField = new C4GDateField();
                    $reservationBeginDateField->setMinDate(C4gReservationObjectModel::getMinDate($reservationObjects));
                    $reservationBeginDateField->setMaxDate(C4gReservationObjectModel::getMaxDate($reservationObjects));
                    $reservationBeginDateField->setExcludeWeekdays(C4gReservationObjectModel::getWeekdayExclusionString($reservationObjects));
                    $reservationBeginDateField->setExcludeDates(C4gReservationObjectModel::getDateExclusionString($reservationObjects, $type));
                    $reservationBeginDateField->setFieldName('beginDate');
                    $reservationBeginDateField->setCustomFormat($GLOBALS['TL_CONFIG']['dateFormat']);
                    $reservationBeginDateField->setCustomLanguage($GLOBALS['TL_LANGUAGE']);
                    $reservationBeginDateField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDate']);
                    $reservationBeginDateField->setEditable(true);
                    //$reservationBeginDateField->setInitialValue(C4gReservationObjectModel::getMinDate($reservationObjects));
                    $reservationBeginDateField->setComparable(false);
                    $reservationBeginDateField->setSortColumn(true);
                    $reservationBeginDateField->setSortSequence('de_datetime');
                    $reservationBeginDateField->setTableColumn(true);
                    $reservationBeginDateField->setFormField(true);
                    $reservationBeginDateField->setColumnWidth(10);
                    $reservationBeginDateField->setMandatory(true);
                    $reservationBeginDateField->setCondition(array($condition));
                    $reservationBeginDateField->setCallOnChange(true);
                    $reservationBeginDateField->setCallOnChangeFunction("setTimeset(this, " . $this->id . "," . $type['id'] . ",'getCurrentTimeset');");
                    $reservationBeginDateField->setNotificationField(true);
                    $reservationBeginDateField->setAdditionalID($type['id']);
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
                $reservationendTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                $reservationendTimeField->setNotificationField(true);
                $reservationendTimeField->setRemoveWithEmptyCondition(true);
                $reservationendTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                $fieldList[] = $reservationendTimeField;

                if (($type['periodType'] === 'hour') || ($type['periodType'] === 'minute')) {
                    $su_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $type['id']);
                    $su_condition->setModel(C4gReservationObjectModel::class);
                    $su_condition->setFunction('isSunday');
                    $suConditionArr = [$su_condition,$condition];

                    $suReservationTimeField = new C4GRadioGroupField();
                    $suReservationTimeField->setFieldName('beginTime');
                    $suReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                    $suReservationTimeField->setFormField(true);
                    $suReservationTimeField->setOptions(
                        C4gReservationObjectModel::getReservationTimes(
                            $reservationObjects,
                            $type['id'],
                            'su',
                            date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getNextWeekday($reservationObjects, 0)),
                            $additionalDuration,
                            $this->showEndTime,
                            $this->showFreeSeats
                        ));
                    $suReservationTimeField->setMandatory(true);
                    $suReservationTimeField->setInitInvisible(true);
                    $suReservationTimeField->setSort(false);
                    $suReservationTimeField->setCondition($suConditionArr);
                    $suReservationTimeField->setCallOnChange(true);
                    $suReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                    $suReservationTimeField->setAdditionalID($type['id'].'-000');
                    $suReservationTimeField->setNotificationField(true);
                    $suReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $suReservationTimeField->setTurnButton(true);
                    $suReservationTimeField->setRemoveWithEmptyCondition(true);
                    $suReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                    $fieldList[] = $suReservationTimeField;

                    $mo_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $type['id']);
                    $mo_condition->setModel(C4gReservationObjectModel::class);
                    $mo_condition->setFunction('isMonday');
                    $moConditionArr = [$mo_condition,$condition];

                    $moReservationTimeField = new C4GRadioGroupField();
                    $moReservationTimeField->setFieldName('beginTime');
                    $moReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                    $moReservationTimeField->setFormField(true);
                    $moReservationTimeField->setOptions(
                        C4gReservationObjectModel::getReservationTimes(
                            $reservationObjects,
                            $type['id'],
                            'mo',
                            date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getNextWeekday($reservationObjects, 1)),
                            $additionalDuration,
                            $this->showEndTime,
                            $this->showFreeSeats
                        ));
                    $moReservationTimeField->setMandatory(true);
                    $moReservationTimeField->setInitInvisible(true);
                    $moReservationTimeField->setSort(false);
                    $moReservationTimeField->setCondition($moConditionArr);
                    $moReservationTimeField->setCallOnChange(true);
                    $moReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                    $moReservationTimeField->setAdditionalID($type['id'].'-001');
                    $moReservationTimeField->setNotificationField(true);
                    $moReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $moReservationTimeField->setTurnButton(true);
                    $moReservationTimeField->setRemoveWithEmptyCondition(true);
                    $moReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                    $fieldList[] = $moReservationTimeField;

                    $tu_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $type['id']);
                    $tu_condition->setModel(C4gReservationObjectModel::class);
                    $tu_condition->setFunction('isTuesday');
                    $tuConditionArr = [$tu_condition,$condition];

                    $tuReservationTimeField = new C4GRadioGroupField();
                    $tuReservationTimeField->setFieldName('beginTime');
                    $tuReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                    $tuReservationTimeField->setFormField(true);
                    $tuReservationTimeField->setOptions(
                        C4gReservationObjectModel::getReservationTimes(
                            $reservationObjects,
                            $type['id'],
                            'tu',
                            date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getNextWeekday($reservationObjects, 2)),
                            $additionalDuration,
                            $this->showEndTime,
                            $this->showFreeSeats
                        ));
                    $tuReservationTimeField->setMandatory(true);
                    $tuReservationTimeField->setInitInvisible(true);
                    $tuReservationTimeField->setSort(false);
                    $tuReservationTimeField->setCondition($tuConditionArr);
                    $tuReservationTimeField->setCallOnChange(true);
                    $tuReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                    $tuReservationTimeField->setAdditionalID($type['id'].'-002');
                    $tuReservationTimeField->setNotificationField(true);
                    $tuReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $tuReservationTimeField->setTurnButton(true);
                    $tuReservationTimeField->setRemoveWithEmptyCondition(true);
                    $tuReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                    $fieldList[] = $tuReservationTimeField;

                    $we_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $type['id']);
                    $we_condition->setModel(C4gReservationObjectModel::class);
                    $we_condition->setFunction('isWednesday');
                    $weConditionArr = [$we_condition,$condition];

                    $weReservationTimeField = new C4GRadioGroupField();
                    $weReservationTimeField->setFieldName('beginTime');
                    $weReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                    $weReservationTimeField->setFormField(true);
                    $weReservationTimeField->setOptions(
                        C4gReservationObjectModel::getReservationTimes(
                            $reservationObjects,
                            $type['id'],
                            'we',
                            date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getNextWeekday($reservationObjects, 3)),
                            $additionalDuration,
                            $this->showEndTime,
                            $this->showFreeSeats
                        ));
                    $weReservationTimeField->setMandatory(true);
                    $weReservationTimeField->setInitInvisible(true);
                    $weReservationTimeField->setSort(false);
                    $weReservationTimeField->setCondition($weConditionArr);
                    $weReservationTimeField->setCallOnChange(true);
                    $weReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                    $weReservationTimeField->setAdditionalID($type['id'].'-003');
                    $weReservationTimeField->setNotificationField(true);
                    $weReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $weReservationTimeField->setTurnButton(true);
                    $weReservationTimeField->setRemoveWithEmptyCondition(true);
                    $weReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                    $fieldList[] = $weReservationTimeField;

                    $th_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $type['id']);
                    $th_condition->setModel(C4gReservationObjectModel::class);
                    $th_condition->setFunction('isThursday');
                    $thConditionArr = [$th_condition,$condition];

                    $thReservationTimeField = new C4GRadioGroupField();
                    $thReservationTimeField->setFieldName('beginTime');
                    $thReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                    $thReservationTimeField->setFormField(true);
                    $thReservationTimeField->setOptions(
                        C4gReservationObjectModel::getReservationTimes(
                            $reservationObjects,
                            $type['id'],
                            'th',
                            date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getNextWeekday($reservationObjects, 4)),
                            $additionalDuration,
                            $this->showEndTime,
                            $this->showFreeSeats
                        ));
                    $thReservationTimeField->setMandatory(true);
                    $thReservationTimeField->setInitInvisible(true);
                    $thReservationTimeField->setSort(false);
                    $thReservationTimeField->setCondition($thConditionArr);
                    $thReservationTimeField->setCallOnChange(true);
                    $thReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                    $thReservationTimeField->setAdditionalID($type['id'].'-004');
                    $thReservationTimeField->setNotificationField(true);
                    $thReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $thReservationTimeField->setTurnButton(true);
                    $thReservationTimeField->setRemoveWithEmptyCondition(true);
                    $thReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                    $fieldList[] = $thReservationTimeField;

                    $fr_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $type['id']);
                    $fr_condition->setModel(C4gReservationObjectModel::class);
                    $fr_condition->setFunction('isFriday');
                    $frConditionArr = [$fr_condition,$condition];

                    $frReservationTimeField = new C4GRadioGroupField();
                    $frReservationTimeField->setFieldName('beginTime');
                    $frReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                    $frReservationTimeField->setFormField(true);
                    $frReservationTimeField->setOptions(
                        C4gReservationObjectModel::getReservationTimes(
                            $reservationObjects,
                            $type['id'],
                            'fr',
                            date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getNextWeekday($reservationObjects, 5)),
                            $additionalDuration,
                            $this->showEndTime,
                            $this->showFreeSeats
                        ));
                    $frReservationTimeField->setMandatory(true);
                    $frReservationTimeField->setInitInvisible(true);
                    $frReservationTimeField->setSort(false);
                    $frReservationTimeField->setCondition($frConditionArr);
                    $frReservationTimeField->setCallOnChange(true);
                    $frReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                    $frReservationTimeField->setAdditionalID($type['id'].'-005');
                    $frReservationTimeField->setNotificationField(true);
                    $frReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $frReservationTimeField->setTurnButton(true);
                    $frReservationTimeField->setRemoveWithEmptyCondition(true);
                    $frReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                    $fieldList[] = $frReservationTimeField;

                    $sa_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $type['id']);
                    $sa_condition->setModel(C4gReservationObjectModel::class);
                    $sa_condition->setFunction('isSaturday');
                    $saConditionArr = [$sa_condition,$condition];

                    $saReservationTimeField = new C4GRadioGroupField();
                    $saReservationTimeField->setFieldName('beginTime');
                    $saReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                    $saReservationTimeField->setFormField(true);
                    $saReservationTimeField->setEditable(false);
                    $saReservationTimeField->setOptions(
                        C4gReservationObjectModel::getReservationTimes(
                            $reservationObjects,
                            $type['id'],
                            'sa',
                            date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getNextWeekday($reservationObjects, 6)),
                            $additionalDuration,
                            $this->showEndTime,
                            $this->showFreeSeats
                        ));
                    $saReservationTimeField->setMandatory(true);
                    $saReservationTimeField->setInitInvisible(true);
                    $saReservationTimeField->setSort(false);
                    $saReservationTimeField->setCondition($saConditionArr);
                    $saReservationTimeField->setCallOnChange(true);
                    $saReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                    $saReservationTimeField->setAdditionalID($type['id'].'-006');
                    $saReservationTimeField->setNotificationField(true);
                    $saReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $saReservationTimeField->setTurnButton(true);
                    $saReservationTimeField->setRemoveWithEmptyCondition(true);
                    $saReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                    $fieldList[] = $saReservationTimeField;

                }

                $objects = [];
                foreach ($reservationObjects as $reservationObject) {

                    //ToDo Check Capacity
                    $objects[] = array(
                        'id' => $reservationObject->getId(),
                        'name' => $reservationObject->getCaption(),
                        'min' => $reservationObject->getDesiredCapacity()[0] ? $reservationObject->getDesiredCapacity()[0] : 1,
                        'max' => $reservationObject->getDesiredCapacity()[1] ? $reservationObject->getDesiredCapacity()[1] : 1
                    );
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
                        'name' => $reservationObject->getCaption(),
                        'min' => $reservationObject->getDesiredCapacity()[0] ? $reservationObject->getDesiredCapacity()[0] : 1,
                        'max' => $reservationObject->getDesiredCapacity()[1] ? $reservationObject->getDesiredCapacity()[1] : 1
                    );
                }

                //save event id as reservation object
                $reservationObjectDBField = new C4GNumberField();
                $reservationObjectDBField->setFieldName('reservation_object');
                $reservationObjectDBField->setDatabaseField(true);
                $reservationObjectDBField->setFormField(false);
                $fieldList[] = $reservationObjectDBField;

                //save beginDate
                $reservationBeginDateDBField = new C4GNumberField();
                $reservationBeginDateDBField->setFieldName('beginDate');
                $reservationBeginDateDBField->setInitialValue(0);
                $reservationBeginDateDBField->setDatabaseField(true);
                $reservationBeginDateDBField->setFormField(false);
                $reservationBeginDateDBField->setMax(999999999999);
                $fieldList[] = $reservationBeginDateDBField;

                //save beginTime
                $reservationBeginTimeDBField = new C4GNumberField();
                $reservationBeginTimeDBField->setFieldName('beginTime');
                $reservationBeginTimeDBField->setInitialValue(0);
                $reservationBeginTimeDBField->setDatabaseField(true);
                $reservationBeginTimeDBField->setFormField(false);
                $reservationBeginTimeDBField->setMax(999999999999);
                $fieldList[] = $reservationBeginTimeDBField;
            }

            //save endDate
            $reservationEndDateDBField = new C4GNumberField();
            $reservationEndDateDBField->setFieldName('endDate');
            $reservationEndDateDBField->setInitialValue(0);
            $reservationEndDateDBField->setDatabaseField(true);
            $reservationEndDateDBField->setFormField(false);
            $reservationEndDateDBField->setMax(9999999999999);
            $fieldList[] = $reservationEndDateDBField;

            //save endTime
            $reservationEndTimeDBField = new C4GNumberField();
            $reservationEndTimeDBField->setFieldName('endTime');
            $reservationEndTimeDBField->setInitialValue(0);
            $reservationEndTimeDBField->setDatabaseField(true);
            $reservationEndTimeDBField->setFormField(false);
            $reservationEndTimeDBField->setMax(9999999999999);
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
            $reservationObjectField->setRangeField('desiredCapacity_' . $type['id']);
            $reservationObjectField->setStyleClass($isEvent ? 'reservation-event-object displayReservationObjects' : 'reservation-object displayReservationObjects');
            $reservationObjectField->setWithEmptyOption(!$isEvent); //ToDo
            $reservationObjectField->setShowIfEmpty(true); //ToDo
            $reservationObjectField->setDatabaseField(!$isEvent);
            $reservationObjectField->setEmptyOptionLabel($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object_none']);
            $reservationObjectField->setCondition([$condition]);
            $reservationObjectField->setRemoveWithEmptyCondition(true);
            $reservationObjectField->setCallOnChange($isEvent);
            $reservationObjectField->setCallOnChangeFunction("checkEventFields(this)");
            $reservationObjectField->setAdditionalID($type["id"]);
            $fieldList[] = $reservationObjectField;

            if ($isEvent) {
                foreach ($reservationObjects as $reservationObject) {
                    $val_condition = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'reservation_object_event_' . $type['id'], $reservationObject->getId());
                    $obj_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'reservation_object_event_' . $type['id']);
                    $obj_condition->setModel(C4gReservationObjectModel::class);
                    $obj_condition->setFunction('isEventObject');
                    $objConditionArr = [$obj_condition,$val_condition];

                    $reservationBeginDateField = new C4gDateField();
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
                    $reservationBeginDateField->setAdditionalID($type['id'].'-22'.$reservationObject->getId());
                    $reservationBeginDateField->setStyleClass('begindate-event');
                    $fieldList[] = $reservationBeginDateField;

                    $reservationEndDateField = new C4GDateField();
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
                    $reservationEndDateField->setAdditionalID($type['id'].'-22'.$reservationObject->getId());
                    $reservationEndDateField->setShowIfEmpty(false);
                    $reservationEndDateField->setStyleClass('enddate-event');
                    $fieldList[] = $reservationEndDateField;

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
                    $reservationBeginTimeField->setAdditionalID($type['id'].'-22'.$reservationObject->getId());
                    $reservationBeginTimeField->setNotificationField(true);
                    $reservationBeginTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $reservationBeginTimeField->setTurnButton(true);
                    $reservationBeginTimeField->setRemoveWithEmptyCondition(true);
                    $reservationBeginTimeField->setStyleClass('reservation_time_event_button reservation_time_event_button_'.$type['id'].'-22'.$reservationObject->getId());
                    $fieldList[] = $reservationBeginTimeField;

                    //ToDo implement additional event information
                    /*
                      {{c4gevent::<?= $this->id ?>::number}}<br>
                      {{c4gevent::<?= $this->id ?>::title}}<br>
                      {{c4gevent::<?= $this->id ?>::topic}}<br>
                      {{c4gevent::<?= $this->id ?>::audience}}<br>
                      {{c4gevent::<?= $this->id ?>::speaker}}<br>
                      {{c4gevent::<?= $this->id ?>::location}}<br>
                      {{c4gevent::<?= $this->id ?>::address}}<br>
                     */
                }
            }

            $params = $type['additionalParams'];
            $additionalParamsArr = [];

            if ($params) {
                foreach ($params as $paramId) {
                    $additionalParam = C4gReservationParamsModel::findByPk($paramId);
                    if ($additionalParam && $additionalParam->caption && ($additionalParam->price && $this->showPrices)) {
                        $additionalParamsArr[] = ['id' => $paramId, 'name' => $additionalParam->caption."<span class='price'>(+".number_format($additionalParam->price,2)." Euro)</span>"]; //ToDO Einheit, Foratierung
                    } else if ($additionalParam && $additionalParam->caption) {
                        $additionalParamsArr[] = ['id' => $paramId, 'name' => $additionalParam->caption];
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
                $additionalParams->setNotificationField(true);
                $additionalParams->setCondition(array($condition));
                $additionalParams->setRemoveWithEmptyCondition(true);
                $additionalParams->setAdditionalID($type['id'].'-00'.$reservationObject->getId());
                $additionalParams->setStyleClass('additional-params');
                $fieldList[] = $additionalParams;
            }
        }
        //end foreach type

        if (!$typelist || count($typelist) <= 0){
            $reservationNoneTypeField = new C4GLabelField();
            $reservationNoneTypeField->setDatabaseField(false);
            $reservationNoneTypeField->setInitialValue($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_none']);
            $fieldList[] = $reservationNoneTypeField;
        }

        $bookerHeadline = new C4GHeadlineField();
        $bookerHeadline->setTitle('Ihre Daten'); //ToDo
        $fieldList[] = $bookerHeadline;

        $salutation = [
            ['id' => 'man' ,'name' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['man']],
            ['id' => 'woman','name' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['woman']],
            ['id' => 'various','name' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['various']],
        ];
        
        $salutationField = new C4GSelectField();
        $salutationField->setFieldName('salutation');
        $salutationField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['salutation']);
        $salutationField->setSortColumn(false);
        $salutationField->setTableColumn(false);
        $salutationField->setOptions($salutation);
        $salutationField->setMandatory(false);
        //$reservationTypeField->setChosen(true);
        $salutationField->setCallOnChange(true);
        $salutationField->setInitialCallOnChange(false);
        $salutationField->setNotificationField(true);
//        $salutationField->setCondition(array($condition));
//        $salutationField->setRemoveWithEmptyCondition(true);
        $salutationField->setStyleClass('salutation');
        $fieldList[] = $salutationField;

        $firstnameField = new C4GTextField();
        $firstnameField->setFieldName('firstname');
        $firstnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname']);
        $firstnameField->setColumnWidth(10);
        $firstnameField->setSortColumn(false);
        $firstnameField->setTableColumn(true);
        $firstnameField->setMandatory(true);
        $firstnameField->setNotificationField(true);
//        $firstnameField->setCondition(array($condition));
//        $firstnameField->setRemoveWithEmptyCondition(true);
        $firstnameField->setStyleClass('firsname');
        $fieldList[] = $firstnameField;

        $lastnameField = new C4GTextField();
        $lastnameField->setFieldName('lastname');
        $lastnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname']);
        $lastnameField->setColumnWidth(10);
        $lastnameField->setSortColumn(false);
        $lastnameField->setTableColumn(true);
        $lastnameField->setMandatory(true);
        $lastnameField->setNotificationField(true);
//        $lastnameField->setCondition(array($condition));
//        $lastnameField->setRemoveWithEmptyCondition(true);
        $lastnameField->setStyleClass('lastname');
        $fieldList[] = $lastnameField;

        $emailField = new C4GEmailField();
        $emailField->setFieldName('email');
        $emailField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['email']);
        $emailField->setColumnWidth(10);
        $emailField->setSortColumn(false);
        $emailField->setTableColumn(false);
        $emailField->setMandatory(true);
        $emailField->setNotificationField(true);
//        $emailField->setCondition(array($condition));
//        $emailField->setRemoveWithEmptyCondition(true);
        $emailField->setStyleClass('email');
        $fieldList[] = $emailField;

        $additionaldatas = StringUtil::deserialize($this->hide_selection);
        foreach ($additionaldatas as $rowdata) {
            $rowField = $rowdata['additionaldatas'];
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
//                $organisationField->setCondition(array($condition));
//                $organisationField->setRemoveWithEmptyCondition(true);
                $organisationField->setStyleClass('organisation');
                $fieldList[] = $organisationField;

            } else if ($rowField == "phone") {
                $phoneField = new C4GTelField();
                $phoneField->setFieldName('phone');
                $phoneField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['phone']);
                $phoneField->setColumnWidth(10);
                $phoneField->setSortColumn(false);
                $phoneField->setMandatory($rowMandatory);
                $phoneField->setTableColumn(false);
                $phoneField->setNotificationField(true);
//                $phoneField->setCondition(array($condition));
//                $phoneField->setRemoveWithEmptyCondition(true);
                $phoneField->setStyleClass('phone');
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
//                $addressField->setCondition(array($condition));
//                $addressField->setRemoveWithEmptyCondition(true);
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
//                $postalField->setCondition(array($condition));
//                $postalField->setRemoveWithEmptyCondition(true);
                $postalField->setStyleClass('postal');
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
//                $cityField->setCondition(array($condition));
//                $cityField->setRemoveWithEmptyCondition(true);
                $cityField->setStyleClass('city');
                $fieldList[] = $cityField;

            } else if ($rowField == "comment") {
                $commentField = new C4GTextareaField();
                $commentField->setFieldName('comment');
                $commentField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['comment']);
                //$commentField->setDescription($GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_comment']);
                $commentField->setColumnWidth(60);
                $commentField->setSortColumn(false);
                $commentField->setTableColumn(false);
                $commentField->setMandatory($rowMandatory);
                $commentField->setNotificationField(true);
//                $commentField->setCondition(array($condition));
//                $commentField->setRemoveWithEmptyCondition(true);
                $commentField->setStyleClass('comment');
                $fieldList[] = $commentField;
            } else if ($rowField == "participants") {

                $participantsHeadline = new C4GHeadlineField();
                $participantsHeadline->setTitle('Bitte tragen Sie hier alle Teilnehmer ein'); //ToDo
                $fieldList[] = $participantsHeadline;

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
                $firstnameField->setMandatory(true);
                $firstnameField->setNotificationField(true);
                $participants[] = $firstnameField;

                $lastnameField = new C4GTextField();
                $lastnameField->setFieldName('lastname');
                $lastnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname']);
                $lastnameField->setColumnWidth(10);
                $lastnameField->setSortColumn(false);
                $lastnameField->setTableColumn(true);
                $lastnameField->setMandatory(true);
                $lastnameField->setNotificationField(true);
                $participants[] = $lastnameField;

                $emailField = new C4GEmailField();
                $emailField->setFieldName('email');
                $emailField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['email']);
                $emailField->setColumnWidth(10);
                $emailField->setSortColumn(false);
                $emailField->setTableColumn(false);
                $emailField->setMandatory(false);
                $emailField->setNotificationField(true);
                $participants[] = $emailField;

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
                $reservationParticipants->setMax(intval($type->maxParticipantsPerBooking) > 0 ? $type->maxParticipantsPerBooking : -1); //ToDo Test
                $fieldList[] = $reservationParticipants;
            }
        }

        $reservationIdField = new C4GTextField();
        $reservationIdField->setFieldName('reservation_id');
        $reservationIdField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_id']);
        $reservationIdField->setDescription($GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_reservation_id']);
        $reservationIdField->setColumnWidth(10);
        $reservationIdField->setSortColumn(true);
        $reservationIdField->setTableColumn(true);
        $reservationIdField->setMandatory(true);
        $reservationIdField->setInitialValue(C4GBrickCommon::getUUID());
        $reservationIdField->setTableRow(true);
        //$reservationIdField->setTableRowLabelWidth(98);
        $reservationIdField->setEditable(false);
        $reservationIdField->setUnique(true);
        $reservationIdField->setNotificationField(true);
        $reservationIdField->setDbUnique(true);
        $reservationIdField->setSimpleTextWithoutEditing(false);
        $reservationIdField->setDatabaseField(true);
        $reservationIdField->setDbUniqueResult($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_id_exists']);
        $reservationIdField->setDbUniqueAdditionalCondition("tl_c4g_reservation.cancellation <> '1' AND tl_c4g_reservation.beginDate > UNIX_TIMESTAMP(NOW())");
        //$reservationIdField->setCondition(array($condition));
        //$reservationIdField->setRemoveWithEmptyCondition(true);
        $reservationIdField->setStyleClass('reservation-id');
        $fieldList[] = $reservationIdField;

        if ($this->privacy_policy_text) {
            $privacyPolicyText = new C4GTextField();
            $privacyPolicyText->setSimpleTextWithoutEditing(true);
            $privacyPolicyText->setFieldName('privacy_policy_text');
            $privacyPolicyText->setInitialValue(\Contao\Controller::replaceInsertTags($this->privacy_policy_text));
            $privacyPolicyText->setSize(4);
            //$privacyPolicyText->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['privacy_policy_text']);
            //$privacyPolicyText->setDescription($GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_comment']);
            //$privacyPolicyText->setColumnWidth(60);
            //$privacyPolicyText->setSortColumn(false);
            $privacyPolicyText->setTableColumn(false);
            $privacyPolicyText->setEditable(false);
            $privacyPolicyText->setDatabaseField(false);
            $privacyPolicyText->setMandatory(false);
            $privacyPolicyText->setNotificationField(false);
            //$privacyPolicyText->setCondition(array($condition));
            //$privacyPolicyText->setRemoveWithEmptyCondition(true);
            $privacyPolicyText->setStyleClass('privacy-policy-text');
            $fieldList[] = $privacyPolicyText;
        }

        $agreedField = new C4GCheckboxField();
        $agreedField->setFieldName('agreed');
        $agreedField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['agreed']);
        if ($this->privacy_policy_site) {
            $href = \Contao\Controller::replaceInsertTags('{{link_url::' . $this->privacy_policy_site . '}}');
            $agreedField->setDescription($GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed'] . '<a href="' . $href . '" target="_blank" rel="noopener">' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed_link_text'] . '</a>');
        }
        $agreedField->setTableRow(true);
        $agreedField->setColumnWidth(5);
        $agreedField->setSortColumn(false);
        $agreedField->setTableColumn(false);
        $agreedField->setMandatory(true);
        $agreedField->setNotificationField(true);
        //$agreedField->setCondition(array($condition));
        //$agreedField->setRemoveWithEmptyCondition(true);
        $agreedField->setStyleClass('agreed');
        $fieldList[] = $agreedField;

        $clickButton = new C4GBrickButton(C4GBrickConst::BUTTON_CLICK, $GLOBALS['TL_LANG']['fe_c4g_reservation']['button_reservation'], $visible = true, $enabled = true, $action = '', $accesskey = '', $defaultByEnter = true);
        $buttonField = new C4GButtonField($clickButton);
        $buttonField->setOnClickType(C4GBrickConst::ONCLICK_TYPE_SERVER);
        $buttonField->setOnClick('clickReservation');
        $buttonField->setWithoutLabel(true);
        //$buttonField->setCondition(array($condition));
        //$buttonField->setRemoveWithEmptyCondition(true);
        $fieldList[] = $buttonField;


        $business_name = new C4GTextField();
        $business_name->setFieldName('business_name');
        $business_name->setSortColumn(false);
        $business_name->setFormField(false);
        $business_name->setTableColumn(true);
        $business_name->setNotificationField(true);
        $business_name->setStyleClass('contact-name');
        $fieldList[] = $business_name;

        $business_phone = new C4GTelField();
        $business_phone->setFieldName('business_phone');
        $business_phone->setFormField(false);
        $business_phone->setTableColumn(false);
        $business_phone->setNotificationField(true);
        $business_phone->setStyleClass('contact-phone');
        $fieldList[] = $business_phone;

        $business_email = new C4GEmailField();
        $business_email->setFieldName('business_email');
        $business_email->setTableColumn(false);
        $business_email->setFormField(false);
        $business_email->setNotificationField(true);
        $business_email->setStyleClass('contact-email');
        $fieldList[] = $business_email;


        $business_street = new C4GTextField();
        $business_street->setFieldName('business_street');
        $business_street->setTableColumn(false);
        $business_street->setFormField(false);
        $business_street->setNotificationField(true);
        $business_street->setStyleClass('contact-street');
        $fieldList[] = $business_street;


        $business_postal = new C4GPostalField();
        $business_postal->setFieldName('business_postal');
        $business_postal->setFormField(false);
        $business_postal->setTableColumn(false);
        $business_postal->setNotificationField(true);
        $business_postal->setStyleClass('contact-postal');
        $fieldList[] = $business_postal;


        $business_city = new C4GTextField();
        $business_city->setFieldName('business_city');
        $business_city->setTableColumn(false);
        $business_city->setFormField(false);
        $business_city->setNotificationField(true);
        $business_city->setStyleClass('contact-city');
        $fieldList[] = $business_city;

        $this->fieldList = $fieldList;
    }




    public function createIcs($begin_date,$begin_time, $objectId,$typeId)
    {
        $checkdb = $this->Database->prepare("SELECT * FROM tl_c4g_reservation_object WHERE id=? AND published='1'")
            ->execute($objectId);

        $vcard= $checkdb->vcard_show;

        if ($vcard == null) {
            $icsdb = $this->Database->prepare("SELECT * FROM tl_c4g_reservation_type WHERE id=? AND published='1'")
                ->execute($typeId);
            $business_street = $icsdb->business_street;
            $business_postal = $icsdb->business_postal;
            $business_city = $icsdb->business_city;
        }
        if ($vcard == 1) {
            $business_street = $checkdb->business_street;
            $business_postal = $checkdb->business_postal;
            $business_city = $checkdb->business_city;
        }

        $businessdata = $this->Database->prepare("SELECT * FROM tl_c4g_reservation_type WHERE id=? AND published='1'")
            ->execute($typeId);
        $business_name = $businessdata->business_name;
        $business_email = $businessdata->business_email;

        $icstimezone = 'TZID=Europe/Berlin';
        $icsdaylightsaving= date('I');
        $icsprodid = $business_name;
        $icslocation = $business_street ." ". $business_postal." ". $business_city;
        $icsuid = $business_email;

        if ($icsdaylightsaving == 1) {
            $begin_time = $begin_time - 7200;
        }
        if ($icsdaylightsaving == 0) {
            $begin_time = $begin_time - 3600;
        }

        $b_date =date('Ymd', strtotime($begin_date));
        $b_time = date('His', $begin_time);
        $icsdate = $b_date . 'T' . $b_time . 'Z';

        $dbResult = $this->Database->prepare("SELECT * FROM tl_c4g_reservation_object WHERE id=? AND published='1'")
            ->execute($objectId);

        $icsalert = $dbResult->alert_time;
        $residence = $dbResult->min_residence_time;
        $time_int = $dbResult->time_interval;
        $icssummary = $dbResult->caption;

        $icsalert = $icsalert * 60;
        $icsalert = '-PT'.$icsalert.'M';

        if ($residence != 0) {
            $residence = $residence * 3600;
            $e_date = date('Ymd',strtotime($begin_date));
            $e_time = $begin_time + $residence;
            $e_time = date('His',$e_time) ;
            $icsenddate =$e_date . 'T' . $e_time. 'Z';
        } else {
            $time_int = $time_int * 3600;
            $e_date = date('Ymd',strtotime($begin_date));
            $e_time = $begin_time + $time_int;
            $e_time = date('His',$e_time) ;
            $icsenddate =$e_date . 'T' . $e_time. 'Z';
        }
        $filename = System::getContainer()->getParameter("kernel.project_dir") . "/files/Kalendereintrag.ics";
        try {
            $ics = new File($filename);
        } catch (\Exception $exception) {
            $fs = new Filesystem();
            $fs->touch($filename);
            $ics = new File($filename);
        }
        $ics->openFile("w")->fwrite("BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:$icsprodid\nMETHOD:PUBLISH\nBEGIN:VEVENT\nUID:$icsuid\nLOCATION:$icslocation\nSUMMARY:$icssummary\nCLASS:PUBLIC\nDESCRIPTION:$icssummary\nDTSTART:$icsdate\nDTEND:$icsenddate\nBEGIN:VALARM\nTRIGGER:$icsalert\nACTION:DISPLAY\nDESCRIPTION:$icssummary\nEND:VALARM\nEND:VEVENT\nEND:VCALENDAR\n");

    }

    public function clickReservation($values, $putVars)
    {
        $type = $putVars['reservation_type'];
        $newFieldList = [];
        $removedFromList = [];
        foreach ($this->getFieldList() as $key=>$field) {
            $additionalId = $field->getAdditionalID();
            if ($additionalId && (($additionalId != $type) && (strpos($additionalId, strval($type.'-')) !== 0))) {
                unset($putVars[$field->getFieldName()."_".$additionalId]);
                continue;
            } else if ($additionalId) {
                $removedFromList[$field->getFieldName()] = $field->getAdditionalID();
                unset($putVars[$field->getFieldName()]);
            }

            $reservationType = $this->Database->prepare("SELECT * FROM tl_c4g_reservation_type WHERE id=? AND published='1'")
                ->execute($type);
            $isEvent = $reservationType->reservationObjectType && $reservationType->reservationObjectType === '2' ? true : false;
            if ($isEvent) {
                $key = "reservation_object_event_" . $type;
                $resObject = $putVars[$key];
                $reservationObject = $this->Database->prepare("SELECT * FROM tl_calendar_events WHERE id=? AND published='1'")
                    ->execute($resObject);
            } else {
                $key = "reservation_object_" . $type;
                $resObject = $putVars[$key];
                $reservationObject = $this->Database->prepare("SELECT * FROM tl_c4g_reservation_object WHERE id=? AND published='1'")
                    ->execute($resObject);
            }

            $business_name = $reservationType->business_name;
            $business_email = $reservationType->business_email;
            $vcard = $reservationObject->vcard_show;
            if ($vcard) {
                $business_street = $reservationObject->business_street;
                $business_phone = $reservationObject->business_phone;
                $business_postal = $reservationObject->business_postal;
                $business_city = $reservationObject->business_city;
            } else {
                $business_street = $reservationType->business_street;
                $business_phone = $reservationType->business_phone;
                $business_postal = $reservationType->business_postal;
                $business_city = $reservationType->business_city;
            }

            $putVars['business_name'] = $business_name;
            $putVars['business_phone'] = $business_phone;
            $putVars['business_email'] = $business_email;
            $putVars['business_street'] = $business_street;
            $putVars['business_postal'] = $business_postal;
            $putVars['business_city'] = $business_city;

            if ($field->getFieldName() && (!$removedFromList[$field->getFieldName()] || ($removedFromList[$field->getFieldName()] == $field->getAdditionalId()))) {
                $newFieldList[] = $field;
            }
        }

        if ($isEvent) {
            $putVars['reservationObjectType'] = '2';
            $putVars['reservation_object'] = $putVars['reservation_object_event_'.$type];

            //implement all event possibilities
            $putVars['beginDate'] = $reservationObject->startDate ? intvaL($reservationObject->startDate) : 0; //ToDo undefined check
            $putVars['beginTime'] = $reservationObject->startTime ? intval($reservationObject->startTime) : 0;
            $putVars['endDate'] = $reservationObject->endDate ? intval($reservationObject->endDate) : 0;
            $putVars['endTime'] = $reservationObject->endTime ? intval($reservationObject->endTime) : 0;
       } else {
            $putVars['reservationObjectType'] = '1';
            $beginDate = $putVars['beginDate_'.$type];

            $beginTime = 0;
            foreach ($putVars as $key => $value) {
                if (strpos($key, "beginTime_".$type) !== false) {
                    if ($value) {
                        $beginTime = $value;
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
            if ($duration && (($duration >= $min_residence_time) && ($duration <= $max_residence_time))) {
                //$duration = $duration;
            } else {
                $duration = $time_interval;
            }

            $duration = $duration * $interval;
            $endTime = $beginTime + $duration;

            $putVars['endDate'] = strtotime($putVars['beginDate_'.$type]); //ToDo multiple days
            $putVars['endTime'] = $endTime;
        }

        $action = new C4GSaveAndRedirectDialogAction($this->dialogParams, $this->getListParams(), $newFieldList, $putVars, $this->getBrickDatabase());
        $action->setModule($this);

        //ToDo check with events
        if (!$isEvent) {
            $this->createIcs($beginDate, $beginTime, $resObject, $type);
        }

        return $result = $action->run();
    }

    public function getCurrentTimeset($values, $putVars)
    {
        $date = $values[2];
        $additionalParam = $values[3];
        $duration = $values[4];
        $weekday = -1;
        $wd = -1;

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
            switch ($wd) {
                case 0:
                    $weekday = 'su';
                    break;
                case 1:
                    $weekday = 'mo';
                    break;
                case 2:
                    $weekday = 'tu';
                    break;
                case 3:
                    $weekday = 'we';
                    break;
                case 4:
                    $weekday = 'th';
                    break;
                case 5:
                    $weekday = 'fr';
                    break;
                case 6:
                    $weekday = 'sa';
                    break;
            }
        }

        $objects = C4gReservationObjectModel::getReservationObjectList(array($additionalParam));
        $withEndTimes = $this->showEndTime;
        $withFreeSeats = $this->showFreeSeats;

        $times = C4gReservationObjectModel::getReservationTimes($objects, $additionalParam, $weekday, $date, $duration, $withEndTimes, $withFreeSeats);

        if ($additionalParam) {
            if ($this->fieldList) {
                foreach ($this->fieldList as $key => $field) {
                    if (($field->getFieldName() == 'beginTime') && ($field->getAdditionalId() == $additionalParam . '00' . $wd)) {
                        $this->fieldList[$key]->setOptions($times);
                        break;
                    }
                }
            }
        }

        return array(
            'times' => $times
        );
    }

}

