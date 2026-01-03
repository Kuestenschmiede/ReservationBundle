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
use con4gis\CoreBundle\Classes\Callback\C4GObjectCallback;
use con4gis\ProjectsBundle\Classes\Actions\C4GSaveAndRedirectDialogAction;
use con4gis\ProjectsBundle\Classes\Common\C4GBrickConst;
use con4gis\ProjectsBundle\Classes\Conditions\C4GBrickCondition;
use con4gis\ProjectsBundle\Classes\Conditions\C4GBrickConditionType;
use con4gis\ProjectsBundle\Classes\Fieldlist\C4GBrickFieldSourceType;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GCheckboxField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GDecimalField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GFileField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GImageField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GInfoTextField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GKeyField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GMultiCheckboxField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GNumberField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GSelectField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTextareaField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTextField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTrixEditorField;
use con4gis\ProjectsBundle\Classes\Files\C4GBrickFileType;
use con4gis\ProjectsBundle\Classes\Framework\C4GBaseController;
use con4gis\ProjectsBundle\Classes\Framework\C4GController;
use con4gis\ProjectsBundle\Classes\Views\C4GBrickViewType;
use con4gis\ReservationBundle\Classes\Models\C4gReservationObjectModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationTypeModel;
use con4gis\ReservationBundle\Classes\Projects\C4gReservationBrickTypes;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationCalculator;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationDateChecker;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\FrontendUser;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RequestStack;
use Contao\System;

class C4gReservationObjectsController extends C4GBaseController
{
    public const TYPE = 'C4gReservationObjects';

    protected $tableName    = 'tl_c4g_reservation_object';
    protected $modelClass   = C4gReservationObjectModel::class;
    protected $languageFile = 'fe_c4g_reservation_objects';
    protected $brickKey     = C4gReservationBrickTypes::BRICK_RESERVATION_OBJECTS;
    protected $viewType     = C4GBrickViewType::MEMBERBASED;
    protected $sendEMails   = null;
    protected $withNotification = false;
    protected $permalink_name = 'object';
    protected $permalink_field = 'alias';

    //Resource Params
    protected $loadDefaultResources = true;
    protected $loadTrixEditorResources = true;
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

    protected $withPermissionCheck = true;


    /**
     * @param string $rootDir
     * @param RequestStack $requestStack
     * @param ContaoFramework $framework
     */
    public function __construct(string $rootDir, RequestStack $requestStack, ContaoFramework $framework, ModuleModel $model = null)
    {
        parent::__construct($rootDir, $requestStack, $framework, $model);
    }

    /**
     * @param Template $template
     * @param ModuleModel $model
     * @param Request $request
     * @return Response|null
     */
    public function getResponse(Template $template, ModuleModel $model, Request $request): Response {
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

        $this->dialogParams->setSaveCallBack(new C4GObjectCallback($this, 'saveCallback'));

        $this->listParams->setScrollX(false);
        $this->listParams->setResponsive(true);
        $this->dialogParams->setHideChangesMessage(true);

        if ($this->login_redirect_site) {
            $this->dialogParams->getViewParams()->setLoginRedirect($this->login_redirect_site);
        }

    }

