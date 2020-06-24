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

use con4gis\CoreBundle\Resources\contao\models\C4gLogModel;
use con4gis\ProjectsBundle\Classes\Actions\C4GSaveAndRedirectDialogAction;
use con4gis\ProjectsBundle\Classes\Buttons\C4GBrickButton;
use con4gis\ProjectsBundle\Classes\Common\C4GBrickCommon;
use con4gis\ProjectsBundle\Classes\Common\C4GBrickConst;
use con4gis\ProjectsBundle\Classes\Conditions\C4GBrickCondition;
use con4gis\ProjectsBundle\Classes\Conditions\C4GBrickConditionType;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GButtonField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GCheckboxField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GDateField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GEmailField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GKeyField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GMultiCheckboxField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GNumberField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GPostalField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GRadioGroupField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GSelectField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTelField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTextareaField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTextField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTimepickerField;
use con4gis\ProjectsBundle\Classes\Framework\C4GBrickModuleParent;
use con4gis\ProjectsBundle\Classes\Views\C4GBrickViewType;
use con4gis\ReservationBundle\Classes\C4gReservationBrickTypes;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationObjectModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationParamsModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationTypeModel;
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
    protected $brickScript  = 'bundles/con4gisreservation/js/c4g_brick_reservation_0.4.0.js';
    protected $brickStyle   = 'bundles/con4gisreservation/css/c4g_brick_reservation_0.4.0.css';
    protected $withNotification = true;


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
//        $this->dialogParams->addButton(C4GBrickConst::BUTTON_SAVE_AND_REDIRECT, $GLOBALS['TL_LANG']['fe_c4g_reservation']['button_reservation'], $visible=true, $enabled=true, $action = '', $accesskey = '', $defaultByEnter = true);
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

//        $headlineReservationField = new C4GHeadlineField();
//        $headlineReservationField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_data']);
//        $fieldList[] = $headlineReservationField;
//
        $typelist = array();
        $types = C4gReservationTypeModel::findBy('published', '1');
        $firstType = 0;
        if ($types) {
            $moduleTypes = unserialize($this->reservation_types);
            foreach ($types as $type) {
                if ($moduleTypes && (count($moduleTypes) > 0)) {
                    $arrModuleTypes = $moduleTypes;
                    if (!in_array($type->id, $arrModuleTypes)) {
                        continue;
                    }
                }

                $objects = C4gReservationObjectModel::getReservationObjectList(array($type->id));
                if (!$objects || (count($objects) <= 0)) {
                    continue;
                }

                $captions = unserialize($type->options);
                if ($captions) {
                    foreach ($captions as $caption) {
                        if ($caption['language'] == $GLOBALS['TL_LANGUAGE']) {
                            $typelist[$type->id] = array(
                                'id' => $type->id,
                                'name' => $caption['caption'],
                                'periodType' => $type->periodType,
                                'additionalParams' => unserialize($type->additional_params),
                                'objects' => $objects
                            );

                            if (!$firstType) {
                                $firstType = $type->id;
                            }

                        }
                    }
                }
            }
        }

        if (count($typelist) > 0) {
            $reservationTypeField = new C4GSelectField();
            $reservationTypeField->setFieldName('reservation_type');
            $reservationTypeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_type']);
            $reservationTypeField->setSortColumn(false);
            $reservationTypeField->setTableColumn(false);
            $reservationTypeField->setColumnWidth(20);
            $reservationTypeField->setSize(1);
            $reservationTypeField->setOptions($typelist);
            $reservationTypeField->setMandatory(true);
            //$reservationTypeField->setChosen(true);
            $reservationTypeField->setWithEmptyOption(true);
            $reservationTypeField->setEmptyOptionLabel($GLOBALS['TL_LANG']['fe_c4g_reservation']['pleaseSelect']);
            $reservationTypeField->setCallOnChange(true);
            $reservationTypeField->setCallOnChangeFunction("setTimeset(this, " . $this->id . ", -1 ,'getCurrentTimeset')");
            $reservationTypeField->setInitialCallOnChange(false);
            $reservationTypeField->setInitialValue(-1);
            $reservationTypeField->setNotificationField(true);
            $fieldList[] = $reservationTypeField;
        }

        foreach ($typelist as $type) {
            $reservationObjects = $type['objects'];

            $condition = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'reservation_type', $type['id']);

