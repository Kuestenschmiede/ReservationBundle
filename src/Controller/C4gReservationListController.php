<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 10
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2025, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

namespace con4gis\ReservationBundle\Controller;

use con4gis\CoreBundle\Classes\C4GUtils;
use con4gis\CoreBundle\Classes\C4GVersionProvider;
use con4gis\CoreBundle\Classes\Helper\StringHelper;
use con4gis\ProjectsBundle\Classes\Actions\C4GBrickActionType;
use con4gis\ProjectsBundle\Classes\Buttons\C4GBrickButton;
use con4gis\ProjectsBundle\Classes\Common\C4GBrickCommon;
use con4gis\ProjectsBundle\Classes\Common\C4GBrickConst;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GCheckboxField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GDateField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GDateTimePickerField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GEmailField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GKeyField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GMultiCheckboxField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GPostalField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GSelectField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GSignaturePadField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTelField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTextareaField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTextField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTimepickerField;
use con4gis\ProjectsBundle\Classes\Framework\C4GBaseController;
use con4gis\ProjectsBundle\Classes\Framework\C4GController;
use con4gis\ProjectsBundle\Classes\Views\C4GBrickViewType;
use con4gis\ReservationBundle\Classes\Models\C4gReservationModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationParamsModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationTypeModel;
use con4gis\ReservationBundle\Classes\Projects\C4gReservationBrickTypes;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationCalculator;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationHandler;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RequestStack;

class C4gReservationListController extends C4GBaseController
{
    public const TYPE = 'C4gReservationList';

    protected $tableName    = 'tl_c4g_reservation';
    protected $modelClass   = C4gReservationModel::class;
    protected $languageFile = 'fe_c4g_reservation';
    protected $brickKey     = C4gReservationBrickTypes::BRICK_RESERVATION;
    protected $viewType     = C4GBrickViewType::GROUPBASED;
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
     * @param string $rootDir
     * @param RequestStack $requestStack
     * @param ContaoFramework $framework
     */
    
    public function __construct(string $rootDir, RequestStack $requestStack, ContaoFramework $framework, ModuleModel $model = null)
    {
        parent::__construct($rootDir, $requestStack, $framework, $model);

        if ($model && $model->reservationView) {
            $this->viewType = $model->reservationView;
            if ($this->viewType === 'publicview') {
                $this->modelListFunction = 'getListItems';
            } else if (($this->viewType === 'member') || ($this->viewType === 'memberview')) {
                $this->modelListFunction = 'getListItemsByMember';
            } else if ($this->viewType === 'group') {
                $this->modelListFunction = 'getListItemsByGroup';
            }
        }
    }

    /**
     * @param Template $template
     * @param ModuleModel $model
     * @param Request $request
     * @return Response|null
     */
    public function getResponse(Template $template, ModuleModel $model, Request $request): Response {
        if ($model && $model->reservationView) {
            $this->viewType = $model->reservationView;
            if ($this->viewType === 'publicview') {
                $this->modelListFunction = 'getListItems';
            } else if (($this->viewType === 'member') || ($this->viewType === 'memberview')) {
                $this->modelListFunction = 'getListItemsByMember';
            } else if ($this->viewType === 'group') {
                $this->modelListFunction = 'getListItemsByGroup';
            }
        }

        $result = parent::getResponse($template, $model, $request);
        System::loadLanguageFile('fe_c4g_reservation');
        $this->setBrickCaption($GLOBALS['TL_LANG']['fe_c4g_reservation']['brick_caption']);
        $this->setBrickCaptionPlural($GLOBALS['TL_LANG']['fe_c4g_reservation']['brick_caption_plural']);

        return $result;
    }

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

        $this->listParams->setScrollX(false);
        $this->listParams->setResponsive(true);

        $types = unserialize($this->selectReservationTypes);
        if ($types && (count($types) > 0)) {
            $types = implode(',', $types);
            $this->listParams->setModelListParams([$this->past_day_number ?: 1, $types]);
        } else {
            $this->listParams->setModelListParams([$this->past_day_number ?: 1]);
        }

