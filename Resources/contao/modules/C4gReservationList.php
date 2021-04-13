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

use con4gis\ProjectsBundle\Classes\Common\C4GBrickCommon;
use con4gis\ProjectsBundle\Classes\Common\C4GBrickConst;
use con4gis\ProjectsBundle\Classes\Fieldlist\C4GBrickField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GCheckboxField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GDateField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GEmailField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GForeignKeyField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GKeyField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GMultiCheckboxField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GSelectField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GSubDialogField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTextField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTimeField;
use con4gis\ProjectsBundle\Classes\Framework\C4GBrickModuleParent;
use con4gis\ProjectsBundle\Classes\Views\C4GBrickViewType;
use con4gis\ReservationBundle\Classes\C4gReservationBrickTypes;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationObjectModel;
use con4gis\ReservationBundle\Resources\contao\models\C4GReservationParamsModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationTypeModel;

class C4gReservationList extends C4GBrickModuleParent
{
    protected $tableName    = 'tl_c4g_reservation';
    protected $modelClass   = C4gReservationModel::class;
    protected $languageFile = 'fe_c4g_reservation';
    protected $brickKey     = C4gReservationBrickTypes::BRICK_RESERVATION;
    protected $viewType     = C4GBrickViewType::PUBLICVIEW;
    protected $sendEMails   = null;
    protected $brickScript  = 'bundles/con4gisreservation/js/c4g_brick_reservation.js';
    protected $brickStyle   = 'bundles/con4gisreservation/css/c4g_brick_reservation.css';
    protected $strTemplate  = 'mod_c4g_brick_simple';
    protected $withNotification = false;

    protected $jQueryUseTable = true;
    protected $jQueryUseScrollPane = false;
    protected $jQueryUsePopups = true;
    protected $loadChosenResources = false;
    protected $loadCkEditor5Resources = false;
    protected $loadCkEditorResources = false;
    protected $loadMoreButtonResources = false;
    protected $loadFontAwesomeResources = false;