//                $reservationPeriodType = new C4GSelectField();
//                $reservationPeriodType->setFieldName('periodType');
//                $reservationPeriodType->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['periodType']);
//                $reservationPeriodType->setFormField(true);
//                $reservationPeriodType->setEditable(false);
//                $reservationPeriodType->setCondition($condition);
//                $reservationPeriodType->setMandatory(false);
//                $reservationPeriodType->setOptions(array(array('id' => $type->id, 'name' => $GLOBALS['TL_LANG']['fe_c4g_reservation'][$type['periodType']])));
//                //$reservationPeriodType->setCallOnChange(true);
//                //$reservationObjMehrtägig TestectField->setNotificationField(true);
//                $fieldList[] = $reservationPeriodType;

            $conditionCapacity = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'desiredCapacity_'.$type['id']);
            $reservationDesiredCapacity = new C4GNumberField();
            $reservationDesiredCapacity->setFieldName('desiredCapacity');
            $reservationDesiredCapacity->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['desiredCapacity']);
            $reservationDesiredCapacity->setFormField(true);
            $reservationDesiredCapacity->setEditable(true);
            $reservationDesiredCapacity->setCondition(array($condition));
            $reservationDesiredCapacity->setMandatory(true);
            $reservationDesiredCapacity->setCallOnChange(true);
            $reservationDesiredCapacity->setCallOnChangeFunction("setTimeset(this, " . $this->id . "," . $type['id'] . ",'getCurrentTimeset');");
            $reservationDesiredCapacity->setNotificationField(true);
            $reservationDesiredCapacity->setAdditionalID($type['id']);
            $fieldList[] = $reservationDesiredCapacity;


            if (($type['periodType'] === 'minute') || ($type['periodType'] === 'hour')  /*|| ($type['periodType'] === 'event') || ($type['periodType'] === 'hour_period') || ($type['periodType'] === 'minute_period')*/ ) {
                $conditionDate = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'beginDate_'.$type['id']);
                $reservationBeginDateField = new C4GDateField();
                $reservationBeginDateField->setMinDate(C4gReservationObjectModel::getMinDate($reservationObjects));
                $reservationBeginDateField->setMaxDate(C4gReservationObjectModel::getMaxDate($reservationObjects));
                $reservationBeginDateField->setExcludeWeekdays(C4gReservationObjectModel::getWeekdayExclusionString($reservationObjects));
                $reservationBeginDateField->setExcludeDates(C4gReservationObjectModel::getDateExclusionString($reservationObjects));
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
                $fieldList[] = $reservationBeginDateField;
            }

            if (($type['periodType'] === 'minute_period') || ($type['periodType'] === 'hour_period')) {
                $su_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $type['id']);
                $su_condition->setModel(C4gReservationObjectModel::class);
                $su_condition->setFunction('isSunday');
                $suConditionArr = [$su_condition,$condition, $conditionCapacity, $conditionDate];

                $suReservationTimeField = new C4GRadioGroupField();
                $suReservationTimeField->setFieldName('beginTime');
                $suReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                $suReservationTimeField->setFormField(true);
                $suReservationTimeField->setOptions(C4gReservationObjectModel::getReservationTimes($reservationObjects, $type['id'], 'su', date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getMinDate($reservationObjects))));
                $suReservationTimeField->setMandatory(false);
                $suReservationTimeField->setSort(false);
                $suReservationTimeField->setCondition($suConditionArr);
                $suReservationTimeField->setCallOnChange(true);
                $suReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                $suReservationTimeField->setAdditionalID($type['id'].'000');
                $suReservationTimeField->setNotificationField(true);
                $suReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                $suReservationTimeField->setTurnButton(true);
                $suReservationTimeField->setRemoveWithEmptyCondition(true);
                $suReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                $fieldList[] = $suReservationTimeField;

                $mo_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $type['id']);
                $mo_condition->setModel(C4gReservationObjectModel::class);
                $mo_condition->setFunction('isMonday');
                $moConditionArr = [$mo_condition,$condition, $conditionCapacity, $conditionDate];

                $moReservationTimeField = new C4GRadioGroupField();
                $moReservationTimeField->setFieldName('beginTime');
                $moReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                $moReservationTimeField->setFormField(true);
                $moReservationTimeField->setOptions(C4gReservationObjectModel::getReservationTimes($reservationObjects, $type['id'], 'mo', date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getMinDate($reservationObjects))));
                $moReservationTimeField->setMandatory(false);
                $moReservationTimeField->setSort(false);
                $moReservationTimeField->setCondition($moConditionArr);
                $moReservationTimeField->setCallOnChange(true);
                $moReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                $moReservationTimeField->setAdditionalID($type['id'].'001');
                $moReservationTimeField->setNotificationField(true);
                $moReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                $moReservationTimeField->setTurnButton(true);
                $moReservationTimeField->setRemoveWithEmptyCondition(true);
                $moReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                $fieldList[] = $moReservationTimeField;

                $tu_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $type['id']);
                $tu_condition->setModel(C4gReservationObjectModel::class);
                $tu_condition->setFunction('isTuesday');
                $tuConditionArr = [$tu_condition,$condition, $conditionCapacity, $conditionDate];

                $tuReservationTimeField = new C4GRadioGroupField();
                $tuReservationTimeField->setFieldName('beginTime');
                $tuReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                $tuReservationTimeField->setFormField(true);
                $tuReservationTimeField->setOptions(C4gReservationObjectModel::getReservationTimes($reservationObjects, $type['id'], 'tu', date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getMinDate($reservationObjects))));
                $tuReservationTimeField->setMandatory(false);
                $tuReservationTimeField->setSort(false);
                $tuReservationTimeField->setCondition($tuConditionArr);
                $tuReservationTimeField->setCallOnChange(true);
                $tuReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                $tuReservationTimeField->setAdditionalID($type['id'].'002');
                $tuReservationTimeField->setNotificationField(true);
                $tuReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                $tuReservationTimeField->setTurnButton(true);
                $tuReservationTimeField->setRemoveWithEmptyCondition(true);
                $tuReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                $fieldList[] = $tuReservationTimeField;

                $we_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $type['id']);
                $we_condition->setModel(C4gReservationObjectModel::class);
                $we_condition->setFunction('isWednesday');
                $weConditionArr = [$we_condition,$condition, $conditionCapacity, $conditionDate];

                $weReservationTimeField = new C4GRadioGroupField();
                $weReservationTimeField->setFieldName('beginTime');
                $weReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                $weReservationTimeField->setFormField(true);
                $weReservationTimeField->setOptions(C4gReservationObjectModel::getReservationTimes($reservationObjects, $type['id'], 'we', date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getMinDate($reservationObjects))));
                $weReservationTimeField->setMandatory(false);
                $weReservationTimeField->setSort(false);
                $weReservationTimeField->setCondition($weConditionArr);
                $weReservationTimeField->setCallOnChange(true);
                $weReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                $weReservationTimeField->setAdditionalID($type['id'].'003');
                $weReservationTimeField->setNotificationField(true);
                $weReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                $weReservationTimeField->setTurnButton(true);
                $weReservationTimeField->setRemoveWithEmptyCondition(true);
                $weReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                $fieldList[] = $weReservationTimeField;

                $th_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $type['id']);
                $th_condition->setModel(C4gReservationObjectModel::class);
                $th_condition->setFunction('isThursday');
                $thConditionArr = [$th_condition,$condition, $conditionCapacity, $conditionDate];

                $thReservationTimeField = new C4GRadioGroupField();
                $thReservationTimeField->setFieldName('beginTime');
                $thReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                $thReservationTimeField->setFormField(true);
                $thReservationTimeField->setOptions(C4gReservationObjectModel::getReservationTimes($reservationObjects, $type['id'], 'th', date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getMinDate($reservationObjects))));
                $thReservationTimeField->setMandatory(false);
                $thReservationTimeField->setSort(false);
                $thReservationTimeField->setCondition($thConditionArr);
                $thReservationTimeField->setCallOnChange(true);
                $thReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                $thReservationTimeField->setAdditionalID($type['id'].'004');
                $thReservationTimeField->setNotificationField(true);
                $thReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                $thReservationTimeField->setTurnButton(true);
                $thReservationTimeField->setRemoveWithEmptyCondition(true);
                $thReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                $fieldList[] = $thReservationTimeField;

                $fr_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $type['id']);
                $fr_condition->setModel(C4gReservationObjectModel::class);
                $fr_condition->setFunction('isFriday');
                $frConditionArr = [$fr_condition,$condition, $conditionCapacity, $conditionDate];

                $frReservationTimeField = new C4GRadioGroupField();
                $frReservationTimeField->setFieldName('beginTime');
                $frReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                $frReservationTimeField->setFormField(true);
                $frReservationTimeField->setOptions(C4gReservationObjectModel::getReservationTimes($reservationObjects, $type['id'], 'fr', date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getMinDate($reservationObjects))));
                $frReservationTimeField->setMandatory(false);
                $frReservationTimeField->setSort(false);
                $frReservationTimeField->setCondition($frConditionArr);
                $frReservationTimeField->setCallOnChange(true);
                $frReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                $frReservationTimeField->setAdditionalID($type['id'].'005');
                $frReservationTimeField->setNotificationField(true);
                $frReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                $frReservationTimeField->setTurnButton(true);
                $frReservationTimeField->setRemoveWithEmptyCondition(true);
                $frReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                $fieldList[] = $frReservationTimeField;

                $sa_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $type['id']);
                $sa_condition->setModel(C4gReservationObjectModel::class);
                $sa_condition->setFunction('isSaturday');
                $saConditionArr = [$sa_condition,$condition, $conditionCapacity, $conditionDate];

                $saReservationTimeField = new C4GRadioGroupField();
                $saReservationTimeField->setFieldName('beginTime');
                $saReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                $saReservationTimeField->setFormField(true);
                $saReservationTimeField->setEditable(false);
                $saReservationTimeField->setOptions(C4gReservationObjectModel::getReservationTimes($reservationObjects, $type['id'], 'sa', date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getMinDate($reservationObjects))));
                $saReservationTimeField->setMandatory(false); //true doesn't work
                $saReservationTimeField->setSort(false);
                $saReservationTimeField->setCondition($saConditionArr);
                $saReservationTimeField->setCallOnChange(true);
                $saReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                $saReservationTimeField->setAdditionalID($type['id'].'006');
                $saReservationTimeField->setNotificationField(true);
                $saReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                $saReservationTimeField->setTurnButton(true);
                $saReservationTimeField->setRemoveWithEmptyCondition(true);
                $saReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                $fieldList[] = $saReservationTimeField;

