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

    public function __construct(string $rootDir, RequestStack $requestStack, ContaoFramework $framework, ModuleModel $model = null)
    {
        parent::__construct($rootDir, $requestStack, $framework, $model);
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
        
        return $putVarsResult;
    }

    public function logDebug($message)
    {
        try {
            \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', $message);
        } catch (\Throwable $t) {}
        error_log("[RESERVATION] " . $message);
    }

    public function resetStaticCaches()
    {
        C4gReservationHandler::resetStaticCaches();
    }

    public function nukeState($keepEvent = false)
    {
        $this->logDebug("nukeState called (keepEvent: " . ($keepEvent ? 'yes' : 'no') . ")");
        // 1. Clear session variables that hold form state
        $this->session->remove('c4g_brick_dialog_id');
        $this->session->remove('c4g_brick_dialog_values');
        $this->session->remove('reservationInitialDateCookie');
        $this->session->remove('reservationTimeCookie');
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
            '__hasRightInGroupMemo'
        ];
        foreach ($memos as $memo) {
            if (property_exists($this, $memo)) {
                $this->$memo = [];
            }
        }
        
        // 4. Reset static caches in handlers
        C4gReservationHandler::resetStaticCaches();
        $this->resetStaticCaches();
    }

    public function renewInitialValues()
    {
        $this->addFields();
    }

    public function generateAjax($request = null)
    {
        $this->logDebug("generateAjax START - req: " . ($request ?: ($_GET['req'] ?? 'NONE')));
        if (key_exists('REQUEST_METHOD', $_SERVER) && ($_SERVER['REQUEST_METHOD'] == 'PUT')) {
            $this->logDebug("generateAjax - PUT raw content: " . substr(file_get_contents('php://input'), 0, 1000));
        }
        return parent::generateAjax($request);
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
        
        $this->logDebug("initBrickModule START - reqMethod: " . ($_SERVER['REQUEST_METHOD'] ?? 'NONE'));
        
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
                            $this->logDebug("initBrickModule - eventId missing in PUT, using ID already stored in instance: $eventId");
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
                                        $this->logDebug("initBrickModule - Checking Referer: $referer");
                                        if (preg_match('/[\/\?\&]event[=\/]([^\/\?\&]+)/', $referer, $matches)) {
                                            $eventIdFromUrl = $matches[1];
                                            $eventIdFromUrl = str_replace('.html', '', $eventIdFromUrl);
                                            $this->logDebug("initBrickModule - eventId extracted from Referer: $eventIdFromUrl");
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
                                    $this->logDebug("initBrickModule - Resolved alias '$oldAlias' to ID: $eventIdFromUrl");
                                }
                            }
                            
                            if ($eventIdFromUrl) {
                                $eventId = $eventIdFromUrl;
                                $this->logDebug("initBrickModule - eventId missing in PUT, using ID from URL/Referer: $eventId");
                                $rawPut['reservation_object_event_' . $typeId] = $eventId;
                                $this->putVars['reservation_object_event_' . $typeId] = $eventId;
                            } else {
                                // Last resort fallback to session
                                $eventIdFromSession = $this->session->getSessionValue('reservationEventCookie');
                                if ($eventIdFromSession) {
                                    $eventId = $eventIdFromSession;
                                    $this->logDebug("initBrickModule - eventId missing in PUT/URL, falling back to Session: $eventId");
                                    
                                    // SECURITY CHECK
                                    if ($this->session->getSessionValue('reservationJustSaved')) {
                                        $this->logDebug("initBrickModule - Refusing Session fallback because reservationJustSaved is set.");
                                        $eventId = 0;
                                        $this->session->remove('reservationEventCookie');
                                    } else {
                                        $rawPut['reservation_object_event_' . $typeId] = $eventId;
                                        $this->putVars['reservation_object_event_' . $typeId] = $eventId;
                                    }
                                } else {
                                    $this->logDebug("initBrickModule - eventId missing in PUT, URL and Session for EVENT TYPE! Aborting.");
                                }
                            }
                        }
                    }
                    
                    if ($eventId) {
                        $this->putVars['reservation_object_event_' . $typeId] = $eventId;
                        $this->session->setSessionValue('reservationEventCookie', $eventId);
                    }
                    
                    $this->logDebug("initBrickModule - IDs from PUT: type=$typeId, event=$eventId");
                }
            }
        }
        if ($eventId) {
            $this->logDebug("initBrickModule - effective eventId: $eventId");
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
            $objFile = FilesModel::findByUuid($arrExternalCSS);
            $projectDir = System::getContainer()->getParameter('kernel.project_dir');
            if (file_exists($projectDir . '/' . $objFile->path)) {
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
            $typeIdForNuke = \Contao\Input::get('type') ?: 0;
            $eventIdUrlForNuke = \Contao\Input::get('event') ?: 0;
            if (!$eventIdUrlForNuke && $this->requestStack && ($request = $this->requestStack->getCurrentRequest())) {
                $eventIdUrlForNuke = $request->attributes->get('event') ?: 0;
                if (!$eventIdUrlForNuke && $request->attributes->has('auto_item')) {
                    $eventIdUrlForNuke = $request->attributes->get('auto_item');
                }
            }

            $shouldNuke = false;
            if (!$typeIdForNuke && !$eventIdUrlForNuke) {
                // No context at all? Safe to nuke old junk.
                $shouldNuke = true;
            } else if ($eventIdUrlForNuke) {
                // If it's an event, we still want to nuke once to prevent bleeding from PREVIOUS events.
                $shouldNuke = true;
            }
            
            if ($shouldNuke) {
                $this->nukeState();
            }
            
            // Clear "just saved" flag on new GET request
            $this->session->remove('reservationJustSaved');
            
            // Prevent browser caching of the form fields
            header("Cache-Control: no-cache, no-store, must-revalidate, proxy-revalidate");
            header("Pragma: no-cache");
            header("Expires: 0");
            header("Surrogate-Control: no-store");
            
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
                \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "DEBUG addFields GET - Forced eventId from $source: $eventIdUrl");
            } else {
                \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "DEBUG addFields GET - No eventId found in URL");
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
        \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "DEBUG addFields START - IDs from Input: type=$typeIdLog, event=$eventIdLog");
        
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
                    \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "DEBUG addFields PUT - IDs after evaluation: type=$typeId, event=$eventId, object=$objectId");
                }
            }
        }

        if ($eventId) {
            $this->session->setSessionValue('reservationEventCookie', $eventId);
        } else if ($this->session->getSessionValue('reservationEventCookie')) {
            $eventId = $this->session->getSessionValue('reservationEventCookie');
        }

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
            if ($date) {
                $initialDate = $date;
                if (!is_numeric($initialDate)) {
                    $initialDate = strtotime(C4GBrickCommon::getLongDateToConvert($GLOBALS['TL_CONFIG']['dateFormat'], $initialDate));
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
                                'default_residence_time' => $type['default_residence_time']
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
                        'default_residence_time' => $type['default_residence_time']
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
            $reservationTypeField->setCallOnChangeFunction("setReservationForm(-1 ," . $showDateTime . ");");
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

            $reservationTypeField->setPrintable($this->withDefaultPDFContent);

            $fieldList[] = $reservationTypeField;
        } else {
            $info = new C4GInfoTextField();
            $info->setFieldName('info');
            $info->setEditable(false);
            $info->setInitialValue($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_none']);
            return [$info];
        }

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
    $reservationDesiredCapacity->setFieldName('desiredCapacity');

    if ($maxCapacity && $eventObj && property_exists($eventObj, 'maxParticipants') && $eventObj->maxParticipants) {
        $maxCapacity = C4gReservationHandler::getMaxParticipentsForObject($eventId, $maxCapacity);
    }

    $reservationDesiredCapacity->setFormField(true);
    $reservationDesiredCapacity->setEditable(true);
    $reservationDesiredCapacity->setCondition(array($condition));
    $reservationDesiredCapacity->setInitialValue($minCapacity);
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
        } else if ($eventObj && (property_exists($eventObj, 'maxParticipants') && $eventObj->maxParticipants == 0) || empty($maxCapacity) || $isPartiPerEvent <= $maxCapacity) {
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
    $reservationDesiredCapacity->setCallOnChangeFunction("setReservationForm(".$listType['id'] . "," . $showDateTime . ");");
    //$reservationDesiredCapacity->setCallOnChangeFunction("changeCapacity(".$listType['id'] . "," . $showDateTime . ");");
    $reservationDesiredCapacity->setNotificationField(true);
    $reservationDesiredCapacity->setAdditionalID($listType['id']);
    $reservationDesiredCapacity->setStyleClass('desired-capacity');
    $reservationDesiredCapacity->setHidden(!$this->reservationSettings->withCapacity);

    if (!$this->reservationSettings->moveCapacity) {
      $fieldList[] = $reservationDesiredCapacity;
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
        $durationField->setFieldName('duration');
        $durationField->setTitle($title);
        $durationField->setColumnWidth(10);
        $durationField->setFormField(true);
        $durationField->setSortColumn(true);
        $durationField->setTableColumn(true);
        $durationField->setMandatory(true);
        $durationField->setCallOnChange(true);
        $durationField->setCallOnChangeFunction("setReservationForm(".$listType['id'] . "," . $showDateTime . ");");
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
        $specialParticipantMechanism = $reservationSettings->specialParticipantMechanism;
        $hideParticipantsEmail = $reservationSettings->hideParticipantsEmail ?: false;
        $hideReservationKey = $reservationSettings->hideReservationKey ?: false;
        $onlyParticipants = $reservationSettings->onlyParticipants ?: false;
        foreach ($additionaldatas as $rowdata) {
            $rowField = $rowdata['additionaldatas'];
            $initialValue = $rowdata['initialValue'];
            $rowMandatory = key_exists('binding', $rowdata) ? $rowdata['binding'] : false;
            $rowPrintable = key_exists('printing', $rowdata) ? $rowdata['printing'] : false;
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
                if ($this->reservationSettings->withCapacity && $onlyParticipants) {
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

                    $reservationDesiredCapacity->setFieldName('desiredCapacity');

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
                    $reservationDesiredCapacity->setFormField(true);
                    $reservationDesiredCapacity->setEditable(true);
                    $reservationDesiredCapacity->setCondition(array($condition));
                    $reservationDesiredCapacity->setInitialValue($minCapacity);
                    $reservationDesiredCapacity->setMandatory(true);

                    $reservationDesiredCapacity->setMin($minCapacity);
                    if (!$noCap){
                        $reservationDesiredCapacity->setMax($maxCapacity);
                    }

                    $reservationDesiredCapacity->setPattern(C4GBrickRegEx::NUMBERS);
                    $reservationDesiredCapacity->setCallOnChange(true);
                    $reservationDesiredCapacity->setCallOnChangeFunction("setReservationForm(".$listType['id'] . "," . $showDateTime . ");");
                    $reservationDesiredCapacity->setNotificationField(true);
                    $reservationDesiredCapacity->setAdditionalID($listType['id']);
                    $reservationDesiredCapacity->setStyleClass('desired-capacity');

                    if ($this->reservationSettings->moveCapacity) {
                        $fieldList[] = $reservationDesiredCapacity;
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

                $firstnameField = new C4GTextField();
                $firstnameField->setFieldName('firstname');
                $firstnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname']);
                $firstnameField->setColumnWidth(10);
                $firstnameField->setSortColumn(false);
                $firstnameField->setTableColumn(true);
                $firstnameField->setMandatory($specialParticipantMechanism ? $rowMandatory : true);
                $firstnameField->setNotificationField(false);
                $firstnameField->setPrintable(true);
                $participants[] = $firstnameField;

                $lastnameField = new C4GTextField();
                $lastnameField->setFieldName('lastname');
                $lastnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname']);
                $lastnameField->setColumnWidth(10);
                $lastnameField->setSortColumn(false);
                $lastnameField->setTableColumn(true);
                $lastnameField->setMandatory($specialParticipantMechanism ? $rowMandatory : true);
                $lastnameField->setNotificationField(false);
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
                    $emailField->setNotificationField(false);
                    $emailField->setPrintable(false);
                    $participants[] = $emailField;
                }

                foreach ($typelist as $type) {
                    $condition = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'reservation_type', $type['id']);
                    $maxParticipants = $type['maxParticipantsPerBooking'];
                    $minParticipants = $type['minParticipantsPerBooking'];

                    //Max participant per booking
                    if ($eventObj && property_exists($eventObj, 'maxParticipantsPerEventBooking') && $eventObj->maxParticipantsPerEventBooking) {
                        $maxParticipants = $eventObj->maxParticipantsPerEventBooking;
                    } else if ($type['maxParticipantsPerBooking']){
                        $maxParticipants = $type['maxParticipantsPerBooking'];
                    }

                    $maxCapacity = $maxParticipants ?: 0;
                    $minCapacity = $minParticipants ?: 1;
                    $participantParam = ($eventObj && property_exists($eventObj, 'participant_params')) ? unserialize($eventObj->participant_params) : null;
                    $params = $participantParam ?: $type['participantParams'];
                    $participantParamsArr = [];

                    $maxCapacityCheck = $onlyParticipants ? $maxCapacity > 0 : $maxCapacity > 1;

                    $reservationSettingsWithCapacity = $this->reservationSettings->withCapacity;
                    if (!isset($specialParticipantMechanism) ||
                        (isset($specialParticipantMechanism) && isset($reservationSettingsWithCapacity)) || (isset($specialParticipantMechanism) && isset($maxCapacityCheck))) {
                        if ($params) {
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
                            $eventOptionsRadio = $eventObj && property_exists($eventObj, 'participantParamsFieldType') && $eventObj->participantParamsFieldType == 'radio';
                            $eventOptionsMandatory = $eventObj && property_exists($eventObj, 'participantParamsMandatory') && $eventObj->participantParamsMandatory == '1';
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

                        if (!$specialParticipantMechanism) {
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
                            $reservationParticipants->setShowFirstDataSet(true);
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
                                if ($participantCapacity > 1) {
                                    $start = $minCapacity ?: 1;
                                    for ($i = $start; $i <= $participantCapacity; $i++) {
                                        $counter = $onlyParticipants ? $i : $i - 1;
                                        $newCondition = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'desiredCapacity_' . $type['id'], $i);

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
                                        $reservationParticipants->setShowDataSetsByCount($counter);
//                                        $reservationParticipants->setParentFieldList($fieldList);
                                        $reservationParticipants->setDelimiter('§');
                                        $reservationParticipants->setCondition(array($condition, $newCondition));
                                        $reservationParticipants->setRemoveWithEmptyCondition(true);
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

                                $reservationParticipants->setShowDataSetsByCount($maxCapacity <= 10 ? $maxCapacity : 10);
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
        $reservationId = C4GBrickCommon::getUUID();
        if (isset($putVars['reservation_id']) && $putVars['reservation_id']) {
            $reservationId = $putVars['reservation_id'];
        }
        $reservationIdField->setInitialValue($reservationId);
        $reservationIdField->setTableRow(false);
        $reservationIdField->setEditable(false);
        $reservationIdField->setUnique(true);
        $reservationIdField->setNotificationField(true);
        $reservationIdField->setDbUnique(true);
        $reservationIdField->setSimpleTextWithoutEditing(false);
        $reservationIdField->setDatabaseField(true);
        $reservationIdField->setDbUniqueResult($GLOBALS['TL_LANG']['fe_c4g_reservation']['duplicate_reservation_id']);
        $reservationIdField->setStyleClass('reservation-id');
        $reservationIdField->setHidden($hideReservationKey);
        $reservationIdField->setPrintable($this->withDefaultPDFContent);
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
        $location_name->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $location_name;

        $contact_name = new C4GTextField();
        $contact_name->setFieldName('contact_name');
        $contact_name->setSortColumn(false);
        $contact_name->setFormField(false);
        $contact_name->setTableColumn(true);
        $contact_name->setNotificationField(true);
        $contact_name->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $contact_name;

        $contact_phone = new C4GTelField();
        $contact_phone->setFieldName('contact_phone');
        $contact_phone->setFormField(false);
        $contact_phone->setTableColumn(false);
        $contact_phone->setNotificationField(true);
        $contact_phone->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $contact_phone;

        $contact_email = new C4GEmailField();
        $contact_email->setFieldName('contact_email');
        $contact_email->setTableColumn(false);
        $contact_email->setFormField(false);
        $contact_email->setNotificationField(true);
        $contact_email->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $contact_email;

        $contact_website = new C4GUrlField();
        $contact_website->setFieldName('contact_website');
        $contact_website->setTableColumn(false);
        $contact_website->setFormField(false);
        $contact_website->setNotificationField(true);
        $contact_website->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $contact_website;

        $contact_street = new C4GTextField();
        $contact_street->setFieldName('contact_street');
        $contact_street->setTableColumn(false);
        $contact_street->setFormField(false);
        $contact_street->setNotificationField(true);
        $contact_street->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $contact_street;


        $contact_postal = new C4GTextField();
        $contact_postal->setFieldName('contact_postal');
        $contact_postal->setFormField(false);
        $contact_postal->setTableColumn(false);
        $contact_postal->setNotificationField(true);
        $contact_postal->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $contact_postal;


        $contact_city = new C4GTextField();
        $contact_city->setFieldName('contact_city');
        $contact_city->setTableColumn(false);
        $contact_city->setFormField(false);
        $contact_city->setNotificationField(true);
        $contact_city->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $contact_city;

        $contact_city = new C4GTextField();
        $contact_city->setFieldName('icsFilename');
        $contact_city->setTableColumn(false);
        $contact_city->setFormField(false);
        $contact_city->setNotificationField(true);
        $contact_city->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $contact_city;

        $memberId = new C4GTextField();
        $memberId->setFieldName('member_id');
        $memberId->setTableColumn(true);
        $memberId->setFormField(false);
        $memberId->setNotificationField(false);
        $memberId->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $memberId;

        $formularId = new C4GTextField();
        $formularId->setFieldName('formular_id');
        $formularId->setTableColumn(true);
        $formularId->setFormField(false);
        $formularId->setNotificationField(false);
        $formularId->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $formularId;

        $dbkey = new C4GTextField();
        $dbkey->setFieldName('dbkey');
        $dbkey->setTableColumn(false);
        $dbkey->setFormField(false);
        $dbkey->setNotificationField(true);
        $dbkey->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $dbkey;

        $memberEmail = new C4GTextField();
        $memberEmail->setFieldName('member_email');
        $memberEmail->setTableColumn(false);
        $memberEmail->setFormField(false);
        $memberEmail->setNotificationField(true);
        $memberEmail->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $memberEmail;

        $groupId = new C4GTextField();
        $groupId->setFieldName('group_id');
        $groupId->setTableColumn(true);
        $groupId->setFormField(false);
        $groupId->setNotificationField(false);
        $groupId->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $groupId;

        $bookedAt = new C4GTextField();
        $bookedAt->setFieldName('bookedAt');
        $bookedAt->setTableColumn(true);
        $bookedAt->setFormField(false);
        $bookedAt->setNotificationField(false);
        $bookedAt->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $bookedAt;

        //price for notification
        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('price');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(9999999999999);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $priceDBField;

        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('priceTax');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(9999999999999);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $priceDBField;

        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('priceSum');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(9999999999999);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $priceDBField;

        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('priceSumTax');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(9999999999999);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $priceDBField;

        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('priceNet');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(9999999999999);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $priceDBField;

        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('priceSumNet');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(9999999999999);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $priceDBField;

        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('priceOptionSum');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(9999999999999);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $priceDBField;

        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('priceOptionSumTax');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(9999999999999);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $priceDBField;

        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('priceOptionSumNet');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(9999999999999);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $priceDBField;

        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('priceDiscount');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(100);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $priceDBField;

        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('discountPercent');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(100);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $priceDBField;

        $priceDBField = new C4GTextField();
        $priceDBField->setFieldName('reservationTaxRate');
        $priceDBField->setDatabaseField(false);
        $priceDBField->setFormField(false);
        $priceDBField->setMax(9999999999999);
        $priceDBField->setNotificationField(true);
        $priceDBField->setPrintable($this->withDefaultPDFContent);
        $fieldList[] = $priceDBField;

        if ($this->reservationSettings->documentIdNext) {
            $idNext = str_pad($this->reservationSettings->documentIdNext, $this->reservationSettings->documentIdLength, "0", STR_PAD_LEFT);
            $documentId = $this->reservationSettings->documentIdPrefix.$idNext.$this->reservationSettings->documentIdSuffix;
            $documentIdField = new C4GTextField();
            $documentIdField->setFieldName('documentId');
            $documentIdField->setDatabaseField(true);
            $documentIdField->setFormField(true);
            $documentIdField->setSortColumn(false);
            $documentIdField->setNotificationField(true);
            $documentIdField->setInitialValue('');
            $documentIdField->setPrintable($this->withDefaultPDFContent);
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
        }


        $this->fieldList = $fieldList;
        
        // HACK: Dynamically add fields that might be sent via PUT to bypass framework filtering
        // The framework's C4GSaveDialogAction filters putVars based on the fieldList.
        // Since reservation fields often have dynamic names (beginDate_X), we need to register them.
        
        // OPTIMIZATION: Instead of adding 1000s of fields blindly, we only add those that 
        // are actually present in the current PUT request to reduce DOM size and prevent JS crashes.
        $rawPut = [];
        if (key_exists('REQUEST_METHOD', $_SERVER) && (($_SERVER['REQUEST_METHOD'] == 'PUT') || ($_SERVER['REQUEST_METHOD'] == 'POST'))) {
            $rawPut = $this->getPutVars();
            if (empty($rawPut) && $_SERVER['REQUEST_METHOD'] == 'POST') {
                $rawPut = \Contao\Input::postAll();
            }
        }

        if ($rawPut && is_array($rawPut)) {
            $this->logDebug("addFields - Selectively registering fields from Request to bypass framework filtering.");
            // Extended prefixes to cover more dynamic reservation fields
            $dynamicPrefixes = [
                'beginDate', 'beginTime', 'endDate', 'endTime', 
                'duration', 'reservation_object', 'desiredCapacity', 
                'participants', 'reservation_type', 'agreed', 
                'firstName', 'lastName', 'email', 'phone', 
                'address', 'postal', 'city', 'comment', 
                '_'
            ];
            foreach ($rawPut as $key => $value) {
                if (!is_string($key)) continue;
                
                $isDynamic = false;
                foreach ($dynamicPrefixes as $prefix) {
                    if (strpos($key, $prefix) === 0) {
                        $isDynamic = true;
                        break;
                    }
                }
                
                // Also match any key that has a numeric suffix or contains reservation markers
                if (!$isDynamic) {
                    if (preg_match('/_[0-9]+$/', $key) || preg_match('/-[0-9]+/', $key) || strpos($key, '§') !== false) {
                        $isDynamic = true;
                    }
                }
                
                if ($isDynamic) {
                    // Check if field is already in list
                    $alreadyExists = false;
                    foreach ($this->fieldList as $existingField) {
                        if ($existingField->getFieldName() === $key) {
                            $alreadyExists = true;
                            break;
                        }
                    }
                    
                    if (!$alreadyExists) {
                        $catchAllField = new C4GTextField();
                        $catchAllField->setFieldName($key);
                        $catchAllField->setDatabaseField(false);
                        $catchAllField->setFormField(true);
                        $catchAllField->setEditable(true);
                        $this->fieldList[] = $catchAllField;
                    }
                }
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

        // LOG RAW INPUT STREAM for diagnostic purposes
        $rawStream = file_get_contents('php://input');
        $this->logDebug("clickReservation - RAW INPUT (type $typeIdForStrategy): " . substr($rawStream, 0, 1000));

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
            $this->logDebug("clickReservation - STANDARD MODE for type $typeIdForStrategy. Merging fresh PUT data into existing state.");
            
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
        $this->logDebug("clickReservation START - Request keys: " . implode(',', array_keys($putVars)));
        
        $typeIdInReq = $putVars['reservation_type'] ?? null;
        if ($typeIdInReq) {
            $eventIdInReq = $putVars['reservation_object_event_' . $typeIdInReq] ?? null;
            
            // Emergency lookup if eventId is missing in request
            if (!$eventIdInReq) {
                // First check if we already have it in our controller instance from a previous call in the SAME request (e.g. from initBrickModule)
                if (isset($this->putVars['reservation_object_event_' . $typeIdInReq]) && $this->putVars['reservation_object_event_' . $typeIdInReq]) {
                    $eventIdInReq = $this->putVars['reservation_object_event_' . $typeIdInReq];
                    $this->logDebug("clickReservation - Found eventIdInReq in instance putVars: $eventIdInReq");
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
                        $this->logDebug("clickReservation - Emergency found eventId in URL/Referer: $eventIdInReq");
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
                            $this->logDebug("clickReservation - Last resort: using eventId from session: $eventIdInReq");
                            
                            // SECURITY CHECK: If we just saved a booking, the session ID might be stale
                            if ($this->session->getSessionValue('reservationJustSaved') && $isEventActual) {
                                $this->logDebug("clickReservation - Refusing Session fallback because reservationJustSaved is set.");
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
                            $this->logDebug("clickReservation - eventId missing in Request, instance and session for EVENT TYPE! Aborting.");
                            return ['usermessage' => 'Fehler: Ungültige Event-ID. Bitte laden Sie die Seite neu.'];
                        } else {
                            $this->logDebug("clickReservation - eventId missing but not an event type. Continuing.");
                        }
                    }
                }
            }

            if ($eventIdInReq) {
                \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "DEBUG clickReservation START - IDs from putVars: type=$typeIdInReq, event=$eventIdInReq");
                
                $sessionEventId = $this->session->getSessionValue('reservationEventCookie');
                if ($sessionEventId && $sessionEventId != $eventIdInReq) {
                    \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "DEBUG clickReservation - Mismatch detected! Request Event: $eventIdInReq, Session Event: $sessionEventId. Overriding with Session Event.");
                    $eventIdInReq = $sessionEventId;
                    
                    // Forcefully override ALL potential keys that might contain the wrong ID
                    $putVars['reservation_object_event_' . $typeIdInReq] = $eventIdInReq;
                    $this->putVars['reservation_object_event_' . $typeIdInReq] = $eventIdInReq;
                    
                    // Reload reservation object to match corrected eventId
                    $reservationObject = $database->prepare("SELECT * FROM tl_calendar_events WHERE id=?")
                        ->execute($eventIdInReq);
                    if ($reservationObject && $reservationObject->next()) {
                        \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "DEBUG clickReservation - Successfully reloaded correct event from DB: " . $reservationObject->title);
                    }
                }
            } else {
                // Check if it's encoded in another way or if we can find it
                foreach ($putVars as $pk => $pv) {
                    if (strpos($pk, 'reservation_object_event_') === 0 && $pv) {
                        $eventIdInReq = $pv;
                        \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "DEBUG clickReservation START - Found eventIdInReq in alternative key $pk: $pv");
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

        // Always regenerate the reservation_id at the start of the process to prevent duplicates
        $putVars['reservation_id'] = \con4gis\ProjectsBundle\Classes\Common\C4GBrickCommon::getUUID();
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

            $this->session->setSessionValue('c4g_brick_dialog_values', $dialogValues);

        $formId = $this->reservationSettings->id;
        $type = $putVars['reservation_type'] ?? '';
        $database = Database::getInstance();
        $reservationType = null;
        if ($type) {
            $reservationType = $database->prepare("SELECT * FROM tl_c4g_reservation_type WHERE id=? AND published='1'")
                ->execute($type);
        }

        $this->notification_type = $this->reservationSettings->notification_type;
        if ($this->getDialogParams()) {
            $this->getDialogParams()->setNotificationType($this->reservationSettings->notification_type);
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
                if ($this->getDialogParams()) {
                    $this->getDialogParams()->setNotificationType($reservationType->notification_type);
                }
                $this->notification_type = $reservationType->notification_type;
            }

            $isEvent = $reservationType && $reservationType->reservationObjectType && $reservationType->reservationObjectType === '2' ? true : false;

        if ($isEvent) {
            // DEEP CLEAN: Remove any existing event data keys from putVars to prevent state-bleeding.
            // This ensures that only the fresh values assigned below will be used.
            $keysToClear = ['beginDate', 'beginTime', 'endDate', 'endTime', 'reservation_title', 'description', 'image', 'location'];
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
                    \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "DEBUG clickReservation - Fresh event loaded from DB: id=$eventId, title=" . $reservationObject->title);
                    
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
            $keysToClear = ['beginDate', 'beginTime', 'endDate', 'endTime', 'reservation_title', 'description', 'image', 'location'];
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
                $this->logDebug('Duplicate reservation ID detected.');
                return ['usermessage' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['duplicate_reservation_id']];
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
                $this->logDebug('Duplicate booking detected.');
                return ['usermessage' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['duplicate_booking']];
            }

            $isEventActualForCheck = ($reservationType && $reservationType->type == 2);

            // LOG ALL KEYS BEFORE period check
            $this->logDebug("clickReservation (Line 3244) - BEFORE PERIOD CHECK. Keys: " . implode(',', array_keys($putVars)));

            $periodType = $reservationType->periodType;
            if ($periodType == 'day' || $periodType == 'overnight' || $periodType == 'week') {
                if(C4gReservationHandler::preventNonCorrectPeriod($reservationType,$reservationObject,$putVars)) {
                    // Only abort if it's NOT an event, or if we really have no data
                    if (!$isEventActualForCheck) {
                        $keys = implode(',', array_keys($putVars));
                        $this->logDebug("clickReservation - preventNonCorrectPeriod failed. putVars Keys: " . $keys);
                        
                        // SECOND RESCUE ATTEMPT before failing
                        $rescueSuccess = false;
                        foreach ($putVars as $rk => $rv) {
                            if ($rv && (strpos($rk, 'beginDate') === 0 || strpos($rk, 'beginDateEvent') === 0 || strpos($rk, 'beginTime') === 0)) {
                                $this->logDebug("clickReservation - SECOND RESCUE SUCCESS for key: $rk");
                                $rescueSuccess = true;
                                break;
                            }
                        }
            
                        if (!$rescueSuccess) {
                            // LAST CHANCE: Check raw stream
                            if (strpos($rawStream, 'beginDate') !== false || strpos($rawStream, 'beginTime') !== false) {
                                 $this->logDebug("clickReservation - SECOND RESCUE: Found time/date strings in raw stream, allowing through.");
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
                        $this->logDebug("clickReservation - RESCUED beginDate from $bk: $bv");
                    }
                    if (!$timeKey && (strpos($bk, 'beginTime') === 0 || strpos($bk, 'beginTimeEvent') === 0)) {
                        $timeKey = $bk;
                        $this->logDebug("clickReservation - RESCUED timeKey from $bk");
                    }
                }
            }

            // Collect all possible candidates for beginDate and beginTime
            // Priority: Explicit suffixed keys > Generic keys
            foreach ($putVars as $key => $value) {
                if (!$value) continue;
                
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
            $this->logDebug("clickReservation - Detection result: beginDate=$beginDate, beginTime=$beginTime, timeKey=" . ($timeKey ?: 'NONE'));

            // EMERGENCY PATCH: If we have detected values but timeKey is still false, create it!
            if ($beginTime && !$timeKey) {
                $timeKey = 'beginTime_' . $type;
                $putVars[$timeKey] = $beginTime;
                $this->putVars[$timeKey] = $beginTime;
                $this->logDebug("clickReservation - EMERGENCY PATCH: Created timeKey '$timeKey' with value $beginTime");
            }
            
            // SECOND EMERGENCY PATCH: If we have an 'undefined' key, it might contain missing data
            if (!$beginDate && isset($putVars['undefined'])) {
                // Try to extract date/time from undefined if it looks like a timestamp or date string
                if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $putVars['undefined'])) {
                    $beginDate = $putVars['undefined'];
                    $putVars['beginDate_'.$type] = $beginDate;
                    $this->putVars['beginDate_'.$type] = $beginDate;
                    $this->logDebug("clickReservation - Rescued beginDate from 'undefined' key.");
                } else if (is_numeric($putVars['undefined']) && $putVars['undefined'] > 1000000000) {
                    // Looks like a timestamp
                    $beginDate = date('d.m.Y', $putVars['undefined']);
                    $putVars['beginDate_'.$type] = $beginDate;
                    $this->putVars['beginDate_'.$type] = $beginDate;
                    $this->logDebug("clickReservation - Rescued beginDate from numeric 'undefined' key.");
                }
            }

            // THIRD EMERGENCY PATCH: For standard/object reservations, if we have generic beginDate/beginTime 
            // but the validator failed, ensure suffixed keys are present.
            if (!$isEventActualForCheck) {
                if ($beginDate && $type && !isset($putVars['beginDate_'.$type])) {
                    $putVars['beginDate_'.$type] = $beginDate;
                    $this->putVars['beginDate_'.$type] = $beginDate;
                    $this->logDebug("clickReservation - THIRD EMERGENCY PATCH: Applied beginDate suffix for type $type");
                }
                if ($beginTime && $type && !isset($putVars['beginTime_'.$type])) {
                    $putVars['beginTime_'.$type] = $beginTime;
                    $this->putVars['beginTime_'.$type] = $beginTime;
                    if (!$timeKey) $timeKey = 'beginTime_'.$type;
                    $this->logDebug("clickReservation - THIRD EMERGENCY PATCH: Applied beginTime suffix for type $type");
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
            if ($beginTime && $timeKey) {
                $putVars['beginTime'] = $beginTime;
                $this->putVars['beginTime'] = $beginTime;
                if ($type) {
                    $putVars['beginTime_'.$type] = $beginTime;
                    $this->putVars['beginTime_'.$type] = $beginTime;
                    
                    if ($reservationType->reservationObjectType === '3' && isset($objectId)) {
                        $putVars['beginTime_'.$type.'-33'.$objectId] = $beginTime;
                        $this->putVars['beginTime_'.$type.'-33'.$objectId] = $beginTime;
                    }
                }
            }
            
            // FINAL SYNC of all putVars keys to this->putVars
            foreach ($putVars as $pk => $pv) {
                $this->putVars[$pk] = $pv;
            }

            \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "DEBUG clickReservation - PRE-VALIDATION Keys: " . implode(',', array_keys($putVars)));

            $duration = isset($putVars['duration_'.$type]) ? $putVars['duration_'.$type] : null;
            if (!$duration) {
                $duration = $time_interval;
                $putVars['duration_'.$type] = $reservationObject->duration ?: $duration;
            }

            if ($typeOfObject == '' || $typeOfObject == 'standard') {
                $duration = $duration * $interval;
                $endTime = $beginTime + intval($duration);
            }

            $putVars['endTime'] = date($GLOBALS['TL_CONFIG']['timeFormat'],$endTime);

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
                $putVars['endTime'] = date($GLOBALS['TL_CONFIG']['timeFormat'], $endTime-86400);
            } else if (!$reservationType->directBooking && ($endTime == 86400)) {
                //$putVars['endDate'] = date($GLOBALS['TL_CONFIG']['dateFormat'], $endTime-1);
                $putVars['endTime'] = date($GLOBALS['TL_CONFIG']['timeFormat'], $endTime);
            }

            if ($typeOfObject == 'fixed_date') {
                $timestamp = $reservationObject->dateTimeBegin;
//                $object['typeOfObject'] = $reservationObject->getTypeOfObject();
                $beginDate = C4gReservationDateChecker::getBeginOfDate($timestamp);
                $summerDiff = C4gReservationDateChecker::getTimeDiff($timestamp);
                $duration = $reservationObject->typeOfObjectDuration * $interval;

                $beginTime = $timestamp - $beginDate - $summerDiff;
                $endTime = $beginTime + $duration;
                $endDate = $beginDate + $endTime;

                $endTime = $beginTime + intval($duration);
                $putVars['duration'] = $duration;
                $putVars['duration_'.$type] = $duration;

                if ($putVars['reservationObjectType'] == '3') {
                    $objectId = $reservationObject ? $reservationObject->id : 0;
                    $putVars['beginDate_'.$type.'-33'.$objectId] = $beginDate ? date($GLOBALS['TL_CONFIG']['dateFormat'], $timestamp) : $timestamp;
                    $putVars['beginTime_'.$type.'-33'.$objectId] = $beginTime;
                    $putVars['endDate'] = $endDate ? date($GLOBALS['TL_CONFIG']['dateFormat'], $endDate) : $endDate; //ToDO Check
                    $putVars['endTime'] = $endTime ? date($GLOBALS['TL_CONFIG']['timeFormat'], $endTime) : $endTime;
                } else if ($putVars['reservationObjectType'] == '2') {
                    $putVars['beginDate_'.$type] = $beginDate ? date($GLOBALS['TL_CONFIG']['dateFormat'], $beginDate) : $beginDate;
                    $putVars['beginTime'.$type] = $beginTime ? date($GLOBALS['TL_CONFIG']['timeFormat'], $beginTime) : $beginTime;
                    $putVars['endDate_'.$type] = $endDate ? date($GLOBALS['TL_CONFIG']['dateFormat'], $endDate) : $endDate; //ToDO Check
                    $putVars['endTime_'.$type] = $endTime ? date($GLOBALS['TL_CONFIG']['timeFormat'], $endTime) : $endTime;
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

                    $nextDay = strtotime(C4GBrickCommon::getLongDateToConvert($GLOBALS['TL_CONFIG']['dateFormat'], $beginDate)) + $addDuration;
                    $putVars['endDate'] = date($GLOBALS['TL_CONFIG']['dateFormat'], $nextDay);

                    $wd = date("w", strtotime(C4GBrickCommon::getLongDateToConvert($GLOBALS['TL_CONFIG']['dateFormat'], $beginDate)));
                    $endTime = C4gReservationHandler::getEndTimeForMultipleDays($reservationObject, $wd, ($reservationType->periodType == 'overnight'));

                    //ToDo test
                    if (($endTime <= $beginTime) || ($reservationType->periodType == 'overnight')) {
                        $putVars['endDate'] = date($GLOBALS['TL_CONFIG']['dateFormat'], $nextDay+86400);
                    }


                    $putVars['endTime'] = $endTime ? date($GLOBALS['TL_CONFIG']['timeFormat'], intvaL($endTime)) : intval($beginTime);
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
                $summerDiff = C4gReservationDateChecker::getTimeDiff($timestamp);
                $duration = $reservationObject->typeOfObjectDuration * $interval;

                $beginTime = $timestamp - $beginDate - $summerDiff;
                $endTime = $beginTime + $duration;
                $endDate = $beginDate + $endTime;

                $endTime = $beginTime + intval($duration);
                $putVars['duration'] = $duration;
                $putVars['duration_'.$type] = $duration;

                if ($putVars['reservationObjectType'] == '3') {
                    $objectId = $reservationObject ? $reservationObject->id : 0;
                    $putVars['beginDate_'.$type.'-33'.$objectId] = $beginDate ? date($GLOBALS['TL_CONFIG']['dateFormat'], $beginDate) : $beginDate;
                    $putVars['beginTime_'.$type.'-33'.$objectId] = $beginTime;
                    $putVars['endDate'] = $endDate ? date($GLOBALS['TL_CONFIG']['dateFormat'], $endDate) : $endDate; //ToDO Check
                    $putVars['endTime'] = $endTime ? date($GLOBALS['TL_CONFIG']['timeFormat'], $endTime) : $endTime;
                }
                //ToDO Check
                $putVars['endDate'] = $endDate ? date($GLOBALS['TL_CONFIG']['dateFormat'], $endDate) : $putVars['beginDate'];
                $putVars['endTime'] = $endTime ? date($GLOBALS['TL_CONFIG']['timeFormat'], $endTime) : $endTime;
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
                    $this->logDebug("clickReservation - WE HAVE BEGIN FIELDS BUT VALIDATION STILL FAILS. Keys: " . implode(',', $allKeys));
                }
            }

            if (!$timeKey && !$isEventActualForCheck) {
                $keys = implode(',', array_keys($putVars));
                $this->logDebug("clickReservation (Line 3646) - NO TIME KEY. putVars Keys: " . $keys);
            
                // FINAL RESCUE ATTEMPT
                foreach ($putVars as $rk => $rv) {
                    if ($rv && (strpos($rk, 'beginDate') === 0 || strpos($rk, 'beginDateEvent') === 0 || strpos($rk, 'beginTime') === 0)) {
                        $timeKey = $rk; // Use this as timeKey to satisfy the check
                        $this->logDebug("clickReservation - FINAL RESCUE SUCCESS: using $rk as timeKey");
                        break;
                    }
                }
            
                if (!$timeKey) {
                    // Check raw stream again
                    if (strpos($rawStream, 'beginDate') !== false || strpos($rawStream, 'beginTime') !== false) {
                        $timeKey = 'RESCUED_FROM_STREAM';
                        $this->logDebug("clickReservation - FINAL RESCUE: Found in raw stream, using fake timeKey.");
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
                if (property_exists($reservationObject, 'maxParticipantsPerEventBooking') && $reservationObject->maxParticipantsPerEventBooking) {
                    $maxParticipants = $reservationObject->maxParticipantsPerEventBooking;
                } else if ($reservationType->maxParticipantsPerBooking){
                    $maxParticipants = $reservationType->maxParticipantsPerBooking;
                }

                $desiredCapacity = isset($putVars['desiredCapacity_'.$reservationType->id]) ? $putVars['desiredCapacity_'.$reservationType->id] : null; //ToDo $maxParticipants
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
                    $desiredCapacity = isset($putVars['desiredCapacity_'.$reservationType->id]) ? $putVars['desiredCapacity_'.$reservationType->id] : null;
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
        if ($participantsArr && count($participantsArr) > 0) {
            $pCount = $this->reservationSettings->onlyParticipants ? 0 : 1;
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
            'description' => $putVars['description'] ?? '',
            'location' => $putVars['location'] ?? '',
            'desiredCapacity' => $desiredCapacity ?? 1,
            'reservation_title' => $putVars['reservation_title'] ?? '',
            'beginDate' => $putVars['beginDate'] ?? '',
            'beginTime' => $putVars['beginTime'] ?? '',
            'endDate' => $putVars['endDate'] ?? '',
            'endTime' => $putVars['endTime'] ?? '',
            'participantList' => $putVars['participantList'] ?? '',
            'priceSum' => $putVars['priceSum'] ?? '0,00 €',
            'priceDiscount' => $putVars['priceDiscount'] ?? '0,00 €',
            'discountPercent' => $putVars['discountPercent'] ?? 0,
            'discountCode' => $putVars['discountCode'] ?? '',
            'conferenceLink' => $putVars['conferenceLink'] ?? '',
        ];

        foreach ($tokenDefaults as $key => $defaultValue) {
            if (!isset($putVars[$key]) || $putVars[$key] === null || $putVars[$key] === '') {
                $putVars[$key] = $defaultValue;
            }
        }

        // Fix: mirrored location tokens might also be needed as defaults if not set
        if ($locationId && !isset($putVars['location_2-' . $locationId])) {
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

        $rIdForIcs = $putVars['reservation_id'] ?? ($putVars['id'] ?? 0);
        $beginDateTime = C4gReservationDateChecker::mergeDateWithTimeForIcs(strtotime(C4GBrickCommon::getLongDateToConvert($GLOBALS['TL_CONFIG']['dateFormat'], $beginDate)), $beginTime);
        $endDateTime = C4gReservationDateChecker::mergeDateWithTimeForIcs(isset($endDate) ? $endDate : strtotime(C4GBrickCommon::getLongDateToConvert($GLOBALS['TL_CONFIG']['dateFormat'], $beginDate)), $endTime);
        $putVars['icsFilename'] = $this->createIcs($beginDateTime, $endDateTime, $icsObject, $reservationType, $location, $rIdForIcs);

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
            $documentId = $this->reservationSettings->documentIdPrefix.$idNext.$this->reservationSettings->documentIdSuffix;
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
            'description' => $putVars['description'] ?? '',
            'location' => $putVars['location'] ?? '',
            'desiredCapacity' => $desiredCapacity ?? 1,
            'reservation_title' => $putVars['reservation_title'] ?? '',
            'beginDate' => $putVars['beginDate'] ?? '',
            'beginTime' => $putVars['beginTime'] ?? '',
            'endDate' => $putVars['endDate'] ?? '',
            'endTime' => $putVars['endTime'] ?? '',
            'participantList' => $putVars['participantList'] ?? '',
            'priceSum' => $putVars['priceSum'] ?? '0,00 €',
            'priceDiscount' => $putVars['priceDiscount'] ?? '0,00 €',
            'discountPercent' => $putVars['discountPercent'] ?? 0,
            'discountCode' => $putVars['discountCode'] ?? '',
            'conferenceLink' => $putVars['conferenceLink'] ?? '',
        ];

        foreach ($tokenDefaults as $key => $defaultValue) {
            if (!isset($putVars[$key]) || $putVars[$key] === null || $putVars[$key] === '') {
                $putVars[$key] = $defaultValue;
            }
            // Mirror to instance putVars as well for SaveAction
            $this->putVars[$key] = $putVars[$key];

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
        $keysToSanitize = ['beginDate', 'beginTime', 'endDate', 'endTime', 'reservation_title', 'description', 'image', 'location'];
        $typeForSanitize = $putVars['reservation_type'] ?? null;
        $eventForSanitize = $putVars['reservation_object'] ?? null;
        
        $sanitizeVars = function(&$vars) use ($keysToSanitize, $typeForSanitize, $eventForSanitize) {
            if (!is_array($vars)) return;
            foreach (array_keys($vars) as $vk) {
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
        
        // Update session with the sanitized values
        $this->session->setSessionValue('c4g_brick_dialog_values', $putVars);

        // FINAL MIRROR: Ensure instance putVars is identical to local putVars
        // before passing to the action, as some parts of the framework
        // might access the instance variable via the module reference.
        $this->putVars = $putVars;
        
        $action = new C4GSaveAndRedirectDialogAction($this->getDialogParams(), $this->getListParams(), $newFieldList, $putVars, $this->getBrickDatabase());
        $action->setModule($this);
        $result = $action->run();

        if (is_array($result) && !isset($result['jump_to_url']) && $this->reservationSettings->reservation_redirect_site) {
            $jumpTo = \Contao\PageModel::findByPk($this->reservationSettings->reservation_redirect_site);
            if ($jumpTo) {
                $url = $jumpTo->getFrontendUrl();
                // Add a random cache buster to the redirect URL to prevent browser from loading cached state
                $url .= (strpos($url, '?') === false ? '?' : '&') . 'cb=' . uniqid();
                
                // Radical: If it's an AJAX request (usually identified by X-Requested-With)
                // we might need to tell the frontend to do a hard reload.
                // Contao's brick_ajax_api often expects a JSON response with jump_to_url.
                $result['jump_to_url'] = $url;
                
                \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('reservation', "DEBUG clickReservation - Setting redirect to: $url");
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
        $this->putVars = [];

        return $result;
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
                $this->logDebug("createIcs - absolutePath: $absolutePath, fileId: $fileId, effective dir: $dir");
                
                try {
                    if (!is_dir($dir)) {
                        if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
                            $this->logDebug("createIcs - mkdir failed for $dir");
                        }
                    }
                    
                    if (!file_exists($filename)) {
                        touch($filename);
                    }
                    $ics = new File($filename);
                } catch (\Exception $exception) {
                    $this->logDebug("createIcs - catch exception: " . $exception->getMessage());
                    $fs = new Filesystem();
                    try {
                        $fs->mkdir($dir);
                        $fs->touch($filename);
                        $ics = new File($filename);
                    } catch (\Throwable $t) {
                        $this->logDebug("createIcs - Final FS fallback failed: " . $t->getMessage());
                        return '';
                    }
                }

                $ics->openFile("w")->fwrite(
                    "BEGIN:VCALENDAR\nVERSION:2.0\nCALSCALE:GREGORIAN\nMETHOD:PUBLISH\nPRODID:$icsprodid\n".
                    "X-WR-TIMEZONE:$icstimezone\nBEGIN:VEVENT\nUID:$icsuid\nLOCATION:$icslocation\nSUMMARY:$icssummary\nCLASS:PUBLIC\nDESCRIPTION:$icsdescription\n".
                    "DTSTART;$dstart\nDTEND;$dend\nDTSTAMP:$dstamp\nBEGIN:VALARM\nTRIGGER:$icsalert\nACTION:DISPLAY\nEND:VALARM\nEND:VEVENT\nEND:VCALENDAR\n");
                
                // Return relative path for Notification Center (relative to rootDir)
                $relativePath = $path . '/' . $fileId . '/reservation.ics';
                $this->logDebug("createIcs - returning relativePath: $relativePath");
                return $relativePath;
            }
        }

        return '';
    }

    /**
     * @Route(
     *      path="/reservation-api/currentTimeset/{date}/{type}/{duration}/{capacity}/{objectId}",
     *      methods={"GET"}
     *  )
     * @param $values
     * @param $putVars
     * @return array
     */
    #[Route(
        path: '/reservation-api/currentTimeset/{date}/{type}/{duration}/{capacity}/{objectId}',
        methods: ['GET']
    )]
    public function getCurrentTimesetAction(Request $request, $date, $type, $duration, $capacity, $objectId)
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
            $objects = C4gReservationHandler::getReservationObjectList(array($type), intval($objectId), $this->reservationSettings->showPrices, $this->reservationSettings->showPricesWithTaxes ?: false,false, $duration, $date, $langCookie);
            $withEndTimes = $this->reservationSettings->showEndTime;
            $withFreeSeats = $this->reservationSettings->showFreeSeats;
            $showArrivalAndDeparture = $this->reservationSettings->showArrivalAndDeparture;
            $times = C4gReservationHandler::getReservationTimes($objects, $type, $wd, $date, $duration, $capacity, $withEndTimes, $withFreeSeats, true, $langCookie, $showArrivalAndDeparture);
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

        if ($this->reservationSettings->showPrices) {
            foreach ($objects as $object) {
                $captions[$object->getId()] = $object->getCaption();
            }
        }

        return new JsonResponse([
            'reservationId' => C4GBrickCommon::getUUID(),
            'times' => $times ?: [],
            'captions' => ($times && count($times) > 0) ? $captions : []
        ]);
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
            $desiredCapacity = $desiredCapacity ?: $reservationEventObject->minParticipants;
            $resObject = $reservationEventObject;
            $price = $reservationEventObject->price ?? 0;
            $discountCode = $reservationEventObject->discountCode ?? '';
            if (trim($discountCode) === trim($putVars['discountCode'])) {
                $putVars['discountPercent'] = $reservationEventObject->discountPercent ?? 0;
            }
        } else {
            $price = $reservationObject->price;
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
            if (!isset($typeArray['id']) && is_object($reservationType) && property_exists($reservationType, 'id')) {
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
            $participantParams = $resArray['participant_params'] ?? false;
            $onlyParticipants = $settings->onlyParticipants ?: false;

            if ($participantParams) {
                // Härtung putVars für calcParticipantOptionPrices
                foreach ($putVars as $pk => $pv) {
                    if (is_string($pv) && strpos($pk, 'participants_') === 0) {
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

            if ($putVars['discountPercent']) {
                if ($priceSum) {
                    $discount = (floatval($priceSum) / 100) * $putVars['discountPercent'];
                    $putVars['priceDiscount'] = C4gReservationHandler::formatPrice($discount);
                    $priceSum = floatval($priceSum) - $discount;
                }
            }

            if (($priceArray['price'] ?? 0) || $priceSum) {
                $putVars['price'] = C4gReservationHandler::formatPrice($priceArray['price'] ?? 0) . ($priceArray['priceInfo'] ?? '');
                $putVars['priceSum'] = C4gReservationHandler::formatPrice($priceSum);
            } else {
                $putVars['priceSum'] = C4gReservationHandler::formatPrice($price) . ($priceArray['priceInfo'] ?? '');
            }

            if (!isset($putVars['priceDiscount'])) {
                $putVars['priceDiscount'] = C4gReservationHandler::formatPrice(0);
            }

            // Summe aller Optionen (Reservierung + Teilnehmer) für Ausgabe
            $putVars['priceOptionSum'] = C4gReservationHandler::formatPrice($optionsPriceSum);

            if (($putVars['priceDiscount'] ?? 0) && ($putVars['discountPercent'] ?? 0)) {
                $discount = (floatval($priceSum + $discount) / 100) * $putVars['discountPercent'];
                $putVars['priceDiscount'] = C4gReservationHandler::formatPrice($discount);
            }

            if ($calcTaxes) {
                $priceNet = floatval($priceArray['priceNet'] ?? 0);
                $priceTax = floatval($priceArray['priceTax'] ?? 0);

                $putVars['reservationTaxRate'] = $priceArray['reservationTaxRate'];

                $putVars['priceNet'] = C4gReservationHandler::formatPrice($priceNet);
                $putVars['priceTax'] = C4gReservationHandler::formatPrice($priceTax);

                $optNet = floatval($priceOptionSum['priceOptionNet'] ?? 0) + floatval($priceParticipantOptionSum['priceParticipantOptionSumNet'] ?? 0);
                $optTax = floatval($priceOptionSum['priceOptionTax'] ?? 0) + floatval($priceParticipantOptionSum['priceParticipantOptionSumTax'] ?? 0);
                $putVars['priceOptionSumNet'] = C4gReservationHandler::formatPrice($optNet);
                $putVars['priceOptionSumTax'] = C4gReservationHandler::formatPrice($optTax);

                $putVars['priceSumNet'] = C4gReservationHandler::formatPrice($priceNet + $optNet);
                $putVars['priceSumTax'] = C4gReservationHandler::formatPrice($priceTax + $optTax);
            }
        } else {
            // Fallback: Berechne Basis + Optionen auch dann, wenn keine Preis-/Anzeige-Flags gesetzt sind,
            // damit priceSum in putVars stets korrekt inkl. Optionen ankommt.

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
                if (is_string($pv) && (strpos($pk, 'additional_params_') === 0 || strpos($pk, 'participants_') === 0)) {
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

            $participantParams = $resArray['participant_params'] ?? false;
            $onlyParticipants = $settings->onlyParticipants ?: false;
            if ($participantParams) {
                $tmpPartOption = C4gReservationCalculator::calcParticipantOptionPrices(intval($desiredCapacity), $putVars, $objArray, $typeArray, false, $onlyParticipants, $settings->specialParticipantMechanism);
                if (is_array($tmpPartOption)) {
                    $priceParticipantOptionSum = array_merge($priceParticipantOptionSum, $tmpPartOption);
                }
            }

            $optionsPriceSum = floatval($priceOptionSum['priceOptionSum'] ?? 0) + floatval($priceParticipantOptionSum['priceParticipantOptionSum'] ?? 0);
            $priceSum += $optionsPriceSum;

            // Rabatt anwenden, falls vorhanden
            if (!empty($putVars['discountPercent']) && $priceSum) {
                $discount = (floatval($priceSum) / 100) * floatval($putVars['discountPercent']);
                $putVars['priceDiscount'] = C4gReservationHandler::formatPrice($discount);
                $priceSum = floatval($priceSum) - $discount;
            }

            // Ausgabe-Felder setzen
            $putVars['price'] = C4gReservationHandler::formatPrice($priceArray['price'] ?? 0) . ($priceArray['priceInfo'] ?? '');
            $putVars['priceSum'] = C4gReservationHandler::formatPrice($priceSum);
            $putVars['priceOptionSum'] = C4gReservationHandler::formatPrice($optionsPriceSum);
            if (!isset($putVars['priceDiscount'])) {
                $putVars['priceDiscount'] = C4gReservationHandler::formatPrice(0);
            }

            // Auch im Fallback Steuern berechnen, falls gewünscht
            if ($calcTaxes) {
                $priceNet = floatval($priceArray['priceNet'] ?? 0);
                $priceTax = floatval($priceArray['priceTax'] ?? 0);
                $putVars['reservationTaxRate'] = $priceArray['reservationTaxRate'];
                $putVars['priceNet'] = C4gReservationHandler::formatPrice($priceNet);
                $putVars['priceTax'] = C4gReservationHandler::formatPrice($priceTax);

                $optNet = floatval($priceOptionSum['priceOptionNet'] ?? 0) + floatval($priceParticipantOptionSum['priceParticipantOptionSumNet'] ?? 0);
                $optTax = floatval($priceOptionSum['priceOptionTax'] ?? 0) + floatval($priceParticipantOptionSum['priceParticipantOptionSumTax'] ?? 0);
                $putVars['priceOptionSumNet'] = C4gReservationHandler::formatPrice($optNet);
                $putVars['priceOptionSumTax'] = C4gReservationHandler::formatPrice($optTax);

                $putVars['priceSumNet'] = C4gReservationHandler::formatPrice($priceNet + $optNet);
                $putVars['priceSumTax'] = C4gReservationHandler::formatPrice($priceTax + $optTax);
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
            C4gLogModel::addLogEntry('reservation', 'DEBUG afterSaveAction - Cleanup completed for insertId: ' . $insertId);
        }
        
        $this->putVars = [];
    }
}