    public function initBrickModule($id)
    {
        if ($this->reservationView) {
            $this->viewType = $this->reservationView;
            if ($this->viewType === 'publicview') {
                $this->modelListFunction = 'getListItems';
            }
        }

        parent::initBrickModule($id);

        $this->dialogParams->setWithoutGuiHeader(true);

        $this->dialogParams->deleteButton(C4GBrickConst::BUTTON_SAVE_AND_NEW);
        $this->listParams->deleteButton(C4GBrickConst::BUTTON_ADD);

        if ($this->viewType === 'publicview') {
            $this->dialogParams->setSaveWithoutMessages(true);
        }

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

//        $tstampField = new C4GDateField();
//        $tstampField->setFieldName('tstamp');
//        $tstampField->setCustomFormat($GLOBALS['TL_CONFIG']['dateFormat']);
//        $tstampField->setCustomLanguage($GLOBALS['TL_LANGUAGE']);
//        $tstampField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['tstamp']);
//        $tstampField->setSortColumn(true);
//        $tstampField->setSortType('de_date');
//        $tstampField->setSortSequence(SORT_DESC);
//        $tstampField->setTableColumn(true);
//        $tstampField->setFormField(true);
//        $tstampField->setColumnWidth(5);
//        $tstampField->setStyleClass('begin-date');
//        $tstampField->setEditable(false);
//        $fieldList[] = $tstampField;

        $reservationBeginDateField = new C4GDateField();
        $reservationBeginDateField->setFieldName('beginDate');
        $reservationBeginDateField->setCustomFormat($GLOBALS['TL_CONFIG']['dateFormat']);
        $reservationBeginDateField->setCustomLanguage($GLOBALS['TL_LANGUAGE']);
        $reservationBeginDateField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDateShort']);
        $reservationBeginDateField->setSortColumn(true);
        $reservationBeginDateField->setSortType('de_date');
        $reservationBeginDateField->setSortSequence(SORT_ASC);
        //$reservationBeginDateField->setSortSequence('de_datetime');
        $reservationBeginDateField->setTableColumn(true);
        $reservationBeginDateField->setFormField(true);
        $reservationBeginDateField->setColumnWidth(5);
        $reservationBeginDateField->setStyleClass('begin-date');
        //$reservationBeginDateField->setEditable(false);
        $fieldList[] = $reservationBeginDateField;

        $reservationBeginTimeField = new C4GTimeField();
        $reservationBeginTimeField->setFieldName('beginTime');
        $reservationBeginTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeShort']);
        $reservationBeginTimeField->setTableColumn(true);
        $reservationBeginTimeField->setColumnWidth(5);
        $reservationBeginTimeField->setMandatory(true);
        $reservationBeginTimeField->setNotificationField(true);
        $reservationBeginTimeField->setStyleClass('begin-time');
        //$reservationBeginTimeField->setEditable(false);
        $fieldList[] = $reservationBeginTimeField;

        $t = 'tl_c4g_reservation_type';
        $arrValues = array();
        $arrOptions = array();
        $arrColumns = array("$t.published='1'");
        $types = C4gReservationTypeModel::findBy($arrColumns, $arrValues, $arrOptions);

        if ($types) {
            $typeArr = [];
            $typeList = [];
            foreach ($types as $type) {
                $typeArr[] = $type->id;
                $captions = unserialize($type->options);
                if ($captions && count($captions) > 0) {
                    foreach ($captions as $caption) {
                        if (strpos($GLOBALS['TL_LANGUAGE'],$caption['language']) >= 0) {
                            $typelist[$type->id] = array(
                                'id' => $type->id,
                                'name' => $caption['caption'] ? $caption['caption'] : $type->caption
                            );
                        }
                    }
                } else {
                    $typelist[$type->id] = array(
                        'id' => $type->id,
                        'name' => $type->caption
                    );
                }
            }

            $reservationTypeField = new C4GSelectField();
            $reservationTypeField->setChosen(false);
            $reservationTypeField->setFieldName('reservation_type');
            $reservationTypeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_type_short']);
            $reservationTypeField->setSortColumn(false);
            $reservationTypeField->setTableColumn(true);
            $reservationTypeField->setColumnWidth(20);
            $reservationTypeField->setSize(1);
            $reservationTypeField->setOptions($typelist);
            $reservationTypeField->setMandatory(true);
            $reservationTypeField->setStyleClass('reservation-type');
            $reservationTypeField->setNotificationField(true);
            $reservationTypeField->setEditable(false);
            $fieldList[] = $reservationTypeField;

            $reservationObjects = C4gReservationObjectModel::getReservationObjectList($typeArr,0, false, true);
            $objects = [];

            foreach ($reservationObjects as $type=>$objList) {
                foreach($objList as $reservationObject) {
                    $objects[] = array(
                        'id' => $reservationObject->getId(),
                        'name' => $reservationObject->getCaption(),
                        'type' => $type
                    );
                }
            }

            $reservationObjectField = new C4GSelectField();
            $reservationObjectField->setFieldName('reservation_object');
            $reservationObjectField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object']);
            $reservationObjectField->setTableColumn(true);
            $reservationObjectField->setColumnWidth(20);
            $reservationObjectField->setOptions($objects);
            $reservationObjectField->setMandatory(true);
            $reservationObjectField->setNotificationField(true);
            $reservationObjectField->setStyleClass('reservation-object');
            $reservationObjectField->setShowIfEmpty(false);
            $reservationObjectField->setEditable(false);
            $fieldList[] = $reservationObjectField;
        }

        $lastnameField = new C4GTextField();
        $lastnameField->setFieldName('lastname');
        $lastnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname']);
        $lastnameField->setColumnWidth(10);
        $lastnameField->setSortColumn(false);
        $lastnameField->setTableColumn(true);
        $lastnameField->setMandatory(true);
        $lastnameField->setNotificationField(true);
        $lastnameField->setStyleClass('lastname');
        //$lastnameField->setEditable(false);
        $fieldList[] = $lastnameField;

        $firstnameField = new C4GTextField();
        $firstnameField->setFieldName('firstname');
        $firstnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname']);
        $firstnameField->setColumnWidth(10);
        $firstnameField->setSortColumn(false);
        $firstnameField->setTableColumn(true);
        $firstnameField->setMandatory(true);
        $firstnameField->setNotificationField(true);
        $firstnameField->setStyleClass('firsname');
        //$firstnameField->setEditable(false);
        $fieldList[] = $firstnameField;

        $emailField = new C4GEmailField();
        $emailField->setFieldName('email');
        $emailField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['email']);
        $emailField->setColumnWidth(10);
        $emailField->setSortColumn(false);
        $emailField->setTableColumn(false);
        $emailField->setMandatory(true);
        $emailField->setNotificationField(true);
        $emailField->setStyleClass('email');
        //$emailField->setEditable(false);
        $fieldList[] = $emailField;