    public function addFields() : array
    {
        $hasFrontendUser = System::getContainer()->get('contao.security.token_checker')->hasFrontendUser();
        $fieldList = array();

        $idField = new C4GKeyField();
        $idField->setFieldName('id');
        $idField->setEditable(false);
        $idField->setFormField(false);
        $idField->setSortColumn(false);
        $idField->setPrintable(false);
        $fieldList[] = $idField;

        $aliasField = new C4GTextField();
        $aliasField->setFieldName('alias');
        $aliasField->setEditable(false);
        $aliasField->setFormField(false);
        $aliasField->setSortColumn(false);
        $aliasField->setPrintable(false);
        $fieldList[] = $aliasField;

        $ignorePostal = false;
        if ($this->postals) {
            $ignorePostal = true;
            $contactData = true;
            if ($hasFrontendUser === true) {
                $member = FrontendUser::getInstance();
                if ($member) {
                    $postals = explode(',', $this->postals);
                    foreach ($postals as $postal) {
                        if (trim($postal) == $member->postal) {
                            $ignorePostal = false;
                            break;
                        }
                    }

                    if (!$member->postal || !$member->firstname || !$member->lastname || !$member->street || !$member->phone) {
                        $contactData = false;
                    }
                } else {
                    $contactData = false;
                }
            }

            if ($ignorePostal || !$contactData) {
                $this->dialogParams->setWithoutGuiHeader(true);
                $this->dialogParams->deleteButton(C4GBrickConst::BUTTON_SAVE);
                $this->dialogParams->deleteButton(C4GBrickConst::BUTTON_SAVE_AND_NEW);
                $this->dialogParams->deleteButton(C4GBrickConst::BUTTON_DELETE);
                $this->dialogParams->setIgnoreChanges(true);
                $this->setDialogParams($this->dialogParams);

                if ($ignorePostal) {
                    $message = $GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['wrong_postal'];
                } else {
                    $message = $GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['fill_contact_data'];
                }
                $info = new C4GInfoTextField();
                $info->setFieldName('info');
                $info->setEditable(false);
                $info->setInitialValue($message);
                $info->setDatabaseField(false);
                $info->setFormField(true);
                $info->setComparable(false);
                $fieldList[] = $info;
                return $fieldList;
            }
        }

        $typeArray = StringUtil::deserialize($this->reservation_object_types);
        if ($typeArray) {
            $typeIds = implode(',',$typeArray);
            $t = 'tl_c4g_reservation_type';
            $arrValues = array();
            $arrOptions = array('order' => "$t.caption ASC, $t.options ASC",);
            $arrColumns = array("$t.published='1' AND NOT $t.reservationObjectType='2' AND $t.id IN($typeIds)");
            $types = C4gReservationTypeModel::findBy($arrColumns, $arrValues, $arrOptions);
            $typelist = [];
            foreach ($types as $type) {
                $typelist[] = ['id'=>$type->id, 'name'=>$type->caption]; //ToDo options!!!
            }

            $reservationTypeField = new C4GMultiCheckboxField();
//            $reservationTypeField->setChosen(true);
            $reservationTypeField->setFieldName('viewableTypes');
            $reservationTypeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['viewableTypes']);
            $reservationTypeField->setSortColumn(false);
            $reservationTypeField->setTableColumn(true);
            //$reservationTypeField->setColumnWidth(20);
            //$reservationTypeField->setSize(1); //count($typelist)
            $reservationTypeField->setOptions($typelist);
            $reservationTypeField->setMandatory(true);
            $reservationTypeField->setNotificationField(true);
            $reservationTypeField->setInitialValue($typelist[0]);
            $reservationTypeField->setHidden(count($typelist) == 1);
            $reservationTypeField->setColumnWidth(40);
            $fieldList[] = $reservationTypeField;
        } else {
            //FEHLER KEINE ART AUSGEWÄHLT
        }

        $captionField = new C4GTextField();
        $captionField->setFieldName('caption');
        $captionField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['caption']);
        $captionField->setSortColumn(false);
        $captionField->setTableColumn(true);
        $captionField->setMandatory(true);
        $captionField->setNotificationField(true);
        $captionField->setTableColumnPriority(2);
        $captionField->setColumnWidth(40);
        $fieldList[] = $captionField;

        $quantityField = new C4GNumberField();
        $quantityField->setFieldName('quantity');
        $quantityField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['quantity']);
        $quantityField->setInitialValue(1);
        $quantityField->setFormField(true);
        $quantityField->setTableColumn(true);
        $quantityField->setColumnWidth(10);
        $quantityField->setAlign('center');
        $fieldList[] = $quantityField;

        $descriptionField = new C4GTrixEditorField();
        $descriptionField->setFieldName('description');
        $descriptionField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['description']);
        $descriptionField->setFormField(true);
        $descriptionField->setTableColumn(false);
        $descriptionField->setNotificationField(true);
        $fieldList[] = $descriptionField;

        $condition = new C4GBrickCondition(C4GBrickConditionType::BOOLSWITCH, 'image');

        $imgField = new C4GImageField();
        $imgField->setFieldName('img');
        //$imgField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['image']);
        $imgField->setShowIfEmpty(false);
        $imgField->setFileTypes(C4GBrickFileType::IMAGES_PNG_JPG);
        $imgField->setFormField(true);
        $imgField->setTableColumn(false);
        $imgField->setSize(500);
        $imgField->setDatabaseField(false);
        $imgField->setSource(C4GBrickFieldSourceType::OTHER_FIELD);
        $imgField->setSourceField('image');
        $imgField->setCondition($condition);
        $fieldList[] = $imgField;

        $imgFileField = new C4GFileField();
        $imgFileField->setFieldName('image');
        $imgFileField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['image']);
        $imgFileField->setShowIfEmpty(true);
        $imgFileField->setFileTypes(C4GBrickFileType::IMAGES_PNG_JPG);
        $imgFileField->setFormField(true);
        $imgFileField->setCallOnChange(true);
        $fieldList[] = $imgFileField;

        $quantityField = new C4GSelectField();
        $quantityField->setFieldName('priceoption');
        $quantityField->setInitialValue('pAmount');
        $quantityField->setFormField(false);
        $fieldList[] = $quantityField;

        $priceField = new C4GDecimalField();
        $priceField->setFieldName('price');
        $priceField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['price']);
        //$priceField->setInitialValue(0);
        $priceField->setFormField(true);
        $priceField->setTableColumn(false);
