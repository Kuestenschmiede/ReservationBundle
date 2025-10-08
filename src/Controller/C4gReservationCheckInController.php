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

use con4gis\ProjectsBundle\Classes\Common\C4GBrickConst;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GInfoTextField;
use con4gis\ProjectsBundle\Classes\Framework\C4GBaseController;
use con4gis\ProjectsBundle\Classes\Framework\C4GController;
use con4gis\ProjectsBundle\Classes\Views\C4GBrickViewType;
use con4gis\ReservationBundle\Classes\Models\C4gReservationModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationSettingsModel;
use con4gis\ReservationBundle\Classes\Projects\C4gReservationBrickTypes;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\Input;
use Contao\ModuleModel;
use Contao\System;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * CheckIn (Contao frontend module)
 */
class C4gReservationCheckInController extends C4GBaseController
{
    public const TYPE = 'C4gReservationCheckIn';
    protected $tableName    = 'tl_c4g_reservation';
    protected $modelClass   = C4gReservationModel::class;
    protected $languageFile = 'fe_c4g_reservation_checkin';
    protected $brickKey     = C4gReservationBrickTypes::BRICK_RESERVATION_CHECKIN;
    protected $viewType     = C4GBrickViewType::PUBLICFORM;
    protected $sendEMails   = null;
    protected $brickScript  = 'bundles/con4gisreservation/dist/js/c4g_brick_reservation.js';
    protected $brickStyle   = 'bundles/con4gisreservation/dist/css/c4g_brick_reservation.min.css';
    protected $withNotification = false;

    //Resource Params
    protected $loadDefaultResources = true;
    protected $loadDateTimePickerResources = false;
    protected $loadChosenResources = false;
    protected $loadClearBrowserUrlResources = false;
    protected $loadConditionalFieldDisplayResources = true;
    protected $loadMoreButtonResources = false;
    protected $loadFontAwesomeResources = true;
    protected $loadTriggerSearchFromOtherModuleResources = false;
    protected $loadFileUploadResources = false; //ToDo Check if needed
    protected $loadMultiColumnResources = false;
    protected $loadMiniSearchResources = false;
    protected $loadHistoryPushResources = false;
    protected $loadDatePicker = false;

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

    /**
     * @param string $rootDir
     * @param RequestStack $requestStack
     * @param ContaoFramework $framework
     */

    public function __construct(string $rootDir, RequestStack $requestStack, ContaoFramework $framework, ModuleModel $model = null)
    {
        parent::__construct($rootDir, $requestStack, $framework, $model);
    }

    public function initBrickModule($id)
    {
        $moduleTypes = [];


        $reservationKey  = Input::get('checkIn') ?: '';

        System::loadLanguageFile('fe_c4g_reservation_checkin');
        if ($GLOBALS['TL_LANGUAGE']) {
            $this->session->setSessionValue('reservationLangCookie', $GLOBALS['TL_LANGUAGE']);
        }

        $this->setBrickCaption($GLOBALS['TL_LANG']['fe_c4g_reservation']['brick_caption']);
        $this->setBrickCaptionPlural($GLOBALS['TL_LANG']['fe_c4g_reservation']['brick_caption_plural']);

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
    }

    public function addFields() : array
    {
        if (!$this->reservationSettings && $this->reservation_settings) {
            $this->session->setSessionValue('reservationSettings', $this->reservation_settings);
            $this->reservationSettings = C4gReservationSettingsModel::findByPk($this->reservation_settings);
        }

        System::loadLanguageFile('fe_c4g_reservation_checkin');
        if ($GLOBALS['TL_LANGUAGE']) {
            $this->session->setSessionValue('reservationLangCookie', $GLOBALS['TL_LANGUAGE']);
        }

        $fieldList = array();

        $reservationKey  = Input::get('checkIn') ?: '';

        if ($reservationKey) {
            $database = Database::getInstance();
            $reservations = $database->prepare("SELECT checkedIn FROM `tl_c4g_reservation` WHERE `reservation_id`=? AND NOT `cancellation`=?")
                ->execute(trim($reservationKey), '1')->fetchAllAssoc();
            $count = count($reservations);
            if ($count > 0) {
                if ($count == 1) {
                    $reservation = $reservations[0];
                    if ($reservation) {
                        $checkedIn = $reservation['checkedIn'];
                        if ($checkedIn) {
                            $message = $GLOBALS['TL_LANG']['fe_c4g_reservation_checkin']['reservation_checkin_exists'];
                        } else {
                            $stmt = $database->prepare("UPDATE `tl_c4g_reservation` SET checkedIn = ? WHERE reservation_id = ?");
                            $stmt->execute('1', $reservationKey);

                            $message = $GLOBALS['TL_LANG']['fe_c4g_reservation_checkin']['reservation_checkin_okay'];
                        }
                    } else {
                        $message = $GLOBALS['TL_LANG']['fe_c4g_reservation_checkin']['reservation_checkin_error'];
                    }
                } else {
                    $message = $GLOBALS['TL_LANG']['fe_c4g_reservation_checkin']['reservation_checkin_notclearly'];
                }
            } else {
                $message = $GLOBALS['TL_LANG']['fe_c4g_reservation_checkin']['reservation_checkin_notactive'];
            }
        } else {
            $message = $GLOBALS['TL_LANG']['fe_c4g_reservation_checkin']['reservation_checkin_none'];
        }

        if ($message) {
            $info = new C4GInfoTextField();
            $info->setFieldName('info');
            $info->setEditable(false);
            $info->setInitialValue($message);
            return [$info];
        }

        //ToDo checkIn form

        return $fieldList;
    }
}