        $reservationIdField = new C4GTextField();
        $reservationIdField->setFieldName('reservation_id');
        $reservationIdField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_id']);
        $reservationIdField->setColumnWidth(5);
        $reservationIdField->setSortColumn(false);
        $reservationIdField->setTableColumn(false);
        $reservationIdField->setMandatory(false);
        $reservationIdField->setInitialValue(C4GBrickCommon::getUUID());
        $reservationIdField->setTableRow(false);
        $reservationIdField->setEditable(false);
        $reservationIdField->setUnique(true);
        $reservationIdField->setNotificationField(true);
        $reservationIdField->setDbUnique(true);
        $reservationIdField->setSimpleTextWithoutEditing(false); //!!!
        $reservationIdField->setDatabaseField(true);
        $reservationIdField->setDbUniqueResult($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_id_exists']);
        $reservationIdField->setDbUniqueAdditionalCondition("tl_c4g_reservation.cancellation <> '1' AND tl_c4g_reservation.beginDate > UNIX_TIMESTAMP(NOW())");
        $reservationIdField->setStyleClass('reservation-id');
        $reservationIdField->setEditable(false);
        $fieldList[] = $reservationIdField;

        if ($this->viewType !== 'publicview') {
            $confirmedField = new C4GCheckboxField();
            $confirmedField->setFieldName('confirmed');
            $confirmedField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['confirmed']);
            $confirmedField->setTableRow(false);
            $confirmedField->setColumnWidth(5);
            $confirmedField->setSortColumn(false);
            $confirmedField->setTableColumn(true);
            $confirmedField->setNotificationField(true);
            $confirmedField->setStyleClass('confirmed');
            $confirmedField->setWithoutDescriptionLineBreak(true);
            $fieldList[] = $confirmedField;

            $cancellationField = new C4GCheckboxField();
            $cancellationField->setFieldName('cancellation');
            $cancellationField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['cancellation']);
            $cancellationField->setTableRow(false);
            $cancellationField->setColumnWidth(5);
            $cancellationField->setSortColumn(false);
            $cancellationField->setTableColumn(true);
            $cancellationField->setNotificationField(true);
            $cancellationField->setStyleClass('cancellation');
            $cancellationField->setWithoutDescriptionLineBreak(true);
            $fieldList[] = $cancellationField;


            $params = C4gReservationParamsModel::findBy('published', 1);
            $paramList = [];
            foreach ($params as $param) {
                $paramList[] = ['id' => $param->id, 'name' => $param->caption];
            }