//End time eingabe Timepicker da derzeit radiogroupfield nicht angezeigt wird
//
//                $reservationendTimeField = new C4GRadioGroupField();
//                $reservationendTimeField->setFieldName('endTime');
//                $reservationendTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['endTime']);
//                $reservationendTimeField->setFormField(true);
//                $reservationendTimeField->setEditable(false);
//                $reservationendTimeField->setOptions(C4gReservationObjectModel::getReservationTimes($reservationObjects, $type['id'], date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getMinDate($reservationObjects))));
//                $reservationendTimeField->setMandatory(false); //true doesn't work
//                $reservationendTimeField->setSort(false);
//                $reservationendTimeField->setCondition($saConditionArr);
//                $reservationendTimeField->setCallOnChange(true);
//                $reservationendTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
//                $reservationendTimeField->setNotificationField(true);
//                $reservationendTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
//                $reservationendTimeField->setTurnButton(true);
//                $reservationendTimeField->setRemoveWithEmptyCondition(true);
//                $reservationendTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
//                $fieldList[] = $reservationendTimeField;

                $reservationendTime = new C4GTimepickerField();
                $reservationendTime->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['endTime']);
                $reservationendTime->setFieldName('endTime');
                $reservationendTime->setComparable(false);
                $reservationendTime->setSortColumn(true);
                $reservationendTime->setSortSequence('en_datetime');
                $reservationendTime->setTableColumn(true);
                $reservationendTime->setFormField(true);
                $reservationendTime->setColumnWidth(10);
                $reservationendTime->setMandatory(true);
                $reservationendTime->setCondition(array($condition));
                $reservationendTime->setRemoveWithEmptyCondition(true);
                $fieldList[] = $reservationendTime;

                }

            if (($type['periodType'] === 'hour') || ($type['periodType'] === 'minute')) {
                $su_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $type['id']);
                $su_condition->setModel(C4gReservationObjectModel::class);
                $su_condition->setFunction('isSunday');
                $suConditionArr = [$su_condition,$condition];

                $suReservationTimeField = new C4GRadioGroupField();
                $suReservationTimeField->setFieldName('beginTime');
                $suReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                $suReservationTimeField->setFormField(true);
                $suReservationTimeField->setOptions(C4gReservationObjectModel::getReservationTimes($reservationObjects, $type['id'], 'su', date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getMinDate($reservationObjects))));
                $suReservationTimeField->setMandatory(true);
                $suReservationTimeField->setSort(false);
                $suReservationTimeField->setCondition($suConditionArr);
                $suReservationTimeField->setCallOnChange(true);
                $suReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                $suReservationTimeField->setAdditionalID($type['id'].'000');
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
                $moReservationTimeField->setOptions(C4gReservationObjectModel::getReservationTimes($reservationObjects, $type['id'], 'mo', date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getMinDate($reservationObjects))));
                $moReservationTimeField->setMandatory(true);
                $moReservationTimeField->setSort(false);
                $moReservationTimeField->setCondition($moConditionArr);
                $moReservationTimeField->setCallOnChange(true);
                $moReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                $moReservationTimeField->setAdditionalID($type['id'].'001');
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
                $tuReservationTimeField->setOptions(C4gReservationObjectModel::getReservationTimes($reservationObjects, $type['id'], 'tu', date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getMinDate($reservationObjects))));
                $tuReservationTimeField->setMandatory(true);
                $tuReservationTimeField->setSort(false);
                $tuReservationTimeField->setCondition($tuConditionArr);
                $tuReservationTimeField->setCallOnChange(true);
                $tuReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                $tuReservationTimeField->setAdditionalID($type['id'].'002');
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
                $weReservationTimeField->setOptions(C4gReservationObjectModel::getReservationTimes($reservationObjects, $type['id'], 'we', date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getMinDate($reservationObjects))));
                $weReservationTimeField->setMandatory(true);
                $weReservationTimeField->setSort(false);
                $weReservationTimeField->setCondition($weConditionArr);
                $weReservationTimeField->setCallOnChange(true);
                $weReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                $weReservationTimeField->setAdditionalID($type['id'].'003');
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
                $thReservationTimeField->setOptions(C4gReservationObjectModel::getReservationTimes($reservationObjects, $type['id'], 'th', date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getMinDate($reservationObjects))));
                $thReservationTimeField->setMandatory(true);
                $thReservationTimeField->setSort(false);
                $thReservationTimeField->setCondition($thConditionArr);
                $thReservationTimeField->setCallOnChange(true);
                $thReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                $thReservationTimeField->setAdditionalID($type['id'].'004');
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
                $frReservationTimeField->setOptions(C4gReservationObjectModel::getReservationTimes($reservationObjects, $type['id'], 'fr', date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getMinDate($reservationObjects))));
                $frReservationTimeField->setMandatory(true);
                $frReservationTimeField->setSort(false);
                $frReservationTimeField->setCondition($frConditionArr);
                $frReservationTimeField->setCallOnChange(true);
                $frReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                $frReservationTimeField->setAdditionalID($type['id'].'005');
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
                $saReservationTimeField->setOptions(C4gReservationObjectModel::getReservationTimes($reservationObjects, $type['id'], 'sa', date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getMinDate($reservationObjects))));
                $saReservationTimeField->setMandatory(true); //true doesn't work
                $saReservationTimeField->setSort(false);
                $saReservationTimeField->setCondition($saConditionArr);
                $saReservationTimeField->setCallOnChange(true);
                $saReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                $saReservationTimeField->setAdditionalID($type['id'].'006');
                $saReservationTimeField->setNotificationField(true);
                $saReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                $saReservationTimeField->setTurnButton(true);
                $saReservationTimeField->setRemoveWithEmptyCondition(true);
                $saReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                $fieldList[] = $saReservationTimeField;

            }