        if ($this->viewType === 'publicview') {
            $this->listParams->deleteButton(C4GBrickConst::BUTTON_ADD);
            $this->dialogParams->setSaveWithoutMessages(true);
        } else if (($this->viewType === 'member') || ($this->viewType === 'memberview')) {
            $this->listParams->deleteButton(C4GBrickConst::BUTTON_ADD);
            $this->dialogParams->setSaveWithoutMessages(true);

            if ($this->cancellation_redirect_site) {
                $button = new C4GBrickButton(C4GBrickConst::BUTTON_CLICK,
                    $GLOBALS['TL_LANG']['fe_c4g_reservation']['button_cancellation'],
                    true,
                    true,
                    C4GBrickActionType::ACTION_BUTTONCLICK . ':clickCancellation');

                $buttons = $this->dialogParams->getButtons();
                $found = false;
                foreach ($buttons as $btn) {
                    if ($btn->getCaption() == $GLOBALS['TL_LANG']['fe_c4g_reservation']['button_cancellation']) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $buttons[] = $button;
                    $this->dialogParams->setButtons($buttons);
                }
            }
        } else if (($this->viewType === 'group') && (C4GVersionProvider::isInstalled('con4gis/documents'))) {
            $this->dialogParams->setCaptionField('reservation_id');
            $this->dialogParams->addButton(C4GBrickConst::BUTTON_PRINT);
            $this->dialogParams->setSavePrintoutToField('fileUpload');
            $this->dialogParams->setGeneratePrintoutWithSaving(true);
        }

        if ($this->login_redirect_site) {
            $this->dialogParams->getViewParams()->setLoginRedirect($this->login_redirect_site);
        }

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

        $reservationBeginDateTimeField = new C4GDateTimePickerField();
        //$reservationBeginDateTimeField->setFlipButtonPosition(true);
        $reservationBeginDateTimeField->setFieldName('beginTime');
        //$reservationBeginDateTimeField->setCustomFormat($GLOBALS['TL_CONFIG']['dateFormat']);
        //$reservationBeginDateTimeField->setCustomLanguage($GLOBALS['TL_LANGUAGE']);
        $reservationBeginDateTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDateTime']);
        $reservationBeginDateTimeField->setSortColumn(true);
        $reservationBeginDateTimeField->setSortType('de_datetime');
        $reservationBeginDateTimeField->setSortSequence(SORT_ASC);
        $reservationBeginDateTimeField->setTableColumn(true);
        $reservationBeginDateTimeField->setFormField(false);
        //$reservationBeginDateTimeField->setColumnWidth(5);
        $reservationBeginDateTimeField->setStyleClass('begin-date');
        //$reservationBeginDateTimeField->setEditable(false);
        $reservationBeginDateTimeField->setPrintable(true);
        $reservationBeginDateTimeField->setTableColumnPriority(1);
        $reservationBeginDateTimeField->setDatabaseField(false);
        $reservationBeginDateTimeField->setDateField('beginDate');
        $fieldList[] = $reservationBeginDateTimeField;

        $reservationBeginDateField = new C4GDateField();
        //$reservationBeginDateField->setFlipButtonPosition(true);
        $reservationBeginDateField->setFieldName('beginDate');
        $reservationBeginDateField->setCustomFormat($GLOBALS['TL_CONFIG']['dateFormat']);
        $reservationBeginDateField->setCustomLanguage($GLOBALS['TL_LANGUAGE']);
        $reservationBeginDateField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDateShort']);
        $reservationBeginDateField->setSortColumn(false);
        $reservationBeginDateField->setSortType('de_date');
        $reservationBeginDateField->setSortSequence(SORT_ASC);
        //$reservationBeginDateField->setSortSequence('de_datetime');
        $reservationBeginDateField->setTableColumn(false);
        $reservationBeginDateField->setFormField(true);
        $reservationBeginDateField->setColumnWidth(10);
        $reservationBeginDateField->setStyleClass('begin-date');
        //$reservationBeginDateField->setEditable(false);
        $reservationBeginDateField->setPrintable(true);
        $reservationBeginDateField->setTableColumnPriority(1);
        $fieldList[] = $reservationBeginDateField;

        $reservationBeginTimeField = new C4GTimePickerField();
        $reservationBeginTimeField->setFieldName('beginTime');
        $reservationBeginTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeShort']);
        $reservationBeginTimeField->setTableColumn(false);
        $reservationBeginTimeField->setColumnWidth(10);
        $reservationBeginTimeField->setMandatory(true);
        $reservationBeginTimeField->setNotificationField(true);
        $reservationBeginTimeField->setStyleClass('begin-time');
        $reservationBeginTimeField->setPrintable(true);
        //$reservationBeginTimeField->setEditable(false);
        $reservationBeginTimeField->setTableColumnPriority(1);
        $fieldList[] = $reservationBeginTimeField;