            $includedParams = new C4GMultiCheckboxField();
            $includedParams->setFieldName('included_params');
            $includedParams->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['included_params']);
            $includedParams->setFormField(true);
            $includedParams->setTableColumn(false);
            //$includedParams->setEditable(false);
            $includedParams->setOptions($paramList);
            $includedParams->setMandatory(false);
            $includedParams->setModernStyle(false);
            $includedParams->setStyleClass('included-params');
            $includedParams->setNotificationField(true);
            $includedParams->setShowIfEmpty(false);
            $fieldList[] = $includedParams;

            $additionalParams = new C4GMultiCheckboxField();
            $additionalParams->setFieldName('additional_params');
            $additionalParams->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['additional_params']);
            $additionalParams->setFormField(true);
            $additionalParams->setTableColumn(false);
            //$additionalParams->setEditable(false);
            $additionalParams->setOptions($paramList);
            $additionalParams->setMandatory(false);
            $additionalParams->setModernStyle(false);
            $additionalParams->setStyleClass('additional-params');
            $additionalParams->setNotificationField(true);
            $additionalParams->setAllChecked(true);
            $additionalParams->setShowIfEmpty(false);
            $fieldList[] = $additionalParams;


            $participants = [];

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

            $titleField = new C4GTextField();
            $titleField->setFieldName('title');
            $titleField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['title']);
            $titleField->setSortColumn(false);
            $titleField->setTableColumn(false);
            $titleField->setMandatory(false);
            $titleField->setNotificationField(false);
            $participants[] = $titleField;

            $firstnameField = new C4GTextField();
            $firstnameField->setFieldName('firstname');
            $firstnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname']);
            $firstnameField->setColumnWidth(10);
            $firstnameField->setSortColumn(false);
            $firstnameField->setTableColumn(false);
            $firstnameField->setMandatory(true);
            $firstnameField->setNotificationField(false);
            $participants[] = $firstnameField;

            $lastnameField = new C4GTextField();
            $lastnameField->setFieldName('lastname');
            $lastnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname']);
            $lastnameField->setColumnWidth(10);
            $lastnameField->setSortColumn(false);
            $lastnameField->setTableColumn(false);
            $lastnameField->setMandatory(true);
            $lastnameField->setNotificationField(false);
            $participants[] = $lastnameField;

            $emailField = new C4GEmailField();
            $emailField->setFieldName('email');
            $emailField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['email']);
            $emailField->setColumnWidth(10);
            $emailField->setSortColumn(false);
            $emailField->setTableColumn(false);
            $emailField->setMandatory(false);
            $emailField->setNotificationField(false);
            $participants[] = $emailField;

            $participantParamField = new C4GMultiCheckboxField();
            $participantParamField->setFieldName('participant_params');
            $participantParamField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['participant_params']);
            $participantParamField->setFormField(true);
            //$participantParamField->setEditable(true);
            $participantParamField->setOptions($paramList);
            $participantParamField->setMandatory(false);
            $participantParamField->setModernStyle(false);
            $participantParamField->setStyleClass('participant-params');
            $participantParamField->setNotificationField(false);
            $participantParamField->setShowIfEmpty(false);

            $participants[] = $participantParamField;

            $reservationParticipants = new C4GSubDialogField();
            $reservationParticipants->setFieldName('participants');
            $reservationParticipants->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['participants']);
            $reservationParticipants->setAddButton($GLOBALS['TL_LANG']['fe_c4g_reservation']['addParticipant']);
            $reservationParticipants->setRemoveButton($GLOBALS['TL_LANG']['fe_c4g_reservation']['removeParticipant']);
            $reservationParticipants->setRemoveButtonMessage($GLOBALS['TL_LANG']['fe_c4g_reservation']['removeParticipantMessage']);
            $reservationParticipants->setTable('tl_c4g_reservation_participants');
            $reservationParticipants->addFields($participants);
            $reservationParticipants->setKeyField($participantsKey);
            $reservationParticipants->setForeignKeyField($participantsForeign);
            $reservationParticipants->setMandatory(false);
            $reservationParticipants->setNotificationField(false);
            //$reservationParticipants->setShowFirstDataSet(false);
            //$reservationParticipants->setShowIfEmpty(false);
            $reservationParticipants->setDelimiter('~');
            $reservationParticipants->setTableColumn(false);
            //$reservationParticipants->setEditable(false);
            //$reservationParticipants->setShowButtons(false);

            $fieldList[] = $reservationParticipants;
        }

        $this->fieldList = $fieldList;
    }

}

