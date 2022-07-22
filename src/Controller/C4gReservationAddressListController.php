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

use con4gis\ProjectsBundle\Classes\Common\C4GBrickConst;
use con4gis\ProjectsBundle\Classes\Dialogs\C4GBrickGrid;
use con4gis\ProjectsBundle\Classes\Dialogs\C4GBrickGridElement;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GDateField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GGridField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GKeyField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GPostalField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTextField;
use con4gis\ProjectsBundle\Classes\Framework\C4GBaseController;
use con4gis\ProjectsBundle\Classes\Framework\C4GController;
use con4gis\ProjectsBundle\Classes\Views\C4GBrickViewType;
use con4gis\ReservationBundle\Classes\Models\C4gReservationModel;
use con4gis\ReservationBundle\Classes\Projects\C4gReservationBrickTypes;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationCalculator;
use Contao\StringUtil;

class C4gReservationAddressListController extends C4GBaseController
{
    public const TYPE = 'C4gReservationAddressList';

    protected $tableName    = 'tl_c4g_reservation';
    protected $modelClass   = C4gReservationModel::class;
    protected $languageFile = 'fe_c4g_reservation';
    protected $brickKey     = C4gReservationBrickTypes::BRICK_RESERVATION;
    protected $viewType     = C4GBrickViewType::GROUPBASED;
    protected $modelListFunction = 'getAddressListItemsByGroup';
    protected $sendEMails   = null;
    protected $brickScript  = 'bundles/con4gisreservation/dist/js/c4g_brick_reservation.js';
    protected $brickStyle   = 'bundles/con4gisreservation/dist/css/c4g_brick_reservation.min.css';
    protected $withNotification = false;
    protected $permalink_field = 'id';
    protected $permalink_name = 'reservation';

    //Resource Params
    protected $loadDefaultResources = true;
    protected $loadTrixEditorResources = false;
    protected $loadDateTimePickerResources = false;
    protected $loadChosenResources = false;
    protected $loadClearBrowserUrlResources = false;
    protected $loadConditionalFieldDisplayResources = false;
    protected $loadMoreButtonResources = false;
    protected $loadFontAwesomeResources = true;
    protected $loadTriggerSearchFromOtherModuleResources = false;
    protected $loadFileUploadResources = true;
    protected $loadMultiColumnResources = false;
    protected $loadMiniSearchResources = false;
    protected $loadHistoryPushResources = true;

    protected $loadSignaturePadResources = true;

    //JQuery GUI Resource Params
    protected $jQueryAddCore = true;
    protected $jQueryAddJquery = true;
    protected $jQueryAddJqueryUI = true;
    protected $jQueryUseTree = false;
    protected $jQueryUseTable = true;
    protected $jQueryUseHistory = false;
    protected $jQueryUseTooltip = false;
    protected $jQueryUseMaps = false;
    protected $jQueryUseGoogleMaps = false;
    protected $jQueryUseMapsEditor = false;
    protected $jQueryUseWswgEditor = false;
    protected $jQueryUseScrollPane = true;
    protected $jQueryUsePopups = false;

    protected $withPermissionCheck = false;

    /**
     * @param $id
     * @return void
     */
    public function initBrickModule($id)
    {
        parent::initBrickModule($id);

        $this->dialogParams->setWithoutGuiHeader(true);

        $this->dialogParams->deleteButton(C4GBrickConst::BUTTON_SAVE_AND_NEW);
        $this->dialogParams->deleteButton(C4GBrickConst::BUTTON_DELETE);
        $this->listParams->deleteButton(C4GBrickConst::BUTTON_ADD);

        $this->listParams->setScrollX(false);
        $this->listParams->setResponsive(true);

        $this->listParams->setModelListParams(StringUtil::deserialize($this->reservation_object_types));

        $this->dialogParams->addButton(C4GBrickConst::BUTTON_PRINT);
        $this->dialogParams->setGeneratePrintoutWithSaving(false);

        if ($this->printTpl) {
            $this->printTemplate = $this->printTpl;
        }
    }

    public function addFields() : array
    {
        $fieldList = array();

        $idField = new C4GKeyField();
        $idField->setFieldName('id');
        $idField->setEditable(false);
        $idField->setFormField(false);
        $idField->setSortColumn(false);
        $idField->setPrintable(false);
        $fieldList[] = $idField;

        $titleField = new C4GTextField();
        $titleField->setFieldName('title');
        $titleField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['title']);
        $titleField->setSortColumn(false);
        $titleField->setTableColumn(false);
        $titleField->setNotificationField(true);
        $titleField->setStyleClass('title');
        $titleField->setShowIfEmpty(false);
        $titleField->setPrintable(true);
        $fieldList[] = $titleField;

