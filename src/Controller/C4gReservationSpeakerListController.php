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

use con4gis\ProjectsBundle\Classes\Buttons\C4GBrickButton;
use con4gis\ProjectsBundle\Classes\Common\C4GBrickConst;
use con4gis\ProjectsBundle\Classes\Dialogs\C4GBrickGrid;
use con4gis\ProjectsBundle\Classes\Dialogs\C4GBrickGridElement;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GEmailField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GForeignArrayField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GGridField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GImageField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GKeyField;
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
use con4gis\ReservationBundle\Classes\Projects\C4gReservationBrickTypes;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationCalculator;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\ModuleModel;
use Contao\System;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class C4gReservationSpeakerListController extends C4GBaseController
{
    public const TYPE = 'C4gReservationSpeakerList';

    protected $tableName = 'tl_c4g_reservation_event_speaker';
    protected $modelClass = C4gReservationEventSpeakerModel::class;
    protected $languageFile = 'fe_c4g_reservation_speaker';
    protected $brickKey = C4gReservationBrickTypes::BRICK_RESERVATION_SPEAKER;
    protected $viewType = C4GBrickViewType::PUBLICVIEW;
    protected $sendEMails = null;
    //protected $brickScript  = 'bundles/con4gisreservation/dist/js/c4g_brick_reservation.js';
    //protected $brickStyle   = 'bundles/con4gisreservation/dist/css/c4g_brick_reservation.min.css';
    protected $withNotification = false;
    protected $permalink_field = 'id';
    protected $permalink_name = 'speaker';

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
    protected $loadFileUploadResources = false;
    protected $loadMultiColumnResources = false;
    protected $loadMiniSearchResources = false;
    protected $loadHistoryPushResources = false;

    protected $loadSignaturePadResources = false;

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
            /*$GLOBALS['TL_LANG']['fe_c4g_reservation_speaker']['brick_caption']*/'',
            $GLOBALS['TL_LANG']['fe_c4g_reservation_speaker']['brick_caption_plural']
        );
        
        $this->listParams->setRenderMode($this->renderMode ?: C4GBrickRenderMode::TILEBASED);

        $this->dialogParams->changeButtonCaption(C4GBrickConst::BUTTON_CANCEL, $GLOBALS['TL_LANG']['fe_c4g_reservation_speaker']['button_cancel']);
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
        $idField->setShowIfEmpty(false);
        $fieldList[] = $idField;

        $photo = new C4GImageField();
        //$photo->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_speaker']['photo']);
        $photo->setFieldName('photo');
        $photo->setEditable(false);
        $photo->setFormField(true);
        $photo->setTableColumn(true);
        $photo->setWithoutLabel(true);
        $photo->setShowIfEmpty(false);
        //$fieldList[] = $photo;

        $titleField = new C4GTextField();
        $titleField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_speaker']['title']);
        $titleField->setFieldName('title');
        $titleField->setEditable(false);
        $titleField->setFormField(true);
        $titleField->setTableColumn(false);
        $titleField->setShowIfEmpty(false);
        //$fieldList[] = $titleField;

        $firstname = new C4GTextField();
        $firstname->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_speaker']['firstname']);
        $firstname->setFieldName('firstname');
        $firstname->setEditable(false);
        $firstname->setFormField(true);
        $firstname->setTableColumn(true);
        $firstname->setShowIfEmpty(false);
        //$fieldList[] = $firstname;

        $lastname = new C4GTextField();
        $lastname->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_speaker']['lastname']);
        $lastname->setFieldName('lastname');
        $lastname->setEditable(false);
        $lastname->setFormField(true);
        $lastname->setTableColumn(true);
        $lastname->setShowIfEmpty(false);
        //$fieldList[] = $lastname;

        //C4GBrickCSSGrid
        $grid = new C4GBrickGrid([
            new C4GBrickGridElement($titleField),
            new C4GBrickGridElement($firstname),
            new C4GBrickGridElement($lastname)
        ], 3);

        $nameField = new C4GGridField($grid);
        $nameField->setTitle("");
        $nameField->setFieldName('nameGrid');
        $nameField->setTableColumn(true); //ToDO
        $nameField->setFormField(true);
        $nameField->setDatabaseField(false);
        //$fieldList[] = $nameField;

        $address = new C4GTextField();
        $address->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_speaker']['address']);
        $address->setFieldName('address');
        $address->setEditable(false);
        $address->setFormField(true);
        $address->setTableColumn(false);
        $address->setShowIfEmpty(false);
        //$fieldList[] = $address;

        $postal = new C4GPostalField();
        $postal->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_speaker']['postal']);
        $postal->setFieldName('postal');
        $postal->setEditable(false);
        $postal->setFormField(true);
        $postal->setTableColumn(false);
        $postal->setShowIfEmpty(false);
        //$fieldList[] = $postal;

        $city = new C4GTextField();
        $city->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_speaker']['city']);
        $city->setFieldName('city');
        $city->setEditable(false);
        $city->setFormField(true);
        $city->setTableColumn(false);
        $city->setShowIfEmpty(false);
        //$fieldList[] = $city;

        $grid = new C4GBrickGrid([
            new C4GBrickGridElement($postal),
            new C4GBrickGridElement($city)
        ],2);

        $postalCityField = new C4GGridField($grid);
        $postalCityField->setFieldName('postalCityGrid');
        $postalCityField->setTableColumn(false);
        $postalCityField->setFormField(true);
        $postalCityField->setDatabaseField(false);
        //$fieldList[] = $postalCityField;

        $phone = new C4GTelField();
        $phone->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_speaker']['phone']);
        $phone->setFieldName('phone');
        $phone->setEditable(false);
        $phone->setFormField(true);
        $phone->setTableColumn(true);
        $phone->setShowIfEmpty(false);
        //$fieldList[] = $phone;

        $email = new C4GEmailField();
        $email->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_speaker']['email']);
        $email->setFieldName('email');
        $email->setEditable(false);
        $email->setFormField(true);
        $email->setTableColumn(true);
        $email->setShowIfEmpty(false);
        //$fieldList[] = $email;

        $website = new C4GUrlField();
        $website->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_speaker']['website']);
        $website->setFieldName('website');
        $website->setEditable(false);
        $website->setFormField(true);
        $website->setTableColumn(true);
        $website->setShowIfEmpty(false);
        //$fieldList[] = $website;

        $grid = new C4GBrickGrid([
            new C4GBrickGridElement($nameField),
            new C4GBrickGridElement($address),
            new C4GBrickGridElement($postalCityField),
            new C4GBrickGridElement($phone),
            new C4GBrickGridElement($email),
            new C4GBrickGridElement($website)
        ], 1);

        $personalDataField = new C4GGridField($grid);
        $personalDataField->setTitle(""); //ToDO Language
        $personalDataField->setFieldName('header-content');
        $personalDataField->setTableColumn(true); //ToDO
        $personalDataField->setFormField(true);
        $personalDataField->setDatabaseField(false);
        //$fieldList[] = $personalDataField;

        $grid = new C4GBrickGrid([
            new C4GBrickGridElement($photo),
            new C4GBrickGridElement($personalDataField),
        ], 2);

        $headerField = new C4GGridField($grid);
        $headerField->setTitle(""); //ToDO Language
        $headerField->setFieldName('header-content');
        $headerField->setTableColumn(false); //ToDO
        $headerField->setFormField(true);
        $headerField->setDatabaseField(false);
        $fieldList[] = $headerField;

        $vita = new C4GTrixEditorField();
        $vita->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_speaker']['vita']);
        $vita->setFieldName('vita');
        $vita->setEditable(false);
        $vita->setFormField(true);
        $vita->setTableColumn(false);
        $vita->setShowIfEmpty(false);
        $fieldList[] = $vita;

        $grid = new C4GBrickGrid([
            new C4GBrickGridElement($photo),
            new C4GBrickGridElement($nameField),
            new C4GBrickGridElement($phone),
            new C4GBrickGridElement($email),
            new C4GBrickGridElement($website)
        ], 1);

        $tileContent = new C4GGridField($grid);
        $tileContent->setTitle(""); //ToDO Language
        $tileContent->setFieldName('tile-content');
        $tileContent->setTableColumn(true); //ToDO
        $tileContent->setFormField(false);
        $tileContent->setDatabaseField(false);
        $fieldList[] = $tileContent;

//        if ($this->dialogId) {
//
//        }
//
//        $events = new C4GForeignArrayField();
//        $events->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_speaker']['events']);
//        $events->setFieldName('events');
//        $events->setIdentifier('events');
//        $events->setAutoAdd(false);
//        $events->setForeignTable('tl_calendar_events');
//        $events->setModelClass(\Contao\CalendarEventsModel::class);
//        $events->setForeignKey('id');
//        $events->setForeignFieldList(array($eventGrid));
//        $events->setDelimiter('µ');
//        $fieldList[] = $events;
        return $fieldList;
    }
}

