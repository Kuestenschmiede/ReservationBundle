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
use con4gis\ProjectsBundle\Classes\Fieldlist\C4GBrickFieldNumeric;
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

    protected $withPermissionCheck = false;

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
        } else {
            $this->dialogParams->setCaptionField('reservation_id');
            $this->dialogParams->addButton(C4GBrickConst::BUTTON_PRINT);
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
        $idField->setPrintable(false);
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
        $reservationBeginDateField->setFlipButtonPosition(true);
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
        $reservationBeginDateField->setPrintable(true);
        $fieldList[] = $reservationBeginDateField;

        $reservationBeginTimeField = new C4GTimeField();
        $reservationBeginTimeField->setFieldName('beginTime');
        $reservationBeginTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeShort']);
        $reservationBeginTimeField->setTableColumn(true);
        $reservationBeginTimeField->setColumnWidth(5);
        $reservationBeginTimeField->setMandatory(true);
        $reservationBeginTimeField->setNotificationField(true);
        $reservationBeginTimeField->setStyleClass('begin-time');
        $reservationBeginTimeField->setPrintable(true);
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
            $reservationTypeField->setMandatory(false);
            $reservationTypeField->setStyleClass('reservation-type');
            $reservationTypeField->setNotificationField(true);
            $reservationTypeField->setEditable(false);
            $reservationTypeField->setPrintable(true);
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
            $reservationObjectField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object_short']);
            $reservationObjectField->setTableColumn(true);
            $reservationObjectField->setColumnWidth(20);
            $reservationObjectField->setOptions($objects);
            $reservationObjectField->setMandatory(false);
            $reservationObjectField->setNotificationField(true);
            $reservationObjectField->setStyleClass('reservation-object');
            $reservationObjectField->setShowIfEmpty(false);
            $reservationObjectField->setEditable(false);
            $reservationObjectField->setPrintable(true);
            $fieldList[] = $reservationObjectField;
        }

        if ($this->viewType === 'publicview') {
            $lastnameField = new C4GTextField();
            $lastnameField->setFieldName('lastname');
            $lastnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname']);
            $lastnameField->setColumnWidth(10);
            $lastnameField->setSortColumn(false);
            $lastnameField->setTableColumn(true);
            $lastnameField->setMandatory(true);
            $lastnameField->setNotificationField(true);
            $lastnameField->setStyleClass('lastname');
            $lastnameField->setPrintable(true);
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
            $firstnameField->setPrintable(false);
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
            $emailField->setPrintable(false);
            $fieldList[] = $emailField;
        } else if ($this->viewType !== 'publicview') {
            $organisationField = new C4GTextField();
            $organisationField->setFieldName('organisation');
            $organisationField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['organisation']);
            $organisationField->setColumnWidth(10);
            $organisationField->setSortColumn(false);
            $organisationField->setTableColumn(false);
            $organisationField->setNotificationField(true);
            $organisationField->setStyleClass('organisation');
            $fieldList[] = $organisationField;

            $titleField = new C4GTextField();
            $titleField->setFieldName('title');
            $titleField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['title']);
            $titleField->setSortColumn(false);
            $titleField->setTableColumn(false);
            $titleField->setNotificationField(true);
            $titleField->setStyleClass('title');
            $fieldList[] = $titleField;

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
            $salutationField->setNotificationField(true);
            $salutationField->setStyleClass('salutation');
            $fieldList[] = $salutationField;

            $firstnameField = new C4GTextField();
            $firstnameField->setFieldName('firstname');
            $firstnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname']);
            $firstnameField->setColumnWidth(10);
            $firstnameField->setSortColumn(false);
            $firstnameField->setTableColumn(false);
            $firstnameField->setNotificationField(true);
            $firstnameField->setStyleClass('firstname');
            $fieldList[] = $firstnameField;

            $lastnameField = new C4GTextField();
            $lastnameField->setFieldName('lastname');
            $lastnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname']);
            $lastnameField->setColumnWidth(10);
            $lastnameField->setSortColumn(false);
            $lastnameField->setTableColumn(false);

            $lastnameField->setNotificationField(true);
            $lastnameField->setStyleClass('lastname');
            $fieldList[] = $lastnameField;

            $addressField = new C4GTextField();
            $addressField->setFieldName('address');
            $addressField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['address']);
            $addressField->setColumnWidth(60);
            $addressField->setSortColumn(false);
            $addressField->setTableColumn(false);
            $addressField->setNotificationField(true);
            $addressField->setStyleClass('address');
            $fieldList[] = $addressField;

            $postalField = new C4GPostalField();
            $postalField->setFieldName('postal');
            $postalField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['postal']);
            $postalField->setColumnWidth(60);
            $postalField->setSize(5); //international 32
            $postalField->setSortColumn(false);
            $postalField->setTableColumn(false);
            $postalField->setNotificationField(true);
            $postalField->setStyleClass('postal');
            $fieldList[] = $postalField;

            $cityField = new C4GTextField();
            $cityField->setFieldName('city');
            $cityField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['city']);
            $cityField->setColumnWidth(60);
            $cityField->setSortColumn(false);
            $cityField->setTableColumn(false);
            $cityField->setNotificationField(true);
            $cityField->setStyleClass('city');
            $fieldList[] = $cityField;

            $emailField = new C4GEmailField();
            $emailField->setFieldName('email');
            $emailField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['email']);
            $emailField->setColumnWidth(10);
            $emailField->setSortColumn(false);
            $emailField->setTableColumn(false);
            $emailField->setMandatory(false);
            $emailField->setNotificationField(true);
            $emailField->setStyleClass('email');

            $phoneField = new C4GTelField();
            $phoneField->setFieldName('phone');
            $phoneField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['phone']);
            $phoneField->setColumnWidth(10);
            $phoneField->setSortColumn(false);
            $phoneField->setTableColumn(false);
            $phoneField->setNotificationField(true);
            $phoneField->setStyleClass('phone');
            $fieldList[] = $phoneField;

            $birthDateField = new C4GDateField();
            $birthDateField->setFlipButtonPosition(true);
            $birthDateField->setFieldName('dateOfBirth');
            $birthDateField->setMinDate(strtotime('-120 year'));
            $birthDateField->setMaxDate(strtotime('-1 year'));
            $birthDateField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['dateOfBirth']);
            $birthDateField->setColumnWidth(60);
            $birthDateField->setSortColumn(false);
            $birthDateField->setTableColumn(false);
            $birthDateField->setSortSequence('de_datetime');
            $birthDateField->setNotificationField(true);
            $birthDateField->setStyleClass('dateOfBirth');
            $fieldList[] = $birthDateField;

            $organisationField2 = new C4GTextField();
            $organisationField2->setFieldName('organisation2');
            $organisationField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['organisation2']);
            $organisationField2->setColumnWidth(10);
            $organisationField2->setSortColumn(false);
            $organisationField2->setTableColumn(true);
            $organisationField2->setNotificationField(true);
            $organisationField2->setStyleClass('organisation');
            $fieldList[] = $organisationField2;

            $salutationField2 = new C4GSelectField();
            $salutationField2->setFieldName('salutation2');
            $salutationField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['salutation2']);
            $salutationField2->setSortColumn(false);
            $salutationField2->setTableColumn(false);
            $salutationField2->setOptions($salutation);
            $salutationField2->setNotificationField(true);
            $salutationField2->setStyleClass('salutation');
            $fieldList[] = $salutationField2;

            $titleField2 = new C4GTextField();
            $titleField2->setFieldName('title2');
            $titleField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['title2']);
            $titleField2->setSortColumn(false);
            $titleField2->setTableColumn(false);
            $titleField2->setMandatory(false);
            $titleField2->setNotificationField(true);
            $titleField2->setStyleClass('title');
            $fieldList[] = $titleField2;

            $firstnameField2 = new C4GTextField();
            $firstnameField2->setFieldName('firstname2');
            $firstnameField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname2']);
            $firstnameField2->setColumnWidth(10);
            $firstnameField2->setSortColumn(false);
            $firstnameField2->setTableColumn(true);
            $firstnameField2->setNotificationField(true);
            $firstnameField2->setStyleClass('firstname');
            $fieldList[] = $firstnameField2;

            $lastnameField2 = new C4GTextField();
            $lastnameField2->setFieldName('lastname2');
            $lastnameField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname2']);
            $lastnameField2->setColumnWidth(10);
            $lastnameField2->setSortColumn(false);
            $lastnameField2->setTableColumn(true);
            $lastnameField2->setNotificationField(true);
            $lastnameField2->setStyleClass('lastname');
            $fieldList[] = $lastnameField2;

            $addressField2 = new C4GTextField();
            $addressField2->setFieldName('address2');
            $addressField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['address2']);
            $addressField2->setColumnWidth(60);
            $addressField2->setSortColumn(false);
            $addressField2->setTableColumn(false);
            $addressField2->setNotificationField(true);
            $addressField2->setStyleClass('address');
            $fieldList[] = $addressField2;

            $postalField2 = new C4GPostalField();
            $postalField2->setFieldName('postal2');
            $postalField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['postal2']);
            $postalField2->setColumnWidth(60);
            $postalField2->setSize(5); //international 32
            $postalField2->setSortColumn(false);
            $postalField2->setTableColumn(false);
            $postalField2->setNotificationField(true);
            $postalField2->setStyleClass('postal');
            $fieldList[] = $postalField2;

            $cityField2 = new C4GTextField();
            $cityField2->setFieldName('city2');
            $cityField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['city2']);
            $cityField2->setColumnWidth(60);
            $cityField2->setSortColumn(false);
            $cityField2->setTableColumn(false);
            $cityField2->setNotificationField(true);
            $cityField2->setStyleClass('city');
            $fieldList[] = $cityField2;

            $emailField2 = new C4GEmailField();
            $emailField2->setFieldName('email2');
            $emailField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['email2']);
            $emailField2->setColumnWidth(10);
            $emailField2->setSortColumn(false);
            $emailField2->setTableColumn(false);
            $emailField2->setNotificationField(true);
            $emailField2->setStyleClass('email');
            $fieldList[] = $emailField2;

            $phoneField2 = new C4GTelField();
            $phoneField2->setFieldName('phone2');
            $phoneField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['phone2']);
            $phoneField2->setColumnWidth(10);
            $phoneField2->setSortColumn(false);
            $phoneField2->setTableColumn(false);
            $phoneField2->setNotificationField(true);
            $phoneField2->setStyleClass('phone');
            $fieldList[] = $phoneField2;

            $commentField = new C4GTextareaField();
            $commentField->setFieldName('comment');
            $commentField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['comment']);
            $commentField->setColumnWidth(60);
            $commentField->setSortColumn(false);
            $commentField->setTableColumn(false);
            $commentField->setNotificationField(true);
            $commentField->setStyleClass('comment');
            $fieldList[] = $commentField;

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
            $reservationIdField->setPrintable(false);
            $fieldList[] = $reservationIdField;

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
            $confirmedField->setPrintable(false);
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
            $cancellationField->setPrintable(false);
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
            $includedParams->setPrintable(false);
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
            $additionalParams->setPrintable(false);
            $fieldList[] = $additionalParams;

            $participants = [];

            $participantsKey = new C4GKeyField();
            $participantsKey->setFieldName('id');
            $participantsKey->setComparable(false);
            $participantsKey->setEditable(false);
            $participantsKey->setHidden(true);
            $participantsKey->setFormField(true);
            $participantsKey->setPrintable(false);

            $participantsForeign = new C4GForeignKeyField();
            $participantsForeign->setFieldName('pid');
            $participantsForeign->setHidden(true);
            $participantsForeign->setFormField(true);
            $participantsForeign->setPrintable(false);

            $titleField = new C4GTextField();
            $titleField->setFieldName('title');
            $titleField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['title']);
            $titleField->setSortColumn(false);
            $titleField->setTableColumn(false);
            $titleField->setMandatory(false);
            $titleField->setNotificationField(false);
            $titleField->setPrintable(false);
            $participants[] = $titleField;

            $firstnameField = new C4GTextField();
            $firstnameField->setFieldName('firstname');
            $firstnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname']);
            $firstnameField->setColumnWidth(10);
            $firstnameField->setSortColumn(false);
            $firstnameField->setTableColumn(false);
            $firstnameField->setMandatory(true);
            $firstnameField->setNotificationField(false);
            $firstnameField->setPrintable(false);
            $participants[] = $firstnameField;

            $lastnameField = new C4GTextField();
            $lastnameField->setFieldName('lastname');
            $lastnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname']);
            $lastnameField->setColumnWidth(10);
            $lastnameField->setSortColumn(false);
            $lastnameField->setTableColumn(false);
            $lastnameField->setMandatory(true);
            $lastnameField->setNotificationField(false);
            $lastnameField->setPrintable(false);
            $participants[] = $lastnameField;

            $emailField = new C4GEmailField();
            $emailField->setFieldName('email');
            $emailField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['email']);
            $emailField->setColumnWidth(10);
            $emailField->setSortColumn(false);
            $emailField->setTableColumn(false);
            $emailField->setMandatory(false);
            $emailField->setNotificationField(false);
            $emailField->setPrintable(false);
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
            $participantParamField->setPrintable(false);
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
            $reservationParticipants->setPrintable(false);
            $fieldList[] = $reservationParticipants;
        }

        $this->fieldList = $fieldList;
    }

}