        $firstnameField = new C4GTextField();
        $firstnameField->setFieldName('firstname');
        $firstnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname']);
        //$firstnameField->setColumnWidth(10);
        $firstnameField->setSortColumn(false);
        $firstnameField->setTableColumn(true);
        $firstnameField->setNotificationField(true);
        $firstnameField->setStyleClass('firstname');
        $firstnameField->setShowIfEmpty(true);
        $firstnameField->setPrintable(true);
        $firstnameField->setTableColumnPriority(2);
        $firstnameField->setMandatory(false);
        $firstnameField->setFormField(false);

        $lastnameField = new C4GTextField();
        $lastnameField->setFieldName('lastname');
        $lastnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname']);
        //$lastnameField->setColumnWidth(10);
        $lastnameField->setSortColumn(false);
        $lastnameField->setTableColumn(true);
        $lastnameField->setNotificationField(true);
        $lastnameField->setStyleClass('lastname');
        $lastnameField->setShowIfEmpty(true);
        $lastnameField->setPrintable(true);
        $lastnameField->setFormField(false);
        $lastnameField->setTableColumnPriority(2);
        $lastnameField->setMandatory(false);

        $grid = new C4GBrickGrid([
            new C4GBrickGridElement($firstnameField),
            new C4GBrickGridElement($lastnameField)
        ], 2);

        $nameField = new C4GGridField($grid);
        $nameField->setTitle("Name"); //ToDo
        $nameField->setFieldName('nameGrid');
        $nameField->setTableColumn(true); //ToDO
        $nameField->setFormField(true);
        $nameField->setDatabaseField(false);
        $nameField->setPrintable(true);
        $nameField->setStyleClass('name');
        $nameField->setWithoutLabel(true);
        $fieldList[] = $nameField;

        $addressField = new C4GTextField();
        $addressField->setFieldName('address');
        $addressField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['address']);
        //$addressField->setColumnWidth(60);
        $addressField->setSortColumn(false);
        $addressField->setTableColumn(true);
        $addressField->setNotificationField(true);
        $addressField->setStyleClass('address');
        $addressField->setShowIfEmpty(true);
        $addressField->setPrintable(true);
        $fieldList[] = $addressField;

        $postalField = new C4GPostalField();
        $postalField->setFieldName('postal');
        $postalField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['postal']);
        //$postalField->setColumnWidth(60);
        $postalField->setSize(5); //international 32
        $postalField->setSortColumn(false);
        $postalField->setTableColumn(true);
        $postalField->setNotificationField(true);
        $postalField->setStyleClass('postal');
        $postalField->setShowIfEmpty(true);
        $postalField->setPrintable(true);
        $postalField->setInitialValue("");

        $cityField = new C4GTextField();
        $cityField->setFieldName('city');
        $cityField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['city']);
        //$cityField->setColumnWidth(60);
        $cityField->setSortColumn(false);
        $cityField->setTableColumn(true);
        $cityField->setNotificationField(true);
        $cityField->setStyleClass('city');
        $cityField->setShowIfEmpty(true);
        $cityField->setPrintable(true);

        $grid = new C4GBrickGrid([
            new C4GBrickGridElement($postalField),
            new C4GBrickGridElement($cityField)
        ],2);

        $postalCityField = new C4GGridField($grid);
        $postalCityField->setTitle('Ort');
        $postalCityField->setFieldName('postalCityGrid');
        $postalCityField->setTableColumn(true);
        $postalCityField->setFormField(true);
        $postalCityField->setDatabaseField(false);
        $postalCityField->setPrintable(true);
        $postalCityField->setStyleClass('city');
        $postalCityField->setWithoutLabel(true);
        $fieldList[] = $postalCityField;

        $birthDateField = new C4GDateField();
        $birthDateField->setFlipButtonPosition(true);
        $birthDateField->setFieldName('dateOfBirth');
        $birthDateField->setMinDate(strtotime('-120 year'));
        $year = date('Y');
        $birthDateField->setMaxDate(strtotime($year . '-12-31'));
        $birthDateField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['dateOfBirth']);
        $birthDateField->setSortColumn(false);
        $birthDateField->setTableColumn(false);
        $birthDateField->setSortSequence('de_datetime');
        $birthDateField->setNotificationField(true);
        $birthDateField->setStyleClass('dateOfBirth');
        $birthDateField->setShowIfEmpty(true);
        $birthDateField->setPrintable(true);
        $birthDateField->setMandatory(true);
        $fieldList[] = $birthDateField;

        return $fieldList;
    }

}