        $selectTypes = unserialize($this->selectReservationTypes);
        if ($selectTypes && count($selectTypes) > 0) {
            $database = Database::getInstance();
            $allTypes = implode(',', $selectTypes);
            $types = $database->prepare('SELECT * FROM `tl_c4g_reservation_type` WHERE ' .
                '`id` IN (' . $allTypes . ') AND `published`=?')
                ->execute('1')->fetchAllAssoc();
        } else {
            $database = Database::getInstance();
            $types = $database->prepare('SELECT * FROM `tl_c4g_reservation_type` WHERE `published`=?')
                ->execute('1')->fetchAllAssoc();
        }

        if ($types) {
            $typelist = [];
            foreach ($types as $type) {
                $captions = \Contao\StringUtil::deserialize($type['options']);
                if ($captions && count($captions) > 0) {
                    foreach ($captions as $caption) {
                        if (strpos($GLOBALS['TL_LANGUAGE'],$caption['language']) >= 0) {
                            $typelist[$type['id']] = array(
                                'id' => $type['id'],
                                'name' => $caption['caption'] ? StringHelper::addSpaceBeforeBracket($caption['caption']) : $type['caption'],
                                'type' => $type['reservationObjectType']
                            );
                        }
                    }
                } else {
                    $typelist[$type['id']] = array(
                        'id' => $type['id'],
                        'name' => $type['caption'],
                        'type' => $type['reservationObjectType']
                    );
                }
            }

            $reservationTypeField = new C4GSelectField();
            $reservationTypeField->setChosen(false);
            $reservationTypeField->setFieldName('reservation_type');
            $reservationTypeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_type_short']);
            $reservationTypeField->setSortColumn(false);
            $reservationTypeField->setTableColumn($this->showReservationType);
            $reservationTypeField->setFormField($this->showReservationType);
            //$reservationTypeField->setColumnWidth(20);
            $reservationTypeField->setSize(1);
            $reservationTypeField->setOptions($typelist);
            $reservationTypeField->setMandatory(false);
            $reservationTypeField->setStyleClass('reservation-type');
            $reservationTypeField->setNotificationField(true);
            $reservationTypeField->setEditable(false);
            $reservationTypeField->setPrintable(true);
            $reservationTypeField->setTableColumnPriority(3);
            $reservationTypeField->setComparable(false);
            $fieldList[] = $reservationTypeField;

            $startTime = time() - (($this->past_day_number ?: 1) * 86400);
            $reservationObjects = C4gReservationHandler::getReservationObjectList($selectTypes, 0, $this->showPrices, $this->showPricesWithTaxes,true, 0, 0, '', $startTime);
            $objects = [];

            foreach ($reservationObjects as $objectType=>$objList) {
                foreach ($objList as $reservationObject) {
                    $objects[] = array(
                        'id' => $reservationObject->getId(),
                        'name' => $reservationObject->getCaption(),
                        'type' => $objectType
                    );
                }
            }

            $reservationObjectField = new C4GSelectField();
            $reservationObjectField->setFieldName('reservation_object');
            $reservationObjectField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object_short']);
            $reservationObjectField->setTableColumn(true);
            //$reservationObjectField->setColumnWidth(20);
            $reservationObjectField->setOptions($objects);
            $reservationObjectField->setMandatory(false);
            $reservationObjectField->setNotificationField(true);
            $reservationObjectField->setStyleClass('reservation-object');
            $reservationObjectField->setShowIfEmpty(false);
            $reservationObjectField->setEditable(false);
            $reservationObjectField->setPrintable(true);
            $reservationObjectField->setTableColumnPriority(1);
            $reservationObjectField->setTableColumn($this->showReservationObject);
            $reservationObjectField->setFormField($this->showReservationObject);
            $reservationObjectField->setComparable(false);
            $fieldList[] = $reservationObjectField;
        }

