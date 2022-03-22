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

use con4gis\CoreBundle\Classes\C4GVersionProvider;
use con4gis\ProjectsBundle\Classes\Buttons\C4GBrickButton;
use con4gis\ProjectsBundle\Classes\Common\C4GBrickConst;
use con4gis\ProjectsBundle\Classes\Dialogs\C4GBrickGrid;
use con4gis\ProjectsBundle\Classes\Dialogs\C4GBrickGridElement;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GButtonField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GEmailField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GGeopickerField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GGridField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GHeadlineField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GImageField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GKeyField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GLinkField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GMultiLinkField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GPostalField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTelField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTextField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTrixEditorField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GUrlField;
use con4gis\ProjectsBundle\Classes\Framework\C4GBaseController;
use con4gis\ProjectsBundle\Classes\Framework\C4GController;
use con4gis\ProjectsBundle\Classes\Lists\C4GBrickRenderMode;
use con4gis\ProjectsBundle\Classes\Views\C4GBrickViewType;
use con4gis\ReservationBundle\Classes\Models\C4gReservationEventSpeakerModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationLocationModel;
use con4gis\ReservationBundle\Classes\Projects\C4gReservationBrickTypes;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationCalculator;
use Contao\CalendarEventsModel;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Input;
use Contao\ModuleModel;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class C4gReservationLocationListController extends C4GBaseController
{
    public const TYPE = 'C4gReservationLocationList';

    protected $tableName = 'tl_c4g_reservation_location';
    protected $modelClass = C4gReservationLocationModel::class;
    protected $languageFile = 'fe_c4g_reservation_location';
    protected $brickKey = C4gReservationBrickTypes::BRICK_RESERVATION_LOCATION;
    protected $viewType = C4GBrickViewType::PUBLICVIEW;
    protected $sendEMails = null;
    protected $withNotification = false;
    protected $permalink_name = 'location';
    protected $permalink_field = 'alias';

    //Resource Params
    protected $loadDefaultResources = true;
    protected $loadTrixEditorResources = false;
    protected $loadDateTimePickerResources = false;
    protected $loadChosenResources = false;
    protected $loadClearBrowserUrlResources = false;
    protected $loadConditionalFieldDisplayResources = false;
    protected $loadMoreButtonResources = false;
    protected $loadFontAwesomeResources = true;
    protected $loadTriggerSearchFromOtherModuleResources = true;
    protected $loadFileUploadResources = false;
    protected $loadMultiColumnResources = false;
    protected $loadMiniSearchResources = false;
    protected $loadHistoryPushResources = true;

    protected $loadSignaturePadResources = false;

    //JQuery GUI Resource Params
    protected $jQueryAddCore = true;
    protected $jQueryAddJquery = true;
    protected $jQueryAddJqueryUI = false;
    protected $jQueryUseTree = false;
    protected $jQueryUseTable = false;
    protected $jQueryUseHistory = false;
    protected $jQueryUseTooltip = false;
    protected $jQueryUseMaps = true;
    protected $jQueryUseGoogleMaps = false;
    protected $jQueryUseMapsEditor = false;
    protected $jQueryUseWswgEditor = false;
    protected $jQueryUseScrollPane = false;
    protected $jQueryUsePopups = false;

    protected $withPermissionCheck = false;

    /**
     * @param string $rootDir
     * @param Session $session
     * @param ContaoFramework $framework
     */
    public function __construct(string $rootDir, Session $session, ContaoFramework $framework, ModuleModel $model = null)
    {
        parent::__construct($rootDir, $session, $framework, $model);

        if ($model && $model->renderMode) {
            $this->renderMode = $model->renderMode;
            if ($this->renderMode == C4GBrickRenderMode::TABLEBASED) {
                $this->jQueryAddJqueryUI = true;
                $this->jQueryUseTable = true;
            }
        }
    }

    /**
     * @param Template $template
     * @param ModuleModel $model
     * @param Request $request
     * @return Response|null
     */
    public function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        if ($model && $model->renderMode) {
            $this->renderMode = $model->renderMode;
            if ($this->renderMode == C4GBrickRenderMode::TABLEBASED) {
                $this->jQueryAddJqueryUI = true;
                $this->jQueryUseTable = true;
            }
        }

        $result = parent::getResponse($template, $model, $request);

        return $result;
    }

    /**
     * @param $id
     * @return void
     */
    public function initBrickModule($id)
    {
        parent::initBrickModule($id);

        $this->setBrickCaptions(
            '',
            $GLOBALS['TL_LANG']['fe_c4g_reservation_location']['brick_caption_plural']
        );
        
        $this->listParams->setRenderMode($this->renderMode ?: C4GBrickRenderMode::TILEBASED);

        $this->dialogParams->deleteButton(C4GBrickConst::BUTTON_CANCEL);
    }

    public function addFields() : array
    {
        $fieldList = array();

        $tableBased = ($this->renderMode == C4GBrickRenderMode::TABLEBASED) && !($this->dialogParams && $this->dialogParams->getId() && ($this->dialogParams->getId() > 0));

        $idField = new C4GKeyField();
        $idField->setFieldName('id');
        $idField->setEditable(false);
        $idField->setFormField(false);
        $idField->setSortColumn(false);
        $idField->setPrintable(false);
        $idField->setShowIfEmpty(false);
        $fieldList[] = $idField;

        $aliasField = new C4GTextField();
        $aliasField->setFieldName('alias');
        $aliasField->setEditable(false);
        $aliasField->setFormField(false);
        $aliasField->setSortColumn(false);
        $aliasField->setPrintable(false);
        $fieldList[] = $aliasField;

        $nameField = new C4GTextField();
        $nameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_location']['name']);
        $nameField->setFieldName('name');
        $nameField->setEditable(false);
        $nameField->setFormField(true);
        $nameField->setTableColumn(true);
        $nameField->setShowIfEmpty(false);
        $fieldList[] = $nameField;

        if ($this->withMap && C4GVersionProvider::isInstalled('con4gis/maps')) {
            $geopickerField = new C4GGeopickerField();
            $geopickerField->setFieldName('geopicker');
            $geopickerField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_location']['geopicker']);
            //$geopickerField->setDescription($GLOBALS['TL_LANG']['fe_c4g_reservation_location']['geopicker'][1]);
            $geopickerField->setSortColumn(false);
            $geopickerField->setTableColumn(false);
            $geopickerField->setMandatory(true);
            $geopickerField->setWithoutAddressReloadButton(true);
            $geopickerField->setWithoutAddressRow(true);
            $geopickerField->setEditable(false);
            $geopickerField->setComparable(false);
            $geopickerField->setLatitudeField('locgeoy');
            $geopickerField->setLongitudeField('locgeox');
            $geopickerField->setGeocodeAddressFields(['contact_street','contact_postal','contact_city']);
            $geopickerField->setShowIfEmpty(false);
            $fieldList[] = $geopickerField;
        }

        $headlineField = new C4GHeadlineField();
        $headlineField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_location']['contact_headline']);
        $headlineField->setFormField(true);
        $fieldList[] = $headlineField;

        $contactNameField = new C4GTextField();
        $contactNameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_location']['contact_name']);
        $contactNameField->setFieldName('contact_name');
        $contactNameField->setEditable(false);
        $contactNameField->setFormField(true);
        $contactNameField->setTableColumn(false);
        $contactNameField->setShowIfEmpty(false);
        $fieldList[] = $contactNameField;

        $contactStreetField = new C4GTextField();
        $contactStreetField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_location']['contact_street']);
        $contactStreetField->setFieldName('contact_street');
        $contactStreetField->setEditable(false);
        $contactStreetField->setFormField(true);
        $contactStreetField->setTableColumn(true);
        $contactStreetField->setShowIfEmpty(false);
        $fieldList[] = $contactStreetField;

        $contactPhoneField = new C4GUrlField();
        $contactPhoneField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_location']['contact_phone']);
        $contactPhoneField->setFieldName('contact_phone');
        $contactPhoneField->setEditable(false);
        $contactPhoneField->setFormField(true);
        $contactPhoneField->setTableColumn(false);
        $contactPhoneField->setShowIfEmpty(false);
        $contactPhoneField->setLinkType(C4GUrlField::LINK_TYPE_PHONE);
        $fieldList[] = $contactPhoneField;

        $contactEmailField = new C4GUrlField();
        $contactEmailField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_location']['contact_email']);
        $contactEmailField->setFieldName('contact_email');
        $contactEmailField->setEditable(false);
        $contactEmailField->setFormField(true);
        $contactEmailField->setTableColumn(false);
        $contactEmailField->setShowIfEmpty(false);
        $contactEmailField->setLinkType(C4GUrlField::LINK_TYPE_EMAIL);
        $fieldList[] = $contactEmailField;

        $postal = new C4GPostalField();
        $postal->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_location']['contact_postal']);
        $postal->setFieldName('contact_postal');
        $postal->setEditable(false);
        $postal->setFormField(true);
        $postal->setTableColumn(true);
        $postal->setShowIfEmpty(false);

        $city = new C4GTextField();
        $city->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_location']['contact_city']);
        $city->setFieldName('contact_city');
        $city->setEditable(false);
        $city->setFormField(true);
        $city->setTableColumn(true);
        $city->setShowIfEmpty(false);

        if (!$tableBased) {
            $grid = new C4GBrickGrid([
                new C4GBrickGridElement($postal),
                new C4GBrickGridElement($city)
            ],2);

            $postalCityField = new C4GGridField($grid);
            $postalCityField->setFieldName('postalCityGrid');
            $postalCityField->setTableColumn(true);
            $postalCityField->setFormField(true);
            $postalCityField->setDatabaseField(false);
            $fieldList[] = $postalCityField;
        } else {
            $fieldList[] = $postal;
            $fieldList[] = $city;
        }

        $clickButton = new C4GBrickButton(
            C4GBrickConst::BUTTON_CLICK,
            $GLOBALS['TL_LANG']['fe_c4g_reservation_location']['back'],
            $visible = true,
            $enabled = true,
            $action = '',
            $accesskey = '',
            $defaultByEnter = true);

        $buttonField = new C4GButtonField($clickButton);
        $buttonField->setOnClickType(C4GBrickConst::ONCLICK_TYPE_CLIENT);
        $buttonField->setOnClick('backWithRefresh();return false;');
        $buttonField->setWithoutLabel(true);
        $fieldList[] = $buttonField;

        return $fieldList;
    }
}

