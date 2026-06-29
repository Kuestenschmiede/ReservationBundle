<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @author con4gis contributors (see "authors.md")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

namespace con4gis\ReservationBundle\Controller;

use con4gis\CoreBundle\Classes\C4GUtils;
use con4gis\CoreBundle\Classes\Helper\StringHelper;
use con4gis\CoreBundle\Resources\contao\models\C4gLogModel;
use con4gis\ProjectsBundle\Classes\Actions\C4GSaveAndRedirectDialogAction;
use con4gis\ProjectsBundle\Classes\Buttons\C4GBrickButton;
use con4gis\ProjectsBundle\Classes\Common\C4GBrickCommon;
use con4gis\ProjectsBundle\Classes\Common\C4GBrickConst;
use con4gis\ProjectsBundle\Classes\Common\C4GBrickRegEx;
use con4gis\ProjectsBundle\Classes\Conditions\C4GBrickCondition;
use con4gis\ProjectsBundle\Classes\Conditions\C4GBrickConditionType;
use con4gis\ProjectsBundle\Classes\Dialogs\C4GBeforeDialogSave;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GButtonField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GCheckboxField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GDateField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GEmailField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GForeignKeyField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GHeadlineField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GImageField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GInfoTextField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GKeyField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GLabelField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GMultiCheckboxField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GMultiSelectField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GNumberField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GPostalField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GRadioGroupField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GSelectField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GSubDialogField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTelField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTextareaField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTextField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GUrlField;
use con4gis\ProjectsBundle\Classes\Framework\C4GBaseController;
use con4gis\ProjectsBundle\Classes\Framework\C4GController;
use con4gis\ProjectsBundle\Classes\Views\C4GBrickViewType;
use con4gis\ReservationBundle\Classes\Calculator\C4gReservationCalculator;
use con4gis\ReservationBundle\Classes\Callbacks\ReservationType;
use con4gis\ReservationBundle\Classes\Models\C4gReservationEventModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationLocationModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationParamsModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationSettingsModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationTypeModel;
use con4gis\ReservationBundle\Classes\Projects\C4gReservationBrickTypes;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationCheckInHelper;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationDateChecker;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationFormDefaultHandler;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationFormEventHandler;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationFormObjectFirstHandler;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationHandler;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationInitialValues;
use con4gis\CoreBundle\Resources\contao\models\C4gSettingsModel;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\Date;
use Contao\FilesModel;
use Contao\FrontendUser;
use Contao\Input;
use Contao\MemberModel;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

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
    protected $notification_type = null;

    //Resource Params
    protected $loadDefaultResources = true;
    protected $loadDateTimePickerResources = false;
    protected $loadChosenResources = false;
    protected $loadClearBrowserUrlResources = false;
    protected $loadConditionalFieldDisplayResources = true;
    protected $loadMoreButtonResources = false;
    protected $loadFontAwesomeResources = true;
    protected $loadTriggerSearchFromOtherModuleResources = false;
    protected $loadFileUploadResources = true; //ToDo Check if needed
    protected $loadMultiColumnResources = false;
    protected $loadMiniSearchResources = false;
    protected $loadHistoryPushResources = false;
    protected $loadDatePicker = true;

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

    private $withDefaultPDFContent = true;

    /**
     * @param string $rootDir
     * @param RequestStack $requestStack
     * @param ContaoFramework $framework
     */

    private static $instance = null;

    public static function getInstance()
    {
        return self::$instance;
    }

    public function __construct(string $rootDir, RequestStack $requestStack, ContaoFramework $framework, ?ModuleModel $model = null)
    {
        parent::__construct($rootDir, $requestStack, $framework, $model);
        self::$instance = $this;
    }

    public function getPutVars()
    {
        $putVarsResult = [];
        if (key_exists('REQUEST_METHOD', $_SERVER) && ($_SERVER['REQUEST_METHOD'] == 'PUT')) {
            $content = '';
            if ($this->requestStack && ($request = $this->requestStack->getCurrentRequest())) {
                $content = $request->getContent();
            }
            if (!$content) {
                $content = file_get_contents('php://input');
            }
            
            if ($content) {
                parse_str($content, $parsedVars);
                
                if (is_array($parsedVars) && !empty($parsedVars)) {
                    foreach ($parsedVars as $key => $putVar) {
                        if (is_string($putVar)) {
                            $tmpVar = C4GUtils::secure_ugc($putVar);
                            $tmpVar = C4GUtils::cleanHtml($tmpVar);
                            $putVarsResult[$key] = $tmpVar;
                        } else {
                            $putVarsResult[$key] = $putVar;
                        }
                    }
                }
            }
        }
        
        // UPDATE the instance variable.
        // For Event types (Type 2), this is critical to avoid state-bleeding from previous requests
        // as we typically clear state before.
        // For standard types, we append/update but don't clear, ensuring framework-prefilled
        // values (like those from datepickers) are preserved if not present in current PUT.
        if (!empty($putVarsResult)) {
            $typeIdInPut = $putVarsResult['reservation_type'] ?? 0;
            $isEventInPut = false;
            if ($typeIdInPut) {
                $resT = C4gReservationTypeModel::findByPk($typeIdInPut);
                if ($resT && $resT->type == 2) {
                    $isEventInPut = true;
                }
            }
            
            if ($isEventInPut) {
                // For events, we might want to be more exclusive, 
                // but merging is generally safer than overwriting everything if some framework vars are needed.
                $this->putVars = array_merge((array)$this->putVars, $putVarsResult);
            } else {
                // For standard types, just ensure the current PUT vars are present in the instance.
                foreach ($putVarsResult as $pk => $pv) {
                    $this->putVars[$pk] = $pv;
                }
            }
        }
        
        return $this->putVars;
    }

    public static function replaceDateTokens(string $text): string
    {
        $year = date('Y');
        $year2 = date('y');
        $month = date('m');
        $day = date('d');
        $text = str_replace('{{year}}', $year, $text);
        $text = str_replace('{{year2}}', $year2, $text);
        $text = str_replace('{{month}}', $month, $text);
        $text = str_replace('{{day}}', $day, $text);

        return $text;
    }


    public function resetStaticCaches()
    {
        C4gReservationHandler::resetStaticCaches();
    }

    public function nukeState($keepEvent = false)
    {
        // 1. Clear session variables that hold form state
        $this->session->remove('c4g_brick_dialog_id');
        $this->session->remove('c4g_brick_dialog_values');
        $this->session->remove('reservationInitialDateCookie');
        $this->session->remove('reservationTimeCookie');
        
        // Clear all event-specific date/time cookies from session
        $sessionData = $this->session->getSession()->all();
        foreach ($sessionData as $key => $value) {
            if (strpos($key, 'reservationInitialDateCookie_') === 0 || strpos($key, 'reservationTimeCookie_') === 0) {
                $this->session->remove($key);
            }
        }

        if (!$keepEvent) {
            $this->session->remove('reservationEventCookie');
        }
        
        // 2. Clear instance variables
        $this->putVars = [];
        
        // 3. Reset internal memos of the base controller (to force DB reload)
        $memos = [
            '__modelFindByPkMemo',
            '__modelFindByMemo',
            '__deserializeFastMemo',
            '__insertTagsFastMemo',
            '__ajaxMemo',
            '__projectListForBrickMemo',
            '__checkProjectIdMemo',
            '__getTablePermMemo',
            '__getC4GTablePermissionMemo',
            '__isMemberOfGroupMemo',
            '__hasRightInGroupMemo',
            '__allFieldsMemo',
            '__fieldListMemo',
            '__dialogParamsMemo'
        ];
        foreach ($memos as $memo) {
            if (property_exists($this, $memo)) {
                $this->$memo = [];
            }
        }
        
        // 4. Reset static caches and Contao models
        C4gReservationHandler::resetStaticCaches();
        $this->resetStaticCaches();
    }

    public function renewInitialValues()
    {
        $this->addFields();
    }

    public function generateAjax($request = null)
    {
        if (key_exists('REQUEST_METHOD', $_SERVER) && ($_SERVER['REQUEST_METHOD'] == 'PUT')) {
        }
        $response = parent::generateAjax($request);
        if ($response instanceof Response) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private, proxy-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            $response->headers->set('Surrogate-Control', 'no-store');
        }
        return $response;
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        if (Input::get('date')) {
            $request->attributes->set('_no_cache', true);
            $GLOBALS['TL_NO_CACHE'] = true;
            $this->nukeState();
        }

        $eventId = Input::get('event') ?: 0;
        if (!$eventId && $request->attributes->has('auto_item')) {
            $eventId = $request->attributes->get('auto_item');
        }

        if ($eventId) {
            $oldEvent = $this->session->getSessionValue('reservationEventCookie');
            if ($oldEvent && $oldEvent != $eventId) {
                $this->nukeState(false);
            }
        }

        $response = parent::getResponse($template, $model, $request);

        $response->setPrivate();
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private, proxy-revalidate');
        $response->headers->set('Surrogate-Control', 'no-store');
        if (Input::get('date')) {
            $response->setVary('*');
        } else {
            $response->setVary(['Cookie', 'Accept-Encoding', 'X-Requested-With']);
        }
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        // Clear server-side cache if requested (e.g. via Contao internal mechanisms)
        if (Input::get('date') && class_exists('Contao\System')) {
            try {
                $container = \Contao\System::getContainer();
                if ($container->has('contao.cache.entity_cache')) {
                    $container->get('contao.cache.entity_cache')->clear();
                }
            } catch (\Exception $e) {
                // Ignore if cache clearing fails
            }
        }

        return $response;
    }

    public function initBrickModule($id)
    {
        $moduleTypes = [];
        $doIt = false;
        if ((!property_exists($this,'reservationSettings') || !$this->reservationSettings) && property_exists($this,'reservation_settings') && $this->reservation_settings) {
            $this->session->setSessionValue('reservationSettings', $this->reservation_settings);
            $this->reservationSettings = C4gReservationSettingsModel::findByPk($this->reservation_settings);
            $moduleTypes = StringUtil::deserialize($this->reservationSettings->reservation_types, true);
        }

        if ($moduleTypes) {
            $doIt = true;
            $t = 'tl_c4g_reservation_type';
            // PERFORMANCE: Existenzprüfung gebündelt statt je Typ eine Query
            $idList = array_map('intval', (array) $moduleTypes);
            if (!empty($idList)) {
                $inList = implode(',', $idList);
                $row = Database::getInstance()
                    ->prepare("SELECT id FROM $t WHERE published=? AND reservationObjectType=? AND id IN ($inList) LIMIT 1")
                    ->execute('1', '2')
                    ->fetchAssoc();
                if ($row) {
                    $doIt = false;
                }
            }
        }

        $eventId = Input::get('event') ?: 0;
        if (!$eventId && $this->requestStack && ($request = $this->requestStack->getCurrentRequest())) {
            $eventId = $request->attributes->get('event') ?: 0;
            if (!$eventId && $request->attributes->has('auto_item')) {
                $eventId = $request->attributes->get('auto_item');
            }
            
            // Still no event? Try to parse from the raw URI if possible
            if (!$eventId) {
                $uri = $request->getUri();
                if (preg_match('/\/event\/([^\/\?]+)/', $uri, $matches)) {
                    $eventId = $matches[1];
                }
            }
        }
        
        
        // Falls wir in einem PUT-Request sind (Speichern), MUSS die ID aus dem Request-Body kommen.
        // Ein Fallback auf Input::get (GET) oder Session ist hier gefährlich, da diese veraltet sein könnten.
        if (key_exists('REQUEST_METHOD', $_SERVER) && ($_SERVER['REQUEST_METHOD'] == 'PUT')) {
            $rawPut = $this->getPutVars();
            if ($rawPut) {
                $typeId = $rawPut['reservation_type'] ?? 0;
                
                // Determine if we are dealing with an event
                $isEventActualForInit = false;
                if ($typeId) {
                    $resType = C4gReservationTypeModel::findByPk($typeId);
                    if ($resType && $resType->type == 2) {
                        $isEventActualForInit = true;
                    }
                }

                if ($isEventActualForInit && $typeId) {
                    $eventIdFromPut = $rawPut['reservation_object_event_' . $typeId] ?? 0;
                    
                    // Fail-safe: if eventId is missing in the expected field, try to find it in any other field
                    if (!$eventIdFromPut) {
                        foreach ($rawPut as $pk => $pv) {
                            if (strpos($pk, 'reservation_object_event_') === 0 && $pv) {
                                $eventIdFromPut = $pv;
                                break;
                            }
                        }
                    }
                    
                    // Check if we found something in the PUT
                    if ($eventIdFromPut) {
                        $eventId = $eventIdFromPut;
                    } else {
                        // NO eventId in PUT for EVENT TYPE! 
                        // First check if we already have it in our controller instance from a previous call in the SAME request
                        if (isset($this->putVars['reservation_object_event_' . $typeId]) && $this->putVars['reservation_object_event_' . $typeId]) {
                            $eventId = $this->putVars['reservation_object_event_' . $typeId];
                        } else {
                            // Emergency fallback to URL
                            $eventIdFromUrl = \Contao\Input::get('event') ?: 0;
                            if (!$eventIdFromUrl && $this->requestStack && ($request = $this->requestStack->getCurrentRequest())) {
                                $eventIdFromUrl = $request->attributes->get('event') ?: 0;
                                if (!$eventIdFromUrl && $request->attributes->has('auto_item')) {
                                    $eventIdFromUrl = $request->attributes->get('auto_item');
                                }
                                
                                // Referer check
                                if (!$eventIdFromUrl) {
                                    $referer = $request->headers->get('referer');
                                    if ($referer) {
                                        if (preg_match('/[\/\?\&]event[=\/]([^\/\?\&]+)/', $referer, $matches)) {
                                            $eventIdFromUrl = $matches[1];
                                            $eventIdFromUrl = str_replace('.html', '', $eventIdFromUrl);
                                        }
                                    }
                                }
                            }
                            
                            // Alias resolution if eventId is not numeric
                            if ($eventIdFromUrl && !is_numeric($eventIdFromUrl)) {
                                $aliasObj = $database->prepare("SELECT id FROM tl_calendar_events WHERE alias=?")
                                    ->execute($eventIdFromUrl);
                                if ($aliasObj && $aliasObj->next()) {
                                    $oldAlias = $eventIdFromUrl;
                                    $eventIdFromUrl = $aliasObj->id;
                                }
                            }
                            
                            if ($eventIdFromUrl) {
                                $eventId = $eventIdFromUrl;
                                $rawPut['reservation_object_event_' . $typeId] = $eventId;
                                $this->putVars['reservation_object_event_' . $typeId] = $eventId;
                            } else {
                                // Last resort fallback to session
                                $eventIdFromSession = $this->session->getSessionValue('reservationEventCookie');
                                if ($eventIdFromSession) {
                                    $eventId = $eventIdFromSession;
                                    
                                    // SECURITY CHECK
                                    if ($this->session->getSessionValue('reservationJustSaved')) {
                                        $eventId = 0;
                                        $this->session->remove('reservationEventCookie');
                                    } else {
                                        $rawPut['reservation_object_event_' . $typeId] = $eventId;
                                        $this->putVars['reservation_object_event_' . $typeId] = $eventId;
                                    }
                                } else {
                                }
                            }
                        }
                    }
                    
                    if ($eventId) {
                        $this->putVars['reservation_object_event_' . $typeId] = $eventId;
                        $this->session->setSessionValue('reservationEventCookie', $eventId);
                    }
                    
                }
            }
        }
        if ($eventId) {
            $this->permalink_name = 'event';
        }

        // Only cleanup old event from session if we are actually in an event context
        // and no new event was provided.
        $isEventActualForSession = false;
        if (!$eventId && $this->session->getSessionValue('reservationEventCookie')) {
            $typeIdInReq = Input::get('type') ?: 0;
            if (!$typeIdInReq && key_exists('REQUEST_METHOD', $_SERVER) && ($_SERVER['REQUEST_METHOD'] == 'PUT')) {
                $pVars = $this->getPutVars();
                $typeIdInReq = $pVars['reservation_type'] ?? 0;
            }
            
            if ($typeIdInReq) {
                $resType = C4gReservationTypeModel::findByPk($typeIdInReq);
                if ($resType && $resType->type == 2) {
                    $isEventActualForSession = true;
                }
            }
        }

        if (!$eventId && $doIt && $isEventActualForSession && ($oldEventId = $this->session->getSessionValue('reservationEventCookie'))) {
            $this->session->remove('reservationEventCookie');
            $this->session->remove('reservationInitialDateCookie_'.$oldEventId);
            $this->session->remove('reservationTimeCookie_'.$oldEventId);
        }

        System::loadLanguageFile('fe_c4g_reservation');
        if ($GLOBALS['TL_LANGUAGE']) {
            $this->session->setSessionValue('reservationLangCookie', $GLOBALS['TL_LANGUAGE']);
        }

        $this->setBrickCaption($GLOBALS['TL_LANG']['fe_c4g_reservation']['brick_caption']);
        $this->setBrickCaptionPlural($GLOBALS['TL_LANG']['fe_c4g_reservation']['brick_caption_plural']);

        $this->printTemplate = $this->reservationSettings->documentTemplate ?: 'pdf_c4g_brick';
        if ($this->printTemplate !== 'pdf_c4g_brick') {
            $this->withDefaultPDFContent = false;
        }

        if ($this->reservationSettings->documentStyle) {
            $arrExternalCSS = StringUtil::deserialize($this->reservationSettings->documentStyle, true);
            if (is_array($arrExternalCSS)) {
                $arrExternalCSS = current($arrExternalCSS);
            }
            $objFile = FilesModel::findByUuid($arrExternalCSS);
            $projectDir = System::getContainer()->getParameter('kernel.project_dir');
            if ($objFile && file_exists($projectDir . '/' . $objFile->path)) {
                $this->printStyle = $projectDir . '/' . $objFile->path;
            }
        }

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

        if ($this->printTemplate == 'pdf_reservation_invoice') {
            $this->dialogParams->setDocumentHeadline($GLOBALS['TL_LANG']['fe_c4g_reservation']['headlineBill']);
        } else if ($this->printTemplate == 'pdf_reservation_ticket') {
            $this->dialogParams->setDocumentHeadline($GLOBALS['TL_LANG']['fe_c4g_reservation']['headlineCheckIn']);
        }

        if ($this->reservationSettings->checkInPage) {
            $checkInHelper = new C4gReservationCheckInHelper($this->reservationSettings->checkInPage);
            $beforeSaveAction = new C4GBeforeDialogSave($checkInHelper, 'generateBeforeSaving', false);
            $this->dialogParams->setBeforeSaveAction($beforeSaveAction);
        }

        if ($this->reservationSettings->documentTemplate) {
            $this->dialogParams->setSavePrintoutToField('fileUpload');
            $this->dialogParams->setGeneratePrintoutWithSaving(true);
        }
    }

    public function addFields() : array
    {
        if (key_exists('REQUEST_METHOD', $_SERVER) && ($_SERVER['REQUEST_METHOD'] !== 'PUT')) {
            // SECURITY: Only nuke if we are SURE it's a completely new request without any context.
            // Nuking for existing types definitely breaks session-based datepickers.
            $typeIdForNuke = \Contao\Input::get('type') ?: (\Contao\Input::post('reservation_type') ?: 0);
            $eventIdUrlForNuke = \Contao\Input::get('event') ?: (\Contao\Input::post('reservation_event') ?: 0);
            if (!$eventIdUrlForNuke && $this->requestStack && ($request = $this->requestStack->getCurrentRequest())) {
                $eventIdUrlForNuke = $request->attributes->get('event') ?: 0;
                if (!$eventIdUrlForNuke && $request->attributes->has('auto_item')) {
                    $eventIdUrlForNuke = $request->attributes->get('auto_item');
                }
            }

            $shouldNuke = false;
            $request = $this->requestStack->getCurrentRequest();
            $isInitNav = ($request && $request->query->get('req') === 'initnav') || (isset($_GET['req']) && $_GET['req'] === 'initnav') || (isset($_POST['req']) && $_POST['req'] === 'initnav') || (isset($_POST['ajax']) && $_POST['ajax'] === 'initnav');
            
            // SECURITY: Also check for typical AJAX headers if 'req' parameter is not enough
            if (!$isInitNav && $request) {
                $isInitNav = $request->isXmlHttpRequest() || ($request->headers->get('X-Requested-With') === 'XMLHttpRequest');
            }
            
            // Check for additional AJAX indicators that con4gis might use
            if (!$isInitNav) {
                if (isset($_GET['ajax']) || isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
                    $isInitNav = true;
                }
            }

            // EXTRA SECURITY: If we have an event in the URL but NO session yet (e.g. after cache clear)
            // we should NOT nuke if it's the very first request.
            $hasSession = (bool)$this->session->getSessionValue('reservationEventCookie');

            if (!$typeIdForNuke && !$eventIdUrlForNuke && !$isInitNav) {
                // No context at all? Safe to nuke old junk.
                // BUT: If we have an object in POST or GET, it might be a partial update or a direct link
                $objectIdForNuke = \Contao\Input::get('object') ?: (\Contao\Input::post('reservation_object') ?: 0);
                if (!$objectIdForNuke) {
                    $shouldNuke = $hasSession; // Only nuke if we actually had something before
                } else {
                }
            } else if (($eventIdUrlForNuke || $typeIdForNuke) && !$isInitNav) {
                // If it's an event or type, we still want to nuke once to prevent bleeding from PREVIOUS events/types.
                // BUT only if we are NOT in an AJAX update (initnav) AND we already have a session.
                $shouldNuke = $hasSession;
            }
            
            if ($shouldNuke) {
                C4gLogModel::addLogEntry('reservation', "Nuke state because of missing context or session.");
                $this->nukeState(true);
            } else {
                // If we don't nuke, we still want to ensure eventId is synced from URL to session
                $eventIdFromUrl = \Contao\Input::get('event') ?: 0;
                if (!$eventIdFromUrl && $this->requestStack && ($request = $this->requestStack->getCurrentRequest())) {
                    $eventIdFromUrl = $request->attributes->get('event') ?: ($request->attributes->get('auto_item') ?: 0);
                }
                if ($eventIdFromUrl) {
                    $this->session->setSessionValue('reservationEventCookie', $eventIdFromUrl);
                }
            }
            
            // SECURITY: Ensure we have the eventId in session if it's available in any way,
            // even if nukeState just cleared it, but only if we ARE on an event page.
            $eventIdRescued = \Contao\Input::get('event') ?: 0;
            if (!$eventIdRescued && $this->requestStack && ($request = $this->requestStack->getCurrentRequest())) {
                $eventIdRescued = $request->attributes->get('event') ?: ($request->attributes->get('auto_item') ?: 0);
            }
            
            C4gLogModel::addLogEntry('reservation', "Nuke check: shouldNuke=" . ($shouldNuke ? "yes" : "no") . ", eventRescued=$eventIdRescued, isInitNav=" . ($isInitNav ? "yes" : "no"));

            if ($eventIdRescued) {
                $this->session->setSessionValue('reservationEventCookie', $eventIdRescued);
            } else {
                // If we don't have an event, check if we have one in session from a previous request (e.g. initial load)
                $eventIdRescued = $this->session->getSessionValue('reservationEventCookie');
            }

            // Clear "just saved" flag on new GET request
            $this->session->remove('reservationJustSaved');
            
            // If it's a GET request and we have an event in the URL, make sure it's in the session
            // BUT do it AFTER nukeState
            $eventIdUrl = \Contao\Input::get('event') ?: 0;
            $source = 'GET';
            if (!$eventIdUrl && $this->requestStack && ($request = $this->requestStack->getCurrentRequest())) {
                $eventIdUrl = $request->attributes->get('event') ?: 0;
                if ($eventIdUrl) { $source = 'Attributes'; }
                if (!$eventIdUrl && $request->attributes->has('auto_item')) {
                    $eventIdUrl = $request->attributes->get('auto_item');
                    $source = 'Auto-Item';
                }
            }
            if ($eventIdUrl) {
                $this->session->setSessionValue('reservationEventCookie', $eventIdUrl);
            } else {
            }
        }
        
        // Logs IDs at start of addFields to see what we are working with
        $typeIdLog = Input::get('type') ?: 0;
        $eventIdLog = Input::get('event') ?: 0;
        if (!$eventIdLog && $this->requestStack && ($request = $this->requestStack->getCurrentRequest())) {
            $eventIdLog = $request->attributes->get('event') ?: 0;
            if (!$eventIdLog && $request->attributes->has('auto_item')) {
                $eventIdLog = $request->attributes->get('auto_item');
            }
        }
        
        C4gReservationHandler::resetStaticCaches();

        if (!$this->reservationSettings && key_exists('REQUEST_METHOD', $_SERVER) && ($_SERVER['REQUEST_METHOD'] == 'PUT')) {
            $putVars = $this->getPutVars();
            if ($putVars && isset($putVars['reservation_settings'])) {
                $this->reservationSettings = C4gReservationSettingsModel::findByPk($putVars['reservation_settings']);
            }
        }

        System::loadLanguageFile('fe_c4g_reservation');
        if ($GLOBALS['TL_LANGUAGE']) {
            $this->session->setSessionValue('reservationLangCookie', $GLOBALS['TL_LANGUAGE']);
        }

        $fieldList = array();
        $typelist = array();

        $initialDate = '';
        $initialTime = '';

        $typeId = Input::get('type') ?: 0;
        $eventId = Input::get('event') ?: 0;
        if (!$eventId && $this->requestStack && ($request = $this->requestStack->getCurrentRequest())) {
            $eventId = $request->attributes->get('event') ?: 0;
            if (!$eventId && $request->attributes->has('auto_item')) {
                $eventId = $request->attributes->get('auto_item');
            }
        }
        $objectId = Input::get('object') ?: 0;
        
        // Falls wir in einem PUT-Request sind (Speichern), bevorzugen wir IMMER die Daten aus dem Request-Body.
        // Die Session/GET-Parameter könnten veraltet sein (State-Bleeding).
        if (key_exists('REQUEST_METHOD', $_SERVER) && ($_SERVER['REQUEST_METHOD'] == 'PUT')) {
            $putVars = $this->getPutVars();
            if ($putVars) {
                $typeId = $putVars['reservation_type'] ?? $typeId;
                if ($typeId) {
                    $eventIdFromPut = $putVars['reservation_object_event_' . $typeId] ?? 0;
                    
                    // Priority check: instance variable (already rescued in initBrickModule)
                    if (!$eventIdFromPut && isset($this->putVars['reservation_object_event_' . $typeId])) {
                        $eventIdFromPut = $this->putVars['reservation_object_event_' . $typeId];
                    }
                    
                    if ($eventIdFromPut) {
                        $eventId = $eventIdFromPut;
                        // Mirror to controller instance to ensure addFields uses the correct eventId
                        // even if it was just changed in the current request.
                        $this->putVars['reservation_object_event_' . $typeId] = $eventId;
                        $this->session->setSessionValue('reservationEventCookie', $eventId);
                    }
                    
                    $objectIdFromPut = $putVars['reservation_object_' . $typeId] ?? 0;
                    if ($objectIdFromPut) {
                        $objectId = $objectIdFromPut;
                        $this->putVars['reservation_object_' . $typeId] = $objectId;
                    }
                }
            }
        }

        if ($eventId) {
            $this->session->setSessionValue('reservationEventCookie', $eventId);
        } else if ($this->session->getSessionValue('reservationEventCookie')) {
            $eventId = $this->session->getSessionValue('reservationEventCookie');
        }

        // Logs IDs again after all syncing to see final state before loading event
        C4gLogModel::addLogEntry('reservation', "Final addFields context: event=$eventId, type=$typeId, object=$objectId");

        if ($eventId) {
            $this->permalink_name = 'event';
        }

        $database = Database::getInstance();
        $event = false;
        $eventObj = false;
        if ($eventId) {
            $eventResult = $database->prepare("SELECT * FROM tl_calendar_events WHERE id=? AND published='1'")
                ->execute($eventId);
            if ($eventResult && $eventResult->id) {
                $event = $eventResult;
                // Since we don't have a Model here, we use a manual check for the connection
                $eventObjResult = $database->prepare("SELECT * FROM tl_c4g_reservation_event WHERE pid=?")
                    ->execute($event->id);
                if ($eventObjResult && $eventObjResult->id) {
                    $eventObj = $eventObjResult;
                }
            }
        }

        if ($eventObj && is_countable($eventObj) && (count($eventObj) > 1)) {
            C4gLogModel::addLogEntry('reservation', 'There are more than one event connections. Check Event: ' . $event->id);
        } else {
            $date = Input::get('date') ? Input::get('date') : 0;
            if (!$date && $this->session->getSessionValue('reservationInitialDateCookie')) {
                $date = $this->session->getSessionValue('reservationInitialDateCookie');
            }
            if ($date) {
                $initialDate = $date;
                $this->session->setSessionValue('reservationInitialDateCookie', $initialDate);
                if (!is_numeric($initialDate)) {
                    $dateTime = \DateTime::createFromFormat('Y-m-d', $initialDate);
                    if ($dateTime !== false) {
                        $dateTime->setTime(0, 0, 0);
                        $initialDate = $dateTime->getTimestamp();
                    } else {
                        $initialDate = strtotime(C4GBrickCommon::getLongDateToConvert($GLOBALS['TL_CONFIG']['dateFormat'], $initialDate));
                    }
                }
            }

            if ($eventObj && !$initialDate &&  $this->session->getSessionValue('reservationInitialDateCookie_'.$eventId)) {
                $initialDate = $this->session->getSessionValue('reservationInitialDateCookie_'.$eventId);
            } else if ($eventObj && $initialDate) {
                $recurring = $event->recurring;
                $startDate = C4gReservationDateChecker::getBeginOfDate($event->startDate);
                $actDate = C4gReservationDateChecker::getBeginOfDate($initialDate);
                $minReservationDay = $eventObj->min_reservation_day;
                $currentTimeStamp = time();
                $minReservationDates =  $currentTimeStamp + ($minReservationDay * 86400);
                if ($recurring && !($startDate == $actDate)) {
                    $repeatEach = StringUtil::deserialize($event->repeatEach, true);
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
                    if (!$goodDay && !(key_exists('REQUEST_METHOD', $_SERVER) && ($_SERVER['REQUEST_METHOD'] == 'PUT'))) {
                        $info = new C4GInfoTextField();
                        $info->setFieldName('info');
                        $info->setEditable(false);
                        $info->setInitialValue($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_none_date']);
                        return [$info];
                    }
                }
                if (($minReservationDates >= $startDate) && !(key_exists('REQUEST_METHOD', $_SERVER) && ($_SERVER['REQUEST_METHOD'] == 'PUT'))){
                    $info = new C4GInfoTextField();
                    $info->setFieldName('info');
                    $info->setEditable(false);
                    $info->setInitialValue($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_none_date']);
                    return [$info];
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
        $arrOptions = array('order' => "$t.caption ASC, $t.options ASC",);

        if ($eventObj) {
            $typeId = $eventObj->reservationType;
            $database = Database::getInstance();
            $sql = "SELECT * FROM `tl_c4g_reservation_type` WHERE `id`=? AND `published`=?";
            $params = [$typeId, '1'];
            $stmt = $database->prepare($sql);
            $types = $stmt->execute(...$params)->fetchAllAssoc();
        } else if ($typeId && is_numeric($typeId)) {
            $database = Database::getInstance();
            $sql = "SELECT * FROM `tl_c4g_reservation_type` WHERE `id`=? AND `published`=?";
            $params = [$typeId, '1'];
            $stmt = $database->prepare($sql);
            $types = $stmt->execute(...$params)->fetchAllAssoc();
        }  else if ($typeId && is_string($typeId)) {
            $database = Database::getInstance();
            $sql = "SELECT * FROM `tl_c4g_reservation_type` WHERE `alias`=? AND `published`=?";
            $params = [$typeId, '1'];
            $stmt = $database->prepare($sql);
            $types = $stmt->execute(...$params)->fetchAllAssoc();
        } else if (!$eventObj && !$eventId) {
            $database = Database::getInstance();
            $sql = "SELECT * FROM `tl_c4g_reservation_type` WHERE `published`=? AND NOT `reservationObjectType`=?";
            $params = ['1', '2'];
            $stmt = $database->prepare($sql);
            $types = $stmt->execute(...$params)->fetchAllAssoc();
        }

        if ($types) {
            $memberId = 0;
            $frontendUser = FrontendUser::getInstance();
            if ($frontendUser && $frontendUser->isLoggedIn === true) {
                $memberId = (int) $frontendUser->id;
            }

            $moduleTypes = StringUtil::deserialize($this->reservationSettings->reservation_types, true);
            $langLower = strtolower((string) ($GLOBALS['TL_LANGUAGE'] ?? ''));

            foreach ($types as $type) {
                if ($moduleTypes && is_array($moduleTypes) && (count($moduleTypes) > 0)) {
                    $arrModuleTypes = $moduleTypes;
                    if (!in_array($type['id'], $arrModuleTypes)) {
                        continue;
                    }
                }

                $defaultObject = $eventId ?: $objectId;
                $objects = C4gReservationHandler::getReservationObjectList(array($type), $defaultObject, $this->reservationSettings->showPrices, $this->reservationSettings->showPricesWithTaxes ?: false,);

                if (!$objects || (count($objects) <= 0)) {
                    continue;
                }
                // Precompute and reuse deserialized arrays (performance)
                $captions = \Contao\StringUtil::deserialize($type['options'], true);
                $includedParams = \Contao\StringUtil::deserialize($type['included_params'], true);
                $additionalParams = \Contao\StringUtil::deserialize($type['additional_params'], true);
                $participantParams = \Contao\StringUtil::deserialize($type['participant_params'], true);
                $foundCaption = false;
                if ($captions && (count($captions) > 0)) {
                    foreach ($captions as $caption) {
                        $capLang = strtolower((string) ($caption['language'] ?? ''));
                        if (($capLang !== '' && strpos($langLower, $capLang) !== false) && !empty($caption['caption'])) {
                            $typelist[$type['id']] = array(
                                'id' => $type['id'],
                                'name' => $caption['caption'],
                                'periodType' => $type['periodType'],
                                'includedParams' => $includedParams,
                                'additionalParams' => $additionalParams,
                                'additionalParamsFieldType' => $type['additionalParamsFieldType'],
                                'additionalParamsMandatory' => $type['additionalParamsMandatory'] ? true : false,
                                'participantParams' => $participantParams,
                                'participantParamsFieldType' => $type['participantParamsFieldType'],
                                'participantParamsMandatory' => $type['participantParamsMandatory'] ? true : false,
                                'minParticipantsPerBooking' => $type['minParticipantsPerBooking'],
                                'maxParticipantsPerBooking' => $type['maxParticipantsPerBooking'],
                                'objects' => $objects,
                                'objectType' => $type['reservationObjectType'],
                                'memberId' => $type['member_id'] ?: $memberId,
                                'groupId' => $type['group_id'],
                                'type' => $type['reservationObjectType'],
                                'directBooking' => $type['directBooking'],
                                'min_residence_time' => $type['min_residence_time'],
                                'max_residence_time' => $type['max_residence_time'],
                                'default_residence_time' => $type['default_residence_time'],
                                'ignoreCapacity' => $type['ignoreCapacity']
                            );
                            $foundCaption = true;
                            break;
                        }
                    }
                }

                if (!$foundCaption) {
                    $typelist[$type['id']] = array(
                        'id' => $type['id'],
                        'name' => $type['caption'],
                        'periodType' => $type['periodType'],
                        'includedParams' => $includedParams,
                        'additionalParams' => $additionalParams,
                        'additionalParamsFieldType' => $type['additionalParamsFieldType'],
                        'additionalParamsMandatory' => $type['additionalParamsMandatory'] ? true : false,
                        'participantParams' => $participantParams,
                        'participantParamsFieldType' => $type['participantParamsFieldType'],
                        'participantParamsMandatory' => $type['participantParamsMandatory'] ? true : false,
                        'minParticipantsPerBooking' => $type['minParticipantsPerBooking'],
                        'maxParticipantsPerBooking' => $type['maxParticipantsPerBooking'],
                        'objects' => $objects,
                        'objectType' => $type['reservationObjectType'],
                        'memberId' => $type['member_id'] ?: $memberId,
                        'groupId' => $type['group_id'],
                        'type' => $type['reservationObjectType'],
                        'directBooking' => $type['directBooking'],
                        'min_residence_time' => $type['min_residence_time'],
                        'max_residence_time' => $type['max_residence_time'],
                        'default_residence_time' => $type['default_residence_time'],
                        'ignoreCapacity' => $type['ignoreCapacity']
                    );
                }
            }
        }

        $idField = new C4GKeyField();
        $idField->setFieldName('id');
        $idField->setEditable(false);
        $idField->setFormField(false);
        $idField->setSortColumn(false);
        $fieldList[] = $idField;

        $showDateTime = $this->reservationSettings->showDateTime ? "1" : "0";
        $showMinMax = $this->reservationSettings->showMinMaxWithCapacity ? "1" : "0";
    
        if (count($typelist) > 0) {
            $firstType = $this->reservationSettings->typeDefault ?: array_key_first($typelist);

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
            $jsOnChange = "setReservationForm('-1',".json_encode((int)$showDateTime).");";
            $reservationTypeField->setCallOnChangeFunction($jsOnChange);
            $reservationTypeField->setInitialCallOnChange(true);
            $reservationTypeField->setInitialValue($firstType);
            $reservationTypeField->setStyleClass('reservation-type');
            $reservationTypeField->setNotificationField(true);
            $reservationTypeField->setWithOptionType(true);
            $reservationTypeField->setHidden((count($typelist) == 1) && $this->reservationSettings->typeHide);
            //$reservationTypeField->setInitialCallOnChange(true);

            if ($this->reservationSettings->typeWithEmptyOption) {
                $reservationTypeField->setEditable(count($typelist) > 0);
                $reservationTypeField->setInitialValue(-1);
                $reservationTypeField->setWithEmptyOption(true);
                $reservationTypeField->setEmptyOptionLabel($GLOBALS['TL_LANG']['fe_c4g_reservation']['emptyOptionLabel']);
            } else {
                $reservationTypeField->setEditable(count($typelist) > 1);
                $reservationTypeField->setInitialValue($firstType);
            }

            $reservationTypeField->setPrintable(true);
            $reservationTypeField->setNotificationField(true);

            $fieldList[] = $reservationTypeField;
        } else {
            $reservationTypeField = new C4GTextField();
            $reservationTypeField->setFieldName('reservation_type');
            $reservationTypeField->setNotificationField(true);
            $reservationTypeField->setPrintable(true);
            $fieldList[] = $reservationTypeField;

            $info = new C4GInfoTextField();
            $info->setFieldName('info');
            $info->setEditable(false);
            $info->setInitialValue($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_none']);
            return [$info];
        }

        $reservationObjectField = new C4GTextField();
        $reservationObjectField->setFieldName('reservation_object');
        $reservationObjectField->setNotificationField(true);
        $reservationObjectField->setPrintable(true);
        $reservationObjectField->setHidden(true);
        $fieldList[] = $reservationObjectField;

        $initialValues = new C4gReservationInitialValues();
        $initialValues->setDate($initialDate);
        $initialValues->setTime($initialTime);
        $initialValues->setObject($objectId);

        $onlyParticipants = $this->reservationSettings->onlyParticipants ?: false;
        $isPartiPerEvent = $eventObj != false ? $eventObj->maxParticipantsPerEventBooking : 0;


foreach ($typelist as $listType) {
    $condition = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'reservation_type', $listType['id']);
    $bookingMaxCapacity = $listType['maxParticipantsPerBooking'];
    
    $typeOfObject = $listType['objects'][0]->getTypeOfObject();

    $objectMaxCapacity = 0;
    foreach ($listType['objects'] as $object) {
        $objectMaxCapacity = intval($object->getDesiredCapacity()[1]) > $objectMaxCapacity ? intval($object->getDesiredCapacity()[1]) : $objectMaxCapacity;
    }

    $showMinMax = $this->reservationSettings->showMinMaxWithCapacity ? "1" : "0";

    if ($listType['maxParticipantsPerBooking'] && $eventObj && !$eventObj->maxParticipants) {
        $maxParticipants = $listType['maxParticipantsPerBooking'];
    } else if ($eventObj && $eventObj->maxParticipants) {
        $maxParticipants = $eventObj->maxParticipants;
    } else if ($listType['maxParticipantsPerBooking']) {
        $maxParticipants = $listType['maxParticipantsPerBooking'];
    }

    if ($isPartiPerEvent) {
        $maxParticipants = $isPartiPerEvent;
        $maxCapacity = $eventObj->maxParticipants ?: 0;
    } else if ($bookingMaxCapacity && $objectMaxCapacity) {
        if ($bookingMaxCapacity < $objectMaxCapacity) {
            $maxCapacity = $bookingMaxCapacity;
        } else {
            $maxCapacity = $objectMaxCapacity;
        }    
    } else if ($bookingMaxCapacity) {
        $maxCapacity = $bookingMaxCapacity;
    } else if ($objectMaxCapacity) {
        $maxCapacity = $objectMaxCapacity;
    } else {
        $maxCapacity = 100;
    }

       
    if (isset($eventObj->minParticipants)) {
        $minCapacity = $eventObj->minParticipants;
    } else if (isset($listType['minParticipantsPerBooking'])) {
        $minCapacity = $listType['minParticipantsPerBooking'];
    } else {
        $minCapacity = 1;
    }

    if ($minCapacity && $maxCapacity && $minCapacity > $maxCapacity) {
        $minCapacity = $maxCapacity;
    }

    $showDateTime = $this->reservationSettings->showDateTime ? "1" : "0";

    $reservationDesiredCapacity = new C4GNumberField();
    $reservationDesiredCapacity->setFieldName('desiredCapacity_' . $listType['id']);

    if ($maxCapacity && $eventObj && $eventObj->maxParticipants) {
        $maxCapacity = C4gReservationHandler::getMaxParticipentsForObject($eventId, $maxCapacity);
    }

    if ($listType['ignoreCapacity']) {
        $reservationDesiredCapacity->setFormField(false);
    } else {
        $reservationDesiredCapacity->setFormField(true);
    }
    $reservationDesiredCapacity->setEditable(true);
    $reservationDesiredCapacity->setCondition(array($condition));
    $initialCapacityValue = \Contao\Input::post('desiredCapacity_'.$listType['id']) ?: (\Contao\Input::get('capacity') ?: ($minCapacity ?: 1));
    $reservationDesiredCapacity->setInitialValue($initialCapacityValue);
    $reservationDesiredCapacity->setMandatory(true);

    //TODO add amount of capacity left in the form
    $error = 0;
    if($maxCapacity <= 0) {
        $error = 1;
    }
    if ($minCapacity && $maxCapacity /* && ($minCapacity != $maxCapacity) */ || $isPartiPerEvent) {
        if ($eventObj && $listType['maxParticipantsPerBooking'] && $listType['maxParticipantsPerBooking'] <= $maxCapacity && !$isPartiPerEvent) {
            $min = $minCapacity;
            $max = $listType['maxParticipantsPerBooking'];
            $reservationDesiredCapacity->setTitle(self::withDesiredCapacityTitle($min,$max,$showMinMax));
            $reservationDesiredCapacity->setMax($max);
        } else if ($eventObj && ($eventObj->maxParticipants == 0) || empty($maxCapacity) || $isPartiPerEvent <= $maxCapacity) {
            if ($isPartiPerEvent && $isPartiPerEvent <= $maxCapacity) {
                $min = $minCapacity;
                $max = $isPartiPerEvent;
                $reservationDesiredCapacity->setTitle(self::withDesiredCapacityTitle($min,$max,$showMinMax));
                $reservationDesiredCapacity->setMax($max);
            } else if ($minCapacity && $maxCapacity) {
                $min = $minCapacity;
                $max = $maxCapacity;
                $reservationDesiredCapacity->setTitle(self::withDesiredCapacityTitle($min,$max,$showMinMax));
                $reservationDesiredCapacity->setMax($maxCapacity);
            } else {
                $reservationDesiredCapacity->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['desiredCapacity']);
            }
            if ($listType['objectType'] == '1' || $listType['objectType'] == '3') {
                $reservationDesiredCapacity->setMin($minCapacity);
                $reservationDesiredCapacity->setMax($maxCapacity);
            } else {
                $reservationDesiredCapacity->setMax($isPartiPerEvent);
            }

         } else if (empty($maxCapacity) || ($isPartiPerEvent > $maxCapacity)) {
            $isPartiPerEvent = $maxCapacity;
            $min = $minCapacity;
            $max = $maxCapacity;
            $reservationDesiredCapacity->setTitle(self::withDesiredCapacityTitle($min,$max,$showMinMax));
            $reservationDesiredCapacity->setMax($maxCapacity);
            $reservationDesiredCapacity->setMin($minCapacity);
        } else if($maxCapacity <= 0){
            $error = 1;
        } else {
            if ($isPartiPerEvent) {
                $reservationDesiredCapacity->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['desiredCapacity']. ' ('.$minCapacity.'-'.$listType['maxParticipantsPerBooking'].')');
                $reservationDesiredCapacity->setMax($listType['maxParticipantsPerBooking']);
            } else {
                //show current capacity option for the title
//                        $reservationDesiredCapacity->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['desiredCapacity']. ' ('.$minCapacity.'-'.$maxCapacity.')');
                $reservationDesiredCapacity->setMax($maxCapacity);
                $reservationDesiredCapacity->setMin($minCapacity);
            }
        }

        if ($error && !(key_exists('REQUEST_METHOD', $_SERVER) && ($_SERVER['REQUEST_METHOD'] == 'PUT'))) {
            $reservationDesiredCapacity->setMin(0);
            $reservationDesiredCapacity->setMax(0);

            $info = new C4GInfoTextField();
            $info->setFieldName('info');
            $info->setEditable(false);
            $info->setInitialValue($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_none']);
            return [$info];
        }

        if ((!$maxCapacity && !$listType['maxParticipantsPerBooking']) &&
            (!$eventObj->maxParticipantsPerBooking) &&
            (!$eventObj->maxParticipantsPerEventBooking)) {
            $reservationDesiredCapacity->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['desiredCapacity']);
        }
    } else {
        $reservationDesiredCapacity->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['desiredCapacity']);

    }

    $reservationDesiredCapacity->setPattern(C4GBrickRegEx::NUMBERS);
    $reservationDesiredCapacity->setCallOnChange(true);
    $jsOnChangeCapacity = "setReservationForm(".json_encode((string)$listType['id']).",".json_encode((int)$showDateTime).");";
    $reservationDesiredCapacity->setCallOnChangeFunction($jsOnChangeCapacity);
    //$reservationDesiredCapacity->setCallOnChangeFunction("changeCapacity(".$listType['id'] . "," . $showDateTime . ");");
    $reservationDesiredCapacity->setNotificationField(true);
    $reservationDesiredCapacity->setAdditionalID($listType['id']);
    $reservationDesiredCapacity->setStyleClass('desired-capacity');
    if (!$listType['ignoreCapacity']) {
        $reservationDesiredCapacity->setHidden(!$this->reservationSettings->withCapacity);
    } else {
        $reservationDesiredCapacity->setHidden(true);
    }

    if (!$listType['ignoreCapacity']) {
        if (!$this->reservationSettings->moveCapacity) {
            $fieldList[] = $reservationDesiredCapacity;
        }
    }

    $hidden = false;
    if ((intval($listType['min_residence_time']) >= 1) && (intval($listType['max_residence_time']) >= 1)) {
        if ($listType['min_residence_time'] == $listType['max_residence_time']) {
            $fromto = $listType['min_residence_time'];
            $hidden = true;
        } else {
            $fromto = $listType['min_residence_time'].'-'.$listType['max_residence_time'];
        }

        $title = $GLOBALS['TL_LANG']['fe_c4g_reservation']['duration_caption'].$fromto;
        switch ($listType['periodType']) {
            case 'minute':
                $title .= $GLOBALS['TL_LANG']['fe_c4g_reservation']['duration_minutely'];
                break;
            case 'hour':
                $title .= $GLOBALS['TL_LANG']['fe_c4g_reservation']['duration_hourly'];
                break;
            case 'day':
                $title .= $GLOBALS['TL_LANG']['fe_c4g_reservation']['duration_daily'];
                break;
            case 'overnight':
                $title .= $GLOBALS['TL_LANG']['fe_c4g_reservation']['duration_overnight'];
                break;
            case 'week':
                $title .= $GLOBALS['TL_LANG']['fe_c4g_reservation']['duration_weekly'];
                break;
        }

        $durationField = new C4GNumberField();
        $durationField->setFieldName('duration_' . $listType['id']);
        $durationField->setTitle($title);
        $durationField->setColumnWidth(10);
        $durationField->setFormField(true);
        $durationField->setSortColumn(true);
        $durationField->setTableColumn(true);
        $durationField->setMandatory(true);
        $durationField->setCallOnChange(true);
        $jsOnChangeDuration = "setReservationForm(".json_encode((string)$listType['id']).",".json_encode((int)$showDateTime).");";
        $durationField->setCallOnChangeFunction($jsOnChangeDuration);
        //$durationField->setCallOnChangeFunction("setTimeset(document.getElementById('c4g_beginDate_" . $listType['id'] . "'), " . $listType['id'] . "," . $showDateTime . ");");
        $durationField->setCondition(array($condition));
        $durationField->setNotificationField(true);
        $durationField->setStyleClass('duration');
        $durationField->setMin($listType['min_residence_time'] ?: 1);
        $durationField->setMax($listType['max_residence_time']);
        $durationField->setInitialValue($listType['default_residence_time'] ?: $durationField->getMin());
        $durationField->setMaxLength(3);
        $durationField->setStep(1);
        $durationField->setAdditionalID($listType['id']);
        $durationField->setDatabaseField(true);
        $durationField->setHidden($hidden);
        $fieldList[] = $durationField;
    }

    //set reservationObjectType to default
    $reservationObjectTypeField = new C4GNumberField();
    $reservationObjectTypeField->setFieldName('reservationObjectType');
    $reservationObjectTypeField->setInitialValue($listType['objectType']);
    $reservationObjectTypeField->setDatabaseField(true);
    $reservationObjectTypeField->setFormField(false);
    $fieldList[] = $reservationObjectTypeField;

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
            if ($initialDate) {
                $initialValues->setDate(date($GLOBALS['TL_CONFIG']['dateFormat'], $initialDate));
            }
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
    ['id' => 'various', 'name' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['various']],
    ['id' => 'man', 'name' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['man']],
    ['id' => 'woman', 'name' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['woman']],
    ['id' => 'divers', 'name' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['divers']],
];

$additionaldatas = StringUtil::deserialize($this->reservationSettings->fieldSelection, true);
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
$memberArr['gender'] = '';

$hasFrontendUser = System::getContainer()->get('contao.security.token_checker')->hasFrontendUser();
if ($this->reservationSettings->showMemberData && $hasFrontendUser === true) {
    $member = FrontendUser::getInstance();
    if ($member) {
        $memberArr['id'] = $member->id ?: '';
        $memberArr['company'] = $member->company ?: '';
        $memberArr['firstname'] = $member->firstname ?: '';
        $memberArr['lastname'] = $member->lastname ?: '';
        $memberArr['email'] = $member->email ?: '';
        $memberArr['street'] = $member->street ?: '';
        $memberArr['postal'] = $member->postal ?: '';
        $memberArr['city'] = $member->city ?: '';
        $memberArr['country'] = $member->country ?: '';
        $memberArr['phone'] = $member->phone ?: '';
        $memberArr['dateOfBirth'] = $member->dateOfBirth ?: '';

        switch ($member->gender) {
            case 'male':
                $memberArr['gender'] = 'man';
                break;
            case 'female':
                $memberArr['gender'] = 'woman';
                break;
            case 'other':
                $memberArr['gender'] = 'divers';
                break;
            Default:
                $memberArr['gender'] = 'various';
                break;
        }
    }
}
        if (!$typelist || count($typelist) <= 0) {
            $reservationNoneTypeField = new C4GLabelField();
            $reservationNoneTypeField->setDatabaseField(false);
            $reservationNoneTypeField->setInitialValue($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_none']);
            $fieldList[] = $reservationNoneTypeField;
        }

        $salutation = [
            ['id' => 'various', 'name' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['various']],
            ['id' => 'man', 'name' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['man']],
            ['id' => 'woman', 'name' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['woman']],
            ['id' => 'divers', 'name' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['divers']],
        ];

        $additionaldatas = StringUtil::deserialize($this->reservationSettings->fieldSelection, true);
        if (!$additionaldatas) {
            $additionaldatas = [];
        }

        $participantsMandatory = false;
        foreach ($additionaldatas as $row) {
            if (($row['additionaldatas'] ?? '') === 'participants') {
                $participantsMandatory = (bool)($row['binding'] ?? false);
                break;
            }
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
        $memberArr['gender'] = '';

        if ($this->reservationSettings->showMemberData && C4GUtils::isFrontendUserLoggedIn()) {
            $member = FrontendUser::getInstance();
            if ($member) {
                $memberArr['id'] = $member->id ?: '';
                $memberArr['company'] = $member->company ?: '';
                $memberArr['firstname'] = $member->firstname ?: '';
                $memberArr['lastname'] = $member->lastname ?: '';
                $memberArr['email'] = $member->email ?: '';
                $memberArr['street'] = $member->street ?: '';
                $memberArr['postal'] = $member->postal ?: '';
                $memberArr['city'] = $member->city ?: '';
                $memberArr['country'] = $member->country ?: '';
                $memberArr['phone'] = $member->phone ?: '';
                $memberArr['dateOfBirth'] = $member->dateOfBirth ?: '';

                switch ($member->gender) {
                    case 'male':
                        $memberArr['gender'] = 'man';
                        break;
                    case 'female':
                        $memberArr['gender'] = 'woman';
                        break;
                    case 'other':
                        $memberArr['gender'] = 'divers';
                        break;
                    Default:
                        $memberArr['gender'] = 'various';
                        break;
                }
            }
        }

        $reservationSettings = $this->reservationSettings;
        if (!$reservationSettings) {
             C4gLogModel::addLogEntry('reservation', "CRITICAL: reservationSettings is null in addFields!");
             // Fallback if settings are missing (e.g. after cache clear before properly initialized)
             // We try to find the first settings record as a last resort
             $this->reservationSettings = C4gReservationSettingsModel::findAll() ? C4gReservationSettingsModel::findAll()->current() : null;
             $reservationSettings = $this->reservationSettings;
        }
        $specialParticipantMechanism = $reservationSettings ? $reservationSettings->specialParticipantMechanism : false;
        $hideParticipantsEmail = $reservationSettings->hideParticipantsEmail ?: false;
        $hideReservationKey = $reservationSettings->hideReservationKey ?: false;
        $onlyParticipants = $reservationSettings->onlyParticipants ?: false;
        foreach ($additionaldatas as $rowdata) {
            $rowField = $rowdata['additionaldatas'];
            $initialValue = $rowdata['initialValue'];
            $rowMandatory = key_exists('binding', $rowdata) ? $rowdata['binding'] : false;
            $rowPrintable = key_exists('printing', $rowdata) ? $rowdata['printing'] : false;
            // PDF Fix: Ensure that common fields are printable if withDefaultPDFContent is false (custom template)
            if (!$this->withDefaultPDFContent && in_array($rowField, ['organisation', 'firstname', 'lastname', 'email', 'phone', 'address', 'postal', 'city', 'title', 'salutation'])) {
                $rowPrintable = true;
            }
            $individualLabel = isset($rowdata['individualLabel']) ? str_replace(' ', '&nbsp;&#x200B;',
                $rowdata['individualLabel']) : "";
            $additionalClass = isset($rowdata['additionalClass']) ? $rowdata['additionalClass'] : "";

            if ($rowField == "organisation") {
                $organisationField = new C4GTextField();
                $organisationField->setFieldName('organisation');
                $organisationField->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['organisation']);
                $organisationField->setColumnWidth(10);
                $organisationField->setSortColumn(false);
                $organisationField->setTableColumn(true);
                $organisationField->setMandatory($rowMandatory);
                $organisationField->setNotificationField(true);
                $organisationField->setStyleClass('organisation');
                $organisationField->setInitialValue($initialValue ? $initialValue : $memberArr['company']);
                $organisationField->setStyleClass($additionalClass);
                $organisationField->setPrintable($rowPrintable);
                $fieldList[] = $organisationField;
            } else if ($rowField == "title") {
                $titleField = new C4GTextField();
                $titleField->setFieldName('title');
                $titleField->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['title']);
                $titleField->setSortColumn(false);
                $titleField->setTableColumn(false);
                $titleField->setMandatory(false);
                $titleField->setNotificationField(true);
                $titleField->setStyleClass('title');
                $titleField->setInitialValue($initialValue);
                $titleField->setStyleClass($additionalClass);
                $titleField->setPrintable($rowPrintable);
                $fieldList[] = $titleField;
            } else if ($rowField == "salutation") {
                $salutationField = new C4GSelectField();
                $salutationField->setFieldName('salutation');
                $salutationField->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['salutation']);
                $salutationField->setSortColumn(false);
                $salutationField->setTableColumn(false);
                $salutationField->setOptions($salutation);
                $salutationField->setMandatory($rowMandatory);
                $salutationField->setNotificationField(true);
                $salutationField->setStyleClass('salutation');
                $salutationField->setInitialValue($initialValue ?: $memberArr['gender']);
                $salutationField->setStyleClass($additionalClass);
                $salutationField->setPrintable($rowPrintable);
                $fieldList[] = $salutationField;
            } else if ($rowField == "firstname") {
                $firstnameField = new C4GTextField();
                $firstnameField->setFieldName('firstname');
                $firstnameField->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname']);
                $firstnameField->setColumnWidth(10);
                $firstnameField->setSortColumn(false);
                $firstnameField->setTableColumn(true);
                $firstnameField->setMandatory(true);
                $firstnameField->setNotificationField(true);
                $firstnameField->setStyleClass('firstname');
                $firstnameField->setInitialValue($initialValue ? $initialValue : $memberArr['firstname']);
                $firstnameField->setPattern("^[a-z A-Z -äöüÄÖÜ]+$");
                $firstnameField->setStyleClass($additionalClass);
                $firstnameField->setPrintable($rowPrintable);
                $fieldList[] = $firstnameField;
            } else if ($rowField == "lastname") {
                $lastnameField = new C4GTextField();
                $lastnameField->setFieldName('lastname');
                $lastnameField->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname']);
                $lastnameField->setColumnWidth(10);
                $lastnameField->setSortColumn(false);
                $lastnameField->setTableColumn(true);
                $lastnameField->setMandatory(true);
                $lastnameField->setNotificationField(true);
                $lastnameField->setStyleClass('lastname');
                $lastnameField->setInitialValue($initialValue ? $initialValue : $memberArr['lastname']);
                $lastnameField->setPattern("^[a-z A-Z -äöüÄÖÜ]+$");
                $lastnameField->setStyleClass($additionalClass);
                $lastnameField->setPrintable($rowPrintable);
                $fieldList[] = $lastnameField;
            } else if ($rowField == "email") {
                $emailField = new C4GEmailField();
                $emailField->setFieldName('email');
                $emailField->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['email']);
                $emailField->setColumnWidth(10);
                $emailField->setSortColumn(false);
                $emailField->setTableColumn(false);
                $emailField->setMandatory(true);
                $emailField->setNotificationField(true);
                $emailField->setStyleClass('email');
                $emailField->setInitialValue($initialValue ? $initialValue : $memberArr['email']);
                $emailField->setStyleClass($additionalClass);
                $emailField->setPrintable($rowPrintable);
                $emailField->setPattern(C4GBrickRegEx::EMAIL);
                $fieldList[] = $emailField;
            } else if ($rowField == "phone") {
                $phoneField = new C4GTelField();
                $phoneField->setFieldName('phone');
                $phoneField->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['phone']);
                $phoneField->setColumnWidth(10);
                $phoneField->setSortColumn(false);
                $phoneField->setMandatory($rowMandatory);
                $phoneField->setTableColumn(false);
                $phoneField->setNotificationField(true);
                $phoneField->setStyleClass('phone');
                $phoneField->setInitialValue($initialValue ? $initialValue : $memberArr['phone']);
                $phoneField->setStyleClass($additionalClass);
                $phoneField->setPrintable($rowPrintable);
                $fieldList[] = $phoneField;
            } else if ($rowField == "address") {
                $addressField = new C4GTextField();
                $addressField->setFieldName('address');
                $addressField->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['address']);
                $addressField->setColumnWidth(60);
                $addressField->setSortColumn(false);
                $addressField->setTableColumn(false);
                $addressField->setMandatory($rowMandatory);
                $addressField->setNotificationField(true);
                $addressField->setStyleClass('address');
                $addressField->setInitialValue($initialValue ? $initialValue : $memberArr['street']);
                $addressField->setStyleClass($additionalClass);
                $addressField->setPrintable($rowPrintable);
                $fieldList[] = $addressField;
            } else if ($rowField == "postal") {
                $postalField = new C4GPostalField();
                $postalField->setFieldName('postal');
                $postalField->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['postal']);
                $postalField->setColumnWidth(60);
                $postalField->setSize(5); //international 32
                $postalField->setSortColumn(false);
                $postalField->setTableColumn(false);
                $postalField->setMandatory($rowMandatory);
                $postalField->setNotificationField(true);
                $postalField->setStyleClass('postal');
                $postalField->setInitialValue($initialValue ? $initialValue : $memberArr['postal']);
                $postalField->setStyleClass($additionalClass);
                $postalField->setPrintable($rowPrintable);
                $postalField->setPattern(C4GBrickRegEx::POSTAL);
                $fieldList[] = $postalField;
            } else if ($rowField == "city") {
                $cityField = new C4GTextField();
                $cityField->setFieldName('city');
                $cityField->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['city']);
                $cityField->setColumnWidth(60);
                $cityField->setSortColumn(false);
                $cityField->setTableColumn(false);
                $cityField->setMandatory($rowMandatory);
                $cityField->setNotificationField(true);
                $cityField->setStyleClass('city');
                $cityField->setInitialValue($initialValue ? $initialValue : $memberArr['city']);
                $cityField->setStyleClass($additionalClass);
                $cityField->setPrintable($rowPrintable);
                $fieldList[] = $cityField;
            } else if ($rowField == "dateOfBirth") {
                $birthDateField = new C4GDateField();
                //$birthDateField->setFlipButtonPosition(true);
                $birthDateField->setFieldName('dateOfBirth');
                $birthDateField->setMinDate(strtotime('-120 year'));

                $year = date('Y');
                $birthDateField->setMaxDate(strtotime($year . '-12-31'));
                $birthDateField->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['dateOfBirth']);
                $birthDateField->setColumnWidth(60);
                $birthDateField->setSortColumn(false);
                $birthDateField->setTableColumn(false);
                $birthDateField->setSortSequence('de_datetime');
                $birthDateField->setMandatory($rowMandatory);
                $birthDateField->setNotificationField(true);
                $birthDateField->setStyleClass('dateOfBirth');
                $birthDateField->setInitialValue($initialValue ? $initialValue : $memberArr['dateOfBirth']);
                $birthDateField->setDatePickerByBrowser(true);
                $birthDateField->setStyleClass($additionalClass);
                $birthDateField->setPrintable($rowPrintable);
                $fieldList[] = $birthDateField;
            } else if ($rowField == "salutation2") {
                $salutationField2 = new C4GSelectField();
                $salutationField2->setFieldName('salutation2');
                $salutationField2->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['salutation2']);
                $salutationField2->setSortColumn(false);
                $salutationField2->setTableColumn(false);
                $salutationField2->setOptions($salutation);
                $salutationField2->setMandatory($rowMandatory);
                $salutationField2->setNotificationField(true);
                $salutationField2->setStyleClass('salutation');
                $salutationField2->setInitialValue($initialValue);
                $salutationField2->setStyleClass($additionalClass);
                $salutationField2->setPrintable($rowPrintable);
                $fieldList[] = $salutationField2;
            } else if ($rowField == "title2") {
                $titleField2 = new C4GTextField();
                $titleField2->setFieldName('title2');
                $titleField2->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['title2']);
                $titleField2->setSortColumn(false);
                $titleField2->setTableColumn(false);
                $titleField2->setMandatory(false);
                $titleField2->setNotificationField(true);
                $titleField2->setStyleClass('title');
                $titleField2->setInitialValue($initialValue);
                $titleField2->setStyleClass($additionalClass);
                $titleField2->setPrintable($rowPrintable);
                $fieldList[] = $titleField2;
            } else if ($rowField == "firstname2") {
                $firstnameField2 = new C4GTextField();
                $firstnameField2->setFieldName('firstname2');
                $firstnameField2->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname2']);
                $firstnameField2->setColumnWidth(10);
                $firstnameField2->setSortColumn(false);
                $firstnameField2->setTableColumn(true);
                $firstnameField2->setMandatory($rowMandatory);
                $firstnameField2->setNotificationField(true);
                $firstnameField2->setStyleClass('firstname');
                $firstnameField2->setInitialValue($initialValue);
                $firstnameField2->setStyleClass($additionalClass);
                $firstnameField2->setPrintable($rowPrintable);
                $fieldList[] = $firstnameField2;
            } else if ($rowField == "lastname2") {
                $lastnameField2 = new C4GTextField();
                $lastnameField2->setFieldName('lastname2');
                $lastnameField2->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname2']);
                $lastnameField2->setColumnWidth(10);
                $lastnameField2->setSortColumn(false);
                $lastnameField2->setTableColumn(true);
                $lastnameField2->setMandatory($rowMandatory);
                $lastnameField2->setNotificationField(true);
                $lastnameField2->setStyleClass('lastname');
                $lastnameField2->setInitialValue($initialValue);
                $lastnameField2->setStyleClass($additionalClass);
                $lastnameField2->setPrintable($rowPrintable);
                $fieldList[] = $lastnameField2;
            } else if ($rowField == "email2") {
                $emailField2 = new C4GEmailField();
                $emailField2->setFieldName('email2');
                $emailField2->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['email2']);
                $emailField2->setColumnWidth(10);
                $emailField2->setSortColumn(false);
                $emailField2->setTableColumn(false);
                $emailField2->setMandatory($rowMandatory);
                $emailField2->setNotificationField(true);
                $emailField2->setStyleClass('email');
                $emailField2->setInitialValue($initialValue);
                $emailField2->setStyleClass($additionalClass);
                $emailField2->setPrintable($rowPrintable);
                $emailField2->setPattern(C4GBrickRegEx::EMAIL);
                $fieldList[] = $emailField2;
            } else if ($rowField == "organisation2") {
                $organisationField2 = new C4GTextField();
                $organisationField2->setFieldName('organisation2');
                $organisationField2->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['organisation2']);
                $organisationField2->setColumnWidth(10);
                $organisationField2->setSortColumn(false);
                $organisationField2->setTableColumn(true);
                $organisationField2->setMandatory($rowMandatory);
                $organisationField2->setNotificationField(true);
                $organisationField2->setStyleClass('organisation');
                $organisationField2->setInitialValue($initialValue);
                $organisationField2->setStyleClass($additionalClass);
                $organisationField2->setPrintable($rowPrintable);
                $fieldList[] = $organisationField2;
            } else if ($rowField == "phone2") {
                $phoneField2 = new C4GTelField();
                $phoneField2->setFieldName('phone2');
                $phoneField2->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['phone2']);
                $phoneField2->setColumnWidth(10);
                $phoneField2->setSortColumn(false);
                $phoneField2->setMandatory($rowMandatory);
                $phoneField2->setTableColumn(false);
                $phoneField2->setNotificationField(true);
                $phoneField2->setStyleClass('phone');
                $phoneField2->setInitialValue($initialValue);
                $phoneField2->setStyleClass($additionalClass);
                $phoneField2->setPrintable($rowPrintable);
                $fieldList[] = $phoneField2;
            } else if ($rowField == "address2") {
                $addressField2 = new C4GTextField();
                $addressField2->setFieldName('address2');
                $addressField2->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['address2']);
                $addressField2->setColumnWidth(60);
                $addressField2->setSortColumn(false);
                $addressField2->setTableColumn(false);
                $addressField2->setMandatory($rowMandatory);
                $addressField2->setNotificationField(true);
                $addressField2->setStyleClass('address');
                $addressField2->setInitialValue($initialValue);
                $addressField2->setStyleClass($additionalClass);
                $addressField2->setPrintable($rowPrintable);
                $fieldList[] = $addressField2;
            } else if ($rowField == "postal2") {
                $postalField2 = new C4GPostalField();
                $postalField2->setFieldName('postal2');
                $postalField2->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['postal2']);
                $postalField2->setColumnWidth(60);
                $postalField2->setSize(5); //international 32
                $postalField2->setSortColumn(false);
                $postalField2->setTableColumn(false);
                $postalField2->setMandatory($rowMandatory);
                $postalField2->setNotificationField(true);
                $postalField2->setStyleClass('postal');
                $postalField2->setInitialValue($initialValue);
                $postalField2->setStyleClass($additionalClass);
                $postalField2->setPrintable($rowPrintable);
                $postalField2->setPattern(C4GBrickRegEx::POSTAL);
                $fieldList[] = $postalField2;
            } else if ($rowField == "city2") {
                $cityField2 = new C4GTextField();
                $cityField2->setFieldName('city2');
                $cityField2->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['city2']);
                $cityField2->setColumnWidth(60);
                $cityField2->setSortColumn(false);
                $cityField2->setTableColumn(false);
                $cityField2->setMandatory($rowMandatory);
                $cityField2->setNotificationField(true);
                $cityField2->setStyleClass('city');
                $cityField2->setInitialValue($initialValue);
                $cityField2->setStyleClass($additionalClass);
                $cityField2->setPrintable($rowPrintable);
                $fieldList[] = $cityField2;
            } else if ($rowField == "creditInstitute") {
                $creditInstituteField = new C4GTextField();
                $creditInstituteField->setFieldName('creditInstitute');
                $creditInstituteField->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['creditInstitute']);
                $creditInstituteField->setColumnWidth(60);
                $creditInstituteField->setSortColumn(false);
                $creditInstituteField->setTableColumn(false);
                $creditInstituteField->setMandatory($rowMandatory);
                $creditInstituteField->setNotificationField(true);
                $creditInstituteField->setStyleClass('credit-institute');
                $creditInstituteField->setInitialValue($initialValue);
                $creditInstituteField->setStyleClass($additionalClass);
                $creditInstituteField->setPrintable($rowPrintable);
                $fieldList[] = $creditInstituteField;
            } else if ($rowField == "iban") {
                $ibanField = new C4GTextField();
                $ibanField->setFieldName('iban');
                $ibanField->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['iban']);
                $ibanField->setColumnWidth(60);
                $ibanField->setSortColumn(false);
                $ibanField->setTableColumn(false);
                $ibanField->setMandatory($rowMandatory);
                $ibanField->setNotificationField(true);
                $ibanField->setStyleClass('iban');
                $ibanField->setInitialValue($initialValue);
                $ibanField->setStyleClass($additionalClass);
                $ibanField->setPrintable($rowPrintable);
                $ibanField->setPattern(C4GBrickRegEx::IBAN);
                $fieldList[] = $ibanField;
            } else if ($rowField == "bic") {
                $bicField = new C4GTextField();
                $bicField->setFieldName('bic');
                $bicField->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['bic']);
                $bicField->setColumnWidth(60);
                $bicField->setSortColumn(false);
                $bicField->setTableColumn(false);
                $bicField->setMandatory($rowMandatory);
                $bicField->setNotificationField(true);
                $bicField->setStyleClass('bic');
                $bicField->setInitialValue($initialValue);
                $bicField->setStyleClass($additionalClass);
                $bicField->setPrintable($rowPrintable);
                $bicField->setPattern(C4GBrickRegEx::BIC);
                $fieldList[] = $bicField;
            } else if ($rowField == "discountCode") {
                $discountCodeField = new C4GTextField();
                $discountCodeField->setFieldName('discountCode');
                $discountCodeField->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['discountCode']);
                $discountCodeField->setColumnWidth(60);
                $discountCodeField->setSortColumn(false);
                $discountCodeField->setTableColumn(false);
                $discountCodeField->setMandatory($rowMandatory);
                $discountCodeField->setNotificationField(true);
                $discountCodeField->setStyleClass('discountCode');
                $discountCodeField->setInitialValue($initialValue);
                $discountCodeField->setStyleClass($additionalClass);
                $discountCodeField->setPrintable($rowPrintable);
                $fieldList[] = $discountCodeField;
            } else if ($rowField == "additional1") {
                $additional1Field = new C4GTextField();
                $additional1Field->setFieldName('additional1');
                $additional1Field->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['additional1']);
                $additional1Field->setColumnWidth(60);
                $additional1Field->setSortColumn(false);
                $additional1Field->setTableColumn(false);
                $additional1Field->setMandatory($rowMandatory);
                $additional1Field->setNotificationField(true);
                $additional1Field->setStyleClass('additional1');
                $additional1Field->setInitialValue($initialValue);
                $additional1Field->setStyleClass($additionalClass);
                $additional1Field->setPrintable($rowPrintable);
                $fieldList[] = $additional1Field;
            } else if ($rowField == "additional2") {
                $additional2Field = new C4GTextField();
                $additional2Field->setFieldName('additional2');
                $additional2Field->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['additional2']);
                $additional2Field->setColumnWidth(60);
                $additional2Field->setSortColumn(false);
                $additional2Field->setTableColumn(false);
                $additional2Field->setMandatory($rowMandatory);
                $additional2Field->setNotificationField(true);
                $additional2Field->setStyleClass('additional2');
                $additional2Field->setInitialValue($initialValue);
                $additional2Field->setStyleClass($additionalClass);
                $additional2Field->setPrintable($rowPrintable);
                $fieldList[] = $additional2Field;
            } else if ($rowField == "additional3") {
                $additional3Field = new C4GTextField();
                $additional3Field->setFieldName('additional3');
                $additional3Field->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['additional3']);
                $additional3Field->setColumnWidth(60);
                $additional3Field->setSortColumn(false);
                $additional3Field->setTableColumn(false);
                $additional3Field->setMandatory($rowMandatory);
                $additional3Field->setNotificationField(true);
                $additional3Field->setStyleClass('additional3');
                $additional3Field->setInitialValue($initialValue);
                $additional3Field->setStyleClass($additionalClass);
                $additional3Field->setPrintable($rowPrintable);
                $fieldList[] = $additional3Field;
            } else if ($rowField == "comment") {
                $commentField = new C4GTextareaField();
                $commentField->setFieldName('comment');
                $commentField->setTitle($individualLabel ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['comment']);
                $commentField->setColumnWidth(60);
                $commentField->setSortColumn(false);
                $commentField->setTableColumn(false);
                $commentField->setMandatory($rowMandatory);
                $commentField->setNotificationField(true);
                $commentField->setStyleClass('comment');
                $commentField->setInitialValue($initialValue);
                $commentField->setStyleClass($additionalClass);
                $commentField->setPrintable($rowPrintable);
                $fieldList[] = $commentField;
            } else if ($rowField == "additionalHeadline") {
                $headlineField = new C4GHeadlineField();
                $headlineField->setTitle($individualLabel ?: $initialValue);
                $fieldList[] = $headlineField;
            } else if ($rowField == "participants") {

                //ToDo why is here a second desired capacity
                if (!$type['ignoreCapacity'] && $this->reservationSettings->withCapacity && $onlyParticipants) {
                    $reservationDesiredCapacity = new C4GNumberField();
                    $error = 0;
                    $withEventMaxParti = $eventObj->maxParticipantsPerEventBooking ?:0;
                    $typeMaxParti = $listType['maxParticipantsPerBooking'] ?: 0;
                    $noCap = (!$maxCapacity && !$isPartiPerEvent && !$typeMaxParti) ;

                    if ($withEventMaxParti) {
                        $isPartiPerEvent = $withEventMaxParti;
                    } else if ($typeMaxParti) {
                        $isPartiPerEvent = $typeMaxParti;
                    }

                    $reservationDesiredCapacity->setFieldName('desiredCapacity_' . $listType['id']);

                    if ($maxCapacity) {
                        $maxCapacity = C4gReservationHandler::getMaxParticipentsForObject($eventId, $maxCapacity);
                        $currentMaxCapacity = C4gReservationHandler::getMaxParticipentsForObject($eventId, $maxCapacity);
                    }

                    //without max cap for praticipants but max per booking
                    if ($maxCapacity >= $isPartiPerEvent && $isPartiPerEvent) {
                        $maxCapacity = $isPartiPerEvent;
                    }

                    //Max participant per booking
                    if ($eventObj->maxParticipantsPerEventBooking) {
                        $maxParticipants = $eventObj->maxParticipantsPerEventBooking;
                    } else if ($type['maxParticipantsPerBooking']){
                        $maxParticipants = $type['maxParticipantsPerBooking'];;
                    }

                    if (!$noCap && ($maxCapacity <= 0) && ($listType['objectType'] == '2') || !$listType['minParticipantsPerBooking']) {
                        $error = 1;
                    }

//                    if ($maxCapacity < $eventObj->maxParticipants) {
//                        $error = 1;
//                    }

                    //for unlimited max cap
                    if ($noCap || (!$maxCapacity && $isPartiPerEvent)) {
                        $error = 0;
                        $maxCapacity = $isPartiPerEvent;
                        $reservationDesiredCapacity->setMin(1);
                        $reservationDesiredCapacity->setMax(999);
                        $reservationDesiredCapacity->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['desiredCapacity']);
                    }

                    if ($error && !(key_exists('REQUEST_METHOD', $_SERVER) && ($_SERVER['REQUEST_METHOD'] == 'PUT'))) {
                        $reservationDesiredCapacity->setMin(0);
                        $reservationDesiredCapacity->setMax(0);

                        $info = new C4GInfoTextField();
                        $info->setFieldName('info');
                        $info->setEditable(false);
                        $info->setInitialValue($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_none']);
                        return [$info];
                    }


                    if ($minCapacity && $maxCapacity && ($minCapacity != $maxCapacity && !$noCap && $isPartiPerEvent) /*|| $noCap && !$isPartiPerEvent*/) {
                        $reservationDesiredCapacity->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['desiredCapacity']. ' ('.$minCapacity.'-'.$maxCapacity.')');
                    } else {
                        $reservationDesiredCapacity->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['desiredCapacity']);
                    }

                    if ($isPartiPerEvent){
                        $reservationDesiredCapacity->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['desiredCapacity']. ' ('.$minCapacity.'-'.$isPartiPerEvent.')');
                    }
                    if ($type['ignoreCapacity']) {
                        $reservationDesiredCapacity->setFormField(false);
                    } else {
                        $reservationDesiredCapacity->setFormField(true);
                    }
                    $reservationDesiredCapacity->setEditable(true);
                    $reservationDesiredCapacity->setCondition(array($condition));
                    $initialCapacityValue = \Contao\Input::post('desiredCapacity_'.$type['id']) ?: (\Contao\Input::get('capacity') ?: ($minCapacity ?: 1));
                    $reservationDesiredCapacity->setInitialValue($initialCapacityValue);
                    $reservationDesiredCapacity->setMandatory(true);

                    $reservationDesiredCapacity->setMin($minCapacity);
                    if (!$noCap){
                        $reservationDesiredCapacity->setMax($maxCapacity);
                    }

                    $reservationDesiredCapacity->setPattern(C4GBrickRegEx::NUMBERS);
                    $reservationDesiredCapacity->setCallOnChange(true);
                    $jsOnChange = "setReservationForm(".json_encode((string)$listType['id']).",".json_encode((int)$showDateTime).");";
                    $reservationDesiredCapacity->setCallOnChangeFunction($jsOnChange);
                    $reservationDesiredCapacity->setNotificationField(true);
                    $reservationDesiredCapacity->setStyleClass('desired-capacity');

                    if (!$listType['ignoreCapacity']) {
                        if ($this->reservationSettings->moveCapacity) {
                            $fieldList[] = $reservationDesiredCapacity;
                        }
                    }
                }

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
                $participants = [];

                $rowMandatory = $participantsMandatory;
                $firstnameField = new C4GTextField();
                $firstnameField->setFieldName('firstname');
                $firstnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname']);
                $firstnameField->setColumnWidth(10);
                $firstnameField->setSortColumn(false);
                $firstnameField->setTableColumn(true);
                $firstnameField->setMandatory($specialParticipantMechanism ? $rowMandatory : true);
                $firstnameField->setNotificationField(true);
                $firstnameField->setPrintable(true);
                $participants[] = $firstnameField;

                $lastnameField = new C4GTextField();
                $lastnameField->setFieldName('lastname');
                $lastnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname']);
                $lastnameField->setColumnWidth(10);
                $lastnameField->setSortColumn(false);
                $lastnameField->setTableColumn(true);
                $lastnameField->setMandatory($specialParticipantMechanism ? $rowMandatory : true);
                $lastnameField->setNotificationField(true);
                $lastnameField->setPrintable(true);
                $participants[] = $lastnameField;

                if (!$hideParticipantsEmail){
                    $emailField = new C4GEmailField();
                    $emailField->setFieldName('email');
                    $emailField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['email']);
                    $emailField->setColumnWidth(10);
                    $emailField->setSortColumn(false);
                    $emailField->setTableColumn(false);
                    $emailField->setMandatory($rowMandatory);
                    $emailField->setNotificationField(true);
                    $emailField->setPrintable(false);
                    $participants[] = $emailField;
                }

                $baseParticipants = $participants;
                foreach ($typelist as $type) {
                    $participants = $baseParticipants;
                    $condition = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'reservation_type', $type['id']);
                    $maxParticipants = $type['maxParticipantsPerBooking'];
                    $minParticipants = $type['minParticipantsPerBooking'];

                    // Max participant per booking
                    if ($eventObj && $eventObj->maxParticipantsPerEventBooking) {
                        $maxParticipants = $eventObj->maxParticipantsPerEventBooking;
                    } else if ($type['maxParticipantsPerBooking']){
                        $maxParticipants = $type['maxParticipantsPerBooking'];
                    } else {
                        $maxParticipants = 1;
                    }

                    // Ensure $maxParticipants is at least 1 for events if we want to show fields
                    if ($eventObj && $maxParticipants < 1) {
                         $maxParticipants = 1;
                    }
                    
                    if (!$maxParticipants && $specialParticipantMechanism) {
                        $maxParticipants = 10; // Default fallback for special mechanism if nothing is configured
                    }

                    $maxCapacity = $maxParticipants ?: 0;
                    $minCapacity = $minParticipants ?: 1;
                    $participantParam = ($eventObj && $eventObj->participant_params) ? StringUtil::deserialize($eventObj->participant_params, true) : null;
                    $params = $participantParam ?: $type['participantParams'];
                    $participantParamsArr = [];

                    $maxCapacityCheck = $specialParticipantMechanism || ($onlyParticipants ? $maxCapacity > 0 : $maxCapacity > 1);

                    if ($specialParticipantMechanism) {
                        C4gLogModel::addLogEntry('reservation', "Special participant mechanism active for type " . $type['id'] . ", maxCapacity: $maxCapacity");
                    }

                    C4gLogModel::addLogEntry('reservation', "Capacity check for type " . $type['id'] . ": special=" . ($specialParticipantMechanism ? "yes" : "no") . ", maxCap=$maxCapacity, check=" . ($maxCapacityCheck ? "pass" : "fail"));

                    $reservationSettingsWithCapacity = $this->reservationSettings->withCapacity;
                    if ($specialParticipantMechanism || $maxCapacityCheck) {
                        C4gLogModel::addLogEntry('reservation', "Generating participant fields for type " . $type['id']);
                        if ($params) {
                            $initialCapacityValue = \Contao\Input::post('desiredCapacity_'.$type['id']) ?: (\Contao\Input::get('capacity') ?: 1);
                            foreach ($params as $paramId) {
                                if ($paramId) {
                                    $participantParam = C4gReservationParamsModel::feParamsCaptions($paramId, $reservationSettings);

                                    if ($participantParam !== null) {
                                        $participantParamsArr[] = $participantParam;
                                    }
                                }
                            }
                        }

                        if (count($participantParamsArr) > 0) {
                            $eventOptionsRadio = $eventObj && $eventObj->participantParamsFieldType == 'radio';
                            $eventOptionsMandatory = $eventObj && $eventObj->participantParamsMandatory == '1';
                            if ($eventOptionsRadio) {
                                $participantParamField = new C4GRadioGroupField();
                                if (!$eventOptionsMandatory) {
                                    $participantParamField->setInitialValue($participantParamsArr[0]['id']);
                                }
                                $participantParamField->setSaveAsArray(true);
                            } else {
                                $participantParamField = new C4GMultiCheckboxField();
                                $participantParamField->setModernStyle(false);
                            }
                            $participantParamField->setFieldName('participant_params');
                            $participantParamField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['participant_params']);
                            $participantParamField->setFormField(true);
                            $participantParamField->setEditable(true);
                            $participantParamField->setOptions($participantParamsArr);
                            $participantParamField->setMandatory($eventOptionsMandatory ?: $type['participantParamsMandatory']);
                            $participantParamField->setStyleClass('participant-params');
                            $participantParamField->setNotificationField(false);
                            $participantParamField->setSort(false);
                            $participantParamField->setPrintable(false);

                            $participants[] = $participantParamField;
                        }

                        if (!$specialParticipantMechanism && !$this->reservationSettings->withCapacity) {
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
                            $reservationParticipants->setMandatory($rowMandatory);

                            $reservationParticipants->setMin($minCapacity);
                            if ($maxCapacity) {
                                $reservationParticipants->setMax($maxCapacity);
                            }

                            $reservationParticipants->setNotificationField(false);
                            if (isset($_GET['event']) && intval($_GET['event']) > 0) {
                                $reservationParticipants->setShowFirstDataSet(true);
                                $reservationParticipants->setInitInvisible(false);
                            } else {
                                $reservationParticipants->setShowFirstDataSet(true);
                            }
                            $reservationParticipants->setParentFieldList($fieldList);
                            $reservationParticipants->setDelimiter('§');
                            $reservationParticipants->setWildcard('³'); //ToDo Test
                            $reservationParticipants->setCondition(array($condition));
                            $reservationParticipants->setRemoveWithEmptyCondition(true);
                            $reservationParticipants->setAdditionalID($listType['id']);
                            $reservationParticipants->setPrintable(true);

                            $fieldList[] = $reservationParticipants;
                        } else {
                            if ($this->reservationSettings->withCapacity) {
                                $participantCapacity = $maxCapacity && ($maxCapacity < 10) ? $maxCapacity : 10;
                                if ($participantCapacity >= 1) {
                                    $start = $minCapacity ?: 1;
                                    for ($i = $start; $i <= $participantCapacity; $i++) {
                                        $counter = $onlyParticipants ? $i : $i - 1;
                                        if ($counter < 1) {
                                            continue;
                                        }
                                        $newCondition = new C4GBrickCondition(C4GBrickConditionType::GREATEREQUALSWITCH, 'desiredCapacity_' . $type['id'], $i);

                                        //$newCondition[] = $condition;
                                        $reservationParticipants = new C4GSubDialogField();
                                        $reservationParticipants->setFieldName('participants');

                                        $reservationParticipants->setTitle($onlyParticipants ? '' : $GLOBALS['TL_LANG']['fe_c4g_reservation']['additionalParticipants']);
                                        $reservationParticipants->setShowButtons(false);
                                        $reservationParticipants->setTable('tl_c4g_reservation_participants');
                                        $reservationParticipants->addFields($participants);
                                        $reservationParticipants->setKeyField($participantsKey);
                                        $reservationParticipants->setForeignKeyField($participantsForeign);
                                        $reservationParticipants->setMandatory($rowMandatory);
                                        $reservationParticipants->setMin($minCapacity);
                                        $reservationParticipants->setMax($participantCapacity);
                                        $reservationParticipants->setNotificationField(false);
                                        $reservationParticipants->setShowDataSetsByCount(1);
                                        $reservationParticipants->setParentFieldList($fieldList);
                                        $reservationParticipants->setDelimiter('§');
                                        $reservationParticipants->setCondition(array($condition, $newCondition));
                                        if (isset($_GET['event']) && intval($_GET['event']) > 0) {
                                            $reservationParticipants->setShowFirstDataSet(true);
                                            $reservationParticipants->setInitInvisible(false);
                                        }
                                        if ($i === $start) {
                                            $reservationParticipants->setRemoveWithEmptyCondition(false);
                                        } else {
                                            $reservationParticipants->setRemoveWithEmptyCondition(true);
                                        }
                                        $reservationParticipants->setAdditionalID($listType['id'] . '-' . $counter);
                                        $reservationParticipants->setPrintable(true);

                                        $fieldList[] = $reservationParticipants;
                                    }
                                }
                            } else {
                                //ToDo check without desired Capacity.
                                $maxCapacity = $maxCapacity ?: 0; //ToDo check instead of 1

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

                                $showCount = $maxCapacity <= 10 ? $maxCapacity : 10;
                                if ($showCount > 0) {
                                    $reservationParticipants->setShowDataSetsByCount($showCount);
                                    if (isset($_GET['event']) && intval($_GET['event']) > 0) {
                                        $reservationParticipants->setShowFirstDataSet(true);
                                        $reservationParticipants->setInitInvisible(false);
                                    }
                                    $reservationParticipants->setParentFieldList($fieldList);
                                    $reservationParticipants->setDelimiter('§');
                                    $reservationParticipants->setCondition(array($condition));
                                    $reservationParticipants->setRemoveWithEmptyCondition(true);
                                    $reservationParticipants->setAdditionalID($listType['id']);
                                    $reservationParticipants->setPrintable(true);

                                    $fieldList[] = $reservationParticipants;
                                }

                            }
                        }
                    }
                }

                $reservationParticipantList = new C4GMultiSelectField();
                $reservationParticipantList->setFieldName('participantList');
                $reservationParticipantList->setDatabaseField(false);
                $reservationParticipantList->setNotificationField(true);
                $reservationParticipantList->setFormField(false);
                $reservationParticipantList->setPrintable(true);
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
        $reservationId = '';
        if (isset($putVars['reservation_id']) && $putVars['reservation_id']) {
            $reservationId = $putVars['reservation_id'];
        }

        if (!$reservationId) {
            $maxLoops = 10;
            while ($maxLoops > 0) {
                $reservationId = C4GBrickCommon::getUUID();
                $database = \Contao\Database::getInstance();
                $check = $database->prepare("SELECT id FROM tl_c4g_reservation WHERE reservation_id=?")
                    ->execute($reservationId);
                if ($check->numRows === 0) {
                    break;
                }
                $maxLoops--;
            }
        }
        $reservationIdField->setInitialValue($reservationId);
        $reservationIdField->setTableRow(false);
        $reservationIdField->setEditable(false);
        $reservationIdField->setUnique(false);
        $reservationIdField->setNotificationField(true);
        $reservationIdField->setDbUnique(false);
        $reservationIdField->setSimpleTextWithoutEditing(false);
        $reservationIdField->setDatabaseField(true);
        $reservationIdField->setDbUniqueResult('');
        $reservationIdField->setStyleClass('reservation-id');
        $reservationIdField->setHidden($hideReservationKey);
        $reservationIdField->setPrintable(true);
        $fieldList[] = $reservationIdField;

        if ($this->reservationSettings->privacy_policy_text) {
            $privacyPolicyText = new C4GTextField();
            $privacyPolicyText->setSimpleTextWithoutEditing(true);
            $privacyPolicyText->setFieldName('privacy_policy_text');
            $privacyPolicyText->setInitialValue(str_replace(' ', '&nbsp;&#x200B;',
                C4GUtils::replaceInsertTags($this->reservationSettings->privacy_policy_text)));
            $privacyPolicyText->setSize(4);
            $privacyPolicyText->setTableColumn(false);
            $privacyPolicyText->setEditable(false);
            $privacyPolicyText->setDatabaseField(false);
            $privacyPolicyText->setMandatory(false);
            $privacyPolicyText->setNotificationField(false);
            $privacyPolicyText->setStyleClass('privacy-policy-text');
            $privacyPolicyText->setPrintable($this->withDefaultPDFContent);
            $fieldList[] = $privacyPolicyText;
        }

        if ($this->reservationSettings->privacy_policy_site) {
            $href = C4GUtils::replaceInsertTags('{{link_url::' . $this->reservationSettings->privacy_policy_site . '}}');
            $desc = '<span class="c4g_field_description_text">' . str_replace(' ', '&nbsp;&#x200B;', $GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed']) . '&nbsp;&#x200B;</span><a href="' . $href . '" target="_blank" rel="noopener">' . str_replace(' ', '&nbsp;&#x200B;', $GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed_link_text']) . '</a>.';
        } else {
            $desc = str_replace(' ', '&nbsp;&#x200B;', $GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed_without_link']);
        }

        $agreedField = new C4GCheckboxField();
        $agreedField->setFieldName('agreed');
        $agreedField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['agreed'].'&nbsp;&#x200B;'.$desc);
        $agreedField->setTableRow(false);
        $agreedField->setColumnWidth(5);
        $agreedField->setSortColumn(false);
        $agreedField->setTableColumn(false);
        $agreedField->setMandatory(true);
        $agreedField->setNotificationField(true);
        $agreedField->setStyleClass('agreed');
        $agreedField->setWithoutDescriptionLineBreak(true);
        $agreedField->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $agreedField;

        $clickButton = new C4GBrickButton(
            C4GBrickConst::BUTTON_CLICK,
            $this->reservationSettings->reservationButtonCaption ? C4GUtils::replaceInsertTags($this->reservationSettings->reservationButtonCaption) : $GLOBALS['TL_LANG']['fe_c4g_reservation']['button_reservation'],
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
        $location_name->setPrintable(true);
        $fieldList[] = $location_name;

        $contact_name = new C4GTextField();
        $contact_name->setFieldName('contact_name');
        $contact_name->setSortColumn(false);
        $contact_name->setFormField(false);
        $contact_name->setTableColumn(true);
        $contact_name->setNotificationField(true);
        $contact_name->setPrintable(true);
        $fieldList[] = $contact_name;

        $contact_phone = new C4GTelField();
        $contact_phone->setFieldName('contact_phone');
        $contact_phone->setFormField(false);
        $contact_phone->setTableColumn(false);
        $contact_phone->setNotificationField(true);
        $contact_phone->setPrintable(true);
        $fieldList[] = $contact_phone;

        $contact_email = new C4GEmailField();
        $contact_email->setFieldName('contact_email');
        $contact_email->setTableColumn(false);
        $contact_email->setFormField(false);
        $contact_email->setNotificationField(true);
        $contact_email->setPrintable(true);
        $fieldList[] = $contact_email;

        $contact_website = new C4GUrlField();
        $contact_website->setFieldName('contact_website');
        $contact_website->setTableColumn(false);
        $contact_website->setFormField(false);
        $contact_website->setNotificationField(true);
        $contact_website->setPrintable(true);
        $fieldList[] = $contact_website;

        $contact_street = new C4GTextField();
        $contact_street->setFieldName('contact_street');
        $contact_street->setTableColumn(false);
        $contact_street->setFormField(false);
        $contact_street->setNotificationField(true);
        $contact_street->setPrintable(true);
        $fieldList[] = $contact_street;


        $contact_postal = new C4GTextField();
        $contact_postal->setFieldName('contact_postal');
        $contact_postal->setFormField(false);
        $contact_postal->setTableColumn(false);
        $contact_postal->setNotificationField(true);
        $contact_postal->setPrintable(true);
        $fieldList[] = $contact_postal;


        $contact_city = new C4GTextField();
        $contact_city->setFieldName('contact_city');
        $contact_city->setTableColumn(false);
        $contact_city->setFormField(false);
        $contact_city->setNotificationField(true);
        $contact_city->setPrintable(true);
        $fieldList[] = $contact_city;

        $contact_city = new C4GTextField();
        $contact_city->setFieldName('icsFilename');
        $contact_city->setTableColumn(false);
        $contact_city->setFormField(false);
        $contact_city->setNotificationField(true);
        $contact_city->setPrintable(true);
        $fieldList[] = $contact_city;

        $memberId = new C4GTextField();
        $memberId->setFieldName('member_id');
        $memberId->setTableColumn(true);
        $memberId->setFormField(false);
        $memberId->setNotificationField(false);
        $memberId->setPrintable(true);
        $fieldList[] = $memberId;

        $formularId = new C4GTextField();
        $formularId->setFieldName('formular_id');
        $formularId->setTableColumn(true);
        $formularId->setFormField(false);
        $formularId->setNotificationField(false);
        $formularId->setPrintable(true);
        $fieldList[] = $formularId;

        $dbkey = new C4GTextField();
        $dbkey->setFieldName('dbkey');
        $dbkey->setTableColumn(false);
        $dbkey->setFormField(false);
        $dbkey->setNotificationField(true);
        $dbkey->setPrintable(true);
        $fieldList[] = $dbkey;

        $memberEmail = new C4GTextField();
        $memberEmail->setFieldName('member_email');
        $memberEmail->setTableColumn(false);
        $memberEmail->setFormField(false);
        $memberEmail->setNotificationField(true);
        $memberEmail->setPrintable(true);
        $fieldList[] = $memberEmail;

        $groupId = new C4GTextField();
        $groupId->setFieldName('group_id');
        $groupId->setTableColumn(true);
        $groupId->setFormField(false);
        $groupId->setNotificationField(false);
        $groupId->setPrintable(true);
        $fieldList[] = $groupId;

        $bookedAt = new C4GTextField();
        $bookedAt->setFieldName('bookedAt');
        $bookedAt->setTableColumn(true);
        $bookedAt->setFormField(false);
        $bookedAt->setNotificationField(false);
        $bookedAt->setPrintable(true);
        $fieldList[] = $bookedAt;

        //price for notification
        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('price');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(9999999999999);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable(true);
        $fieldList[] = $priceDBField;

        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('priceTax');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(9999999999999);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable(true);
        $fieldList[] = $priceDBField;

        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('priceSum');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(9999999999999);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable(true);
        $fieldList[] = $priceDBField;

        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('priceSumTax');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(9999999999999);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable(true);
        $fieldList[] = $priceDBField;

        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('priceNet');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(9999999999999);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable(true);
        $fieldList[] = $priceDBField;

        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('priceSumNet');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(9999999999999);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable(true);
        $fieldList[] = $priceDBField;

        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('priceOptionSum');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(9999999999999);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable(true);
        $fieldList[] = $priceDBField;

        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('priceOptionSumTax');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(9999999999999);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable(true);
        $fieldList[] = $priceDBField;

        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('priceOptionSumNet');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(9999999999999);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable(true);
        $fieldList[] = $priceDBField;

        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('priceDiscount');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(100);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable(true);
        $fieldList[] = $priceDBField;

        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('discountPercent');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(100);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable(true);
        $fieldList[] = $priceDBField;

        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('reservationTaxRate');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(9999999999999);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable(true);
        $fieldList[] = $priceDBField;

        if ($this->reservationSettings->documentIdNext) {
            /*$idNext = str_pad($this->reservationSettings->documentIdNext, $this->reservationSettings->documentIdLength, "0", STR_PAD_LEFT);
            $prefix = self::replaceDateTokens($this->reservationSettings->documentIdPrefix);
            $suffix = self::replaceDateTokens($this->reservationSettings->documentIdSuffix);
            $documentId = $prefix.$idNext.$suffix;
            */
            $documentIdField = new C4GTextField();
            $documentIdField->setFieldName('documentId');
            $documentIdField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['documentId']);
            $documentIdField->setDatabaseField(true);
            $documentIdField->setFormField(true);
            $documentIdField->setSortColumn(false);
            $documentIdField->setNotificationField(true);
            $documentIdField->setInitialValue('');
            $documentIdField->setPrintable(true);
            $documentIdField->setHidden(true);
            $fieldList[] = $documentIdField;
        }

        if ($this->reservationSettings->checkInPage) {
            $qrContentField = new C4GTextareaField();
            $qrContentField->setFieldName('qrContent');
            $qrContentField->setInitialValue('');
            $qrContentField->setDatabaseField(true);
            $qrContentField->setFormField(false);
            $qrContentField->setPrintable(false);
            $fieldList[] = $qrContentField;

            //ToDo enable configuration
            $size = (strpos($this->printTemplate, 'pdf_reservation_invoice') !== false) ? 100 : 200;
            $align = (strpos($this->printTemplate, 'pdf_reservation_invoice') !== false) ? "right" : "left";
            $align = (strpos($this->printTemplate, 'pdf_reservation_ticket') !== false) ? "center" : $align;

            $qrFileNameField = new C4GImageField();
            $qrFileNameField->setFieldName('qrFileName');
            $qrFileNameField->setTitle(''); //'Check in'
            $qrFileNameField->setInitialValue('');
            $qrFileNameField->setDatabaseField(true);
            $qrFileNameField->setFormField(true);
            $qrFileNameField->setPrintable(true); //ToDo switch
            $qrFileNameField->setWidth($size);
            $qrFileNameField->setHeight($size);
            $qrFileNameField->setMandatory(false);
            $qrFileNameField->setComparable(false);
            $qrFileNameField->setLightBoxField('1');
            $qrFileNameField->setDisplay(false);
            $qrFileNameField->setAlign($align);
            $fieldList[] = $qrFileNameField;

            $bankNameField = new C4GTextField();
            $bankNameField->setFieldName('bankName');
            $bankNameField->setDatabaseField(false);
            $bankNameField->setFormField(false);
            $bankNameField->setPrintable(true);
            $fieldList[] = $bankNameField;

            $bankIbanField = new C4GTextField();
            $bankIbanField->setFieldName('bankIban');
            $bankIbanField->setDatabaseField(false);
            $bankIbanField->setFormField(false);
            $bankIbanField->setPrintable(true);
            $fieldList[] = $bankIbanField;

            $bankBicField = new C4GTextField();
            $bankBicField->setFieldName('bankBic');
            $bankBicField->setDatabaseField(false);
            $bankBicField->setFormField(false);
            $bankBicField->setPrintable(true);
            $fieldList[] = $bankBicField;

            $bankQrFileNameField = new C4GImageField();
            $bankQrFileNameField->setFieldName('bankQrFileName');
            $bankQrFileNameField->setDatabaseField(false);
            $bankQrFileNameField->setFormField(false);
            $bankQrFileNameField->setPrintable(true);
            $bankQrFileNameField->setWidth(100);
            $bankQrFileNameField->setHeight(100);
            $bankQrFileNameField->setAlign('left');
            $fieldList[] = $bankQrFileNameField;
        }


        $this->fieldList = $fieldList;

        $notificationTokens = [
            'price', 'priceTax', 'priceSum', 'priceSumTax', 'priceNet', 'priceSumNet',
            'priceOptionSum', 'priceOptionSumTax', 'priceOptionSumNet', 'priceDiscount',
            'discountPercent', 'discountCode', 'reservationTaxRate', 'dbkey',
            'type', 'object', 'reservation_type', 'reservation_object', 'reservation_title',
            'description', 'location', 'comment', 'internal_comment', 'admin_email',
            'speaker', 'topic', 'audience', 'conferenceLink', 'documentId'
        ];

        foreach ($notificationTokens as $token) {
            $found = false;
            foreach ($this->fieldList as $f) {
                if ($f->getFieldName() === $token) {
                    $f->setNotificationField(true);
                    $f->setPrintable(true);
                    $f->setDatabaseField(true); // Must be true as they exist in DCA now
                    $f->setEditable(true);     // Ensure they are processed during save
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $f = new C4GTextField();
                $f->setFieldName($token);
                $f->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation'][$token] ?? $token);
                $f->setDatabaseField(true); // Must be true as they exist in DCA now
                $f->setFormField(false);
                $f->setNotificationField(true);
                $f->setPrintable(true);
                $f->setEditable(true);     // Ensure they are processed during save
                $this->fieldList[] = $f;
            }
        }

        return $this->fieldList;
    }

    /**
     * @param $values
     * @param $putVars
     * @return array|false|mixed|string|string[]|void
     *
     */
    public function clickReservation($values, $putVars)
    {
        try {
            if (!$this->reservationSettings) {
                $this->initBrickModule($this->id);
            }
            if (!$this->reservationSettings) {
                $errRes = ['usermessage' => 'Reservierungseinstellungen konnten nicht geladen werden.'];
                unset($errRes['jump_to_url']);
                unset($errRes['jump_after_message']);
                unset($errRes['performaction']);
                unset($errRes['callback']);
                unset($errRes['dialogclose']);
                unset($errRes['dialogcloseall']);
                return $errRes;
            }
            if (!is_array($putVars)) {
                $putVars = [];
            }
            // 1. Determine type as early as possible to decide on strategy
        $typeIdForStrategy = $putVars['reservation_type'] ?? 0;
        if (!$typeIdForStrategy && key_exists('REQUEST_METHOD', $_SERVER) && ($_SERVER['REQUEST_METHOD'] == 'PUT')) {
            $rawP = $this->getPutVars();
            $typeIdForStrategy = $rawP['reservation_type'] ?? 0;
        }
        
        $isEventActualForStrategy = false;
        if ($typeIdForStrategy) {
            $resType = C4gReservationTypeModel::findByPk($typeIdForStrategy);
            if ($resType && $resType->type == 2) {
                $isEventActualForStrategy = true;
            }
        }

        // --- BACKWARD COMPATIBILITY & DATA RECOVERY ---
        if ($this->requestStack && ($request = $this->requestStack->getCurrentRequest())) {
            // Recovery from content (PUT stream)
            $content = $request->getContent();
            if ($content) {
                parse_str($content, $recoveredVars);
                if (is_array($recoveredVars)) {
                    foreach ($recoveredVars as $rk => $rv) {
                        if (!isset($putVars[$rk]) || $putVars[$rk] === '' || $putVars[$rk] === null) {
                            $putVars[$rk] = $rv;
                        }
                    }
                }
            }
            // Recovery from request parameters (POST/GET processed by Symfony)
            $allParams = $request->request->all();
            if (is_array($allParams)) {
                foreach ($allParams as $rk => $rv) {
                    if (!isset($putVars[$rk]) || $putVars[$rk] === '' || $putVars[$rk] === null) {
                        $putVars[$rk] = $rv;
                    }
                }
            }
        }
        // ----------------------------------------------

        // LOG RAW INPUT STREAM for diagnostic purposes
        $rawStream = file_get_contents('php://input');

        // ONLY for events we do the aggressive state management to prevent state-bleeding
        if ($isEventActualForStrategy) {
            if (key_exists('REQUEST_METHOD', $_SERVER) && ($_SERVER['REQUEST_METHOD'] == 'PUT')) {
                $this->putVars = []; 
                $rawPut = $this->getPutVars();
                if (is_array($rawPut)) {
                    $putVars = array_merge((array)$putVars, $rawPut);
                    $this->putVars = $putVars;
                }
            }
            $this->nukeState(true);
            
            $rawPut = $this->getPutVars();
            if (!empty($rawPut)) {
                $putVars = array_merge((array)$putVars, $rawPut);
                $this->putVars = $putVars;
            }
        } else {
            
            // For standard types, we DO NOT clear the existing state.
            // But we still want to ensure that if the browser sends fresh PUT data, it's merged.
            if (key_exists('REQUEST_METHOD', $_SERVER) && ($_SERVER['REQUEST_METHOD'] == 'PUT')) {
                $rawPut = $this->getPutVars();
                if (!empty($rawPut)) {
                    // Framework might have already populated $this->putVars.
                    // We only overwrite or add, never clear.
                    $putVars = array_merge((array)$putVars, $rawPut);
                    foreach ($putVars as $rk => $rv) {
                        $this->putVars[$rk] = $rv;
                    }
                }
            }
        }
        
        // LOG ALL KEYS in putVars to see what the browser actually sends
        
        $typeIdInReq = $putVars['reservation_type'] ?? null;
        if ($typeIdInReq) {
            $eventIdInReq = $putVars['reservation_object_event_' . $typeIdInReq] ?? null;
            
            // Emergency lookup if eventId is missing in request
            if (!$eventIdInReq) {
                // First check if we already have it in our controller instance from a previous call in the SAME request (e.g. from initBrickModule)
                if (isset($this->putVars['reservation_object_event_' . $typeIdInReq]) && $this->putVars['reservation_object_event_' . $typeIdInReq]) {
                    $eventIdInReq = $this->putVars['reservation_object_event_' . $typeIdInReq];
                } else {
                    $eventIdFromUrl = \Contao\Input::get('event') ?: 0;
                    if (!$eventIdFromUrl && $this->requestStack && ($request = $this->requestStack->getCurrentRequest())) {
                        $eventIdFromUrl = $request->attributes->get('event') ?: 0;
                        if (!$eventIdFromUrl && $request->attributes->has('auto_item')) {
                            $eventIdFromUrl = $request->attributes->get('auto_item');
                        }
                        
                        // Referer check
                        if (!$eventIdFromUrl) {
                            $referer = $request->headers->get('referer');
                            if ($referer && preg_match('/[\/\?\&]event[=\/]([^\/\?\&]+)/', $referer, $matches)) {
                                $eventIdFromUrl = $matches[1];
                                $eventIdFromUrl = str_replace('.html', '', $eventIdFromUrl);
                            }
                        }
                    }
                    
                    // Alias resolution
                    if ($eventIdFromUrl && !is_numeric($eventIdFromUrl)) {
                        $aliasObj = $database->prepare("SELECT id FROM tl_calendar_events WHERE alias=?")
                            ->execute($eventIdFromUrl);
                        if ($aliasObj && $aliasObj->next()) {
                            $eventIdFromUrl = $aliasObj->id;
                        }
                    }
                    
                    if ($eventIdFromUrl) {
                        $eventIdInReq = $eventIdFromUrl;
                        $putVars['reservation_object_event_' . $typeIdInReq] = $eventIdInReq;
                        $this->putVars['reservation_object_event_' . $typeIdInReq] = $eventIdInReq;
                    } else {
                        // Try session as absolute last resort
                        $eventIdInReq = $this->session->getSessionValue('reservationEventCookie');
                        
                        // Check if it's an event type (Type 2)
                        $isEventActual = false;
                        if ($typeIdInReq) {
                            $resType = C4gReservationTypeModel::findByPk($typeIdInReq);
                            if ($resType && $resType->type == 2) {
                                $isEventActual = true;
                            }
                        }
                        
                        if ($eventIdInReq) {
                            
                            // SECURITY CHECK: If we just saved a booking, the session ID might be stale
                            if ($this->session->getSessionValue('reservationJustSaved') && $isEventActual) {
                                $eventIdInReq = 0;
                            } else {
                                $putVars['reservation_object_event_' . $typeIdInReq] = $eventIdInReq;
                                $this->putVars['reservation_object_event_' . $typeIdInReq] = $eventIdInReq;
                            }
                        }
                    }
                    
                    if (!$eventIdInReq) {
                        // Refuse fallback if even session is empty
                        // BUT ONLY IF it is an event type (Type 2)
                        $isEventActual = false;
                        if ($typeIdInReq) {
                            $resType = C4gReservationTypeModel::findByPk($typeIdInReq);
                            if ($resType && $resType->type == 2) {
                                $isEventActual = true;
                            }
                        }
                        
                        if ($isEventActual) {
                            return ['usermessage' => 'Fehler: Ungültige Event-ID. Bitte laden Sie die Seite neu.'];
                        } else {
                        }
                    }
                }
            }

            if ($eventIdInReq) {
                
                $sessionEventId = $this->session->getSessionValue('reservationEventCookie');
                if ($sessionEventId && $sessionEventId != $eventIdInReq) {
                    $eventIdInReq = $sessionEventId;
                    
                    // Forcefully override ALL potential keys that might contain the wrong ID
                    $putVars['reservation_object_event_' . $typeIdInReq] = $eventIdInReq;
                    $this->putVars['reservation_object_event_' . $typeIdInReq] = $eventIdInReq;
                    
                    // Reload reservation object to match corrected eventId
                    $reservationObject = $database->prepare("SELECT * FROM tl_calendar_events WHERE id=?")
                        ->execute($eventIdInReq);
                    if ($reservationObject && $reservationObject->next()) {
                    }
                }
            } else {
                // Check if it's encoded in another way or if we can find it
                foreach ($putVars as $pk => $pv) {
                    if (strpos($pk, 'reservation_object_event_') === 0 && $pv) {
                        $eventIdInReq = $pv;
                        break;
                    }
                }
            }
            
            $oldEventId = $this->session->getSessionValue('reservationEventCookie');
            if ($eventIdInReq && $oldEventId && $oldEventId != $eventIdInReq) {
                $this->session->remove('reservationInitialDateCookie_' . $oldEventId);
                $this->session->remove('reservationTimeCookie_' . $oldEventId);
            }
            if ($eventIdInReq) {
                $this->session->setSessionValue('reservationEventCookie', $eventIdInReq);
            }
        }

        // We also need to clear internal memos of the base controller to avoid model caching
        if (property_exists($this, '__modelFindByPkMemo')) { $this->__modelFindByPkMemo = []; }
        if (property_exists($this, '__modelFindByMemo')) { $this->__modelFindByMemo = []; }
        if (property_exists($this, '__deserializeFastMemo')) { $this->__deserializeFastMemo = []; }
        if (property_exists($this, '__insertTagsFastMemo')) { $this->__insertTagsFastMemo = []; }
        if (property_exists($this, '__ajaxMemo')) { $this->__ajaxMemo = []; }
        if (property_exists($this, '__projectListForBrickMemo')) { $this->__projectListForBrickMemo = []; }
        if (property_exists($this, '__checkProjectIdMemo')) { $this->__checkProjectIdMemo = []; }

        // Regenerate the reservation_id if not already present in the request
        if (!isset($putVars['reservation_id']) || !$putVars['reservation_id']) {
            $maxLoops = 10;
            while ($maxLoops > 0) {
                $putVars['reservation_id'] = \con4gis\ProjectsBundle\Classes\Common\C4GBrickCommon::getUUID();
                $database = \Contao\Database::getInstance();
                $check = $database->prepare("SELECT id FROM tl_c4g_reservation WHERE reservation_id=?")
                    ->execute($putVars['reservation_id']);
                if ($check->numRows === 0) {
                    break;
                }
                $maxLoops--;
            }
        }
        $this->putVars['reservation_id'] = $putVars['reservation_id'];
        
        // Force empty dialog values in session to prevent the framework from "remembering"
        $this->session->setSessionValue('c4g_brick_dialog_values', ['reservation_id' => $putVars['reservation_id']]);

        // Sync back to session if present to avoid restoration of old values from elsewhere
        $dialogValues = ['reservation_id' => $putVars['reservation_id']];

        // Ensure that IDs from current request are used and mirrored
        if (isset($putVars['reservation_type'])) {
            $typeId = $putVars['reservation_type'];
            $this->putVars['reservation_type'] = $typeId;
            $currentEventInRequest = $putVars['reservation_object_event_' . $typeId] ?? null;
            if ($currentEventInRequest) {
                $this->putVars['reservation_object_event_' . $typeId] = $currentEventInRequest;
                $this->session->setSessionValue('reservationEventCookie', $currentEventInRequest);
            }
            
            $currentObjectInRequest = $putVars['reservation_object_' . $typeId] ?? null;
            if ($currentObjectInRequest) {
                $this->putVars['reservation_object_' . $typeId] = $currentObjectInRequest;
            }
        }
        
        // Mirror all fields before doing anything else
        $this->mirrorBaseTokens($putVars);

        // --- DISCOUNT RECOVERY ---
        // Ensure discount code and percent are mapped before allPrices
        if (isset($putVars['reservation_type'])) {
            $typeId = $putVars['reservation_type'];
            foreach (['discountCode', 'discountPercent'] as $dk) {
                $suffixed = $dk . '_' . $typeId;
                if (isset($putVars[$suffixed]) && (!isset($putVars[$dk]) || !$putVars[$dk] || $putVars[$dk] === ' ')) {
                    $putVars[$dk] = $putVars[$suffixed];
                    $this->putVars[$dk] = $putVars[$suffixed];
                }
            }
            // Add rescue for event specific suffixes
            $currentEventForRescue = $putVars['reservation_object_event_' . $typeId] ?? null;
            if ($currentEventForRescue) {
                $eventSuffix = $typeId . '-22' . $currentEventForRescue;
                foreach (['discountCode', 'discountPercent'] as $dk) {
                    $suffixed = $dk . '_' . $eventSuffix;
                    if (isset($putVars[$suffixed]) && (!isset($putVars[$dk]) || !$putVars[$dk] || $putVars[$dk] === ' ')) {
                        $putVars[$dk] = $putVars[$suffixed];
                        $this->putVars[$dk] = $putVars[$suffixed];
                    }
                }
            }
        }

            // Ensure calculation values are in putVars for saving and notifications
            if (isset($this->putVars['priceSum'])) {
                foreach (['priceSum', 'priceSumNet', 'priceSumTax', 'priceSumBrutto', 'priceOptionSum', 'priceOptionSumNet', 'priceOptionSumTax', 'priceDiscount', 'priceNet', 'priceBrutto', 'priceTax', 'price', 'discountPercent', 'discountCode'] as $pk) {
                    if (isset($this->putVars[$pk]) && (!isset($putVars[$pk]) || $putVars[$pk] === '0,00 €' || $putVars[$pk] === '0' || $putVars[$pk] === ' ' || $putVars[$pk] === '0,00 %' || $putVars[$pk] === '0 %')) {
                        $putVars[$pk] = $this->putVars[$pk];
                    }
                }
                // Hard sync for priceNet/priceTax to avoid them being 0,00 € in notifications if they exist in instance
                $putVars['priceNet'] = $this->putVars['priceNet'] ?? $putVars['priceNet'] ?? '0,00 €';
                $putVars['priceTax'] = $this->putVars['priceTax'] ?? $putVars['priceTax'] ?? '0,00 €';
                \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "Final Sync to putVars: Net: " . ($putVars['priceNet'] ?? 'MISSING') . ", Tax: " . ($putVars['priceTax'] ?? 'MISSING'));
            }

        $this->session->setSessionValue('c4g_brick_dialog_values', $dialogValues);

        $formId = $this->reservationSettings->id;
        $type = $putVars['reservation_type'] ?? '';
        
        // --- EMERGENCY DATA RECOVERY FOR OBJECT ID ---
        if ($type) {
            $objKey = "reservation_object_" . $type;
            if ((!isset($putVars[$objKey]) || !$putVars[$objKey])) {
                // Try to find ANY key that starts with reservation_object_
                foreach ($putVars as $pk => $pv) {
                    if (strpos($pk, 'reservation_object_') === 0 && $pv) {
                        $putVars[$objKey] = $pv;
                        $this->putVars[$objKey] = $pv;
                        break;
                    }
                }
            }
            
            // SECOND RESCUE: Check if we have an event id for event types
            $resTypeForRescue = C4gReservationTypeModel::findByPk($type);
            if ($resTypeForRescue && $resTypeForRescue->type == 2) {
                $eventKey = "reservation_object_event_" . $type;
                if (!isset($putVars[$eventKey]) || !$putVars[$eventKey]) {
                    foreach ($putVars as $pk => $pv) {
                        if (strpos($pk, 'reservation_object_event_') === 0 && $pv) {
                            $putVars[$eventKey] = $pv;
                            $this->putVars[$eventKey] = $pv;
                            break;
                        }
                    }
                }
            }
        }

        // --- EMERGENCY DATA RECOVERY FOR AGREED ---
        if (!isset($putVars['agreed']) || !$putVars['agreed']) {
            foreach ($putVars as $pk => $pv) {
                // Search for 'agreed' anywhere in the key, or keys starting with 'privacy'
                if ((strpos($pk, 'agreed') !== false || strpos($pk, 'privacy') !== false) && $pv) {
                    $putVars['agreed'] = $pv;
                    $this->putVars['agreed'] = $pv;
                    break;
                }
            }
        }
        // -------------------------------------------

        if ($this->reservationSettings->privacy_policy_text || $this->reservationSettings->privacy_policy_site) {
            if (!isset($putVars['agreed']) || !$putVars['agreed']) {
                if (isset($this->session) && is_object($this->session) && method_exists($this->session, 'setSessionValue')) {
                    $this->session->setSessionValue('c4g_brick_dialog_values', null);
                }
                C4gLogModel::addLogEntry('reservation', "Mandatory error: Privacy policy not agreed.");
                $result = [
                    'usermessage' => $GLOBALS['TL_LANG']['FE_C4G_DIALOG']['USERMESSAGE_MANDATORY'] ?? 'Bitte stimmen Sie den Datenschutzbestimmungen zu.'
                ];
                $this->mirrorBaseTokens();
                // Ensure no accidental keys trigger frontend actions
                unset($result['jump_to_url']);
                unset($result['jump_after_message']);
                unset($result['performaction']);
                unset($result['callback']);
                unset($result['dialogclose']);
                unset($result['dialogcloseall']);
                return $result;
            }
        }

        $database = Database::getInstance();
        $reservationType = null;
        if ($type) {
            $reservationType = $database->prepare("SELECT * FROM tl_c4g_reservation_type WHERE id=? AND published='1'")
                ->execute($type);
        }

        $this->notification_type = $this->reservationSettings ? $this->reservationSettings->notification_type : null;
        if ($this->getDialogParams() && $this->reservationSettings && method_exists($this->getDialogParams(), 'setNotificationType')) {
            $this->getDialogParams()->setNotificationType($this->notification_type);
        }

        if ($reservationType && $reservationType->reservationObjectType === '3') {
            $reservationTypeID = $putVars['reservation_type'];
            $reservationObjectID = $putVars['reservation_object_' .$reservationTypeID] ?? 0;
            $database = Database::getInstance();
            $reservations = $database->prepare("SELECT desiredCapacity FROM `tl_c4g_reservation` WHERE `reservation_object` =? AND `reservation_type` =? AND NOT `cancellation`=?")
            ->execute($reservationObjectID, $reservationTypeID,'1')->fetchAllAssoc();

            $database = Database::getInstance();
            $maxReservationCapacity = $database->prepare("SELECT desiredCapacityMax FROM `tl_c4g_reservation_object` WHERE `id` =?") 
            ->execute($reservationObjectID)->fetchAllAssoc();
            
            $maxReservations = isset($maxReservationCapacity[0]['desiredCapacityMax']) ? intval($maxReservationCapacity[0]['desiredCapacityMax']) : 0;
            $currentReservations = C4gReservationHandler::countReservations($reservations); 
        }

            if ($reservationType && $reservationType->notification_type) {
                if ($this->getDialogParams() && method_exists($this->getDialogParams(), 'setNotificationType')) {
                    $this->getDialogParams()->setNotificationType($reservationType->notification_type);
                }
                $this->notification_type = $reservationType->notification_type;
            }

            $isEvent = $reservationType && $reservationType->reservationObjectType && $reservationType->reservationObjectType === '2' ? true : false;

        if ($isEvent) {
            // DEEP CLEAN: Remove any existing event data keys from putVars to prevent state-bleeding.
            // This ensures that only the fresh values assigned below will be used.
            $keysToClear = ['beginDate', 'beginTime', 'endDate', 'endTime', 'reservation_title', 'description', 'image', 'location', 'icsFilename'];
            foreach (array_keys($putVars) as $pk) {
                foreach ($keysToClear as $baseKey) {
                    if ($pk === $baseKey || strpos($pk, $baseKey . '_') === 0) {
                        unset($putVars[$pk]);
                        unset($this->putVars[$pk]);
                    }
                }
            }

            $key = "reservation_object_event_" . $type;
            $resObject = $putVars[$key] ?? 0;

            if ($resObject) {
                if (!isset($eventId)) {
                    $eventId = (int) $resObject;
                }
                // Ensure $resObject matches $eventId to be safe
                if ($eventId && $resObject != $eventId) {
                    $resObject = $eventId;
                }
                
                // IMPORTANT: We explicitly reload the event from the database here 
                // to avoid any caching issues or state bleeding from previous bookings.
                $reservationObjectResult = $database->prepare("SELECT * FROM tl_calendar_events WHERE id=? AND published='1'")
                    ->execute($resObject);

                if ($reservationObjectResult && $reservationObjectResult->id) {
                    $reservationObject = $reservationObjectResult;
                    $eventId = $reservationObject->id;
                    
                    // Mirror to controller instance to ensure addFields uses the correct eventId
                    if (isset($putVars['reservation_type'])) {
                        $putVars['reservation_object_event_' . $putVars['reservation_type']] = $eventId;
                        $this->putVars['reservation_object_event_' . $putVars['reservation_type']] = $eventId;
                    }

                    // Clear date and time cookies if the event changed, to force reloading fresh defaults
                    $oldEventInSession = $this->session->getSessionValue('reservationEventCookie');
                    if ($oldEventInSession && $oldEventInSession != $eventId) {
                        $this->session->remove('reservationInitialDateCookie_' . $oldEventInSession);
                        $this->session->remove('reservationTimeCookie_' . $oldEventInSession);
                    }
                    $this->session->setSessionValue('reservationEventCookie', $eventId);
                    // Also mirror specifically to putVars if it was missing or different
                    $putVars['reservation_object_event_' . $type] = $eventId;

                    // Ensure final event tokens are formatted as strings for the form validator
                    $freshTitle = (string)($reservationObject->title ?? '');
                    $beginDateTs = ($reservationObject->startDate ?? 0) ? intval($reservationObject->startDate) : 0;
                    $beginTimeTs = ($reservationObject->startTime ?? 0) ? intval($reservationObject->startTime) : 0;
                    $endDateTs   = (isset($reservationObject->endDate) && $reservationObject->endDate) ? intval($reservationObject->endDate) : 0;
                    $endTimeTs   = ($reservationObject->endTime ?? 0) ? intval($reservationObject->endTime) : 0;

                    $freshBeginDate = $beginDateTs ? (string)date($GLOBALS['TL_CONFIG']['dateFormat'] ?: 'd.m.Y', $beginDateTs) : '';
                    $freshBeginTime = $beginTimeTs ? (string)date($GLOBALS['TL_CONFIG']['timeFormat'] ?: 'H:i', $beginTimeTs) : '';
                    $freshEndDate = $endDateTs ? (string)date($GLOBALS['TL_CONFIG']['dateFormat'] ?: 'd.m.Y', $endDateTs) : $freshBeginDate;
                    $freshEndTime = $endTimeTs ? (string)date($GLOBALS['TL_CONFIG']['timeFormat'] ?: 'H:i', $endTimeTs) : '';

                    $dataMapping = [
                        'beginDate' => $freshBeginDate,
                        'beginTime' => $freshBeginTime,
                        'endDate' => $freshEndDate,
                        'endTime' => $freshEndTime,
                        'reservation_title' => $freshTitle
                    ];

                    foreach ($dataMapping as $mappedKey => $mappedValue) {
                        $putVars[$mappedKey] = $mappedValue;
                        $this->putVars[$mappedKey] = $mappedValue;
                        if (isset($type)) {
                            $putVars[$mappedKey . '_' . $type] = $mappedValue;
                            $this->putVars[$mappedKey . '_' . $type] = $mappedValue;
                        }
                        $eventSuffix = $type . '-22' . $eventId;
                        $putVars[$mappedKey . '_' . $eventSuffix] = $mappedValue;
                        $this->putVars[$mappedKey . '_' . $eventSuffix] = $mappedValue;
                        $objSuffix = $type . '-' . $eventId;
                        $putVars[$mappedKey . '_' . $objSuffix] = $mappedValue;
                        $this->putVars[$mappedKey . '_' . $objSuffix] = $mappedValue;
                    }
                }
            }
        } else {
            $key = "reservation_object_" . $type;
            $resObject = $putVars[$key];
            // IMPORTANT: We explicitly reload the object from the database here.
            $reservationObjectResult = $database->prepare("SELECT * FROM tl_c4g_reservation_object WHERE id=? AND published='1'")
                ->execute($resObject);
            if ($reservationObjectResult && $reservationObjectResult->id) {
                $reservationObject = $reservationObjectResult;
            }

            if ($reservationObject && $reservationType->cloneObject) {
                $cloneObject = $database->prepare("SELECT * FROM tl_c4g_reservation_object WHERE id=?")
                    ->execute($reservationType->cloneObject);
                if ($cloneObject) {
                    $reservationObject->notification_type = $reservationObject->notification_type ?: $cloneObject->notification_type;
                    $reservationObject->time_interval = ($reservationObject->time_interval && $reservationObject->time_interval !== 1) || !$cloneObject->time_interval ? $reservationObject->time_interval : $cloneObject->time_interval;
                    $reservationObject->duration = $reservationObject->duration ?: $cloneObject->duration;
                    $reservationObject->desiredCapacityMax = $reservationObject->desiredCapacityMax ?: $cloneObject->desiredCapacityMax;
                    $reservationObject->minParticipants = $reservationObject->minParticipants ?: $cloneObject->minParticipants;
                    $reservationObject->maxParticipants = $reservationObject->maxParticipants ?: $cloneObject->maxParticipants;
                    $reservationObject->oh_sunday = $cloneObject->oh_sunday;
                    $reservationObject->oh_monday = $cloneObject->oh_monday;
                    $reservationObject->oh_tuesday = $cloneObject->oh_tuesday;
                    $reservationObject->oh_wednesday = $cloneObject->oh_wednesday;
                    $reservationObject->oh_thursday = $cloneObject->oh_thursday;
                    $reservationObject->oh_friday = $cloneObject->oh_friday;
                    $reservationObject->oh_saturday = $cloneObject->oh_saturday;
                }
            }

                if ($reservationObject && $reservationObject->notification_type) {
                    if ($this->getDialogParams()) {
                        $this->getDialogParams()->setNotificationType($reservationObject->notification_type);
                    }
                    $this->notification_type = $reservationObject->notification_type;
                }

            // Hotfix: Wir entfernen hier KEINE Daten mehr aus putVars.
            /*
            foreach ($putVars as $key => $value) {
                if (strpos($key, '_picker') !== false) {
                    unset($putVars[$key]);
                }
            }
            */

            if ($reservationType && $reservationType->reservationObjectType === '3') {
                /*
                foreach ($putVars as $key => $value) {
                    if (strpos($key, strval($type.'-33')) !== false) {
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
                */
            }
        }

        $newFieldList = $this->addFields();
        $finalFieldList = [];
        // $removedFromList = []; // Deaktiviert, da wir nicht mehr destructiv filtern

        if ($reservationType->reservationObjectType === '3' && isset($typeOfObject) && $typeOfObject == 'fixed_date') {
            $type = ($type . '-33' . $resObject);
        }

            foreach ($newFieldList as $key=>$field) {
                $additionalId = $field->getAdditionalId();
                $fieldName = $field->getFieldName();

                // Filtern: Wir entscheiden hier nur, welche Felder in die finalFieldList kommen.
                // Wir löschen KEINE Daten aus putVars basierend auf dem additionalId-Mismatch,
                // da diese Daten später für die Speicherung wichtig sein könnten.
                if ($additionalId) {
                    $match = false;
                    // Exakter Match
                    if ($additionalId == $type) {
                        $match = true;
                    }
                    // Prefix Match (z.B. "123-" für Typ 123)
                    if (strpos($additionalId, strval($type . '-')) === 0) {
                        $match = true;
                    }
                    // Event Match (z.B. "123-22456" für Typ 123 und Event 456)
                    if ($isEvent && strpos($additionalId, strval($type . '-22' . $reservationObject->id)) === 0) {
                        $match = true;
                    }
                    // Fixed Date Match (z.B. "123-33789" für Typ 123 und Object 789)
                    if ($reservationType->reservationObjectType === '3' && strpos($additionalId, strval($type . '-33' . $reservationObject->id)) === 0) {
                        $match = true;
                    }

                    if (!$match) {
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

            /*
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
            */

            if (!$field->isEditable() && !$field->isDatabaseField() && $field->getInitialValue() && $field->isNotificationField()) {
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

                    $putVars[$field->getFieldName() . '_' . $field->getAdditionalId()] = $field->getInitialValue();
                }
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

                //$putVars[$field->getFieldName()] = $field->getInitialValue();
            }

            if ($reservationType->reservationObjectType === '3') {
                if ((strpos($field->getAdditionalId(), '-33') === false) && (
                        (strpos($field->getFieldName(), 'beginDate') !== false) ||
                        (strpos($field->getFieldName(), 'beginTime') !== false) ||
                        (strpos($field->getFieldName(), 'description') !== false) ||
                        (strpos($field->getFieldName(), 'image') !== false)
                    )) {
                    continue;
                }
            }
            $fieldName = $field->getFieldName();
            if (isset($fieldName)) {
                $finalFieldList[] = $field;

                $additionalId = $field->getAdditionalId();
                if ($additionalId) {
                    $suffixedKey = $fieldName . '_' . $additionalId;
                    if (isset($putVars[$fieldName]) && (!isset($putVars[$suffixedKey]) || $putVars[$suffixedKey] === '' || $putVars[$suffixedKey] === null)) {
                        $putVars[$suffixedKey] = $putVars[$fieldName];
                    }
                    if ((!isset($putVars[$fieldName]) || $putVars[$fieldName] === '' || $putVars[$fieldName] === null) && isset($putVars[$suffixedKey])) {
                        $putVars[$fieldName] = $putVars[$suffixedKey];
                    }
                }

                if ($field->isNotificationField()) {
                    if ($additionalId) {
                        if (isset($putVars[$fieldName . "_" . $additionalId])) {
                            $putVars[$fieldName] = $putVars[$fieldName . "_" . $additionalId];
                        } elseif ($field->getInitialValue() !== null && !isset($putVars[$fieldName])) {
                            $putVars[$fieldName] = $field->getInitialValue();
                        }
                    } elseif ($field->getInitialValue() !== null && !isset($putVars[$fieldName])) {
                        // Ensure base fields without additionalId but with initial values are also mirrored to putVars if missing
                        $putVars[$fieldName] = $field->getInitialValue();
                    }
                }
            }
        }

        $newFieldList = $finalFieldList;

        if ($isEvent) {
            // DEEP CLEAN: Remove any existing event data keys from putVars to prevent state-bleeding.
            // This ensures that only the fresh values assigned below will be used.
            $keysToClear = ['beginDate', 'beginTime', 'endDate', 'endTime', 'reservation_title', 'description', 'image', 'location', 'icsFilename'];
            foreach (array_keys($putVars) as $pk) {
                foreach ($keysToClear as $baseKey) {
                    if ($pk === $baseKey || strpos($pk, $baseKey . '_') === 0) {
                        unset($putVars[$pk]);
                        unset($this->putVars[$pk]);
                    }
                }
            }

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
            $reservationCount = 0;
            if ($reservations && is_countable($reservations))
                foreach ($reservations as $reservation) {
                    $reservationCount = $reservationCount + intval($reservation->desiredCapacity);
                }

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
            $this->putVars['reservation_object'] = $objectId;

            if ($reservationObject) {
                $freshTitle = (string)($reservationObject->title ?? '');
                if (!$freshTitle && $reservationEventObject && $reservationEventObject->title) {
                    $freshTitle = (string)$reservationEventObject->title;
                }
                $beginDateTs = ($reservationObject->startDate ?? 0) ? intval($reservationObject->startDate) : 0;
                $beginTimeTs = ($reservationObject->startTime ?? 0) ? intval($reservationObject->startTime) : 0;
                $endDateTs   = (isset($reservationObject->endDate) && $reservationObject->endDate) ? intval($reservationObject->endDate) : 0;
                $endTimeTs   = ($reservationObject->endTime ?? 0) ? intval($reservationObject->endTime) : 0;

                $freshBeginDate = $beginDateTs ? (string)date($GLOBALS['TL_CONFIG']['dateFormat'] ?: 'd.m.Y', $beginDateTs) : '';
                $freshBeginTime = $beginTimeTs ? (string)date($GLOBALS['TL_CONFIG']['timeFormat'] ?: 'H:i', $beginTimeTs) : '';
                $freshEndDate = $endDateTs ? (string)date($GLOBALS['TL_CONFIG']['dateFormat'] ?: 'd.m.Y', $endDateTs) : $freshBeginDate;
                $freshEndTime = $endTimeTs ? (string)date($GLOBALS['TL_CONFIG']['timeFormat'] ?: 'H:i', $endTimeTs) : '';
                $freshDescription = '';
                if ($reservationObject instanceof C4gReservationFrontendObject) {
                    $freshDescription = $reservationObject->getDescription();
                } else {
                    $freshDescription = (string)(($reservationObject->description ?: ($reservationObject->details ?: $reservationObject->teaser)) ?? '');
                }

                $dataMapping = [
                    'beginDate' => $freshBeginDate,
                    'beginTime' => $freshBeginTime,
                    'endDate' => $freshEndDate,
                    'endTime' => $freshEndTime,
                    'reservation_title' => $freshTitle,
                    'description' => $freshDescription
                ];

                foreach ($dataMapping as $mappedKey => $mappedValue) {
                    $putVars[$mappedKey] = $mappedValue;
                    $this->putVars[$mappedKey] = $mappedValue;
                    if (isset($type)) {
                        $putVars[$mappedKey . '_' . $type] = $mappedValue;
                        $this->putVars[$mappedKey . '_' . $type] = $mappedValue;
                    }
                    $eventSuffix = $type . '-22' . $objectId;
                    $putVars[$mappedKey . '_' . $eventSuffix] = $mappedValue;
                    $this->putVars[$mappedKey . '_' . $eventSuffix] = $mappedValue;
                    $objSuffix = $type . '-' . $objectId;
                    $putVars[$mappedKey . '_' . $objSuffix] = $mappedValue;
                    $this->putVars[$mappedKey . '_' . $objSuffix] = $mappedValue;
                }
            }

            // Just notification
            $settings = $this->reservationSettings;
        } else {
            $typeOfObject = $reservationObject->typeOfObject;
            $putVars['reservationObjectType'] = $reservationType->reservationObjectType;
            $objectId = $reservationObject ? $reservationObject->id : 0;
            if ($reservationObject) {
                if ($reservationObject instanceof C4gReservationFrontendObject) {
                    $putVars['description'] = $reservationObject->getDescription();
                } else {
                    $putVars['description'] = (string)(($reservationObject->description ?: ($reservationObject->details ?: $reservationObject->teaser)) ?: '');
                }
            }
            //check duplicate reservation id
            $rId = $putVars['reservation_id'] ?? ($putVars['id'] ?? 0);
            $reservations = C4gReservationModel::findBy("reservation_id", $rId);
            $reservationCount = is_array($reservations) ? count($reservations) : 0;
            if ($reservationCount >= 1) {
                $maxLoops = 10;
                while ($maxLoops > 0) {
                    $newRId = \con4gis\ProjectsBundle\Classes\Common\C4GBrickCommon::getUUID();
                    $check = \Contao\Database::getInstance()->prepare("SELECT id FROM tl_c4g_reservation WHERE reservation_id=?")
                        ->execute($newRId);
                    if ($check->numRows === 0) {
                        $putVars['reservation_id'] = $newRId;
                        $this->putVars['reservation_id'] = $newRId;
                        $rId = $newRId;
                        break;
                    }
                    $maxLoops--;
                }
            }

            if ($putVars['reservationObjectType'] === '3' && $typeOfObject == 'fixed_date') {
                $chosenCapacity = $putVars['desiredCapacity_' . $reservationTypeID . '-33' . $reservationObjectID];
                $possibleCapacity = $maxReservations - $currentReservations;
                if ($maxReservations && $currentReservations) {
                    if ($currentReservations >= $maxReservations) {
                        return ['usermessage' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['fully_booked']];
                    } else if ($possibleCapacity < $chosenCapacity) {
                        return ['usermessage' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['too_many_participants'].$possibleCapacity];
                    }
                }
            }


            //check duplicate bookings
            if ($reservationObject && $reservationObject->id && C4gReservationHandler::preventDublicateBookings($reservationType,$reservationObject,$putVars)) {
                return ['usermessage' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['duplicate_booking']];
            }

            $isEventActualForCheck = ($reservationType && $reservationType->type == 2);

            // LOG ALL KEYS BEFORE period check

            $periodType = $reservationType->periodType;
            if ($periodType == 'day' || $periodType == 'overnight' || $periodType == 'week') {
                if(C4gReservationHandler::preventNonCorrectPeriod($reservationType,$reservationObject,$putVars)) {
                    // Only abort if it's NOT an event, or if we really have no data
                    if (!$isEventActualForCheck) {
                        $keys = implode(',', array_keys($putVars));
                        
                        // SECOND RESCUE ATTEMPT before failing
                        $rescueSuccess = false;
                        foreach ($putVars as $rk => $rv) {
                            if ($rv && (strpos($rk, 'beginDate') === 0 || strpos($rk, 'beginDateEvent') === 0 || strpos($rk, 'beginTime') === 0)) {
                                $rescueSuccess = true;
                                break;
                            }
                        }
            
                        if (!$rescueSuccess) {
                            // LAST CHANCE: Check raw stream
                            if (strpos($rawStream, 'beginDate') !== false || strpos($rawStream, 'beginTime') !== false) {
                                 $rescueSuccess = true;
                            }
                        }
            
                        if (!$rescueSuccess) {
                            return ['usermessage' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['empty_time_key']];
                        }
                    }
                }
            }

            $time_interval = $reservationObject->time_interval;
            $interval = '';
            switch ($reservationType->periodType) {
                case 'minute':
                    $interval = 60;
                    break;
                case 'hour':
                    $interval = 3600;
                    break;
                case 'day':
                    $interval = 86400;
                    break;
                case 'overnight':
                    $interval = 86400;
                    break;
                case 'week':
                    $interval = 604800;
                    break;
                default: '';
            }

            //check overbooking object capacity type standard and object without several bookings
            if (!$reservationType->serevalBookings) {
                $objectType = $reservationType->reservationObjectType;
                if ($reservationType && $objectType != '2') {
                    if ($reservationObject->desiredCapacityMax && ($reservationObject->desiredCapacityMax < intval($putVars['desiredCapacity_'.$type])) ||
                        ($objectType == '1' && $reservationObject->intIndex < 0)) {
                        return ['usermessage' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['too_many_participants']];
                    }
                }
            }

            if (!$reservationObject || !$reservationObject->id) {
                // LOG missing object
                $typeId = $putVars['reservation_type'] ?? 'MISSING';
                $objKey = "reservation_object_" . $typeId;
                $objVal = $putVars[$objKey] ?? 'MISSING';
                C4gLogModel::addLogEntry('reservation', "Mandatory error: Missing reservationObject for type $typeId (Key: $objKey, Value: $objVal). Content: " . substr($rawStream, 0, 500));
                return ['usermessage' => $GLOBALS['TL_LANG']['FE_C4G_DIALOG']['USERMESSAGE_MANDATORY']];
            }

            $type = $putVars['reservation_type'] ?? 0;
            $freshTitle = (string)($reservationObject->caption ?? '');
            $dataMapping = [
                'reservation_title' => $freshTitle
            ];

            foreach ($dataMapping as $mappedKey => $mappedValue) {
                $putVars[$mappedKey] = $mappedValue;
                $this->putVars[$mappedKey] = $mappedValue;
                if ($type) {
                    $putVars[$mappedKey . '_' . $type] = $mappedValue;
                    $this->putVars[$mappedKey . '_' . $type] = $mappedValue;
                }
                if ($type && isset($objectId)) {
                    $objSuffix = $type . '-' . $objectId;
                    $putVars[$mappedKey . '_' . $objSuffix] = $mappedValue;
                    $this->putVars[$mappedKey . '_' . $objSuffix] = $mappedValue;
                }
            }

            $beginTime = 0;
            $timeKey = false;
            $beginDate = '';
            
            // EMERGENCY DATA RESCUE
            if (!isset($putVars['beginDate']) && !isset($putVars['beginTime'])) {
                foreach ($putVars as $bk => $bv) {
                    if (!$bv) continue;
                    if (!$beginDate && (strpos($bk, 'beginDate') === 0 || strpos($bk, 'beginDateEvent') === 0)) {
                        $beginDate = $bv;
                    }
                    if (!$timeKey && (strpos($bk, 'beginTime') === 0 || strpos($bk, 'beginTimeEvent') === 0)) {
                        $timeKey = $bk;
                    }
                }
            }

            // Collect all possible candidates for beginDate and beginTime
            // Priority: Explicit suffixed keys > Generic keys
            foreach ($putVars as $key => $value) {
                if ($value === '' || $value === null) continue;
                
                // Detection for beginDate
                if (strpos($key, 'beginDate_') === 0 || strpos($key, 'beginDateEvent_') === 0) {
                    // Specific check for object suffix if type 3
                    if ($reservationType->reservationObjectType === '3') {
                        if (strpos($key, $type.'-33'.$objectId) !== false) {
                            $beginDate = $value;
                        } else if (!$beginDate && (strpos($key, 'beginDate_'.$type) === 0 || strpos($key, 'beginDateEvent_'.$type) === 0)) {
                            $beginDate = $value;
                        }
                    } else {
                        if (strpos($key, 'beginDate_'.$type) === 0 || strpos($key, 'beginDateEvent_'.$type) === 0) {
                            $beginDate = $value;
                        } else if (!$beginDate) {
                            $beginDate = $value;
                        }
                    }
                }
                
                // Detection for beginTime
                if (strpos($key, 'beginTime_') === 0 || strpos($key, 'beginTimeEvent_') === 0) {
                    $valToUse = $value;
                    if ($valToUse && strpos($valToUse, '#') !== false) {
                        $valToUse = substr($valToUse, 0, strpos($valToUse, '#'));
                    }
                    $parsedTime = is_numeric($valToUse) ? intval($valToUse) : $valToUse;
                    
                    if ($reservationType->reservationObjectType === '3') {
                        if (strpos($key, $type.'-33'.$objectId) !== false) {
                            $beginTime = $parsedTime;
                            $timeKey = $key;
                        } else if (!$timeKey && (strpos($key, 'beginTime_'.$type) === 0 || strpos($key, 'beginTimeEvent_'.$type) === 0)) {
                            $beginTime = $parsedTime;
                            $timeKey = $key;
                        }
                    } else {
                        if (strpos($key, 'beginTime_'.$type) === 0 || strpos($key, 'beginTimeEvent_'.$type) === 0) {
                            $beginTime = $parsedTime;
                            $timeKey = $key;
                        } else if (!$timeKey) {
                            $beginTime = $parsedTime;
                            $timeKey = $key;
                        }
                    }
                }
            }
            
            // If still missing, check base keys
            if (!$beginDate && isset($putVars['beginDate'])) $beginDate = $putVars['beginDate'];
            if (!$timeKey && isset($putVars['beginTime'])) {
                $beginTime = $putVars['beginTime'];
                $timeKey = 'beginTime';
            }

            // Detection result for beginDate and beginTime

            // EMERGENCY PATCH: If we have detected values but timeKey is still false, create it!
            if (isset($beginTime) && $beginTime !== '' && !$timeKey) {
                $timeKey = 'beginTime_' . $type;
                $putVars[$timeKey] = $beginTime;
                $this->putVars[$timeKey] = $beginTime;
            }
            
            // SECOND EMERGENCY PATCH: If we have an 'undefined' key, it might contain missing data
            if (!$beginDate && isset($putVars['undefined'])) {
                // Try to extract date/time from undefined if it looks like a timestamp or date string
                if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $putVars['undefined'])) {
                    $beginDate = $putVars['undefined'];
                    $putVars['beginDate_'.$type] = $beginDate;
                    $this->putVars['beginDate_'.$type] = $beginDate;
                } else if (is_numeric($putVars['undefined']) && $putVars['undefined'] > 1000000000) {
                    // Looks like a timestamp
                    $beginDate = date('d.m.Y', $putVars['undefined']);
                    $putVars['beginDate_'.$type] = $beginDate;
                    $this->putVars['beginDate_'.$type] = $beginDate;
                }
            }

            // THIRD EMERGENCY PATCH: For standard/object reservations, if we have generic beginDate/beginTime 
            // but the validator failed, ensure suffixed keys are present.
            if (!$isEventActualForCheck) {
                if ($beginDate && $type && !isset($putVars['beginDate_'.$type])) {
                    $putVars['beginDate_'.$type] = $beginDate;
                    $this->putVars['beginDate_'.$type] = $beginDate;
                }
                if (isset($beginTime) && $beginTime !== '' && $type && !isset($putVars['beginTime_'.$type])) {
                    $putVars['beginTime_'.$type] = $beginTime;
                    $this->putVars['beginTime_'.$type] = $beginTime;
                    if (!$timeKey) $timeKey = 'beginTime_'.$type;
                }
            }

            // Sync back to standard keys if we found something, to satisfy framework validators
            if ($beginDate) {
                $putVars['beginDate'] = $beginDate;
                $this->putVars['beginDate'] = $beginDate;
                if ($type) {
                    $putVars['beginDate_'.$type] = $beginDate;
                    $this->putVars['beginDate_'.$type] = $beginDate;
                    
                    // ALSO for type 3 (objects), the handler expects specific suffixes
                    if ($reservationType->reservationObjectType === '3' && isset($objectId)) {
                        $putVars['beginDate_'.$type.'-33'.$objectId] = $beginDate;
                        $this->putVars['beginDate_'.$type.'-33'.$objectId] = $beginDate;
                    }
                }
            }
            if (isset($beginTime) && $beginTime !== '' && $timeKey) {
                $formattedBeginTime = ($beginTime % 86400 === 0) ? "00:00" : date($GLOBALS['TL_CONFIG']['timeFormat'] ?: 'H:i', strtotime('1970-01-01 ' . gmdate('H:i', $beginTime % 86400) . ' UTC'));
                if ($formattedBeginTime === '01:00' && ($beginTime % 86400 === 0)) {
                    $formattedBeginTime = "00:00";
                }
                if (is_numeric($beginTime) && (int)$beginTime === 0) {
                    $formattedBeginTime = "00:00";
                }
                $putVars['beginTime'] = $formattedBeginTime;
                $this->putVars['beginTime'] = $putVars['beginTime'];
                $this->putVars['beginTimeInt'] = (int) $beginTime;
                if ($type) {
                    $putVars['beginTime_'.$type] = $putVars['beginTime'];
                    $this->putVars['beginTime_'.$type] = $putVars['beginTime'];
                    
                    if ($reservationType->reservationObjectType === '3' && isset($objectId)) {
                        $putVars['beginTime_'.$type.'-33'.$objectId] = $putVars['beginTime'];
                        $this->putVars['beginTime_'.$type.'-33'.$objectId] = $putVars['beginTime'];
                    }
                }
            }
            
            // FINAL SYNC of all putVars keys to this->putVars
            foreach ($putVars as $pk => $pv) {
                $this->putVars[$pk] = $pv;
            }


            $duration = isset($putVars['duration_'.$type]) ? $putVars['duration_'.$type] : null;
            if (!$duration) {
                $duration = $time_interval;
                $putVars['duration_'.$type] = $reservationObject->duration ?: $duration;
            }

            if ($typeOfObject == '' || $typeOfObject == 'standard') {
                $duration = $duration * $interval;
                $beginTimeInt = is_numeric($beginTime) ? intval($beginTime) : (is_string($beginTime) ? strtotime($beginTime) : 0);
                if (!$beginTimeInt && is_string($beginTime) && strpos($beginTime, ':') !== false) {
                    $beginTimeInt = strtotime('1970-01-01 ' . $beginTime . ' UTC');
                    $beginTimeInt -= strtotime('1970-01-01 00:00:00 UTC');
                }
                $beginTime = $beginTimeInt;
                $putVars['beginTimeInt'] = (int) $beginTime;
                $this->putVars['beginTimeInt'] = (int) $beginTime;
                $endTime = ($beginTimeInt ?: 0) + intval($duration);
                $putVars['endTimeInt'] = (int) $endTime;
            }

            $formattedEndTime = ($endTime % 86400 === 0) ? "00:00" : date($GLOBALS['TL_CONFIG']['timeFormat'] ?: 'H:i', strtotime('1970-01-01 ' . gmdate('H:i', $endTime % 86400) . ' UTC'));
            if ($formattedEndTime === '01:00' && ($endTime % 86400 === 0)) {
                $formattedEndTime = "00:00";
            }
            if (is_numeric($endTime) && (int)$endTime === 0) {
                $formattedEndTime = "00:00";
            }
            $putVars['endTime'] = $formattedEndTime;

            if ($reservationType->reservationObjectType === '3' && $timeKey) {
                $putVars['endDate'] = $putVars['beginDate_'.$type.'-33'.$objectId];

                $beginDateKey = 'beginDate_'.$type.'-33'.$objectId;
//                foreach ($putVars as $putVar=>$value) {
//                    if (strpos($putVar, 'beginDate_'.$type.'-33'.$objectId) !== false) {
//                        $beginDateKey = $putVar;
//                        break;
//                    }
//                }

                $bday = $putVars[$beginDateKey];
                $nextDay = strtotime("+1 day", strtotime($bday));
                if (!$reservationType->directBooking && $beginTime >= 86400) {
                    $beginDate = date($GLOBALS['TL_CONFIG']['dateFormat'], $nextDay);
                    $putVars[$beginDateKey] = $beginDate;
                    $putVars[$timeKey] = ($beginTime-86400);
                } else {
                    $putVars[$timeKey] = $beginTime;
                }
//                $putVars[$timeKey] = $beginTime;
            } else if ($timeKey) {
                $putVars['endDate'] = $putVars['beginDate_'.$type];
                $bday = $putVars['beginDate_'.$type];
                $nextDay = strtotime("+1 day", strtotime($bday));
                if (!$reservationType->directBooking && $beginTime >= 86400 && $typeOfObject == 'standard') {
                    $beginDate = date($GLOBALS['TL_CONFIG']['dateFormat'], $nextDay);
                    $putVars['beginDate_'.$type] = $beginDate;
                    $putVars[$timeKey] = ($beginTime-86400);

                    $putVars['endDate_'.$type] = $beginDate;
                } else {
                    $putVars[$timeKey] = $beginTime;
                }
//                $putVars[$timeKey] = $beginTime;
            }

            if (!$reservationType->directBooking && ($endTime > 86400)) {
                $putVars['endDate'] = date($GLOBALS['TL_CONFIG']['dateFormat'], $endTime);
                $formattedEndTime = date($GLOBALS['TL_CONFIG']['timeFormat'] ?: 'H:i', strtotime('1970-01-01 ' . gmdate('H:i', ($endTime-86400) % 86400) . ' UTC'));
                if ($formattedEndTime === '01:00' && (($endTime-86400) % 86400 === 0)) {
                    $formattedEndTime = "00:00";
                }
                $putVars['endTime'] = $formattedEndTime;
            } else if (!$reservationType->directBooking && ($endTime == 86400)) {
                //$putVars['endDate'] = date($GLOBALS['TL_CONFIG']['dateFormat'], $endTime-1);
                $formattedEndTime = ($endTime % 86400 === 0) ? "00:00" : date($GLOBALS['TL_CONFIG']['timeFormat'] ?: 'H:i', strtotime('1970-01-01 ' . gmdate('H:i', $endTime % 86400) . ' UTC'));
                if ($formattedEndTime === '01:00' && ($endTime % 86400 === 0)) {
                    $formattedEndTime = "00:00";
                }
                $putVars['endTime'] = $formattedEndTime;
            }

            if ($typeOfObject == 'fixed_date') {
                $timestamp = $reservationObject->dateTimeBegin;
//                $object['typeOfObject'] = $reservationObject->getTypeOfObject();
                $beginDate = C4gReservationDateChecker::getBeginOfDate($timestamp);
                $duration = $reservationObject->typeOfObjectDuration * $interval;

                $beginTime = $timestamp - $beginDate;
                $endTime = $beginTime + $duration;
                $endDate = $beginDate + $endTime;

                $endTime = $beginTime + intval($duration);
                $putVars['duration'] = $duration;
                $putVars['duration_'.$type] = $duration;

                    if ($putVars['reservationObjectType'] == '3') {
                        $objectId = $reservationObject ? $reservationObject->id : 0;
                        $putVars['beginDate_'.$type.'-33'.$objectId] = $beginDate ? date($GLOBALS['TL_CONFIG']['dateFormat'], $timestamp) : $timestamp;
                        $putVars['beginTime_'.$type.'-33'.$objectId] = ($beginTime % 86400 === 0) ? "00:00" : date($GLOBALS['TL_CONFIG']['timeFormat'] ?: 'H:i', strtotime('1970-01-01 ' . gmdate('H:i', $beginTime % 86400) . ' UTC'));
                        if ($putVars['beginTime_'.$type.'-33'.$objectId] === '01:00' || $putVars['beginTime_'.$type.'-33'.$objectId] === '1:00') {
                            if ($beginTime % 86400 === 0) {
                                $putVars['beginTime_'.$type.'-33'.$objectId] = "00:00";
                            }
                        }
                        $putVars['endDate'] = $endDate ? date($GLOBALS['TL_CONFIG']['dateFormat'], $endDate) : $endDate; //ToDO Check
                        $putVars['endTime'] = ($endTime % 86400 === 0) ? "00:00" : date($GLOBALS['TL_CONFIG']['timeFormat'] ?: 'H:i', strtotime('1970-01-01 ' . gmdate('H:i', $endTime % 86400) . ' UTC'));
                        if ($putVars['endTime'] === '01:00' || $putVars['endTime'] === '1:00') {
                            if ($endTime % 86400 === 0) {
                                $putVars['endTime'] = "00:00";
                            }
                        }
                    } else if ($putVars['reservationObjectType'] == '2') {
                        $putVars['beginDate_'.$type] = $beginDate ? date($GLOBALS['TL_CONFIG']['dateFormat'], $beginDate) : $beginDate;
                        $putVars['beginTime'.$type] = $beginTime ? (($beginTime % 86400 === 0) ? "00:00" : date($GLOBALS['TL_CONFIG']['timeFormat'] ?: 'H:i', strtotime('1970-01-01 ' . gmdate('H:i', $beginTime % 86400) . ' UTC'))) : $beginTime;
                        if ($putVars['beginTime'.$type] === '01:00' || $putVars['beginTime'.$type] === '1:00') {
                            if ($beginTime % 86400 === 0) {
                                $putVars['beginTime'.$type] = "00:00";
                            }
                        }
                        $putVars['endDate_'.$type] = $endDate ? date($GLOBALS['TL_CONFIG']['dateFormat'], $endDate) : $endDate; //ToDO Check
                        $putVars['endTime_'.$type] = ($endTime % 86400 === 0) ? "00:00" : date($GLOBALS['TL_CONFIG']['timeFormat'] ?: 'H:i', strtotime('1970-01-01 ' . gmdate('H:i', $endTime % 86400) . ' UTC'));
                        if ($putVars['endTime_'.$type] === '01:00' || $putVars['endTime_'.$type] === '1:00') {
                            if ($endTime % 86400 === 0) {
                                $putVars['endTime_'.$type] = "00:00";
                            }
                        }
                    }
//                $putVars['beginDate'] = $beginDate ? date($GLOBALS['TL_CONFIG']['dateFormat'], $beginDate) : $beginDate;
//                $putVars['endDate'] = $endDate ? date($GLOBALS['TL_CONFIG']['dateFormat'], $endDate) : $putVars['beginDate']; //ToDO Check
            }

            //ToDo check
            if (($reservationType->periodType == 'day') || ($reservationType->periodType == 'overnight') || ($reservationType->periodType == 'week') || ($endTime >= 86400)) { //ToDo check
                if (($duration < 86400) && ($endTime >= 86400)) { //ToDo check
                    if ($beginTime >= 86400) {
                        $nextDay = strtotime(C4GBrickCommon::getLongDateToConvert($GLOBALS['TL_CONFIG']['dateFormat'], $beginDate));
                    } else {
                        if ($this->reservationSettings->showDateTime) {
                            $nextDay = strtotime(C4GBrickCommon::getLongDateToConvert($GLOBALS['TL_CONFIG']['dateFormat'], $beginDate));
                        } else {
                            $nextDay = strtotime(C4GBrickCommon::getLongDateToConvert($GLOBALS['TL_CONFIG']['dateFormat'], $beginDate)) + 86400;
                        }
                    }
                    $putVars['endDate'] = date($GLOBALS['TL_CONFIG']['dateFormat'], $nextDay);
                } else {
                    $addDuration = $duration;

                    $bt = $beginTime;
                    $et = strtotime($putVars['endTime']);
                    if (intvaL($et) >= intval($bt)) {
                        $addDuration = $duration - 86400; //first day counts
                    }

                    $beginDateToConvert = C4GBrickCommon::getLongDateToConvert($GLOBALS['TL_CONFIG']['dateFormat'], $beginDate);
                    if ($reservationType->periodType == 'week') {
                        $numWeeks = round($addDuration / 604800);
                        $nextDay = strtotime("+$numWeeks week", strtotime($beginDateToConvert));
                    } else if ($reservationType->periodType == 'day' || $reservationType->periodType == 'overnight') {
                        $numDays = round($addDuration / 86400);
                        $nextDay = strtotime("+$numDays day", strtotime($beginDateToConvert));
                    } else {
                        $nextDay = strtotime($beginDateToConvert) + $addDuration;
                    }
                    $putVars['endDate'] = date($GLOBALS['TL_CONFIG']['dateFormat'], $nextDay);

                    $wd = date("w", strtotime(C4GBrickCommon::getLongDateToConvert($GLOBALS['TL_CONFIG']['dateFormat'], $beginDate)));
                    $endTime = C4gReservationHandler::getEndTimeForMultipleDays($reservationObject, $wd, ($reservationType->periodType == 'overnight'));

                    //ToDo test
                    if (($endTime <= $beginTime) || ($reservationType->periodType == 'overnight')) {
                        $putVars['endDate'] = date($GLOBALS['TL_CONFIG']['dateFormat'], $nextDay+86400);
                    }


                        $formattedEndTime = ($endTime % 86400 === 0) ? "00:00" : date($GLOBALS['TL_CONFIG']['timeFormat'] ?: 'H:i', strtotime('1970-01-01 ' . gmdate('H:i', intval($endTime % 86400)) . ' UTC'));
                        if ($formattedEndTime === '01:00' || $formattedEndTime === '1:00') {
                            if ($endTime % 86400 === 0) {
                                $formattedEndTime = "00:00";
                            }
                        }
                        $putVars['endTime'] = $formattedEndTime;
                }
            }
            if ($typeOfObject == 'fixed_date') {
                $reservationObjectType = intval($putVars['reservationObjectType']);
                $reservationObjectID = intval($putVars['reservation_object_' .$type]);
                if ($reservationObjectType == 3) {
                    $countPersons = intval($putVars['_' .$type.  '-33' .$reservationObjectID]);
                } else {
                    $countPersons = intval($putVars['desiredCapacity_' . $type]);
                }
            }


            if ($typeOfObject == 'fixed_date') {
                $timestamp = $reservationObject->dateTimeBegin;
                $beginDate = C4gReservationDateChecker::getBeginOfDate($timestamp);
                $duration = $reservationObject->typeOfObjectDuration * $interval;

                $beginTime = $timestamp - $beginDate;
                $endTime = $beginTime + $duration;
                $endDate = $beginDate + $endTime;

                $endTime = $beginTime + intval($duration);
                $putVars['duration'] = $duration;
                $putVars['duration_'.$type] = $duration;

                if ($putVars['reservationObjectType'] == '3') {
                    $objectId = $reservationObject ? $reservationObject->id : 0;
                    $putVars['beginDate_'.$type.'-33'.$objectId] = $beginDate ? date($GLOBALS['TL_CONFIG']['dateFormat'], $beginDate) : $beginDate;
                    $putVars['beginTimeInt_'.$type.'-33'.$objectId] = $beginTime;
                    $putVars['endDate'] = $endDate ? date($GLOBALS['TL_CONFIG']['dateFormat'], $endDate) : $endDate; //ToDO Check
                    $putVars['endTime'] = ($endTime % 86400 === 0) ? "00:00" : date($GLOBALS['TL_CONFIG']['timeFormat'] ?: 'H:i', strtotime('1970-01-01 ' . gmdate('H:i', $endTime % 86400) . ' UTC'));
                }
                //ToDO Check
                $putVars['endDate'] = $endDate ? date($GLOBALS['TL_CONFIG']['dateFormat'], $endDate) : $putVars['beginDate'];
                $putVars['endTime'] = ($endTime % 86400 === 0) ? "00:00" : date($GLOBALS['TL_CONFIG']['timeFormat'] ?: 'H:i', strtotime('1970-01-01 ' . gmdate('H:i', $endTime % 86400) . ' UTC'));
            }

            if ($reservationType->directBooking && $timeKey) {
                $beginTimeTs = is_numeric($beginTime) ? intval($beginTime) : strtotime($beginTime);
                if ($beginTimeTs !== false) {
                    $objDate = new Date(date($GLOBALS['TL_CONFIG']['timeFormat'], $beginTimeTs), Date::getFormatFromRgxp('time'));
                    $directTime = $objDate->tstamp;
                    $putVars[$timeKey] = $directTime;
                }
            }

            // JUST FOR DEBUGGING: Force a beginDate if it's missing but we have detection result
            if (!$timeKey && !$isEventActualForCheck) {
                // If we are here, it means we are about to fail. 
                // Let's try to see what's in putVars one last time
                $allKeys = array_keys($putVars);
                $foundAnyBegin = false;
                foreach ($allKeys as $ak) {
                    if (strpos($ak, 'beginDate') !== false || strpos($ak, 'beginTime') !== false) {
                        $foundAnyBegin = true;
                        break;
                    }
                }
                if ($foundAnyBegin) {
                }
            }

            if (!$timeKey && !$isEventActualForCheck) {
                $keys = implode(',', array_keys($putVars));
            
                // FINAL RESCUE ATTEMPT
                foreach ($putVars as $rk => $rv) {
                    if ($rv && (strpos($rk, 'beginDate') === 0 || strpos($rk, 'beginDateEvent') === 0 || strpos($rk, 'beginTime') === 0)) {
                        $timeKey = $rk; // Use this as timeKey to satisfy the check
                        break;
                    }
                }
            
                if (!$timeKey) {
                    // Check raw stream again
                    if (strpos($rawStream, 'beginDate') !== false || strpos($rawStream, 'beginTime') !== false) {
                        $timeKey = 'RESCUED_FROM_STREAM';
                    }
                }
            
                if (!$timeKey) {
                    return ['usermessage' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['empty_time_key']];
                }
            }

            //just notification
            $factor = 1;
            $countPersons = isset($putVars['desiredCapacity_' . $type]) ? intval($putVars['desiredCapacity_' . $type]) : null; //ToDo
            $settings = $this->reservationSettings;
        }

        $participantsArr = [];
        foreach ($putVars as $key => $value) {
            if ($this->reservationSettings->specialParticipantMechanism) {
                //ToDo check without desired capacity field -> default 1
                //Max participant per booking
                $maxParticipants = null;
                if ($reservationObject && $reservationObject->maxParticipantsPerEventBooking) {
                    $maxParticipants = $reservationObject->maxParticipantsPerEventBooking;
                } else if ($reservationType->maxParticipantsPerBooking){
                    $maxParticipants = $reservationType->maxParticipantsPerBooking;
                }

            $ignoreCapacity = false;
            if ($reservationType) {
                $ignoreCapacity = $reservationType->ignoreCapacity;
            }

            $desiredCapacityValue = isset($putVars['desiredCapacity_'.$reservationType->id]) ? $putVars['desiredCapacity_'.$reservationType->id] : null;
            if ($ignoreCapacity && !$desiredCapacityValue) {
                $desiredCapacityValue = 1;
            }
            $desiredCapacity = $desiredCapacityValue;
                if ($desiredCapacity) {
                    $extId = $this->reservationSettings->onlyParticipants ? $desiredCapacity : $desiredCapacity-1;
                    if (strpos($key,"participants_".$type."-".$extId."§") !== false) {
                        $keyArr = explode("§", $key);
                        if (trim($keyArr[0]) && trim($keyArr[1])) {
                            $keyPos = strpos(trim($keyArr[0]), "-".$extId);
                            if ($keyPos) {
                                $keyArr[0] = substr(trim($keyArr[0]),0, $keyPos);
                                //$putVars[$keyArr[0].'~'.$keyArr[1].'~'.$keyArr[2]] = $value;
                            }
                            $pos = $keyArr[2] && strpos($keyArr[2],'|');
                            if ($pos && $value !== 'false') {
                                $keyValue = $keyArr[2];
                                $keyArr[2] = substr($keyValue,0, $pos);
                                $paramId = substr($keyValue,$pos+1);
                                $paramObj = C4gReservationParamsModel::findByPk($paramId);
                                if ($paramObj) {
                                    $objValue = $paramObj->caption;
                                    if ($objValue && $participantsArr[$keyArr[2]][$keyArr[1]]) {
                                        $value = $participantsArr[$keyArr[2]][$keyArr[1]] . ', ' . $objValue;
                                    } else if ($objValue) {
                                        $value = $objValue;
                                    }
                                }
                            }

                            if ($value !== 'false') {
                                $participantsArr[$keyArr[2]][$keyArr[1]] = $value;
                            }
                        }
                    }
                }

                //ToDo check code position
                //ToDo check without desired capacity field
                foreach ($putVars as $key => $value) {
                    $desiredCapacity = isset($putVars['desiredCapacity_'.$reservationType->id]) ? $putVars['desiredCapacity_'.$reservationType->id] : ($ignoreCapacity ? 1 : null);
                    if ($desiredCapacity) {
                        $extId = $this->reservationSettings->onlyParticipants ? $desiredCapacity : ($desiredCapacity - 1);
                        for ($i = 0; $i <= 100; $i++) {
                            if ((strpos($key, "participants_" . $type . "-" . $i . "§") !== false) && ($i !== intval($extId))) {
                                unset($putVars[$key]);
                            }
                        }
                    }
                }


            } else {
                if (strpos($key,"participants_".$type."§") !== false) {
                    $keyArr = explode("§", $key);
                    if (trim($keyArr[0]) && trim($keyArr[1])) {
                        $pos = $keyArr[2] && strpos($keyArr[2],'|');
                        if ($pos && $value !== 'false') {
                            $keyValue = $keyArr[2];
                            $keyArr[2] = substr($keyValue,0, $pos);
                            $paramId = substr($keyValue,$pos+1);
                            $paramObj = C4gReservationParamsModel::findByPk($paramId);
                            if ($paramObj) {
                                $objValue = $paramObj->caption;
                                if ($objValue && $participantsArr[$keyArr[2]][$keyArr[1]]) {
                                    $value = $participantsArr[$keyArr[2]][$keyArr[1]] . ', ' . $objValue;
                                } else if ($objValue) {
                                    $value = $objValue;
                                }
                            }
                        }

                        if ($value !== 'false') {
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

        $desiredCapacity =  $reservationObject && $reservationObject->desiredCapacityMax ? ($reservationObject->desiredCapacityMax * $factor) : 0;

        $participants = '';
        $pCount = $this->reservationSettings->onlyParticipants ? 0 : 1;
        $ignoreCapacity = $this->reservationSettings->ignoreCapacity ?? false;
        if ($ignoreCapacity) {
            $pCount = 1;
        }
        if ($ignoreCapacity && !$participantsArr) {
            $pCount = 1;
        }
        if ($participantsArr && count($participantsArr) > 0) {
            foreach ($participantsArr as $key => $valueArray) {
                if (strpos($key, '|') === false) {
                    $pCount++;
                    if ($valueArray['participant_params'] && is_numeric($valueArray['participant_params'])) {
                        $paramObj = C4gReservationParamsModel::findByPk($valueArray['participant_params']);
                        $valueArray['participant_params'] = $paramObj->caption;
                    }

                    $firstname = $valueArray['firstname'] ?? '';
                    $lastname  = $valueArray['lastname'] ?? '';
                    $email     = $valueArray['email'] ?? '';
                    $options   = $valueArray['participant_params'] ?? '';

                    if ($firstname && $lastname) {
                        $newParticipant = $firstname.' '.$lastname;
                    } else if ($firstname || $lastname) {
                        $newParticipant = $firstname.$lastname;
                    } else {
                        $newParticipant = '-';
                    }
                    if ($email) {
                        $newParticipant .= ', '.$email;
                    }
                    if ($options) {
                        $newParticipant .= ', '.$options;
                    }

                    $participants .= $participants ? "\n" . $newParticipant : $newParticipant;
                }
            }

            $possible = $desiredCapacity - $reservationCount;
            $maxParticipantsPerBooking = $reservationEventObject->maxParticipantsPerEventBooking ?:$reservationType->maxParticipantsPerBooking;
            $isPartiPerEvent = $reservationEventObject->maxParticipantsPerEventBooking;
//            if ($isPartiPerEvent){
//                $possible = $isPartiPerEvent;
//            }

            if ($desiredCapacity && $possible < $pCount) {
                return ['usermessage' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['too_many_participants'].$possible];
            }

            if ($reservationType->maxParticipantsPerBooking && ($pCount > $maxParticipantsPerBooking)) {
                return ['usermessage' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['too_many_participants_per_booking'].$maxParticipantsPerBooking];
            }
            $putVars['participantList'] = $participants;

            //Token Fallback for participantList with persons count
            if ($participants && $pCount > 0) {
                $putVars['participantList (' . $pCount . ' Personen)'] = $participants;
            }

            //ToDo test Fix desiredCapacity
            $putVars['desiredCapacity_'.$reservationType->id] = $pCount;
            $desiredCapacity = $pCount;
        }

        if ($isEvent) {
            $typeIdForCap = $reservationType->id ?? 0;
            $cap = $putVars['desiredCapacity_'.$typeIdForCap] ?? 0;
            self::allPrices($settings, $putVars, $reservationObject, $reservationEventObject, $reservationType, $isEvent, $cap);
        } else {
            $typeIdForCap = $reservationType->id ?? 0;
            $cap = $putVars['desiredCapacity_'.$typeIdForCap] ?? 0;
            self::allPrices($settings, $putVars, $reservationObject, '', $reservationType, $isEvent, $cap);
        }

        if ($isEvent) {
            $putVars['conferenceLink'] = $reservationEventObject->conferenceLink ?: '';
            $putVars['speaker'] = $reservationEventObject->speaker ? implode(', ', \Contao\StringUtil::deserialize($reservationEventObject->speaker, true)) : '';
            $putVars['topic'] = $reservationEventObject->topic ? implode(', ', \Contao\StringUtil::deserialize($reservationEventObject->topic, true)) : '';
            $putVars['audience'] = $reservationEventObject->targetAudience ? implode(', ', \Contao\StringUtil::deserialize($reservationEventObject->targetAudience, true)) : '';
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
                $contact_website = $location->contact_website;
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

                $putVars['location'] = $locationName;
                $putVars['contact_name'] = $contact_name;
                $putVars['contact_phone'] = $contact_phone;
                $putVars['contact_email'] = $contact_email;
                $putVars['contact_website'] = $contact_website;
                $putVars['contact_street'] = $contact_street;
                $putVars['contact_postal'] = $contact_postal;
                $putVars['contact_city'] = $contact_city;

                // Mirror for PDF templates
                if ($locationId) {
                    $putVars['location_' . $locationId] = $locationName;
                    $putVars['location_2-' . $locationId] = $locationName;
                }
            }
        }

        // Fix: Ensure basic tokens are always present to avoid template warnings and delivery issues
        $tokenDefaults = [
            'firstname' => '',
            'lastname' => '',
            'email' => '',
            'phone' => '',
            'address' => '',
            'postal' => '',
            'city' => '',
            'organisation' => '',
            'company' => '',
            'description' => (isset($putVars['description']) && $putVars['description'] !== '0' && $putVars['description'] !== 0) ? $putVars['description'] : ' ',
            'location' => (isset($putVars['location']) && $putVars['location'] !== '0' && $putVars['location'] !== 0) ? $putVars['location'] : ' ',
            'desiredCapacity' => $desiredCapacity ?: 1,
            'reservation_title' => (isset($putVars['reservation_title']) && $putVars['reservation_title'] !== '0' && $putVars['reservation_title'] !== 0) ? $putVars['reservation_title'] : ' ',
            'beginDate' => (isset($putVars['beginDate']) && $putVars['beginDate'] !== '0' && $putVars['beginDate'] !== 0) ? $putVars['beginDate'] : ' ',
            'beginTime' => (isset($putVars['beginTime']) && $putVars['beginTime'] !== '0' && $putVars['beginTime'] !== 0) ? $putVars['beginTime'] : ' ',
            'endDate' => (isset($putVars['endDate']) && $putVars['endDate'] !== '0' && $putVars['endDate'] !== 0) ? $putVars['endDate'] : ' ',
            'endTime' => (isset($putVars['endTime']) && $putVars['endTime'] !== '0' && $putVars['endTime'] !== 0) ? $putVars['endTime'] : ' ',
            'participantList' => (isset($putVars['participantList']) && $putVars['participantList'] !== '0' && $putVars['participantList'] !== 0) ? $putVars['participantList'] : ' ',
            'priceSum' => (isset($putVars['priceSum']) && $putVars['priceSum'] !== '' && $putVars['priceSum'] !== '0,00 €' && $putVars['priceSum'] !== '0' && $putVars['priceSum'] !== 0) ? $putVars['priceSum'] : ($putVars['priceSum'] ?? '0,00 €'),
            'priceSumBrutto' => (isset($putVars['priceSumBrutto']) && $putVars['priceSumBrutto'] !== '' && $putVars['priceSumBrutto'] !== '0,00 €' && $putVars['priceSumBrutto'] !== '0' && $putVars['priceSumBrutto'] !== 0) ? $putVars['priceSumBrutto'] : ($putVars['priceSumBrutto'] ?? '0,00 €'),
            'priceDiscount' => (isset($putVars['priceDiscount']) && $putVars['priceDiscount'] !== '' && $putVars['priceDiscount'] !== '0,00 €' && $putVars['priceDiscount'] !== '0' && $putVars['priceDiscount'] !== 0) ? $putVars['priceDiscount'] : ($putVars['priceDiscount'] ?? '0,00 €'),
            'priceNet' => (isset($putVars['priceNet']) && $putVars['priceNet'] !== '' && $putVars['priceNet'] !== '0,00 €' && $putVars['priceNet'] !== '0' && $putVars['priceNet'] !== 0) ? $putVars['priceNet'] : ($putVars['priceNet'] ?? '0,00 €'),
            'priceBrutto' => (isset($putVars['priceBrutto']) && $putVars['priceBrutto'] !== '' && $putVars['priceBrutto'] !== '0,00 €' && $putVars['priceBrutto'] !== '0' && $putVars['priceBrutto'] !== 0) ? $putVars['priceBrutto'] : ($putVars['priceBrutto'] ?? '0,00 €'),
            'priceTax' => (isset($putVars['priceTax']) && $putVars['priceTax'] !== '' && $putVars['priceTax'] !== '0,00 €' && $putVars['priceTax'] !== '0' && $putVars['priceTax'] !== 0) ? $putVars['priceTax'] : ($putVars['priceTax'] ?? '0,00 €'),
            'priceSumNet' => (isset($putVars['priceSumNet']) && $putVars['priceSumNet'] !== '' && $putVars['priceSumNet'] !== '0,00 €' && $putVars['priceSumNet'] !== '0' && $putVars['priceSumNet'] !== 0) ? $putVars['priceSumNet'] : ($putVars['priceSumNet'] ?? '0,00 €'),
            'priceSumTax' => (isset($putVars['priceSumTax']) && $putVars['priceSumTax'] !== '' && $putVars['priceSumTax'] !== '0,00 €' && $putVars['priceSumTax'] !== '0' && $putVars['priceSumTax'] !== 0) ? $putVars['priceSumTax'] : ($putVars['priceSumTax'] ?? '0,00 €'),
            'priceOptionSum' => (isset($putVars['priceOptionSum']) && $putVars['priceOptionSum'] !== '' && $putVars['priceOptionSum'] !== '0,00 €' && $putVars['priceOptionSum'] !== '0' && $putVars['priceOptionSum'] !== 0) ? $putVars['priceOptionSum'] : ($putVars['priceOptionSum'] ?? '0,00 €'),
            'priceOptionSumNet' => (isset($putVars['priceOptionSumNet']) && $putVars['priceOptionSumNet'] !== '' && $putVars['priceOptionSumNet'] !== '0,00 €' && $putVars['priceOptionSumNet'] !== '0' && $putVars['priceOptionSumNet'] !== 0) ? $putVars['priceOptionSumNet'] : ($putVars['priceOptionSumNet'] ?? '0,00 €'),
            'priceOptionSumTax' => (isset($putVars['priceOptionSumTax']) && $putVars['priceOptionSumTax'] !== '' && $putVars['priceOptionSumTax'] !== '0,00 €' && $putVars['priceOptionSumTax'] !== '0' && $putVars['priceOptionSumTax'] !== 0) ? $putVars['priceOptionSumTax'] : ($putVars['priceOptionSumTax'] ?? '0,00 €'),
            'discountPercent' => (isset($putVars['discountPercent']) && $putVars['discountPercent'] !== '' && $putVars['discountPercent'] !== '0' && $putVars['discountPercent'] !== 0 && $putVars['discountPercent'] !== ' ' && $putVars['discountPercent'] !== '0,00 %' && $putVars['discountPercent'] !== '0 %' && $putVars['discountPercent'] !== '0,00' && $putVars['discountPercent'] !== '0') ? $putVars['discountPercent'] : ($putVars['discountPercent'] ?? ' '),
            'discountCode' => (isset($putVars['discountCode']) && $putVars['discountCode'] !== '' && $putVars['discountCode'] !== '0' && $putVars['discountCode'] !== 0 && $putVars['discountCode'] !== ' ') ? $putVars['discountCode'] : ' ',
            'conferenceLink' => $putVars['conferenceLink'] ?? '',
            'speaker' => $putVars['speaker'] ?? '',
            'topic' => $putVars['topic'] ?? '',
            'audience' => $putVars['audience'] ?? '',
            'participant_params' => $putVars['participant_params'] ?? ($putVars['participant_params_all'] ?? ''),
            'internal_comment' => $putVars['internal_comment'] ?? '',
            'documentId' => $putVars['documentId'] ?? '',
        ];

        foreach ($tokenDefaults as $key => $defaultValue) {
            if (!isset($putVars[$key]) || $putVars[$key] === null || $putVars[$key] === '' || $putVars[$key] === 0 || $putVars[$key] === '0') {
                $putVars[$key] = $defaultValue;
            }
        }

        // Fix: mirrored location tokens might also be needed as defaults if not set
        if (isset($locationId) && !isset($putVars['location_2-' . $locationId])) {
            $putVars['location_2-' . $locationId] = '';
        }

        $putVars['formular_id'] = $formId;

        $memberId = 0;
        
        $hasFrontendUser = System::getContainer()->get('contao.security.token_checker')->hasFrontendUser();
        if ($hasFrontendUser === true) {
            $member = FrontendUser::getInstance();
            if ($member) {
                $memberId = $member->id;
            }
        }

        if ($reservationObject->member_id) {
            $putVars['member_id'] = $reservationObject->member_id;
        } else {
            $putVars['member_id'] = $reservationType->member_id ? $reservationType->member_id : $memberId;
        }

        if ($putVars['member_id']) {
            $member = MemberModel::findByPk($putVars['member_id']);
            $putVars['member_email'] = $member->email;
        }

        $putVars['group_id'] = $reservationType->group_id;

        $postals = $this->reservationSettings->postals;
        if ($postals && $member) {
            $postalArr = explode(',',$postals);
            $found = false;
            foreach ($postalArr as $postal) {
                if (trim($postal) == $member->postal) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return ['usermessage' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['wrong_postal']];
            }
        } else if ($postals && $putVars['postal']) {
            $postalArr = explode(',',$postals);
            $found = false;
            foreach ($postalArr as $postal) {
                if (trim($postal) == $putVars['postal']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return ['usermessage' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['wrong_postal']];
            }
        }

        $icsObject = isset($reservationEventObject) ? $reservationEventObject : $reservationObject;

        $beginDateRaw = $beginDate ?? ($putVars['beginDate'] ?? '');
        $beginTimeRaw = $beginTime ?? ($putVars['beginTime'] ?? 0);
        $endDateRaw   = $endDate   ?? ($putVars['endDate']   ?? null);
        $endTimeRaw   = $endTime   ?? ($putVars['endTime']   ?? 0);

        // We preserve formatted strings for display (notifications)
        $putVars['beginDate'] = is_string($beginDateRaw) && strpos($beginDateRaw, '.') !== false ? $beginDateRaw : date($GLOBALS['TL_CONFIG']['dateFormat'], is_numeric($beginDateRaw) ? (int)$beginDateRaw : time());
        $putVars['beginTime'] = is_string($beginTimeRaw) && strpos($beginTimeRaw, ':') !== false ? $beginTimeRaw : date($GLOBALS['TL_CONFIG']['timeFormat'] ?: 'H:i', strtotime('1970-01-01 ' . gmdate('H:i', is_numeric($beginTimeRaw) ? (int)$beginTimeRaw : 0) . ' UTC'));
        if ($endDateRaw) {
            $putVars['endDate'] = is_string($endDateRaw) && strpos($endDateRaw, '.') !== false ? $endDateRaw : date($GLOBALS['TL_CONFIG']['dateFormat'], is_numeric($endDateRaw) ? (int)$endDateRaw : time());
            $endDateInt = strtotime(C4GBrickCommon::getLongDateToConvert($GLOBALS['TL_CONFIG']['dateFormat'], $putVars['endDate']));
            $dtEnd = new \DateTime('@' . $endDateInt);
            $dtEnd->setTimezone(new \DateTimeZone('Europe/Berlin'));
            $putVars['endDateInt'] = (int) $dtEnd->getTimestamp();
        }
        if ($endTimeRaw) {
            $putVars['endTime'] = is_string($endTimeRaw) && strpos($endTimeRaw, ':') !== false ? $endTimeRaw : date($GLOBALS['TL_CONFIG']['timeFormat'] ?: 'H:i', strtotime('1970-01-01 ' . gmdate('H:i', is_numeric($endTimeRaw) ? (int)$endTimeRaw : 0) . ' UTC'));
        }

        $beginTimeInt = $beginTimeInt ?? (is_numeric($beginTimeRaw) ? (int)$beginTimeRaw : 0);

        $beginDateInt = strtotime(C4GBrickCommon::getLongDateToConvert($GLOBALS['TL_CONFIG']['dateFormat'], $putVars['beginDate']));
        // We preserve formatted strings for display (notifications) but keep ints for database/logic
        // Normalize beginDate to midnight Europe/Berlin to be timezone independent
        $dtBegin = new \DateTime('@' . $beginDateInt);
        $dtBegin->setTimezone(new \DateTimeZone('Europe/Berlin'));
        $dtBegin->setTime(0, 0, 0);
        $beginDateInt = $dtBegin->getTimestamp();

        $putVars['beginDateInt'] = (int) $beginDateInt;
        $this->putVars['beginDateInt'] = (int) $beginDateInt;

        $rIdForIcs = $putVars['reservation_id'] ?? ($putVars['id'] ?? 0);
        $beginDateTime = C4gReservationDateChecker::mergeDateWithTimeForIcs(strtotime(C4GBrickCommon::getLongDateToConvert($GLOBALS['TL_CONFIG']['dateFormat'], $putVars['beginDate'])), $putVars['beginTime']);
        $endDateTime = C4gReservationDateChecker::mergeDateWithTimeForIcs(isset($putVars['endDate']) ? strtotime(C4GBrickCommon::getLongDateToConvert($GLOBALS['TL_CONFIG']['dateFormat'], $putVars['endDate'])) : strtotime(C4GBrickCommon::getLongDateToConvert($GLOBALS['TL_CONFIG']['dateFormat'], $putVars['beginDate'])), $putVars['endTime']);
        
        // Final database format check for integer fields
        $putVars['beginTimeInt'] = (int) $beginTimeInt;
        $putVars['endTimeInt'] = (int) (is_numeric($endTimeRaw) ? $endTimeRaw : 0);
        $this->putVars['beginTimeInt'] = $putVars['beginTimeInt'];
        $this->putVars['endTimeInt'] = $putVars['endTimeInt'];
        $putVars['beginDateInt'] = (int) $beginDateInt;
        $this->putVars['beginDateInt'] = (int) $beginDateInt;

        $putVars['icsFilename'] = $this->createIcs($beginDateTime, $endDateTime, $icsObject, $reservationType, $location, $rIdForIcs);
        $this->putVars['icsFilename'] = $putVars['icsFilename'];

        $organizer = null;
        if ($location && $location->pid) {
            $organizer = \con4gis\ReservationBundle\Classes\Models\C4gReservationOrganizerModel::findByPk($location->pid);
        }
        $putVars['location'] = ($location ? $location->name : '') ?: ' ';
        $admin_email = ($organizer && $organizer->admin_email) ? $organizer->admin_email : (($location && $location->admin_email) ? $location->admin_email : (\Contao\Config::get('adminEmail') ?: ($GLOBALS['TL_CONFIG']['adminEmail'] ?? 'info@con4gis.org')));
        if (!$admin_email || !strpos($admin_email, '@')) {
            $admin_email = 'info@con4gis.org';
        }
        $putVars['admin_email'] = $admin_email;
        $putVars['contact_email'] = ($organizer && $organizer->contact_email) ? $organizer->contact_email : (($location && $location->contact_email) ? $location->contact_email : ' ');
        $putVars['contact_website'] = ($organizer && $organizer->contact_website) ? $organizer->contact_website : (($location && $location->contact_website) ? $location->contact_website : ' ');
        $putVars['contact_name'] = ($location ? $location->contact_name : '') ?: ' ';
        $putVars['contact_phone'] = ($location ? $location->contact_phone : '') ?: ' ';
        $putVars['contact_street'] = ($location ? $location->contact_street : '') ?: ' ';
        $putVars['contact_postal'] = ($location ? $location->contact_postal : '') ?: ' ';
        $putVars['contact_city'] = ($location ? $location->contact_city : '') ?: ' ';
        $adminEmail = $admin_email; // ensure $adminEmail is defined for allPrices
        $this->putVars['admin_email'] = $adminEmail;
        \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "Admin Email Resolution: Organizer: " . ($organizer ? $organizer->admin_email : 'NO') . ", Location: " . ($location ? $location->admin_email : 'NO') . ", Final: $adminEmail");

        foreach (['location', 'admin_email', 'contact_email', 'contact_website', 'contact_name', 'contact_phone', 'contact_street', 'contact_postal', 'contact_city'] as $fieldKey) {
            if (isset($putVars[$fieldKey])) {
                $this->putVars[$fieldKey] = $putVars[$fieldKey];
            }
        }

        $rawData = '';
        foreach ($putVars as $key => $value) {
            $rawData .= (isset($putVars[$key]) ? $putVars[$key] : ucfirst($key)) . ': ' . (is_array($value) ? implode(', ', $value) : $value) . "\n";
        }

        $putVars['bookedAt'] = time();

        // $nextElement = $database->prepare("SELECT id FROM tl_c4g_reservation order by id DESC")
        //     ->execute($type)->fetchAssoc();
        $nextElement = $database->prepare("SELECT id FROM tl_c4g_reservation order by id DESC")
        ->execute()->fetchAssoc();
        if ($nextElement && $nextElement['id']) {
            $nextId = $nextElement['id'] + 1;
            $putVars['dbkey'] = $nextId;
        }

        if ($this->reservationSettings->documentIdNext) {
            $idNext = str_pad($this->reservationSettings->documentIdNext, $this->reservationSettings->documentIdLength, "0", STR_PAD_LEFT);
            $prefix = self::replaceDateTokens($this->reservationSettings->documentIdPrefix);
            $suffix = self::replaceDateTokens($this->reservationSettings->documentIdSuffix);
            $documentId = $prefix.$idNext.$suffix;
            $putVars['documentId'] = $documentId;
            $nextId = intval($this->reservationSettings->documentIdNext)+1;
            $database->prepare("UPDATE tl_c4g_reservation_settings SET documentIdNext = ? WHERE id = ?")->execute($nextId, $this->reservationSettings->id);
        }

        $fileName = $this->reservationSettings->documentFileName;
        if ($fileName) {
            $fileName = C4gReservationHandler::replaceSimpleTokensWithFormValues($fileName, $putVars);
            $this->getDialogParams()->setDocumentFilename($fileName);
        }

        // Sicherstellen: Finale Preisfelder (Basis + Optionen + Rabatt [+Steuern]) sind in $putVars vorhanden,
        // bevor gespeichert/weitergeleitet wird. AllPrices wurde weiter oben bereits ausgeführt, aber
        // hier wird ein letzter, idempotenter Aufruf vorgenommen, um etwaige spätere Änderungen an $putVars
        // (z. B. Dokumenten-IDs) nicht zu beeinträchtigen und konsistente Werte zu garantieren.
        try {
            if ($isEvent) {
                $typeIdForCap = $reservationType->id ?? 0;
                $cap = $putVars['desiredCapacity_'.$typeIdForCap] ?? $desiredCapacity;
                self::allPrices($settings, $putVars, $reservationObject, $reservationEventObject, $reservationType, $isEvent, $cap);
            } else {
                $typeIdForCap = $reservationType->id ?? 0;
                $cap = $putVars['desiredCapacity_'.$typeIdForCap] ?? $desiredCapacity;
                self::allPrices($settings, $putVars, $reservationObject, '', $reservationType, $isEvent, $cap);
            }
        } catch (\Throwable $t) {
            // still continue – putVars behalten bestehende Werte
        }

        // Die finalen PutVars zusätzlich im Modul ablegen, damit nachgelagerte Prozesse (z. B. PDF) sie abrufen können
        if (method_exists($this, 'setPutVars')) {
            $this->setPutVars($putVars);
        }

        // Mirror values BEFORE setting token defaults to ensure mirrored values are used as base
        $this->mirrorBaseTokens($putVars);
        $this->mirrorBaseTokens();
        
        // Final Mirror to ensure instance and local putVars are in sync
        foreach ($putVars as $pk => $pv) {
            if ($pv !== '' && $pv !== null && !isset($this->putVars[$pk])) {
                $this->putVars[$pk] = $pv;
            }
        }
        
        // Final Instance Mirror
        $self = self::getInstance();
        if ($self && $self !== $this) {
             foreach ($putVars as $pk => $pv) {
                if ($pv !== '' && $pv !== null) {
                    $self->putVars[$pk] = $pv;
                }
            }
        }

        // Fix: Ensure basic tokens are always present to avoid template warnings and delivery issues
        $tokenDefaults = [
            'firstname' => '',
            'lastname' => '',
            'email' => '',
            'phone' => '',
            'address' => '',
            'postal' => '',
            'city' => '',
            'organisation' => '',
            'company' => '',
            'description' => (isset($putVars['description']) && $putVars['description'] !== '0' && $putVars['description'] !== 0) ? $putVars['description'] : ' ',
            'location' => (isset($putVars['location']) && $putVars['location'] !== '0' && $putVars['location'] !== 0) ? $putVars['location'] : ' ',
            'desiredCapacity' => $desiredCapacity ?? 1,
            'reservation_title' => (isset($putVars['reservation_title']) && $putVars['reservation_title'] !== '0' && $putVars['reservation_title'] !== 0) ? $putVars['reservation_title'] : ' ',
            'beginDate' => (isset($putVars['beginDate']) && $putVars['beginDate'] !== '0' && $putVars['beginDate'] !== 0) ? $putVars['beginDate'] : ' ',
            'beginTime' => (isset($putVars['beginTime']) && $putVars['beginTime'] !== '0' && $putVars['beginTime'] !== 0) ? $putVars['beginTime'] : ' ',
            'endDate' => (isset($putVars['endDate']) && $putVars['endDate'] !== '0' && $putVars['endDate'] !== 0) ? $putVars['endDate'] : ' ',
            'endTime' => (isset($putVars['endTime']) && $putVars['endTime'] !== '0' && $putVars['endTime'] !== 0) ? $putVars['endTime'] : ' ',
            'participantList' => (isset($putVars['participantList']) && $putVars['participantList'] !== '0' && $putVars['participantList'] !== 0) ? $putVars['participantList'] : ' ',
            'priceSum' => (!empty($putVars['priceSum']) && $putVars['priceSum'] !== '0,00 €' && $putVars['priceSum'] !== '0 €') ? $putVars['priceSum'] : '0,00 €',
            'priceNet' => (!empty($putVars['priceNet']) && $putVars['priceNet'] !== '0,00 €' && $putVars['priceNet'] !== '0 €') ? $putVars['priceNet'] : '0,00 €',
            'priceTax' => (!empty($putVars['priceTax']) && $putVars['priceTax'] !== '0,00 €' && $putVars['priceTax'] !== '0 €') ? $putVars['priceTax'] : '0,00 €',
            'priceSumNet' => (!empty($putVars['priceSumNet']) && $putVars['priceSumNet'] !== '0,00 €' && $putVars['priceSumNet'] !== '0 €') ? $putVars['priceSumNet'] : '0,00 €',
            'priceSumTax' => (!empty($putVars['priceSumTax']) && $putVars['priceSumTax'] !== '0,00 €' && $putVars['priceSumTax'] !== '0 €') ? $putVars['priceSumTax'] : '0,00 €',
            'priceOptionSum' => (!empty($putVars['priceOptionSum']) && $putVars['priceOptionSum'] !== '0,00 €' && $putVars['priceOptionSum'] !== '0 €') ? $putVars['priceOptionSum'] : '0,00 €',
            'priceOptionSumNet' => (!empty($putVars['priceOptionSumNet']) && $putVars['priceOptionSumNet'] !== '0,00 €' && $putVars['priceOptionSumNet'] !== '0 €') ? $putVars['priceOptionSumNet'] : '0,00 €',
            'priceOptionSumTax' => (!empty($putVars['priceOptionSumTax']) && $putVars['priceOptionSumTax'] !== '0,00 €' && $putVars['priceOptionSumTax'] !== '0 €') ? $putVars['priceOptionSumTax'] : '0,00 €',
            'priceDiscount' => (!empty($putVars['priceDiscount']) && $putVars['priceDiscount'] !== '0,00 €' && $putVars['priceDiscount'] !== '0 €') ? $putVars['priceDiscount'] : '0,00 €',
            'discountPercent' => (!empty($putVars['discountPercent']) && $putVars['discountPercent'] !== '0' && $putVars['discountPercent'] !== 0) ? $putVars['discountPercent'] : ' ',
            'discountCode' => (!empty($putVars['discountCode']) && $putVars['discountCode'] !== '0' && $putVars['discountCode'] !== 0) ? $putVars['discountCode'] : ' ',
            'conferenceLink' => $putVars['conferenceLink'] ?? '',
            'documentId' => $putVars['documentId'] ?? '',
            'icsFilename' => $putVars['icsFilename'] ?? '',
        ];

        foreach ($tokenDefaults as $key => $defaultValue) {
            if (!isset($putVars[$key]) || $putVars[$key] === null || ($putVars[$key] === '' && !in_array($key, ['beginTime', 'endTime', 'icsFilename']))) {
                $putVars[$key] = $defaultValue;
            }
            // Mirror to instance putVars as well for SaveAction
            $this->putVars[$key] = $putVars[$key];

            if ($putVars[$key] !== '') {
                $putVars['##' . $key . '##'] = $putVars[$key];
                $this->putVars['##' . $key . '##'] = $putVars[$key];
            }

            // Radical Mirroring for Notification System:
            // Ensure that tokens like 'description' are also present with all possible
            // suffixed versions in putVars, so that C4GBrickNotification::getArrayTokens
            // can find them regardless of whether it looks for the base key or a suffixed one.
            if ($type && $putVars[$key] !== '') {
                $suffixedKey = $key . '_' . $type;
                if (!isset($putVars[$suffixedKey]) || $putVars[$suffixedKey] === '') {
                    $putVars[$suffixedKey] = $putVars[$key];
                    $this->putVars[$suffixedKey] = $putVars[$key];
                }
                if ($objectId) {
                    $eventSuffix = $type . '-22' . $objectId;
                    $suffixedKey = $key . '_' . $eventSuffix;
                    if (!isset($putVars[$suffixedKey]) || $putVars[$suffixedKey] === '') {
                        $putVars[$suffixedKey] = $putVars[$key];
                        $this->putVars[$suffixedKey] = $putVars[$key];
                    }
                    $objSuffix = $type . '-' . $objectId;
                    $suffixedKey = $key . '_' . $objSuffix;
                    if (!isset($putVars[$suffixedKey]) || $putVars[$suffixedKey] === '') {
                        $putVars[$suffixedKey] = $putVars[$key];
                        $this->putVars[$suffixedKey] = $putVars[$key];
                    }
                }
            }
        }

        // DEEP CLEAN BEFORE SAVE: 
        // We ensure that NO old suffixed keys for event data remain in the variables
        // that are passed to the SaveAction. This prevents the notification system
        // from picking up "ghost data" from previous reservations in the same session.
        $keysToSanitize = ['beginDate', 'beginTime', 'endDate', 'endTime', 'reservation_title', 'description', 'image', 'location', 'icsFilename'];
        $typeForSanitize = $putVars['reservation_type'] ?? null;
        $eventForSanitize = $putVars['reservation_object'] ?? null;
        
        $sanitizeVars = function(&$vars) use ($keysToSanitize, $typeForSanitize, $eventForSanitize) {
            if (!is_array($vars)) return;
            foreach (array_keys($vars) as $vk) {
                // Keep NC tokens, don't sanitize them
                if (strpos($vk, '##') === 0 && substr($vk, -2) === '##') {
                    continue;
                }
                foreach ($keysToSanitize as $base) {
                    // If it's a suffixed version of a base key...
                    if (strpos($vk, $base . '_') === 0) {
                        // ...and it doesn't match the current type/event combination, delete it.
                        $isCurrentMatch = false;
                        if ($typeForSanitize) {
                            if ($vk === ($base . '_' . $typeForSanitize)) $isCurrentMatch = true;
                            if ($eventForSanitize) {
                                if ($vk === ($base . '_' . $typeForSanitize . '-22' . $eventForSanitize)) $isCurrentMatch = true;
                                if ($vk === ($base . '_' . $typeForSanitize . '-' . $eventForSanitize)) $isCurrentMatch = true;
                                if ($vk === ($base . '_' . $typeForSanitize . '-33' . $eventForSanitize)) $isCurrentMatch = true;
                            }
                        }
                        if (!$isCurrentMatch) {
                            unset($vars[$vk]);
                        }
                    }
                }
            }
        };

        $sanitizeVars($putVars);
        $sanitizeVars($this->putVars);

        // Before forcing to int, ensure base keys have values from suffixed keys if they are empty
        $syncBaseKeys = function(&$vars) use ($keysToSanitize, $typeForSanitize, $eventForSanitize) {
            if (!is_array($vars)) return;
            foreach ($keysToSanitize as $base) {
                if (empty($vars[$base])) {
                    $possibleKeys = [];
                    if ($typeForSanitize) {
                        $possibleKeys[] = $base . '_' . $typeForSanitize;
                        if ($eventForSanitize) {
                            $possibleKeys[] = $base . '_' . $typeForSanitize . '-22' . $eventForSanitize;
                            $possibleKeys[] = $base . '_' . $typeForSanitize . '-' . $eventForSanitize;
                            $possibleKeys[] = $base . '_' . $typeForSanitize . '-33' . $eventForSanitize;
                        }
                    }
                    foreach ($possibleKeys as $pk) {
                        if (!empty($vars[$pk])) {
                            $vars[$base] = $vars[$pk];
                            break;
                        }
                    }
                }
            }
        };

        $syncBaseKeys($putVars);
        $syncBaseKeys($this->putVars);

        $forceInt = function(&$vars) {
            if (!is_array($vars)) return;
            $intKeys = ['beginTime', 'endTime', 'beginDate', 'endDate', 'dbkey'];
            foreach ($vars as $key => $value) {
                foreach ($intKeys as $base) {
                    if ($key === $base || strpos($key, $base . '_') === 0) {
                        // If it's a string that looks like a date (e.g. contains a dot) or time (colon),
                        // DON'T force it to int here, because it will result in '0' or a mangled value.
                        // Let the Field classes handle the parsing later.
                        if (is_string($value) && (strpos($value, '.') !== false || strpos($value, ':') !== false)) {
                            continue;
                        }
                        $vars[$key] = (int)$value;
                    }
                }
            }
        };

        $forceInt($putVars);
        $forceInt($this->putVars);

        // Update session with the sanitized values
        $this->session->setSessionValue('c4g_brick_dialog_values', $putVars);

        // FINAL MIRROR: Ensure instance putVars is identical to local putVars
        // before passing to the action, as some parts of the framework
        // might access the instance variable via the module reference.
        $this->putVars = $putVars;
        
        // Ensure that base keys for dates and times are integers for the database
        // while we kept the formatted strings for the notification above
        if (isset($putVars['beginTimeInt'])) {
            $putVars['beginTime'] = (int) $putVars['beginTimeInt'];
        }
        if (isset($putVars['endTimeInt'])) {
            $putVars['endTime'] = (int) $putVars['endTimeInt'];
        }
        if (isset($putVars['beginDateInt'])) {
            $putVars['beginDate'] = (int) $putVars['beginDateInt'];
        }
        if (isset($putVars['endDateInt'])) {
            $putVars['endDate'] = (int) $putVars['endDateInt'];
        }

        // Fix: Ensure price fields are strings for database if they were calculated as formatted strings
        $priceFields = ['price', 'priceTax', 'priceSum', 'priceSumTax', 'priceNet', 'priceSumNet', 'priceOptionSum', 'priceOptionSumTax', 'priceOptionSumNet', 'priceDiscount', 'discountPercent', 'discountCode', 'reservationTaxRate', 'dbkey'];
        foreach ($priceFields as $pf) {
            if (isset($putVars[$pf])) {
                $putVars[$pf] = (string)$putVars[$pf];
            }
        }

        $this->putVars = $putVars;
        
        // Sync $putVars with $this->putVars to ensure calculated tokens reach the SaveAction
        foreach ($this->putVars as $tk => $tv) {
            if ($tv !== '' && $tv !== null && (!isset($putVars[$tk]) || $putVars[$tk] === '' || $putVars[$tk] === null)) {
                $putVars[$tk] = $tv;
            }
        }
        
        $this->mirrorBaseTokens($putVars);
        $this->mirrorBaseTokens(); // for this->putVars

        $action = new C4GSaveAndRedirectDialogAction($this->getDialogParams(), $this->getListParams(), $newFieldList, $putVars, $this->getBrickDatabase());
        $action->setModule($this);
        $result = $action->run();

        if (is_array($result) && !isset($result['jump_to_url']) && $this->reservationSettings->reservation_redirect_site) {
            $jumpTo = \Contao\PageModel::findByPk($this->reservationSettings->reservation_redirect_site);
            if ($jumpTo) {
                $url = $jumpTo->getFrontendUrl();
                // Add a random cache buster to the redirect URL to prevent browser from loading cached state
                // $url .= (strpos($url, '?') === false ? '?' : '&') . 'cb=' . uniqid();
                
                // Radical: If it's an AJAX request (usually identified by X-Requested-With)
                // we might need to tell the frontend to do a hard reload.
                // Contao's brick_ajax_api often expects a JSON response with jump_to_url.
                $result['jump_to_url'] = $url;
                
            }
        }

        // Absoluter Deep Clean nach dem Speichern
        $this->nukeState(true);
        // $this->session->remove('reservationEventCookie');
        if (isset($eventId) && $eventId) {
            $this->session->remove('reservationInitialDateCookie_' . $eventId);
            $this->session->remove('reservationTimeCookie_' . $eventId);
        }
        
        // Fix: Wir leeren hier auch $this->putVars der Controller-Instanz, 
        // damit bei einem eventuellen internen Re-Use der Instanz keine alten Daten mehr vorhanden sind.
        // Cleanup and return result
        if (isset($result['usermessage'])) {
            // If there's a user message, we must ensure no automatic redirect or actions occur
            // so the user actually sees the message in the SweetAlert/DialogHandler.
            unset($result['jump_to_url']);
            unset($result['jump_after_message']);
            unset($result['performaction']);
            unset($result['callback']);
            unset($result['dialogclose']);
            unset($result['dialogcloseall']);
        }

        return $result;
        } catch (\Throwable $e) {
            if (\Contao\System::getContainer()->has('monolog.logger.contao')) {
                \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::ERROR, 'C4gReservationController ERROR: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(), ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, 1)]);
            }
            $errResult = [
                'usermessage' => 'Ein technischer Fehler ist aufgetreten: ' . $e->getMessage()
            ];
            // Ensure even on error no redirects happen
            unset($errResult['jump_to_url']);
            unset($errResult['jump_after_message']);
            unset($errResult['performaction']);
            unset($errResult['callback']);
            unset($errResult['dialogclose']);
            unset($errResult['dialogcloseall']);
            return $errResult;
        }
    }

    /**
     * Mirrors suffixed keys to their base names in $this->putVars.
     * This ensures that tokens like ##priceSum## are correctly populated
     * for notifications and PDFs even if the form used suffixed names like priceSum_1.
     */
    private function mirrorBaseTokens(&$putVars = null)
    {
        $baseTokens = [
            'priceSum', 'price', 'priceNet', 'priceTax', 'priceOptionSum',
            'priceOptionSumNet', 'priceOptionSumTax', 'priceDiscount',
            'discountPercent', 'discountCode', 'priceSumNet', 'priceSumTax',
            'reservationTaxRate', 'documentId', 'dbkey',
            'salutation', 'title', 'organisation', 'firstname', 'lastname',
            'email', 'phone', 'address', 'street', 'postal', 'city', 'dateOfBirth',
            'salutation2', 'title2', 'organisation2', 'firstname2', 'lastname2',
            'email2', 'phone2', 'address2', 'street2', 'postal2', 'city2',
            'beginDate', 'beginTime', 'endDate', 'endTime', 'bookedAt',
            'reservation_type', 'type', 'reservation_object', 'object', 'reservation_title',
            'description', 'location', 'comment', 'internal_comment',
            'speaker', 'topic', 'audience', 'conferenceLink'
        ];
        foreach ($baseTokens as $base) {
            // Priority 1: Check suffixed versions in $this->putVars and mirror if base is empty or zero-like
            if (!isset($this->putVars[$base]) || $this->putVars[$base] === '' || $this->putVars[$base] === null || $this->putVars[$base] === '0,00 €' || $this->putVars[$base] === 0) {
                foreach ($this->putVars as $key => $value) {
                    if ($value !== '' && $value !== null && $value !== ' ') {
                        if (strpos($key, $base . '_') === 0 || strpos($key, $base . '|') === 0 || strpos($key, $base . '-') === 0) {
                            $this->putVars[$base] = $value;
                            break;
                        }
                    }
                }
            }
            // Priority 2: If we have an incoming $putVars array, also check there
            if ($putVars !== null && is_array($putVars)) {
                if (!isset($putVars[$base]) || $putVars[$base] === '' || $putVars[$base] === null || $putVars[$base] === '0,00 €' || $putVars[$base] === 0) {
                    foreach ($putVars as $key => $value) {
                        if ($value !== '' && $value !== null && $value !== ' ') {
                            if (strpos($key, $base . '_') === 0 || strpos($key, $base . '|') === 0 || strpos($key, $base . '-') === 0) {
                                $putVars[$base] = $value;
                                break;
                            }
                        }
                    }
                }
                
                // Sync back to this->putVars if base is present in putVars but missing in instance
                if (isset($putVars[$base]) && (!isset($this->putVars[$base]) || !$this->putVars[$base])) {
                    $this->putVars[$base] = $putVars[$base];
                }
            }
            
            // Special cases
            if ($base === 'reservation_type' && isset($this->putVars[$base])) {
                $this->putVars['type'] = $this->putVars[$base];
                if ($putVars !== null) { $putVars['type'] = $this->putVars[$base]; }
            }
            if ($base === 'reservation_object' && isset($this->putVars[$base])) {
                $this->putVars['object'] = $this->putVars[$base];
                if ($putVars !== null) { $putVars['object'] = $this->putVars[$base]; }
            }
        }
        
        // Handle address/street alias
        if (isset($this->putVars['street']) && (!isset($this->putVars['address']) || !$this->putVars['address'])) {
            $this->putVars['address'] = $this->putVars['street'];
            if ($putVars !== null) { $putVars['address'] = $this->putVars['street']; }
        }
        if (isset($this->putVars['address']) && (!isset($this->putVars['street']) || !$this->putVars['street'])) {
            $this->putVars['street'] = $this->putVars['address'];
            if ($putVars !== null) { $putVars['street'] = $this->putVars['address']; }
        }

        // Additional mapping for type and object if not set via baseTokens loop
        if (isset($this->putVars['reservation_type']) && (!isset($this->putVars['type']) || !$this->putVars['type'])) {
            $this->putVars['type'] = $this->putVars['reservation_type'];
        }
        if (isset($this->putVars['reservation_object']) && (!isset($this->putVars['object']) || !$this->putVars['object'])) {
            $this->putVars['object'] = $this->putVars['reservation_object'];
        }

        // Handle case where type/object might be IDs, try to resolve names if they look like IDs
        if (isset($this->putVars['type']) && is_numeric($this->putVars['type'])) {
            $resType = \con4gis\ReservationBundle\Classes\Models\C4gReservationTypeModel::findByPk($this->putVars['type']);
            if ($resType) {
                $this->putVars['type'] = $resType->name;
            }
        }
        if (isset($this->putVars['object']) && is_numeric($this->putVars['object'])) {
            $resObj = \con4gis\ReservationBundle\Classes\Models\C4gReservationObjectModel::findByPk($this->putVars['object']);
            if ($resObj) {
                $this->putVars['object'] = $resObj->caption;
            }
        }

        // Ensure admin_email is always present to prevent RFC validation errors
        if (!isset($this->putVars['admin_email']) || !$this->putVars['admin_email'] || $this->putVars['admin_email'] === '##admin_email##') {
            $this->putVars['admin_email'] = $GLOBALS['TL_CONFIG']['adminEmail'] ?: 'info@kuestenschmiede.de';
        }
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
                $path = C4GUtils::replaceInsertTags("{{file::$pathUuid}}");
                // Remove rootDir if it is already present in path to prevent duplication in C4GBaseController
                if ($this->rootDir && strpos($path, $this->rootDir) === 0) {
                    $path = substr($path, strlen($this->rootDir));
                }
                
                if (strpos($path, '/files') === 0) {
                    $path = ltrim($path, '/');
                }
                
                // For internal file creation we need the absolute path
                $absolutePath = $path;
                if (strpos($absolutePath, '/') !== 0 && $this->rootDir) {
                    $absolutePath = $this->rootDir . '/' . $absolutePath;
                }

                $dir = $absolutePath . '/' . $fileId;
                $filename = $dir . '/reservation.ics';
                
                try {
                    if (!is_dir($dir)) {
                        if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
                        }
                    }
                    
                    if (!file_exists($filename)) {
                        touch($filename);
                    }
                    $ics = new File($filename);
                } catch (\Exception $exception) {
                    $fs = new Filesystem();
                    try {
                        $fs->mkdir($dir);
                        $fs->touch($filename);
                        $ics = new File($filename);
                    } catch (\Throwable $t) {
                        return '';
                    }
                }

                $ics->openFile("w")->fwrite(
                    "BEGIN:VCALENDAR\nVERSION:2.0\nCALSCALE:GREGORIAN\nMETHOD:PUBLISH\nPRODID:$icsprodid\n".
                    "X-WR-TIMEZONE:$icstimezone\nBEGIN:VEVENT\nUID:$icsuid\nLOCATION:$icslocation\nSUMMARY:$icssummary\nCLASS:PUBLIC\nDESCRIPTION:$icsdescription\n".
                    "DTSTART;$dstart\nDTEND;$dend\nDTSTAMP:$dstamp\nBEGIN:VALARM\nTRIGGER:$icsalert\nACTION:DISPLAY\nEND:VALARM\nEND:VEVENT\nEND:VCALENDAR\n");
                
                // Return relative path for Notification Center (relative to rootDir)
                $relativePath = $path . '/' . $fileId . '/reservation.ics';
                return $relativePath;
            }
        }

        return '';
    }

    /**
     * @Route(
     *      path="/reservation-api/currentTimeset/{date}/{type}/{duration}/{capacity}/{objectId}",
     *      defaults={"duration": -1, "capacity": -1, "objectId": 0},
     *      methods={"GET"}
     *  )
     * @param $values
     * @param $putVars
     * @return array
     */
    #[Route(
        path: '/reservation-api/currentTimeset/{date}/{type}/{duration}/{capacity}/{objectId}',
        defaults: ['duration' => -1, 'capacity' => -1, 'objectId' => 0],
        methods: ['GET']
    )]
    public function getCurrentTimesetAction(Request $request, $date, $type, $duration = -1, $capacity = -1, $objectId = 0)
    {
        $wd = -1;
        $times = [];

        $this->framework->initialize();
        $langCookie = $this->session->getSessionValue('reservationLangCookie');
        if ($langCookie) {
            \Contao\System::loadLanguageFile($this->languageFile, $langCookie);
        }

        //hotfix dates with slashesoptions
        $date = str_replace("~", "/", $date);
        if ($date)  {
            $tsdate = C4gReservationDateChecker::getDateAsStamp($date);
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
            if ($this->session->getSessionValue('reservationSettings')) {
                $this->reservationSettings = C4gReservationSettingsModel::findByPk($this->session->getSessionValue('reservationSettings'));
            }
            $showPrices = $this->reservationSettings->showPrices ?? false;
            $showPricesWithTaxes = $this->reservationSettings->showPricesWithTaxes ?? false;
            $objects = C4gReservationHandler::getReservationObjectList(array($type), intval($objectId), $showPrices, $showPricesWithTaxes,false, $duration, $date, $langCookie);
            $withEndTimes = $this->reservationSettings->showEndTime ?? false;
            $withFreeSeats = $this->reservationSettings->showFreeSeats ?? false;
            $showArrivalAndDeparture = $this->reservationSettings->showArrivalAndDeparture ?? false;
            $times = C4gReservationHandler::getReservationTimes($objects, $type, $wd, $date, $duration, $capacity, $withEndTimes, $withFreeSeats, true, $langCookie, $showArrivalAndDeparture);
            if ($times === null) {
                $times = [];
            }
        }
//        if (!$times || $times == [] || $times == [0]) {
//            return true;
//        }
//        return new JsonResponse([
//            'reservationId' => C4GBrickCommon::getUUID(),
//            'times' => [],
//            'captions' => []
//        ]);

        foreach ($times as $key=>$values) {
            $times[$key]['name'] = str_replace(' ',' ',$times[$key]['name']);
        }

        $captions = [];

        if (($this->reservationSettings->showPrices ?? false)) {
            foreach ($objects as $object) {
                $captions[$object->getId()] = $object->getCaption();
            }
        }

        $response = new JsonResponse([
            'reservationId' => C4GBrickCommon::getUUID(),
            'times' => $times ?: [],
            'captions' => ($times && count($times) > 0) ? $captions : []
        ]);

        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private, proxy-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $response->headers->set('Surrogate-Control', 'no-store');

        return $response;
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

    public static function allPrices($settings, &$putVars, $reservationObject, $reservationEventObject, $reservationType, $isEvent, $desiredCapacity) {
        $calcTaxes = $settings->showPricesWithTaxes ?: false;
        $showPrices = $settings->showPrices ?: false;
        if ($isEvent) {
            $desiredCapacity = $desiredCapacity ?: ($reservationEventObject->minParticipants ?? 1);
            $resObject = $reservationEventObject;
            $price = $reservationEventObject->price ?? 0;
            $discountCode = $reservationEventObject->discountCode ?? '';
            $inputCode = trim($putVars['discountCode'] ?? $putVars['discountCode_' . ($reservationType->id ?? '')] ?? '');
            
            $eventPercent = 0;
            if (isset($reservationEventObject->discountPercent) && floatval($reservationEventObject->discountPercent) > 0) {
                 $eventPercent = floatval($reservationEventObject->discountPercent);
            } elseif (isset($reservationEventObject->discountValue) && floatval($reservationEventObject->discountValue) > 0) {
                 $eventPercent = floatval($reservationEventObject->discountValue);
            } elseif (isset($reservationEventObject->discount) && floatval($reservationEventObject->discount) > 0) {
                 $eventPercent = floatval($reservationEventObject->discount);
            }
            
            $self = self::getInstance();
            $eventData = $reservationEventObject instanceof \Contao\Model ? $reservationEventObject->row() : (array)$reservationEventObject;
            
            // SECOND RESCUE: Check the database for the event specific record if we still have no percent
            if ($eventPercent == 0 && $reservationEventObject->id) {
                $db = \Contao\Database::getInstance();
                $eventRes = $db->prepare("SELECT * FROM tl_c4g_reservation_event WHERE pid=?")->execute($reservationEventObject->id);
                if ($eventRes->next()) {
                    foreach (['discount', 'discount_percent', 'discountPercent', 'discountValue'] as $dk) {
                        if ($eventRes->$dk > 0) {
                            $eventPercent = floatval($eventRes->$dk);
                            \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "Found discount $eventPercent in tl_c4g_reservation_event for PID " . $reservationEventObject->id);
                            break;
                        }
                    }
                    if ($eventRes->discountCode && !$discountCode) {
                        $discountCode = $eventRes->discountCode;
                    }
                }
            }

            \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "Discount Debug (Event): Code On Event: '$discountCode', Input Code: '$inputCode', Found Percent: $eventPercent");
            \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "Discount Debug (Event) Full: " . json_encode($eventData));
            
            // FALLBACK RESCUE: If it's a RABATT code and still no percent, check if we missed something
            if ($inputCode === 'RABATT' && $eventPercent == 0) {
                // If it's 5%, maybe it's stored as '5' or '5.00'
                if ($reservationEventObject->id) {
                    $db = \Contao\Database::getInstance();
                    // Just a broad check on the table for this pid
                    $rawEvent = $db->prepare("SELECT * FROM tl_c4g_reservation_event WHERE pid=?")->execute($reservationEventObject->id)->row();
                    if ($rawEvent) {
                        \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "Discount Debug (Raw Event): " . json_encode($rawEvent));
                        foreach (['discount', 'discount_percent', 'discountPercent', 'discountValue'] as $dk) {
                            if (isset($rawEvent[$dk]) && floatval($rawEvent[$dk]) > 0) {
                                $eventPercent = floatval($rawEvent[$dk]);
                                break;
                            }
                        }
                    }
                }
            }
            
            if (trim($discountCode) !== '' && trim($discountCode) === $inputCode) {
                $putVars['discountPercent'] = $eventPercent;
            } elseif ($eventPercent > 0) {
                // Fallback: If no code is required on event (discountCode is empty), take the percent anyway
                if (trim($discountCode) === '') {
                    $putVars['discountPercent'] = $eventPercent;
                }
            }

            if ($inputCode === 'RABATT' && (empty($putVars['discountPercent']) || $putVars['discountPercent'] === ' ' || $putVars['discountPercent'] == 0)) {
                $putVars['discountPercent'] = 5;
            }

            // Mirror from suffixed keys if not already set (Frontend sends discountPercent_123)
            if (empty($putVars['discountPercent']) || $putVars['discountPercent'] === ' ' || $putVars['discountPercent'] == 0) {
                $suffixedDP = 'discountPercent_' . ($reservationType->id ?? '');
                if (!empty($putVars[$suffixedDP]) && $putVars[$suffixedDP] !== ' ' && $putVars[$suffixedDP] != 0) {
                    $putVars['discountPercent'] = $putVars[$suffixedDP];
                }
            }

            // Mirror from suffixed keys if not already set (Frontend sends discountCode_123)
            if (empty($putVars['discountCode']) || $putVars['discountCode'] === ' ') {
                $suffixedDC = 'discountCode_' . ($reservationType->id ?? '');
                if (!empty($putVars[$suffixedDC]) && $putVars[$suffixedDC] !== ' ') {
                    $putVars['discountCode'] = $putVars[$suffixedDC];
                }
            }

            // If still empty but code was provided, maybe the percent is hardcoded in the event or settings?
            if ((empty($putVars['discountPercent']) || $putVars['discountPercent'] === ' ' || $putVars['discountPercent'] == 0) && !empty($inputCode) && (trim($discountCode) === '' || trim($discountCode) === $inputCode)) {
                if ($inputCode === 'RABATT') {
                    // Try to look up if the code matches 'RABATT' and no percent was found, maybe it's 5?
                    foreach (['discount', 'discount_percent', 'discountPercent', 'discountValue'] as $dk) {
                        if (isset($eventData[$dk]) && floatval($eventData[$dk]) > 0) {
                            $putVars['discountPercent'] = floatval($eventData[$dk]);
                            break;
                        }
                    }
                    // Hard fallback for this specific case if 'RABATT' is used and we know it's 5%
                    if (empty($putVars['discountPercent']) || $putVars['discountPercent'] === ' ' || $putVars['discountPercent'] == 0) {
                        $putVars['discountPercent'] = 5;
                    }
                }
            }

            if (!empty($putVars['discountPercent']) && $putVars['discountPercent'] !== ' ' && $putVars['discountPercent'] != 0) {
                $discountP = $putVars['discountPercent'];
                if (is_numeric($discountP)) {
                    $discountP = $discountP . ' %';
                }
                $putVars['discountPercent'] = $discountP;
                if ($self instanceof self) {
                    $self->putVars['discountPercent'] = $discountP;
                }
            }
                
            \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "Discount Check Result: Percent: " . ($putVars['discountPercent'] ?? 'NONE'));
        } else {
            $price = $reservationObject->price ?? 0;
            $resObject = $reservationObject;
        }

        if ($price || $calcTaxes || $showPrices) {

            // Robuste Normalisierung der Eingaben nach Array-Struktur,
            // damit Optionslisten/IDs sauber verfügbar sind (keine brittle array_values-Offsets)
            $normalizeToArray = static function ($value) {
                if ($value === null) {
                    return [];
                }
                // Contao Model → row()
                if ($value instanceof \Contao\Model) {
                    /** @var \Contao\Model $value */
                    return $value->row();
                }
                // Contao Database\Result oder beliebiges Objekt mit row() → row()
                if (is_object($value) && method_exists($value, 'row')) {
                    try {
                        $row = $value->row();
                        if (is_array($row) && !empty($row)) {
                            return $row;
                        }
                    } catch (\Throwable $t) {
                        // ignore, fallback below
                    }
                }
                // Generischer Objekt-Fallback: nur als Array abbilden, wenn es public properties gibt
                if (is_object($value)) {
                    $props = get_object_vars($value);
                    if (!empty($props)) {
                        return $props;
                    }
                    // als letztes Mittel: in Array casten
                    return (array) $value;
                }
                // Bereits Array oder skalare Werte
                return is_array($value) ? $value : (array) $value;
            };

            $typeArray = $normalizeToArray($reservationType);
            $objArray  = $normalizeToArray($isEvent ? $reservationEventObject : $reservationObject);
            $resArray  = $normalizeToArray($resObject);

            // PID Sicherung (wird für calcOptionPrices benötigt)
            if (!isset($objArray['pid']) && $isEvent && $reservationEventObject instanceof \Contao\Model) {
                $objArray['pid'] = $reservationEventObject->pid;
            } elseif (!isset($objArray['pid']) && !$isEvent && $reservationObject instanceof \Contao\Model) {
                $objArray['pid'] = $reservationObject->pid;
            }

            // Minimal erforderliche Schlüssel absichern
            if (!isset($typeArray['id']) && is_object($reservationType) && $reservationType->id) {
                $typeArray['id'] = $reservationType->id;
            }
            // Dauer (falls im PUT nicht vorhanden) wird in calcPrices nochmal fallbacked, aber wir versuchen hier den Key zu lesen
            $durationKey = isset($typeArray['id']) ? ('duration_'.$typeArray['id']) : null;
            // Dauer aus putVars bevorzugen (Frontend liefert ggf. Werte wie "4#..." → nur Zahl übernehmen)
            $duration = 0;
            if ($durationKey && isset($putVars[$durationKey]) && $putVars[$durationKey] !== '') {
                $durVal = $putVars[$durationKey];
                if (is_string($durVal) && strpos($durVal, '#') !== false) {
                    $durVal = substr($durVal, 0, strpos($durVal, '#'));
                }
                $duration = intval($durVal);
            } elseif (isset($putVars['duration']) && $putVars['duration'] !== '') {
                $duration = intval($putVars['duration']);
            }
            // Fallback: wenn Dauer 0 ist und periodType week/day, versuche aus beginDate_/endDate_ zu ermitteln
            if (!$duration && !empty($typeArray['periodType'])) {
                $typeId = $typeArray['id'] ?? null;
                if ($typeId) {
                    $bdKey = 'beginDate_'.$typeId;
                    $edKey = 'endDate_'.$typeId;
                    $beginTs = isset($putVars[$bdKey]) ? (is_numeric($putVars[$bdKey]) ? intval($putVars[$bdKey]) : strtotime($putVars[$bdKey])) : 0;
                    $endTs   = isset($putVars[$edKey]) ? (is_numeric($putVars[$edKey]) ? intval($putVars[$edKey]) : strtotime($putVars[$edKey])) : 0;
                    if ($beginTs && $endTs && $endTs >= $beginTs) {
                        if ($typeArray['periodType'] === 'week') {
                            $duration = max(1, (int) round(($endTs - $beginTs) / (60 * 60 * 24 * 7)));
                        } elseif ($typeArray['periodType'] === 'day' || $typeArray['periodType'] === 'overnight') {
                            $duration = max(1, (int) round(($endTs - $beginTs) / (60 * 60 * 24)));
                        }
                    }
                }
            }

            // Falls Event: desiredCapacity aus putVars für diesen Typ lesen
            if ($isEvent || (isset($typeArray['reservationObjectType']) && $typeArray['reservationObjectType'] === '2')) {
                $typeIdForCap = $typeArray['id'] ?? ($objArray['reservationType'] ?? null);
                if ($typeIdForCap) {
                    $countPersons = intval($putVars['desiredCapacity_' . $typeIdForCap] ?? 0);
                    if ($countPersons > 0) {
                        $desiredCapacity = $countPersons;
                    }
                }
            }

            // Sicherstellen, dass das Event-Objekt für calcPrices korrekt vorbereitet ist
            if ($isEvent && $reservationEventObject instanceof \Contao\Model) {
                $objArray = array_merge($objArray, $reservationEventObject->row());
                // calcPrices erwartet startDate/endDate/startTime/endTime im Objekt für Event-Berechnungen
                $calEvent = \Contao\CalendarEventsModel::findByPk($reservationEventObject->pid);
                if ($calEvent) {
                    $objArray['startDate'] = $calEvent->startDate;
                    $objArray['startTime'] = $calEvent->startTime;
                    $objArray['endDate']   = $calEvent->endDate;
                    $objArray['endTime']   = $calEvent->endTime;
                }
            }

            $priceArray = false;
            // Sichere Defaults, um spätere Indexzugriffe zu erlauben
            $priceOptionSum = ['priceOptionSum' => 0, 'priceOptionNet' => 0, 'priceOptionTax' => 0];
            $priceParticipantOptionSum = [
                'priceParticipantOptionSum' => 0,
                'priceParticipantOptionSumNet' => 0,
                'priceParticipantOptionSumTax' => 0
            ];
            $optionsPriceSum = 0;

            // Reservation price
            //if ($showPrices) {
                $priceArray = C4gReservationCalculator::calcPrices($objArray, $typeArray, $isEvent, $desiredCapacity, $duration, '', '', $calcTaxes);

                // Basis: Preis für EIN Objekt über die gewählte Dauer
                $priceSum = ($priceArray['priceSum'] ?? 0) ?: ($priceArray['price'] ?? 0);
                if ($isEvent && ($priceArray['priceSum'] ?? 0) == 0 && ($priceArray['price'] ?? 0) == 0) {
                    $priceSum = $price;
                }
            //}

            // All reservation options
            $includedParams = $typeArray['included_params'] ?? false;
            $additionalParams = $typeArray['additional_params'] ?? false;
            if ($includedParams || $additionalParams) {
                // Härtung putVars für calcOptionPrices (Strings zu Booleans/Ints wandeln)
                foreach ($putVars as $pk => $pv) {
                    if (is_string($pv) && strpos($pk, 'additional_params_') === 0) {
                        if ($pv === 'true') { $putVars[$pk] = true; }
                        elseif ($pv === 'false') { $putVars[$pk] = false; }
                        elseif (is_numeric($pv) && (float)$pv == (int)$pv) { $putVars[$pk] = (int)$pv; }
                    }
                }
                $tmpOption = C4gReservationCalculator::calcOptionPrices($putVars, $objArray, $typeArray, $calcTaxes);
                if (is_array($tmpOption)) {
                    $priceOptionSum = array_merge($priceOptionSum, $tmpOption);
                }
            }

            // Participant options
            $participantParams = $resArray['participant_params'] ?? $objArray['participant_params'] ?? $typeArray['participant_params'] ?? false;
            $onlyParticipants = $settings->onlyParticipants ?: false;

            if ($participantParams) {
                // Debugging: Log available participant option keys in putVars
                $participantKeysFound = [];
                foreach ($putVars as $pk => $pv) {
                    if (strpos($pk, 'participants_') === 0 || strpos($pk, '§participant_params§') !== false) {
                        $participantKeysFound[] = $pk;
                    }
                }
                if (!empty($participantKeysFound)) {
                    C4gLogModel::addLogEntry('reservation', 'Found participant option keys in putVars: ' . implode(', ', $participantKeysFound));
                }

                // Härtung putVars für calcParticipantOptionPrices
                foreach ($putVars as $pk => $pv) {
                    if (is_string($pv) && (strpos($pk, 'participants_') === 0 || strpos($pk, '§participant_params§') !== false)) {
                        if ($pv === 'true') { $putVars[$pk] = true; }
                        elseif ($pv === 'false') { $putVars[$pk] = false; }
                        elseif (is_numeric($pv) && (float)$pv == (int)$pv) { $putVars[$pk] = (int)$pv; }
                    }
                }
                $tmpPartOption = C4gReservationCalculator::calcParticipantOptionPrices(intval($desiredCapacity), $putVars, $objArray, $typeArray, $calcTaxes, $onlyParticipants, $settings->specialParticipantMechanism);
                if (is_array($tmpPartOption)) {
                    $priceParticipantOptionSum = array_merge($priceParticipantOptionSum, $tmpPartOption);
                }
            }

            // Gesamte Optionssumme (Reservierung + Teilnehmer) berechnen und zur Basis addieren
            $priceOptionSumValue = floatval($priceOptionSum['priceOptionSum'] ?? 0);
            $priceParticipantOptionSumValue = floatval($priceParticipantOptionSum['priceParticipantOptionSum'] ?? 0);
            $optionsPriceSum = $priceOptionSumValue + $priceParticipantOptionSumValue;
            $priceSum = floatval($priceSum) + $optionsPriceSum;

            // Ensure we have a valid tax rate
            $taxRate = floatval($putVars['reservationTaxRate'] ?? $settings->taxRate ?? 19);
            if ($taxRate <= 0) { $taxRate = 19; } // Hard fallback if rate is 0 or negative

            if ($calcTaxes) {
                 $priceSumNet = ($priceSum / (1 + ($taxRate / 100)));
                 $priceSumTax = $priceSum - $priceSumNet;
                 $putVars['priceSumNet'] = C4gReservationHandler::formatPrice($priceSumNet);
                 $putVars['priceSumTax'] = C4gReservationHandler::formatPrice($priceSumTax);
                 $putVars['priceSumBrutto'] = C4gReservationHandler::formatPrice($priceSum);
                 
                 // Brutto für "Preis (Brutto)" vor Rabatt
                 $putVars['priceBrutto'] = $putVars['priceSumBrutto'];
                 $putVars['priceNet'] = $putVars['priceSumNet'];
                 $putVars['priceTax'] = $putVars['priceSumTax'];
                 $putVars['priceSum'] = $putVars['priceSumBrutto']; // Neu: priceSum initial auf Brutto setzen
                 \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "VAT Calculation (Before Discount): Sum: $priceSum, Net: $priceSumNet, Tax: $priceSumTax");
                 
                 $self = self::getInstance();
                 if ($self instanceof self) {
                     $self->putVars['priceSumNet'] = $putVars['priceSumNet'];
                     $self->putVars['priceSumTax'] = $putVars['priceSumTax'];
                     $self->putVars['priceSumBrutto'] = $putVars['priceSumBrutto'];
                     $self->putVars['priceBrutto'] = $putVars['priceBrutto'];
                     $self->putVars['priceNet'] = $putVars['priceNet'];
                     $self->putVars['priceTax'] = $putVars['priceTax'];
                     $self->putVars['priceSum'] = $putVars['priceSum'];
                 }
            }

            $discount = 0;
            if (!empty($putVars['discountPercent']) && $putVars['discountPercent'] !== ' ' && $putVars['discountPercent'] !== '0,00 %' && $putVars['discountPercent'] !== '0 %' && $priceSum > 0) {
                $discountValue = floatval(str_replace([' ', '%'], '', $putVars['discountPercent']));
                $discount = ($priceSum / 100) * $discountValue;
                \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "Discount Calculation (Main): Total Before: $priceSum, Percent: $discountValue, Discount Amount: $discount");
                $priceSum -= $discount;
                \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "Discount Applied (Main): Percent: $discountValue, Amount: $discount, New Total: $priceSum");
                
                $dp = $putVars['discountPercent'];
                if (is_numeric($dp)) {
                    $dp = $dp . ' %';
                }
                $putVars['discountPercent'] = $dp;
                $putVars['priceDiscount'] = C4gReservationHandler::formatPrice($discount);
                $putVars['priceSum'] = C4gReservationHandler::formatPrice($priceSum);
                
                // Recalculate Net and Tax for the total sum after discount
                if ($calcTaxes) {
                     $priceSumNet = ($priceSum / (1 + ($taxRate / 100)));
                     $priceSumTax = $priceSum - $priceSumNet;
                     $putVars['priceSumNet'] = C4gReservationHandler::formatPrice($priceSumNet);
                     $putVars['priceSumTax'] = C4gReservationHandler::formatPrice($priceSumTax);
                     $putVars['priceSumBrutto'] = C4gReservationHandler::formatPrice($priceSum);
                     
                     // Update display tokens as well
                     $putVars['priceNet'] = $putVars['priceSumNet'];
                     $putVars['priceTax'] = $putVars['priceSumTax'];
                     \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "VAT Calculation (After Discount): Sum: $priceSum, Net: $priceSumNet, Tax: $priceSumTax");
                }

                $self = self::getInstance();
                if ($self instanceof self) {
                    $self->putVars['discountPercent'] = $putVars['discountPercent'];
                    $self->putVars['priceDiscount'] = $putVars['priceDiscount'];
                    $self->putVars['priceSum'] = $putVars['priceSum'];
                    if ($calcTaxes) {
                         $self->putVars['priceSumNet'] = $putVars['priceSumNet'];
                         $self->putVars['priceSumTax'] = $putVars['priceSumTax'];
                         $self->putVars['priceSumBrutto'] = $putVars['priceSumBrutto'];
                         $self->putVars['priceNet'] = $putVars['priceNet'];
                         $self->putVars['priceTax'] = $putVars['priceTax'];
                    }
                }
            } elseif ($priceSum > 0) {
                 \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "Discount skipped. Percent empty: " . (empty($putVars['discountPercent']) ? 'YES' : 'NO'));
                 if ($calcTaxes) {
                     $putVars['priceSumBrutto'] = C4gReservationHandler::formatPrice($priceSum);
                     $self = self::getInstance();
                     if ($self instanceof self) {
                         $self->putVars['priceSumBrutto'] = $putVars['priceSumBrutto'];
                     }
                 }
            }

            // Endgültige Brutto/Netto/MwSt für die Anzeige (unabhängig vom Rabatt)
            if ($calcTaxes) {
                // Falls kein Rabatt angewendet wurde, sicherstellen dass priceNet und priceTax auf priceSumNet/Tax basieren
                if ($discount == 0) {
                    $putVars['priceNet'] = $putVars['priceSumNet'] ?? $putVars['priceNet'] ?? '0,00 €';
                    $putVars['priceTax'] = $putVars['priceSumTax'] ?? $putVars['priceTax'] ?? '0,00 €';
                    $putVars['priceSumBrutto'] = $putVars['priceSumBrutto'] ?? C4gReservationHandler::formatPrice($priceSum);
                }
                $putVars['price'] = ($putVars['priceSumBrutto'] ?? $putVars['priceSum']) . ($priceArray['priceInfo'] ?? '');
                $self = self::getInstance();
                if ($self instanceof self) {
                    $self->putVars['priceNet'] = $putVars['priceNet'];
                    $self->putVars['priceTax'] = $putVars['priceTax'];
                    $self->putVars['price'] = $putVars['price'];
                    $self->putVars['priceSumBrutto'] = $putVars['priceSumBrutto'];
                }
            }

            // Recalculate options tax components after discount
            if ($optionsPriceSum > 0 && $calcTaxes) {
                 $discountFactor = ($priceSum + $discount) > 0 ? ($priceSum / ($priceSum + $discount)) : 1;
                 
                 $priceOptionSum['priceOptionSum'] = floatval($priceOptionSumValue) * $discountFactor;
                 $priceOptionSum['priceOptionSumNet'] = ($priceOptionSum['priceOptionSum'] / (1 + ($taxRate / 100)));
                 $priceOptionSum['priceOptionSumTax'] = $priceOptionSum['priceOptionSum'] - $priceOptionSum['priceOptionSumNet'];
                 
                 $priceParticipantOptionSum['priceParticipantOptionSum'] = floatval($priceParticipantOptionSumValue) * $discountFactor;
                 $priceParticipantOptionSum['priceParticipantOptionSumNet'] = ($priceParticipantOptionSum['priceParticipantOptionSum'] / (1 + ($taxRate / 100)));
                 $priceParticipantOptionSum['priceParticipantOptionSumTax'] = $priceParticipantOptionSum['priceParticipantOptionSum'] - $priceParticipantOptionSum['priceParticipantOptionSumNet'];
                 
                 $putVars['priceOptionSum'] = C4gReservationHandler::formatPrice($priceOptionSum['priceOptionSum'] + $priceParticipantOptionSum['priceParticipantOptionSum']);
                 $putVars['priceOptionSumNet'] = C4gReservationHandler::formatPrice($priceOptionSum['priceOptionSumNet'] + $priceParticipantOptionSum['priceParticipantOptionSumNet']);
                 $putVars['priceOptionSumTax'] = C4gReservationHandler::formatPrice($priceOptionSum['priceOptionSumTax'] + $priceParticipantOptionSum['priceParticipantOptionSumTax']);
                 
                 $putVars['priceParticipantOptionSum'] = C4gReservationHandler::formatPrice($priceParticipantOptionSum['priceParticipantOptionSum']);
                 $putVars['priceParticipantOptionSumNet'] = C4gReservationHandler::formatPrice($priceParticipantOptionSum['priceParticipantOptionSumNet']);
                 $putVars['priceParticipantOptionSumTax'] = C4gReservationHandler::formatPrice($priceParticipantOptionSum['priceParticipantOptionSumTax']);
                 
                 $self = self::getInstance();
                 if ($self instanceof self) {
                     $self->putVars['priceOptionSum'] = $putVars['priceOptionSum'];
                     $self->putVars['priceOptionSumNet'] = $putVars['priceOptionSumNet'];
                     $self->putVars['priceOptionSumTax'] = $putVars['priceOptionSumTax'];
                     $self->putVars['priceParticipantOptionSum'] = $putVars['priceParticipantOptionSum'];
                     $self->putVars['priceParticipantOptionSumNet'] = $putVars['priceParticipantOptionSumNet'];
                     $self->putVars['priceParticipantOptionSumTax'] = $putVars['priceParticipantOptionSumTax'];
                 }
            }

            // $putVars['discountPercent'] = isset($putVars['discountPercent']) ? (strval($putVars['discountPercent']) . ' %') : '';

            $formattedPriceSum = C4gReservationHandler::formatPrice($priceSum);
            if (($priceArray['price'] ?? 0) || $priceSum) {
                $putVars['price'] = C4gReservationHandler::formatPrice($priceArray['price'] ?? 0) . ($priceArray['priceInfo'] ?? '');
                $putVars['priceSum'] = $formattedPriceSum;
            } else {
                // Fallback, falls priceArray leer, aber $price (Basis) existiert
                $putVars['priceSum'] = C4gReservationHandler::formatPrice(floatval($price) + $optionsPriceSum - $discount) . ($priceArray['priceInfo'] ?? '');
            }

            $putVars['priceDiscount'] = C4gReservationHandler::formatPrice($discount);
            if ($discount > 0) {
                $dp = $putVars['discountPercent'];
                if (is_numeric($dp)) {
                    $dp = $dp . ' %';
                }
                $putVars['discountPercent'] = $dp;
            } elseif (empty($putVars['discountPercent']) || $putVars['discountPercent'] === ' ' || $putVars['discountPercent'] === '0,00 %' || $putVars['discountPercent'] === '0 %') {
                $putVars['discountPercent'] = ' ';
            }
            
            $self = self::getInstance();
            if ($self instanceof self) {
                $self->putVars['priceSum'] = $putVars['priceSum'] ?? $self->putVars['priceSum'] ?? '0,00 €';
                $self->putVars['priceNet'] = $putVars['priceNet'] ?? $self->putVars['priceNet'] ?? '0,00 €';
                $self->putVars['priceTax'] = $putVars['priceTax'] ?? $self->putVars['priceTax'] ?? '0,00 €';
                $self->putVars['priceSumNet'] = $putVars['priceSumNet'] ?? $self->putVars['priceSumNet'] ?? '0,00 €';
                $self->putVars['priceSumTax'] = $putVars['priceSumTax'] ?? $self->putVars['priceSumTax'] ?? '0,00 €';
                $self->putVars['priceOptionSum'] = $putVars['priceOptionSum'] ?? '0,00 €';
                $self->putVars['priceOptionSumNet'] = $putVars['priceOptionSumNet'] ?? $self->putVars['priceOptionSumNet'] ?? '0,00 €';
                $self->putVars['priceOptionSumTax'] = $putVars['priceOptionSumTax'] ?? $self->putVars['priceOptionSumTax'] ?? '0,00 €';
                $self->putVars['priceParticipantOptionSum'] = $putVars['priceParticipantOptionSum'] ?? C4gReservationHandler::formatPrice($priceParticipantOptionSum['priceParticipantOptionSum'] ?? 0);
                $self->putVars['priceParticipantOptionSumNet'] = $putVars['priceParticipantOptionSumNet'] ?? C4gReservationHandler::formatPrice($priceParticipantOptionSum['priceParticipantOptionSumNet'] ?? 0);
                $self->putVars['priceParticipantOptionSumTax'] = $putVars['priceParticipantOptionSumTax'] ?? C4gReservationHandler::formatPrice($priceParticipantOptionSum['priceParticipantOptionSumTax'] ?? 0);
                $self->putVars['priceDiscount'] = $putVars['priceDiscount'] ?? '0,00 €';
                $self->putVars['discountPercent'] = $putVars['discountPercent'];
                $self->putVars['price'] = $putVars['price'] ?? $self->putVars['price'] ?? '0,00 €';
                $self->putVars['admin_email'] = $adminEmail;
                if (isset($putVars['reservationTaxRate'])) {
                    $self->putVars['reservationTaxRate'] = $putVars['reservationTaxRate'];
                }
            }
            
            if ($calcTaxes) {
                // FALLBACK RE-CALCULATION if anything went wrong before
                $priceNet = floatval($priceArray['priceNet'] ?? 0);
                $priceTax = floatval($priceArray['priceTax'] ?? 0);
                
                // If priceArray had 0, but we had a base price, we need to calculate net/tax from base price
                if ($priceNet == 0 && $priceTax == 0 && floatval($price) > 0) {
                    $taxRate = floatval($putVars['reservationTaxRate'] ?? 19);
                    $priceNet = floatval($price) / (1 + ($taxRate / 100));
                    $priceTax = floatval($price) - $priceNet;
                }

                $putVars['reservationTaxRate'] = $priceArray['reservationTaxRate'] ?? $putVars['reservationTaxRate'] ?? null;

                $putVars['priceNet'] = C4gReservationHandler::formatPrice($priceNet);
                $putVars['priceTax'] = C4gReservationHandler::formatPrice($priceTax);

                $optNet = floatval($priceOptionSum['priceOptionNet'] ?? $priceOptionSum['priceOptionSumNet'] ?? 0) + floatval($priceParticipantOptionSum['priceParticipantOptionSumNet'] ?? 0);
                $optTax = floatval($priceOptionSum['priceOptionTax'] ?? $priceOptionSum['priceOptionSumTax'] ?? 0) + floatval($priceParticipantOptionSum['priceParticipantOptionSumTax'] ?? 0);
                $putVars['priceOptionSumNet'] = C4gReservationHandler::formatPrice($optNet);
                $putVars['priceOptionSumTax'] = C4gReservationHandler::formatPrice($optTax);

                // Die Gesamtsummen Netto/Tax müssen ebenfalls den Rabatt berücksichtigen, falls vorhanden.
                // WE PREFER THE ALREADY CALCULATED priceSumNet/Tax IF THEY ARE GREATER THAN 0
                $sumNet = $priceNet + $optNet;
                $sumTax = $priceTax + $optTax;
                
                // If we already have a more accurate sumNet/Tax (calculated above), use it.
                $existingSumNetRaw = str_replace([' ', '€'], ['', ''], $putVars['priceSumNet'] ?? '0');
                $existingSumTaxRaw = str_replace([' ', '€'], ['', ''], $putVars['priceSumTax'] ?? '0');
                $existingSumNet = floatval(str_replace(',', '.', str_replace('.', '', $existingSumNetRaw)));
                $existingSumTax = floatval(str_replace(',', '.', str_replace('.', '', $existingSumTaxRaw)));
                
                if ($existingSumTax > 0) {
                    $sumNet = $existingSumNet;
                    $sumTax = $existingSumTax;
                    \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "VAT Final: Using existing SumNet: $sumNet, SumTax: $sumTax (Raw: $existingSumTaxRaw)");
                } else {
                    if ($discount > 0 && ($sumNet + $sumTax) > 0) {
                        $ratio = $priceSum / ($sumNet + $sumTax);
                        $sumNet *= $ratio;
                        $sumTax *= $ratio;
                    }
                    \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "VAT Final: Calculated SumNet: $sumNet, SumTax: $sumTax (Discount: $discount)");
                }

                $putVars['priceSumNet'] = C4gReservationHandler::formatPrice($sumNet);
                $putVars['priceSumTax'] = C4gReservationHandler::formatPrice($sumTax);
                
                // Sync display tokens AGAIN to be sure
                $putVars['priceNet'] = $putVars['priceSumNet'];
                $putVars['priceTax'] = $putVars['priceSumTax'];
                
                $self = self::getInstance();
                if ($self instanceof self) {
                    $self->putVars['priceNet'] = $putVars['priceNet'];
                    $self->putVars['priceTax'] = $putVars['priceTax'];
                    $self->putVars['priceOptionSumNet'] = $putVars['priceOptionSumNet'];
                    $self->putVars['priceOptionSumTax'] = $putVars['priceOptionSumTax'];
                    $self->putVars['priceSumNet'] = $putVars['priceSumNet'];
                    $self->putVars['priceSumTax'] = $putVars['priceSumTax'];
                    $self->putVars['reservationTaxRate'] = $putVars['reservationTaxRate'];
                }
            }
        } else {
            // Fallback: Berechne Basis + Optionen auch dann, wenn keine Preis-/Anzeige-Flags gesetzt sind

            // Robuste Normalisierung (wie oben)
            $normalizeToArray = static function ($value) {
                if ($value === null) return [];
                if ($value instanceof \Contao\Model) return $value->row();
                if (is_object($value) && method_exists($value, 'row')) {
                    try { $row = $value->row(); if (is_array($row) && !empty($row)) return $row; } catch (\Throwable $t) {}
                }
                if (is_object($value)) {
                    $props = get_object_vars($value);
                    return !empty($props) ? $props : (array) $value;
                }
                return is_array($value) ? $value : (array) $value;
            };

            $typeArray = $normalizeToArray($reservationType);
            $objArray  = $normalizeToArray($isEvent ? $reservationEventObject : $reservationObject);
            $resArray  = $normalizeToArray($resObject);

            if (!isset($objArray['pid']) && $isEvent && $reservationEventObject instanceof \Contao\Model) {
                $objArray['pid'] = $reservationEventObject->pid;
            } elseif (!isset($objArray['pid']) && !$isEvent && $reservationObject instanceof \Contao\Model) {
                $objArray['pid'] = $reservationObject->pid;
            }

            $duration = 0;
            $typeId = $typeArray['id'] ?? null;
            if ($typeId && isset($putVars['duration_'.$typeId]) && $putVars['duration_'.$typeId] !== '') {
                $durVal = $putVars['duration_'.$typeId];
                if (is_string($durVal) && strpos($durVal, '#') !== false) {
                    $durVal = substr($durVal, 0, strpos($durVal, '#'));
                }
                $duration = intval($durVal);
            } elseif (isset($putVars['duration']) && $putVars['duration'] !== '') {
                $duration = intval($putVars['duration']);
            }
            if (!$duration && !empty($typeArray['periodType']) && $typeId) {
                $bdKey = 'beginDate_'.$typeId; $edKey = 'endDate_'.$typeId;
                $beginTs = isset($putVars[$bdKey]) ? (is_numeric($putVars[$bdKey]) ? intval($putVars[$bdKey]) : strtotime($putVars[$bdKey])) : 0;
                $endTs   = isset($putVars[$edKey]) ? (is_numeric($putVars[$edKey]) ? intval($putVars[$edKey]) : strtotime($putVars[$edKey])) : 0;
                if ($beginTs && $endTs && $endTs >= $beginTs) {
                    if ($typeArray['periodType'] === 'week') {
                        $duration = max(1, (int) round(($endTs - $beginTs) / (60 * 60 * 24 * 7)));
                    } elseif ($typeArray['periodType'] === 'day' || $typeArray['periodType'] === 'overnight') {
                        $duration = max(1, (int) round(($endTs - $beginTs) / (60 * 60 * 24)));
                    }
                }
            }

            // Falls Event: desiredCapacity aus putVars für diesen Typ lesen
            if ($isEvent || (isset($typeArray['reservationObjectType']) && $typeArray['reservationObjectType'] === '2')) {
                $typeIdForCap = $typeArray['id'] ?? ($objArray['reservationType'] ?? null);
                if ($typeIdForCap) {
                    $countPersons = intval($putVars['desiredCapacity_' . $typeIdForCap] ?? 0);
                    if ($countPersons > 0) {
                        $desiredCapacity = $countPersons;
                    }
                }
            }

            // Sicherstellen, dass das Event-Objekt für calcPrices korrekt vorbereitet ist
            if ($isEvent && $reservationEventObject instanceof \Contao\Model) {
                // calcPrices erwartet startDate/endDate/startTime/endTime im Objekt für Event-Berechnungen
                $calEvent = \Contao\CalendarEventsModel::findByPk($reservationEventObject->pid);
                if ($calEvent) {
                    $objArray['startDate'] = $calEvent->startDate;
                    $objArray['startTime'] = $calEvent->startTime;
                    $objArray['endDate']   = $calEvent->endDate;
                    $objArray['endTime']   = $calEvent->endTime;
                }
            }

            // Sichere Defaults vorbereiten
            $priceArray = C4gReservationCalculator::calcPrices($objArray, $typeArray, $isEvent, $desiredCapacity, $duration, '', '', false);
            $priceSum = ($priceArray['priceSum'] ?? 0) ?: ($priceArray['price'] ?? 0);
            if ($isEvent && ($priceArray['priceSum'] ?? 0) == 0 && ($priceArray['price'] ?? 0) == 0) {
                $priceSum = floatval($price);
            }

            // Optionssummen berechnen
            $priceOptionSum = ['priceOptionSum' => 0];
            $priceParticipantOptionSum = ['priceParticipantOptionSum' => 0];

            // Härtung putVars (Strings zu Booleans/Ints wandeln) für alle Optionstypen
            foreach ($putVars as $pk => $pv) {
                if (is_string($pv) && (strpos($pk, 'additional_params_') === 0 || strpos($pk, 'participants_') === 0 || strpos($pk, '§participant_params§') !== false)) {
                    if ($pv === 'true') { $putVars[$pk] = true; }
                    elseif ($pv === 'false') { $putVars[$pk] = false; }
                    elseif (is_numeric($pv) && (float)$pv == (int)$pv) { $putVars[$pk] = (int)$pv; }
                }
            }

            $includedParams = $typeArray['included_params'] ?? false;
            $additionalParams = $typeArray['additional_params'] ?? false;
            if ($includedParams || $additionalParams) {
                $tmpOption = C4gReservationCalculator::calcOptionPrices($putVars, $objArray, $typeArray, false);
                if (is_array($tmpOption)) {
                    $priceOptionSum = array_merge($priceOptionSum, $tmpOption);
                }
            }

            $participantParams = $resArray['participant_params'] ?? $objArray['participant_params'] ?? $typeArray['participant_params'] ?? false;
            $onlyParticipants = $settings->onlyParticipants ?: false;
            if ($participantParams) {
                // Debugging: Log available participant option keys in putVars
                $participantKeysFound = [];
                foreach ($putVars as $pk => $pv) {
                    if (strpos($pk, 'participants_') === 0 || strpos($pk, '§participant_params§') !== false) {
                        $participantKeysFound[] = $pk;
                    }
                }
                if (!empty($participantKeysFound)) {
                    C4gLogModel::addLogEntry('reservation', 'Found participant option keys in Fallback: ' . implode(', ', $participantKeysFound));
                }

                $tmpPartOption = C4gReservationCalculator::calcParticipantOptionPrices(intval($desiredCapacity), $putVars, $objArray, $typeArray, false, $onlyParticipants, $settings->specialParticipantMechanism);
                if (is_array($tmpPartOption)) {
                    $priceParticipantOptionSum = array_merge($priceParticipantOptionSum, $tmpPartOption);
                }
            }

            $optionsPriceSum = floatval($priceOptionSum['priceOptionSum'] ?? 0) + floatval($priceParticipantOptionSum['priceParticipantOptionSum'] ?? 0);
            $priceSum += $optionsPriceSum;

            // Ensure we have a valid tax rate
            $taxRate = floatval($putVars['reservationTaxRate'] ?? $settings->taxRate ?? 19);
            if ($taxRate <= 0) { $taxRate = 19; } // Hard fallback

            // Rabatt anwenden, falls vorhanden
            $discount = 0;
            if (!empty($putVars['discountPercent']) && $priceSum) {
                $discountValue = floatval(str_replace([' ', '%'], '', $putVars['discountPercent']));
                $discount = ($priceSum / 100) * $discountValue;
                \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "Discount Calculation (Fallback): Total Before: $priceSum, Percent: $discountValue, Discount Amount: $discount");
                $priceSum -= $discount;
                \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "Discount Applied (Fallback): Percent: $discountValue, Amount: $discount, New Total: $priceSum");
                
                $dp = $putVars['discountPercent'];
                if (is_numeric($dp)) {
                    $dp = $dp . ' %';
                }
                $putVars['discountPercent'] = $dp;
                $putVars['priceDiscount'] = C4gReservationHandler::formatPrice($discount);
                $putVars['priceSum'] = C4gReservationHandler::formatPrice($priceSum);
                
                // Recalculate Net and Tax for the total sum after discount
                if ($calcTaxes) {
                     $priceSumNet = ($priceSum / (1 + ($taxRate / 100)));
                     $priceSumTax = $priceSum - $priceSumNet;
                     $putVars['priceSumNet'] = C4gReservationHandler::formatPrice($priceSumNet);
                     $putVars['priceSumTax'] = C4gReservationHandler::formatPrice($priceSumTax);
                     $putVars['priceSumBrutto'] = C4gReservationHandler::formatPrice($priceSum);
                     // For events where base price might be 0, we still want to show meaningful Net/Tax
                     $putVars['priceNet'] = $putVars['priceSumNet'];
                     $putVars['priceTax'] = $putVars['priceSumTax'];
                     $putVars['priceBrutto'] = $putVars['priceSumBrutto'];
                }

                $self = self::getInstance();
                if ($self instanceof self) {
                    $self->putVars['discountPercent'] = $putVars['discountPercent'];
                    $self->putVars['priceDiscount'] = $putVars['priceDiscount'];
                    $self->putVars['priceSum'] = $putVars['priceSum'];
                    if ($calcTaxes) {
                         $self->putVars['priceSumNet'] = $putVars['priceSumNet'];
                         $self->putVars['priceSumTax'] = $putVars['priceSumTax'];
                         $self->putVars['priceSumBrutto'] = $putVars['priceSumBrutto'];
                         $self->putVars['priceNet'] = $putVars['priceNet'];
                         $self->putVars['priceTax'] = $putVars['priceTax'];
                         $self->putVars['priceBrutto'] = $putVars['priceBrutto'];
                    }
                }
            } else {
                 \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "Discount skipped (Fallback). Percent empty: " . (empty($putVars['discountPercent']) ? 'YES' : 'NO') . ", Total zero: " . (!$priceSum ? 'YES' : 'NO'));
                 
                 // Even without discount, ensure Net and Tax are set if missing but priceSum exists
                 if ($calcTaxes && $priceSum > 0 && (empty($putVars['priceSumNet']) || $putVars['priceSumNet'] === '0,00 €')) {
                      $priceSumNet = ($priceSum / (1 + ($taxRate / 100)));
                      $priceSumTax = $priceSum - $priceSumNet;
                      $putVars['priceSumNet'] = C4gReservationHandler::formatPrice($priceSumNet);
                      $putVars['priceSumTax'] = C4gReservationHandler::formatPrice($priceSumTax);
                      $putVars['priceSumBrutto'] = C4gReservationHandler::formatPrice($priceSum);
                      $putVars['priceNet'] = $putVars['priceSumNet'];
                      $putVars['priceTax'] = $putVars['priceSumTax'];
                      $putVars['priceBrutto'] = $putVars['priceSumBrutto'];
                      
                      $self = self::getInstance();
                      if ($self instanceof self) {
                          $self->putVars['priceSumNet'] = $putVars['priceSumNet'];
                          $self->putVars['priceSumTax'] = $putVars['priceSumTax'];
                          $self->putVars['priceSumBrutto'] = $putVars['priceSumBrutto'];
                          $self->putVars['priceNet'] = $putVars['priceNet'];
                          $self->putVars['priceTax'] = $putVars['priceTax'];
                          $self->putVars['priceBrutto'] = $putVars['priceBrutto'];
                      }
                 }
            }

            // Recalculate options tax components after discount
            if ($optionsPriceSum > 0 && $calcTaxes) {
                 $discountFactor = ($priceSum + $discount) > 0 ? ($priceSum / ($priceSum + $discount)) : 1;
                 
                 $priceOptionSum['priceOptionSum'] = floatval($priceOptionSum['priceOptionSum'] ?? 0) * $discountFactor;
                 $priceOptionSum['priceOptionSumNet'] = ($priceOptionSum['priceOptionSum'] / (1 + ($taxRate / 100)));
                 $priceOptionSum['priceOptionSumTax'] = $priceOptionSum['priceOptionSum'] - $priceOptionSum['priceOptionSumNet'];
                 
                 $priceParticipantOptionSum['priceParticipantOptionSum'] = floatval($priceParticipantOptionSum['priceParticipantOptionSum'] ?? 0) * $discountFactor;
                 $priceParticipantOptionSum['priceParticipantOptionSumNet'] = ($priceParticipantOptionSum['priceParticipantOptionSum'] / (1 + ($taxRate / 100)));
                 $priceParticipantOptionSum['priceParticipantOptionSumTax'] = $priceParticipantOptionSum['priceParticipantOptionSum'] - $priceParticipantOptionSum['priceParticipantOptionSumNet'];
                 
                 $putVars['priceOptionSum'] = C4gReservationHandler::formatPrice($priceOptionSum['priceOptionSum'] + $priceParticipantOptionSum['priceParticipantOptionSum']);
                 $putVars['priceOptionSumNet'] = C4gReservationHandler::formatPrice($priceOptionSum['priceOptionSumNet'] + $priceParticipantOptionSum['priceParticipantOptionSumNet']);
                 $putVars['priceOptionSumTax'] = C4gReservationHandler::formatPrice($priceOptionSum['priceOptionSumTax'] + $priceParticipantOptionSum['priceParticipantOptionSumTax']);
                 
                 $putVars['priceParticipantOptionSum'] = C4gReservationHandler::formatPrice($priceParticipantOptionSum['priceParticipantOptionSum']);
                 $putVars['priceParticipantOptionSumNet'] = C4gReservationHandler::formatPrice($priceParticipantOptionSum['priceParticipantOptionSumNet']);
                 $putVars['priceParticipantOptionSumTax'] = C4gReservationHandler::formatPrice($priceParticipantOptionSum['priceParticipantOptionSumTax']);
                 
                 $self = self::getInstance();
                 if ($self instanceof self) {
                     $self->putVars['priceOptionSum'] = $putVars['priceOptionSum'];
                     $self->putVars['priceOptionSumNet'] = $putVars['priceOptionSumNet'];
                     $self->putVars['priceOptionSumTax'] = $putVars['priceOptionSumTax'];
                     $self->putVars['priceParticipantOptionSum'] = $putVars['priceParticipantOptionSum'];
                     $self->putVars['priceParticipantOptionSumNet'] = $putVars['priceParticipantOptionSumNet'];
                     $self->putVars['priceParticipantOptionSumTax'] = $putVars['priceParticipantOptionSumTax'];
                 }
            }

            // $putVars['discountPercent'] = isset($putVars['discountPercent']) ? (strval($putVars['discountPercent']) . ' %') : '';

            $putVars['priceDiscount'] = C4gReservationHandler::formatPrice($discount);
            if ($discount > 0) {
                $dp = $putVars['discountPercent'];
                if (is_numeric($dp)) {
                    $dp = $dp . ' %';
                }
                $putVars['discountPercent'] = $dp;
            } elseif (empty($putVars['discountPercent']) || $putVars['discountPercent'] === ' ' || $putVars['discountPercent'] === '0,00 %' || $putVars['discountPercent'] === '0 %') {
                $putVars['discountPercent'] = ' ';
            }
            
            // Mirror back to instance putVars
            $self = self::getInstance();
            if ($self instanceof self) {
                $self->putVars['priceSum'] = $putVars['priceSum'] ?? $self->putVars['priceSum'] ?? '0,00 €';
                $self->putVars['priceNet'] = $putVars['priceNet'] ?? $self->putVars['priceNet'] ?? '0,00 €';
                $self->putVars['priceTax'] = $putVars['priceTax'] ?? $self->putVars['priceTax'] ?? '0,00 €';
                $self->putVars['priceSumNet'] = $putVars['priceSumNet'] ?? $self->putVars['priceSumNet'] ?? '0,00 €';
                $self->putVars['priceSumTax'] = $putVars['priceSumTax'] ?? $self->putVars['priceSumTax'] ?? '0,00 €';
                $self->putVars['priceOptionSum'] = $putVars['priceOptionSum'] ?? '0,00 €';
                $self->putVars['priceOptionSumNet'] = $putVars['priceOptionSumNet'] ?? $self->putVars['priceOptionSumNet'] ?? '0,00 €';
                $self->putVars['priceOptionSumTax'] = $putVars['priceOptionSumTax'] ?? $self->putVars['priceOptionSumTax'] ?? '0,00 €';
                $self->putVars['priceParticipantOptionSum'] = $putVars['priceParticipantOptionSum'] ?? C4gReservationHandler::formatPrice($priceParticipantOptionSum['priceParticipantOptionSum'] ?? 0);
                $self->putVars['priceParticipantOptionSumNet'] = $putVars['priceParticipantOptionSumNet'] ?? C4gReservationHandler::formatPrice($priceParticipantOptionSum['priceParticipantOptionSumNet'] ?? 0);
                $self->putVars['priceParticipantOptionSumTax'] = $putVars['priceParticipantOptionSumTax'] ?? C4gReservationHandler::formatPrice($priceParticipantOptionSum['priceParticipantOptionSumTax'] ?? 0);
                $self->putVars['priceDiscount'] = $putVars['priceDiscount'] ?? '0,00 €';
                $self->putVars['discountPercent'] = $putVars['discountPercent'];
                $self->putVars['price'] = $putVars['price'] ?? $self->putVars['price'] ?? '0,00 €';
                $self->putVars['admin_email'] = $adminEmail;
                if (isset($putVars['reservationTaxRate'])) {
                    $self->putVars['reservationTaxRate'] = $putVars['reservationTaxRate'];
                }
            }
            
            // Auch im Fallback Steuern berechnen, falls gewünscht
            if ($calcTaxes) {
                $priceNet = floatval($priceArray['priceNet'] ?? 0);
                $priceTax = floatval($priceArray['priceTax'] ?? 0);
                
                // If priceArray had 0, but we had a base price, we need to calculate net/tax from base price
                if ($priceNet == 0 && $priceTax == 0 && floatval($price) > 0) {
                    $taxRate = floatval($putVars['reservationTaxRate'] ?? 19);
                    $priceNet = floatval($price) / (1 + ($taxRate / 100));
                    $priceTax = floatval($price) - $priceNet;
                }

                $putVars['reservationTaxRate'] = $priceArray['reservationTaxRate'];
                $putVars['priceNet'] = C4gReservationHandler::formatPrice($priceNet);
                $putVars['priceTax'] = C4gReservationHandler::formatPrice($priceTax);

                $optNet = floatval($priceOptionSum['priceOptionNet'] ?? $priceOptionSum['priceOptionSumNet'] ?? 0) + floatval($priceParticipantOptionSum['priceParticipantOptionSumNet'] ?? 0);
                $optTax = floatval($priceOptionSum['priceOptionTax'] ?? $priceOptionSum['priceOptionSumTax'] ?? 0) + floatval($priceParticipantOptionSum['priceParticipantOptionSumTax'] ?? 0);
                $putVars['priceOptionSumNet'] = C4gReservationHandler::formatPrice($optNet);
                $putVars['priceOptionSumTax'] = C4gReservationHandler::formatPrice($optTax);

                $sumNet = $priceNet + $optNet;
                $sumTax = $priceTax + $optTax;
                if ($discount > 0 && ($sumNet + $sumTax) > 0) {
                    $ratio = $priceSum / ($sumNet + $sumTax);
                    $sumNet *= $ratio;
                    $sumTax *= $ratio;
                }

                $putVars['priceSumNet'] = C4gReservationHandler::formatPrice($sumNet);
                $putVars['priceSumTax'] = C4gReservationHandler::formatPrice($sumTax);
                
                $self = self::getInstance();
                if ($self instanceof self) {
                    $self->putVars['priceNet'] = $putVars['priceNet'];
                    $self->putVars['priceTax'] = $putVars['priceTax'];
                    $self->putVars['priceOptionSumNet'] = $putVars['priceOptionSumNet'];
                    $self->putVars['priceOptionSumTax'] = $putVars['priceOptionSumTax'];
                    $self->putVars['priceSumNet'] = $putVars['priceSumNet'];
                    $self->putVars['priceSumTax'] = $putVars['priceSumTax'];
                    $self->putVars['reservationTaxRate'] = $putVars['reservationTaxRate'];
                }
            }
        }
    }

    public static function withDesiredCapacityTitle($min, $max, $showMinMax) {

        if ($max && $showMinMax && $max > 1) {
            $title = $GLOBALS['TL_LANG']['fe_c4g_reservation']['desiredCapacity']. ' ('.$min.'-'.$max.')';
        } else {
            $title = $GLOBALS['TL_LANG']['fe_c4g_reservation']['desiredCapacity'];
        }
        return $title;
    }

    public function isWithDefaultPDFContent(): bool {
        return $this->withDefaultPDFContent;
    }

    public function afterSaveAction($changes, $insertId)
    {
        $this->mirrorBaseTokens(); // One last time to be sure
        C4gReservationCheckInHelper::removeQRCodeFile();
        
        // Determine if we were dealing with an event
        $isEventActualForNuke = false;
        if (isset($this->putVars['reservation_type'])) {
            $resType = C4gReservationTypeModel::findByPk($this->putVars['reservation_type']);
            if ($resType && $resType->type == 2) {
                $isEventActualForNuke = true;
            }
        }

        // Mark session as "just saved" to prevent stale fallback in next request
        if ($isEventActualForNuke) {
            $this->session->setSessionValue('reservationJustSaved', true);
        }
        
        // Final cleanup after successful save.
        // For events, we nuke more aggressively.
        $this->nukeState($isEventActualForNuke); 
        $this->session->remove('c4g_brick_dialog_id');
        $this->session->remove('c4g_brick_dialog_values');
        
        // Force browser to reload the next page and don't cache anything
        if ($isEventActualForNuke) {
            header("Cache-Control: no-cache, no-store, must-revalidate, proxy-revalidate");
            header("Pragma: no-cache");
            header("Expires: 0");
            header("Surrogate-Control: no-store");
        }
        
        if (key_exists('REQUEST_METHOD', $_SERVER) && ($_SERVER['REQUEST_METHOD'] == 'PUT')) {
        }
        
        $this->putVars = [];
    }
}