        if ($this->viewType === 'publicview') {
            $lastnameField = new C4GTextField();
            $lastnameField->setFieldName('lastname');
            $lastnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname']);
            //$lastnameField->setColumnWidth(10);
            $lastnameField->setSortColumn(false);
            $lastnameField->setTableColumn(true);
            $lastnameField->setMandatory(true);
            $lastnameField->setNotificationField(true);
            $lastnameField->setStyleClass('lastname');
            $lastnameField->setPrintable(true);
            $lastnameField->setTableColumnPriority(2);
            //$lastnameField->setEditable(false);
            $fieldList[] = $lastnameField;

            $firstnameField = new C4GTextField();
            $firstnameField->setFieldName('firstname');
            $firstnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname']);
            //$firstnameField->setColumnWidth(10);
            $firstnameField->setSortColumn(false);
            $firstnameField->setTableColumn(true);
            $firstnameField->setMandatory(true);
            $firstnameField->setNotificationField(true);
            $firstnameField->setStyleClass('firstname');
            //$firstnameField->setEditable(false);
            $firstnameField->setPrintable(false);
            $firstnameField->setTableColumnPriority(2);
            $fieldList[] = $firstnameField;

            $emailField = new C4GEmailField();
            $emailField->setFieldName('email');
            $emailField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['email']);
            //$emailField->setColumnWidth(20);
            //$emailField->setSortColumn(false);
            $emailField->setTableColumn(false);
            $emailField->setMandatory(true);
            $emailField->setNotificationField(true);
            $emailField->setStyleClass('email');
            //$emailField->setEditable(false);
            $emailField->setPrintable(false);
            $fieldList[] = $emailField;

            $commentField = new C4GTextareaField();
            $commentField->setFieldName('comment');
            $commentField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['comment_short']);
            //$commentField->setColumnWidth();
            $commentField->setSortColumn(false);
            $commentField->setTableColumn(true);
            $commentField->setNotificationField(true);
            $commentField->setStyleClass('comment');
            $commentField->setShowIfEmpty(false);
            $commentField->setPrintable(true);
            $fieldList[] = $commentField;
        } else if ($this->viewType !== 'publicview') {
            $reservationIdField = new C4GTextField();
            $reservationIdField->setFieldName('reservation_id');
            $reservationIdField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_id']);
            //$reservationIdField->setColumnWidth(5);
            $reservationIdField->setSortColumn(false);
            $reservationIdField->setTableColumn(true);
            $reservationIdField->setMandatory(false);
            $reservationIdField->setInitialValue(C4GBrickCommon::getUUID());
            $reservationIdField->setTableRow(false);
            $reservationIdField->setEditable(false);
            $reservationIdField->setUnique(true);
            $reservationIdField->setNotificationField(true);
            $reservationIdField->setDbUnique(true);
            $reservationIdField->setSimpleTextWithoutEditing(false); //!!!
            $reservationIdField->setDatabaseField(true);
            $reservationIdField->setDbUniqueResult($GLOBALS['TL_LANG']['fe_c4g_reservation']['duplicate_reservation_id']);
            //$reservationIdField->setDbUniqueAdditionalCondition("tl_c4g_reservation.cancellation <> '1' AND tl_c4g_reservation.beginDate > UNIX_TIMESTAMP(NOW())");
            $reservationIdField->setStyleClass('reservation-id');
            $reservationIdField->setEditable(false);
            $reservationIdField->setPrintable(false);
            $reservationIdField->setTableColumnPriority(3);
            $fieldList[] = $reservationIdField;

            $organisationField = new C4GTextField();
            $organisationField->setFieldName('organisation');
            $organisationField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['organisation']);
            //$organisationField->setColumnWidth(10);
            $organisationField->setSortColumn(false);
            $organisationField->setTableColumn(false);
            $organisationField->setNotificationField(true);
            $organisationField->setStyleClass('organisation');
            $organisationField->setShowIfEmpty(false);
            $organisationField->setPrintable(true);
            $fieldList[] = $organisationField;

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