//          Booking of several days and event bookings will be added in upcoming versions
//            if ($type['periodType'] === 'md')
//                  {
//                  }
//
//        else if ($type['periodType'] === 'event') {
//                $reservationTimeBegin = new C4GTimepickerField();
//                $reservationTimeBegin->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
//                $reservationTimeBegin->setFieldName('beginTime');
//                $reservationTimeBegin->setComparable(false);
//                $reservationTimeBegin->setSortColumn(true);
//                $reservationTimeBegin->setSortSequence('de_datetime');
//                $reservationTimeBegin->setTableColumn(true);
//                $reservationTimeBegin->setFormField(true);
//                $reservationTimeBegin->setColumnWidth(10);
//                $reservationTimeBegin->setMandatory(true);
//                $reservationTimeBegin->setCondition(array($condition));
//                $reservationTimeBegin->setRemoveWithEmptyCondition(true);
//                $fieldList[] = $reservationTimeBegin;
//            }


            $objects = [];
            foreach ($reservationObjects as $reservationObject) {

                //ToDo Check Capacity
                $objects[] = array(
                    'id' => $reservationObject->getId(),
                    'name' => $reservationObject->getCaption(),
                    'min' => $reservationObject->getDesiredCapacity()[0],
                    'max' => $reservationObject->getDesiredCapacity()[1]);
            }

            $reservationObjectField = new C4GSelectField();
            $reservationObjectField->setFieldName('reservation_object');
            $reservationObjectField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object']);
            $reservationObjectField->setFormField(true);
            $reservationObjectField->setEditable(true);
            $reservationObjectField->setOptions($objects);
            $reservationObjectField->setMandatory(true);
            $reservationObjectField->setNotificationField(true);
            $reservationObjectField->setRangeField('desiredCapacity_' . $type['id']);
            $reservationObjectField->setStyleClass('displayReservationObjects');
            $reservationObjectField->setWithEmptyOption(true);
            $reservationObjectField->setShowIfEmpty(true);
            $reservationObjectField->setEmptyOptionLabel($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object_none']);
            $reservationObjectField->setCondition(array($condition));
            $reservationObjectField->setRemoveWithEmptyCondition(true);
            $reservationObjectField->setAdditionalID($type['id']);
            $fieldList[] = $reservationObjectField;

            $params = $type['additionalParams'];
            $additionalParamsArr = [];
            foreach ($params as $paramId) {
                $additionalParam = C4gReservationParamsModel::findByPk($paramId);
                $additionalParamsArr[] = ['id' => $paramId, 'name' => $additionalParam->caption];
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
                $additionalParams->setAdditionalID($type['id']);
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
        $fieldList[] = $salutationField;

        $firstnameField = new C4GTextField();
        $firstnameField->setFieldName('firstname');
        $firstnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname']);
        $firstnameField->setColumnWidth(10);
        $firstnameField->setSortColumn(false);
        $firstnameField->setTableColumn(true);
        $firstnameField->setMandatory(true);
        $firstnameField->setNotificationField(true);
        $fieldList[] = $firstnameField;

        $lastnameField = new C4GTextField();
        $lastnameField->setFieldName('lastname');
        $lastnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname']);
        $lastnameField->setColumnWidth(10);
        $lastnameField->setSortColumn(false);
        $lastnameField->setTableColumn(true);
        $lastnameField->setMandatory(true);
        $lastnameField->setNotificationField(true);
        $fieldList[] = $lastnameField;

        $emailField = new C4GEmailField();
        $emailField->setFieldName('email');
        $emailField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['email']);
        $emailField->setColumnWidth(10);
        $emailField->setSortColumn(false);
        $emailField->setTableColumn(false);
        $emailField->setMandatory(true);
        $emailField->setNotificationField(true);
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
                //   if()
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
                $fieldList[] = $commentField;
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
        $reservationIdField->setDbUniqueAdditionalCondition("tl_c4g_reservation.cancellation <> '1' AND tl_c4g_reservation.reservation_date > UNIX_TIMESTAMP(NOW())");
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
        $fieldList[] = $agreedField;

        $clickButton = new C4GBrickButton(C4GBrickConst::BUTTON_CLICK, $GLOBALS['TL_LANG']['fe_c4g_reservation']['button_reservation'], $visible = true, $enabled = true, $action = '', $accesskey = '', $defaultByEnter = true);
        $buttonField = new C4GButtonField($clickButton);
        $buttonField->setOnClickType(C4GBrickConst::ONCLICK_TYPE_SERVER);
        $buttonField->setOnClick('clickReservation');
        $buttonField->setWithoutLabel(true);
        $fieldList[] = $buttonField;

        $this->fieldList = $fieldList;
    }


    public function createIcs($begin_date,$begin_time, $objectId,$typeId)
    {
        $checkdb = $this->Database->prepare("SELECT * FROM tl_c4g_reservation_object WHERE id=?")
            ->execute($objectId);

        $vcard= $checkdb->vcard_show;

        if($vcard == 1)
        {
            $icsdb = $this->Database->prepare("SELECT * FROM tl_c4g_reservation_type WHERE id=?")
                ->execute($typeId);
            $business_street= $icsdb->business_street;
            $business_postal= $icsdb->business_postal;
            $business_city= $icsdb->business_city;
        }
        if($vcard == 0)
        {
            $business_street= $checkdb->business_street;
            $business_postal= $checkdb->business_postal;
            $business_city= $checkdb->business_city;
        }

        $businessdata = $this->Database->prepare("SELECT * FROM tl_c4g_reservation_type WHERE id=?")
            ->execute($typeId);
        $business_name= $businessdata->business_name;
        $business_email= $businessdata->business_email;

        $icstimezone = 'TZID=Europe/Berlin';
        $icsdaylightsaving= date('I');
        $icsprodid = $business_name;
        $icslocation = $business_street ." ". $business_postal." ". $business_city;
        $icsuid = $business_email;

            if($icsdaylightsaving == 1)
            {
                $begin_time= $begin_time - 7200;
            }
            if($icsdaylightsaving == 0)
            {
                $begin_time= $begin_time - 3600;
            }

        $b_date =date('Ymd', strtotime($begin_date));
        $b_time = date('His', $begin_time);
        $icsdate=$b_date . 'T' . $b_time . 'Z';

        $dbResult = $this->Database->prepare("SELECT * FROM tl_c4g_reservation_object WHERE id=?")
            ->execute($objectId);

        $residence = $dbResult->residence_time;
        $time_int = $dbResult->time_interval;
        $icssummary = $dbResult->caption;

            if($residence != 0)
            {
                $residence = $residence * 3600;
                $e_date = date('Ymd',strtotime($begin_date));
                $e_time = $begin_time + $residence;
                $e_time = date('His',$e_time) ;
                $icsenddate =$e_date . 'T' . $e_time. 'Z';
            }
            else
            {
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
        $ics->openFile("w")->fwrite("BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:$icsprodid\nMETHOD:PUBLISH\nBEGIN:VEVENT\nUID:$icsuid\nLOCATION:$icslocation\nSUMMARY:$icssummary\nCLASS:PUBLIC\nDESCRIPTION:$icssummary\nDTSTART:$icsdate\nDTEND:$icsenddate\nEND:VEVENT\nEND:VCALENDAR\n");
    }

    public function clickReservation($values, $putVars)
    {
        $type = $putVars['reservation_type'];
        $newFieldList = [];
        foreach ($this->getFieldList() as $field) {
            $additionalId = $field->getAdditionalID();
            if ($additionalId && (($additionalId != $type) && (strpos(strval($additionalId), strval($type * 100)) === false))) {
                continue;
            }

            $newFieldList[] = $field;
        }



        $action = new C4GSaveAndRedirectDialogAction($this->dialogParams, $this->getListParams(), $newFieldList, $putVars, $this->getBrickDatabase());
        $action->setModule($this);
        foreach ($putVars as $key => $value) {
            if (strpos($key, "beginDate_") !== false) {
                $beginDate = $value;
            }
            if (strpos($key, "beginTime_") !== false) {
                $beginTime = $value;
            }
            if (strpos($key, "reservation_object_") !== false) {
                $resObject = $value;
            }
            if (strpos($key, "reservation_type") !== false) {
                $resType = $value;
            }
        }
        $this->createIcs($beginDate, $beginTime, $resObject, $resType);
        return $result = $action->run();
    }

    public function getCurrentTimeset($values, $putVars)
    {

        $date = $values[2];
        $additionalParam = $values[3];
        $weekday = -1;
        $wd = -1;
        if ($date) {
            $datetime = strtotime($date);
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
        $times = [];
        if ($additionalParam) {
            $objects = C4gReservationObjectModel::getReservationObjectList(array($additionalParam));
            $times = C4gReservationObjectModel::getReservationTimes($objects, $additionalParam, $weekday, $date);

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