//        $priceField->setDecimalPoint('.');
        $priceField->setDecimals(2);
        $fieldList[] = $priceField;

        $dateRangeDescriptionField = new C4GTextareaField();
        $dateRangeDescriptionField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['days_exclusion_text'][0]);
        $dateRangeDescriptionField->setDescription($GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['days_exclusion_text'][1]);
        $dateRangeDescriptionField->setFieldName('days_exclusion_text');
        $fieldList[] = $dateRangeDescriptionField;

        $publishedField = new C4GCheckboxField();
        $publishedField->setFieldName('published');
        $publishedField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['published']);
        $publishedField->setSortColumn(false);
        $publishedField->setTableColumn(true);
        $publishedField->setNotificationField(true);
        $publishedField->setColumnWidth(10);
        $publishedField->setAlign('center');
        $fieldList[] = $publishedField;

        return $fieldList;
    }

    /**
     * @param $tableName
     * @param $set
     * @param $insertId
     * @param $type
     * @param $fieldList
     * @return void
     */
    public function saveCallback($tableName, $set, $insertId, $type, $fieldList)
    {

        if ($this->dialogParams->getMemberId()) {
            $memberModel = \Contao\MemberModel::findByPk($this->dialogParams->getMemberId());

            if ($this->reservation_add_member_location) {
                $locset['member_id'] = $memberModel->id;
                $locset['tstamp'] = time();
                $locset['name'] = $memberModel->username;
                $locset['contact_name'] = $memberModel->firstname . ' ' . $memberModel->lastname;
                $locset['contact_street'] = $memberModel->street;
                $locset['contact_postal'] = $memberModel->postal;
                $locset['contact_city'] = $memberModel->city;
                $locset['contact_email'] = $memberModel->email;
                $locset['contact_website'] = $memberModel->website;
                $locset['contact_phone'] = $memberModel->phone;

                $coordinates = C4GUtils::geocodeAddress($memberModel->street.' '.$memberModel->postal.' '.$memberModel->city);
                if ($coordinates) {
                    $locset['locgeox'] = $coordinates[0];
                    $locset['locgeoy'] = $coordinates[1];
                }
            }

            $db = Database::getInstance();
            $locationTable = 'tl_c4g_reservation_location';
            $objectTable   = 'tl_c4g_reservation_object';

            $daysExclusionText = $set['days_exclusion_text'];
            if ($daysExclusionText) {
                $daysExclusionArr = explode(',', trim($daysExclusionText));
                $multiColumnArr = [];
                foreach ($daysExclusionArr as $daysExclusionStr) {
                    $fromToArray = explode('-', trim($daysExclusionStr));
                    if (count($fromToArray) == 2) {
                        $beginDate = trim($fromToArray[0]);
                        $endDate = trim($fromToArray[1]);
                    } else if (count($fromToArray) == 1) {
                        $beginDate = trim($fromToArray[0]);
                        $endDate = trim($fromToArray[0]);
                    }

                    if ($beginDate && $endDate) {
                        $beginTime = intval(C4gReservationDateChecker::getBeginOfDate(strtotime($beginDate)));
                        $endTime = intval(C4gReservationDateChecker::getEndOfDate(strtotime($endDate)));
                        if ($beginTime && $endTime) {
                            $multiColumnArr[] = ['date_exclusion' => $beginTime, 'date_exclusion_end' => $endTime];
                        }
                    }
                }
            }

            if ($this->reservation_add_member_location) {
                $stmt = $db->prepare("SELECT * FROM $locationTable WHERE name = ?");
                $result = $locset['name'] ? $stmt->execute($locset['name'])->fetchAssoc() : [];
            }
            $multiColumnStr = serialize($multiColumnArr);
            $alias = System::getContainer()->get('contao.slug')->generate($set['caption'], C4gReservationObjectModel::findByPk($insertId)->jumpTo ?: 0);

            if ($result && count($result)) {
                $stmt = $db->prepare("UPDATE $locationTable %s WHERE name = ?");
                $stmt->set($locset);
                $stmt->execute($locset['name']);
                if ($stmt->affectedRows) {
                    $locationId = $result['id'];
                    $stmt = $db->prepare("UPDATE $objectTable SET alias = ?, location = ?, days_exclusion = ? WHERE id = ?");
                    $stmt->execute($alias, $locationId, $multiColumnStr, $insertId);
                }
            } else {
                if ($this->reservation_add_member_location) {
                    $stmt = $db->prepare("INSERT INTO $locationTable %s");
                    $stmt->set($locset);
                    $stmt->execute();
                    if ($stmt->affectedRows) {
                        $locationId = $stmt->insertId;
                        $stmt = $db->prepare("UPDATE $objectTable SET alias = ?,location = ?, days_exclusion = ? WHERE id = ?");
                        $stmt->execute($alias, $locationId, $multiColumnStr, $insertId);
                    }
                } else {
                    $stmt = $db->prepare("UPDATE $objectTable SET alias = ?,days_exclusion = ? WHERE id = ?");
                    $stmt->execute($alias, $multiColumnStr, $insertId);
                }
            }
        }
    }
}