//            $salutation = [
//                ['id' => 'man' ,'name' => &$GLOBALS['TL_LANG']['fe_c4g_reservation']['man']],
//                ['id' => 'woman','name' => &$GLOBALS['TL_LANG']['fe_c4g_reservation']['woman']],
//                ['id' => 'various','name' => &$GLOBALS['TL_LANG']['fe_c4g_reservation']['various']],
//            ];
//
//            $salutationField = new C4GSelectField();
//            $salutationField->setFieldName('salutation');
//            $salutationField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['salutation']);
//            $salutationField->setSortColumn(false);
//            $salutationField->setTableColumn(false);
//            $salutationField->setOptions($salutation);
//            $salutationField->setNotificationField(true);
//            $salutationField->setStyleClass('salutation');
//            $salutationField->setShowIfEmpty(false);
//            $salutationField->setWithEmptyOption(false);
//            $fieldList[] = $salutationField;

            $firstnameField = new C4GTextField();
            $firstnameField->setFieldName('firstname');
            $firstnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname']);
            //$firstnameField->setColumnWidth(10);
            $firstnameField->setSortColumn(false);
            $firstnameField->setTableColumn(true);
            $firstnameField->setNotificationField(true);
            $firstnameField->setStyleClass('firstname');
            $firstnameField->setShowIfEmpty(false);
            $firstnameField->setPrintable(true);
            $firstnameField->setTableColumnPriority(2);
            $fieldList[] = $firstnameField;

            $lastnameField = new C4GTextField();
            $lastnameField->setFieldName('lastname');
            $lastnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname']);
            //$lastnameField->setColumnWidth(10);
            $lastnameField->setSortColumn(false);
            $lastnameField->setTableColumn(true);
            $lastnameField->setNotificationField(true);
            $lastnameField->setStyleClass('lastname');
            $lastnameField->setShowIfEmpty(false);
            $lastnameField->setPrintable(true);
            $lastnameField->setTableColumnPriority(2);
            $fieldList[] = $lastnameField;

            $addressField = new C4GTextField();
            $addressField->setFieldName('address');
            $addressField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['address']);
            //$addressField->setColumnWidth(60);
            $addressField->setSortColumn(false);
            $addressField->setTableColumn(false);
            $addressField->setNotificationField(true);
            $addressField->setStyleClass('address');
            $addressField->setShowIfEmpty(false);
            $addressField->setPrintable(true);
            $fieldList[] = $addressField;

            $postalField = new C4GPostalField();
            $postalField->setFieldName('postal');
            $postalField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['postal']);
            //$postalField->setColumnWidth(60);
            $postalField->setSize(5); //international 32
            $postalField->setSortColumn(false);
            $postalField->setTableColumn(false);
            $postalField->setNotificationField(true);
            $postalField->setStyleClass('postal');
            $postalField->setShowIfEmpty(false);
            $postalField->setPrintable(true);
            $fieldList[] = $postalField;

            $cityField = new C4GTextField();
            $cityField->setFieldName('city');
            $cityField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['city']);
            //$cityField->setColumnWidth(60);
            $cityField->setSortColumn(false);
            $cityField->setTableColumn(false);
            $cityField->setNotificationField(true);
            $cityField->setStyleClass('city');
            $cityField->setShowIfEmpty(false);
            $cityField->setPrintable(true);
            $fieldList[] = $cityField;

            $emailField = new C4GEmailField();
            $emailField->setFieldName('email');
            $emailField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['email']);
            //$emailField->setColumnWidth(20);
            $emailField->setSortColumn(false);
            $emailField->setTableColumn(false);
            $emailField->setMandatory(false);
            $emailField->setNotificationField(true);
            $emailField->setStyleClass('email');
            $emailField->setShowIfEmpty(false);
            $emailField->setPrintable(true);
            $fieldList[] = $emailField;

            $phoneField = new C4GTelField();
            $phoneField->setFieldName('phone');
            $phoneField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['phone']);
            //$phoneField->setColumnWidth(10);
            $phoneField->setSortColumn(false);
            $phoneField->setTableColumn(false);
            $phoneField->setNotificationField(true);
            $phoneField->setStyleClass('phone');
            $phoneField->setShowIfEmpty(false);
            $phoneField->setPrintable(true);
            $fieldList[] = $phoneField;

            $birthDateField = new C4GDateField();
            //$birthDateField->setFlipButtonPosition(true);
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
            $birthDateField->setShowIfEmpty(false);
            $birthDateField->setPrintable(true);
            $birthDateField->setDatePickerByBrowser(true);
            $fieldList[] = $birthDateField;

            $organisationField2 = new C4GTextField();
            $organisationField2->setFieldName('organisation2');
            $organisationField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['organisation2']);
            //$organisationField2->setColumnWidth(10);
            $organisationField2->setSortColumn(false);
            $organisationField2->setTableColumn(false);
            $organisationField2->setNotificationField(true);
            $organisationField2->setStyleClass('organisation');
            $organisationField2->setShowIfEmpty(false);
            $organisationField2->setPrintable(true);
            $fieldList[] = $organisationField2;

