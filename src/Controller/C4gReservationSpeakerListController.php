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

namespace con4gis\ReservationBundle\Controller;

use con4gis\ProjectsBundle\Classes\Buttons\C4GBrickButton;
use con4gis\ProjectsBundle\Classes\Common\C4GBrickConst;
use con4gis\ProjectsBundle\Classes\Dialogs\C4GBrickGrid;
use con4gis\ProjectsBundle\Classes\Dialogs\C4GBrickGridElement;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GEmailField;
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
    protected $jQueryUseScrollPane = true;
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
            $GLOBALS['TL_LANG']['fe_c4g_reservation_speaker']['brick_caption'],
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

        $titleGridElement = new C4GBrickGridElement($titleField,0,0,'left','top','20%',1,1);
        $firstnameGridElement = new C4GBrickGridElement($firstname,1,0,'left','top','40%',2,1);
        $lastnameGridElement = new C4GBrickGridElement($lastname,2,0,'left','top','40%', 3, 1);
        $grid = new C4GBrickGrid([
            $titleGridElement,
            $firstnameGridElement,
            $lastnameGridElement
        ]);

        $nameField = new C4GGridField($grid);
        $nameField->setTableColumn(true);
        $nameField->setFormField(true);
        $fieldList[] = $nameField;

        $email = new C4GEmailField();
        $email->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_speaker']['email']);
        $email->setFieldName('email');
        $email->setEditable(false);
        $email->setFormField(true);
        $email->setTableColumn(false);
        $email->setShowIfEmpty(false);
        $fieldList[] = $email;

        $phone = new C4GTelField();
        $phone->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_speaker']['phone']);
        $phone->setFieldName('phone');
        $phone->setEditable(false);
        $phone->setFormField(true);
        $phone->setTableColumn(false);
        $phone->setShowIfEmpty(false);
        $fieldList[] = $phone;

        $address = new C4GTextField();
        $address->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_speaker']['address']);
        $address->setFieldName('address');
        $address->setEditable(false);
        $address->setFormField(true);
        $address->setTableColumn(false);
        $address->setShowIfEmpty(false);
        $fieldList[] = $address;

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

        $postalGridElement = new C4GBrickGridElement($postal,0,0,'left','top','50%',1,1);
        $cityGridElement = new C4GBrickGridElement($city,1,0,'left','top','50%',2,1);
        $grid = new C4GBrickGrid([
            $postalGridElement,
            $cityGridElement
        ]);

        $postalCityField = new C4GGridField($grid);
        $postalCityField->setTableColumn(true);
        $postalCityField->setFormField(true);
        $fieldList[] = $postalCityField;

        $website = new C4GUrlField();
        $website->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_speaker']['website']);
        $website->setFieldName('website');
        $website->setEditable(false);
        $website->setFormField(true);
        $website->setTableColumn(true);
        $website->setShowIfEmpty(false);
        $fieldList[] = $website;

        $vita = new C4GTrixEditorField();
        $vita->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_speaker']['vita']);
        $vita->setFieldName('vita');
        $vita->setEditable(false);
        $vita->setFormField(true);
        $vita->setTableColumn(false);
        $vita->setShowIfEmpty(false);
        $fieldList[] = $vita;

        $photo = new C4GImageField();
        //$photo->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_speaker']['photo']);
        $photo->setFieldName('photo');
        $photo->setEditable(false);
        $photo->setFormField(true);
        $photo->setTableColumn(true);
        $photo->setWithoutLabel(true);
        $photo->setShowIfEmpty(false);
        $fieldList[] = $photo;

        return $fieldList;
    }
}