//            $salutationField2 = new C4GSelectField();
//            $salutationField2->setFieldName('salutation2');
//            $salutationField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['salutation2']);
//            $salutationField2->setSortColumn(false);
//            $salutationField2->setTableColumn(false);
//            $salutationField2->setOptions($salutation);
//            $salutationField2->setNotificationField(true);
//            $salutationField2->setStyleClass('salutation');
//            $salutationField2->setShowIfEmpty(false);
//            $salutationField2->setWithEmptyOption(false);
//            $fieldList[] = $salutationField2;

            $titleField2 = new C4GTextField();
            $titleField2->setFieldName('title2');
            $titleField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['title2']);
            $titleField2->setSortColumn(false);
            $titleField2->setTableColumn(false);
            $titleField2->setMandatory(false);
            $titleField2->setNotificationField(true);
            $titleField2->setStyleClass('title');
            $titleField2->setShowIfEmpty(false);
            $titleField2->setPrintable(true);
            $fieldList[] = $titleField2;

            $firstnameField2 = new C4GTextField();
            $firstnameField2->setFieldName('firstname2');
            $firstnameField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname2']);
            //$firstnameField2->setColumnWidth(10);
            $firstnameField2->setSortColumn(false);
            $firstnameField2->setTableColumn(false);
            $firstnameField2->setNotificationField(true);
            $firstnameField2->setStyleClass('firstname');
            $firstnameField2->setShowIfEmpty(false);
            $firstnameField2->setPrintable(true);
            $fieldList[] = $firstnameField2;

            $lastnameField2 = new C4GTextField();
            $lastnameField2->setFieldName('lastname2');
            $lastnameField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname2']);
            //$lastnameField2->setColumnWidth(10);
            $lastnameField2->setSortColumn(false);
            $lastnameField2->setTableColumn(false);
            $lastnameField2->setNotificationField(true);
            $lastnameField2->setStyleClass('lastname');
            $lastnameField2->setShowIfEmpty(false);
            $lastnameField2->setPrintable(true);
            $fieldList[] = $lastnameField2;

            $addressField2 = new C4GTextField();
            $addressField2->setFieldName('address2');
            $addressField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['address2']);
            //$addressField2->setColumnWidth(60);
            $addressField2->setSortColumn(false);
            $addressField2->setTableColumn(false);
            $addressField2->setNotificationField(true);
            $addressField2->setStyleClass('address');
            $addressField2->setShowIfEmpty(false);
            $addressField2->setPrintable(true);
            $fieldList[] = $addressField2;

            $postalField2 = new C4GPostalField();
            $postalField2->setFieldName('postal2');
            $postalField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['postal2']);
            //$postalField2->setColumnWidth(60);
            $postalField2->setSize(5); //international 32
            $postalField2->setSortColumn(false);
            $postalField2->setTableColumn(false);
            $postalField2->setNotificationField(true);
            $postalField2->setStyleClass('postal');
            $postalField2->setShowIfEmpty(false);
            $postalField2->setPrintable(true);
            $fieldList[] = $postalField2;

            $cityField2 = new C4GTextField();
            $cityField2->setFieldName('city2');
            $cityField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['city2']);
            //$cityField2->setColumnWidth(60);
            $cityField2->setSortColumn(false);
            $cityField2->setTableColumn(false);
            $cityField2->setNotificationField(true);
            $cityField2->setStyleClass('city');
            $cityField2->setShowIfEmpty(false);
            $cityField2->setPrintable(true);
            $fieldList[] = $cityField2;

            $emailField2 = new C4GEmailField();
            $emailField2->setFieldName('email2');
            $emailField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['email2']);
            //$emailField2->setColumnWidth(10);
            $emailField2->setSortColumn(false);
            $emailField2->setTableColumn(false);
            $emailField2->setNotificationField(true);
            $emailField2->setStyleClass('email');
            $emailField2->setShowIfEmpty(false);
            $emailField2->setPrintable(true);
            $fieldList[] = $emailField2;

            $phoneField2 = new C4GTelField();
            $phoneField2->setFieldName('phone2');
            $phoneField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['phone2']);
            //$phoneField2->setColumnWidth(10);
            $phoneField2->setSortColumn(false);
            $phoneField2->setTableColumn(false);
            $phoneField2->setNotificationField(true);
            $phoneField2->setStyleClass('phone');
            $phoneField2->setShowIfEmpty(false);
            $phoneField2->setPrintable(true);
            $fieldList[] = $phoneField2;

            $commentField = new C4GTextareaField();
            $commentField->setFieldName('comment');
            $commentField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['comment_short']);
            //$commentField->setColumnWidth();
            $commentField->setSortColumn(false);
            $commentField->setTableColumn(true);
            $commentField->setNotificationField(true);
            $commentField->setStyleClass('comment');
            $commentField->setShowIfEmpty(true);
            $commentField->setPrintable(true);
            $fieldList[] = $commentField;

            if ($this->viewType === 'group') {

                $internalCommentField = new C4GTextareaField();
                $internalCommentField->setFieldName('internal_comment');
                $internalCommentField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['internalComment']);
                $internalCommentField->setSortColumn(false);
                $internalCommentField->setTableColumn(false);
                $internalCommentField->setNotificationField(true);
                $internalCommentField->setStyleClass('comment');
                $internalCommentField->setShowIfEmpty(true);
                $internalCommentField->setPrintable(true);
                $fieldList[] = $internalCommentField;

                $confirmedField = new C4GCheckboxField();
                $confirmedField->setFieldName('confirmed');
                $confirmedField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['confirmed']);
                $confirmedField->setSortColumn(false);
                $confirmedField->setTableColumn(false);
                $confirmedField->setNotificationField(true);
                $confirmedField->setStyleClass('confirmed');
                $confirmedField->setWithoutDescriptionLineBreak(true);
                $confirmedField->setPrintable(true);
                $confirmedField->setTableRow(false);
                $confirmedField->setTableRowWidth('100%');
                $confirmedField->setTableRowLabelWidth('25%');
                $fieldList[] = $confirmedField;

                $specialNotificationField = new C4GCheckboxField();
                $specialNotificationField->setFieldName('specialNotification');
                $specialNotificationField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['specialNotification']);
                $specialNotificationField->setSortColumn(false);
                $specialNotificationField->setTableColumn(false);
                $specialNotificationField->setNotificationField(true);
                $specialNotificationField->setStyleClass('confirmed');
                $specialNotificationField->setWithoutDescriptionLineBreak(true);
                $specialNotificationField->setPrintable(true);
                $specialNotificationField->setTableRow(false);
                $specialNotificationField->setTableRowWidth('100%');
                $specialNotificationField->setTableRowLabelWidth('25%');
                $fieldList[] = $specialNotificationField;

                if ($this->showSignatureField) {
                    $signatureField = new C4GSignaturePadField();
                    $signatureField->setFieldName('signature');
                    $signatureField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['signature']);
                    $signatureField->setEditable(true);
                    $signatureField->setPrintable(true);
                    $signatureField->setMandatory(true);
                    $fieldList[] = $signatureField;
                }

//                $cancellationField = new C4GCheckboxField();
//                $cancellationField->setFieldName('cancellation');
//                $cancellationField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['cancellation']);
//                //$cancellationField->setColumnWidth(5);
//                $cancellationField->setSortColumn(false);
//                $cancellationField->setTableColumn(false);
//                $cancellationField->setNotificationField(true);
//                $cancellationField->setStyleClass('cancellation');
//                $cancellationField->setWithoutDescriptionLineBreak(true);
//                $cancellationField->setPrintable(false);
//                $cancellationField->setTableRow(true);
//                $cancellationField->setTableRowWidth('100%');
//                $cancellationField->setTableRowLabelWidth('25%');
//                $fieldList[] = $cancellationField;
            }

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

//            $participants = [];
//
//            $participantsKey = new C4GKeyField();
//            $participantsKey->setFieldName('id');
//            $participantsKey->setComparable(false);
//            $participantsKey->setEditable(false);
//            $participantsKey->setHidden(true);
//            $participantsKey->setFormField(true);
//            $participantsKey->setPrintable(false);
//
//            $participantsForeign = new C4GForeignKeyField();
//            $participantsForeign->setFieldName('pid');
//            $participantsForeign->setHidden(true);
//            $participantsForeign->setFormField(true);
//            $participantsForeign->setPrintable(false);
//
//            $titleField = new C4GTextField();
//            $titleField->setFieldName('title');
//            $titleField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['title']);
//            $titleField->setSortColumn(false);
//            $titleField->setTableColumn(false);
//            $titleField->setMandatory(false);
//            $titleField->setNotificationField(false);
//            $titleField->setPrintable(true);
//            $participants[] = $titleField;
//
//            $firstnameField = new C4GTextField();
//            $firstnameField->setFieldName('firstname');
//            $firstnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname']);
//            $firstnameField->setColumnWidth(10);
//            $firstnameField->setSortColumn(false);
//            $firstnameField->setTableColumn(false);
//            $firstnameField->setMandatory(true);
//            $firstnameField->setNotificationField(false);
//            $firstnameField->setPrintable(true);
//            $participants[] = $firstnameField;
//
//            $lastnameField = new C4GTextField();
//            $lastnameField->setFieldName('lastname');
//            $lastnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname']);
//            $lastnameField->setColumnWidth(10);
//            $lastnameField->setSortColumn(false);
//            $lastnameField->setTableColumn(false);
//            $lastnameField->setMandatory(true);
//            $lastnameField->setNotificationField(false);
//            $lastnameField->setPrintable(true);
//            $participants[] = $lastnameField;
//
//            $emailField = new C4GEmailField();
//            $emailField->setFieldName('email');
//            $emailField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['email']);
//            $emailField->setColumnWidth(10);
//            $emailField->setSortColumn(false);
//            $emailField->setTableColumn(false);
//            $emailField->setMandatory(false);
//            $emailField->setNotificationField(false);
//            $emailField->setPrintable(true);
//            $participants[] = $emailField;
//
//            $participantParamField = new C4GMultiCheckboxField();
//            $participantParamField->setFieldName('participant_params');
//            $participantParamField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['participant_params']);
//            $participantParamField->setFormField(true);
//            //$participantParamField->setEditable(true);
//            $participantParamField->setOptions($paramList);
//            $participantParamField->setMandatory(false);
//            $participantParamField->setModernStyle(false);
//            $participantParamField->setStyleClass('participant-params');
//            $participantParamField->setNotificationField(false);
//            $participantParamField->setShowIfEmpty(false);
//            $participantParamField->setPrintable(false);
//            $participants[] = $participantParamField;
//
//            $reservationParticipants = new C4GSubDialogField();
//            $reservationParticipants->setFieldName('participants');
//            $reservationParticipants->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['participants']);
//            $reservationParticipants->setAddButton($GLOBALS['TL_LANG']['fe_c4g_reservation']['addParticipant']);
//            $reservationParticipants->setRemoveButton($GLOBALS['TL_LANG']['fe_c4g_reservation']['removeParticipant']);
//            $reservationParticipants->setRemoveButtonMessage($GLOBALS['TL_LANG']['fe_c4g_reservation']['removeParticipantMessage']);
//            $reservationParticipants->setTable('tl_c4g_reservation_participants');
//            $reservationParticipants->addFields($participants);
//            $reservationParticipants->setKeyField($participantsKey);
//            $reservationParticipants->setForeignKeyField($participantsForeign);
//            $reservationParticipants->setMandatory(false);
//            $reservationParticipants->setNotificationField(false);
//            //$reservationParticipants->setShowFirstDataSet(false);
//            //$reservationParticipants->setShowIfEmpty(false);
//            $reservationParticipants->setDelimiter('~');
//            $reservationParticipants->setTableColumn(false);
//            //$reservationParticipants->setEditable(false);
//            //$reservationParticipants->setShowButtons(false);
//            $reservationParticipants->setPrintable(false);
//            $fieldList[] = $reservationParticipants;
        }

        return $fieldList;
    }

    public function clickCancellation($values, $putVars) {
        $return = [];
        if ($this->cancellation_redirect_site) {
            $redirectPage = C4GUtils::replaceInsertTags("{{link_url::".$this->cancellation_redirect_site."}}")."?lastname=".$putVars['lastname']."&reservation_id=".$putVars['reservation_id'];
            $return['jump_to_url'] = $redirectPage;
        }

        return $return;
    }


}

