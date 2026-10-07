<?php
    namespace con4gis\ReservationBundle\Classes\Callbacks;

  
    use con4gis\CoreBundle\Classes\Helper\InputHelper;
    use con4gis\CoreBundle\Resources\contao\models\C4gLogModel;
    use con4gis\ProjectsBundle\Classes\Common\C4GBrickCommon;
    use con4gis\ProjectsBundle\Classes\Notifications\C4GNotification;
    use con4gis\ReservationBundle\Classes\Notifications\C4gReservationConfirmation;
    use con4gis\ReservationBundle\Classes\Models\C4gReservationParamsModel;
    use con4gis\ReservationBundle\Classes\Models\C4gReservationTypeModel;
    use Contao\Controller;
    use Contao\Database;
    use Contao\Image;
    use Contao\StringUtil;

    use Contao\Input;
    use Contao\DC_Table;
    use Contao\DataContainer;
    use Contao\BackendUser;
    use Contao\Backend;
    use Contao\System;
    use Contao\Versions;
    use Contao\CalendarEventsModel;

    /**
     * Class tl_c4g_reservation
     */
    class C4gReservation extends Backend
    {
        /**
         * Import the back end user object
         */
        public function __construct()
        {
            parent::__construct();
            $this->import(BackendUser::class, 'User');
        }

        public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
        {
            if (is_array($title)) {
                $title = $title[0] ?? '';
            }
            if (is_array($label)) {
                $label = $label[0] ?? '';
            }


            $this->import(BackendUser::class, 'User');

            if (is_array($title)) {
                $title = $title[0] ?? '';
            }

            if (strlen(Input::get('tid')))
            {
                $this->toggleVisibility(Input::get('tid'), Input::get('state'));
                $this->redirect($this->getReferer());
            }

            $href .= '&amp;id='.Input::get('id').'&amp;tid='.$row['id'].'&amp;state='.($row['cancellation']);

            if ($row['cancellation'])
            {
                $icon = 'invisible.svg';
            }
            else
            {
                $icon = 'visible.svg';
            }

            return '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';

        }

        public function toggleVisibility($intId, $blnCancellation)
        {
            $objVersions = new Versions('tl_c4g_reservation', $intId);
            $objVersions->initialize();

            // Trigger the save_callback
            /*if (isset($GLOBALS['TL_DCA']['tl_c4g_reservation']['fields']['cancellation']['save_callback']) && is_array($GLOBALS['TL_DCA']['tl_c4g_reservation']['fields']['cancellation']['save_callback']))
            {
                foreach ($GLOBALS['TL_DCA']['tl_c4g_reservation']['fields']['cancellation']['save_callback'] as $callback)
                {
                    $this->import($callback[0]);
                    $blnCancellation = $this->$callback[0]->$callback[1]($blnCancellation == '1' ? '' : '1', $this);
                }
            }*/

            // Update the database
            $this->Database->prepare("UPDATE tl_c4g_reservation SET tstamp=". time() .", cancellation='" . ($blnCancellation == '1' ? '' : '1') . "' WHERE `id`=?")
                ->execute($intId);
            $objVersions = new Versions('tl_c4g_reservation', $intId);
            $objVersions->create();
        }

        public function listFields($arrRow)
        {
            $do = Input::get('do');
            $objectType = $arrRow['reservationObjectType'];
            $object_id = $arrRow['reservation_object'];

            $reservationObjects = '';
            if ($objectType === '2') {
                $event = CalendarEventsModel::findByPk($object_id);
                if ($event) {
                    $object = $event->title;
                }
            } else {
                $reservation_object = \con4gis\ReservationBundle\Classes\Models\C4gReservationObjectModel::findByPk($object_id);
                if ($reservation_object) {
                    $object = $reservation_object->caption;
                }
            }


            $arrRow['reservation_object'] = $object;

            $timeFormat = ($GLOBALS['TL_CONFIG']['timeFormat'] ?? '') ?: 'H:i';
            $dateFormat = ($GLOBALS['TL_CONFIG']['dateFormat'] ?? '') ?: 'd.m.Y';

            if ($arrRow['beginDate']) {
                $originalBeginDate = $arrRow['beginDate'];
                $beginTime = $arrRow['beginTime'] ?? 0;
                $formattedDate = \Contao\Date::parse($dateFormat, (int)$arrRow['beginDate']);
                $formattedTime = ($beginTime !== 0 && $beginTime !== '' && $beginTime !== null && $beginTime !== '0') ? \Contao\Date::parse($timeFormat, (int)$beginTime) : '';
                $arrRow['beginDate'] = trim($formattedDate . ' ' . $formattedTime);
                $arrRow['beginTime'] = $formattedTime;
                $arrRow['beginDateInt'] = $arrRow['beginDate'];
                $arrRow['beginDate'] = $originalBeginDate; 
            } else {
                $arrRow['beginDate'] = '';
                $arrRow['beginTime'] = '';
                $arrRow['beginDateInt'] = '';
            }

            if ($arrRow['endDate']) {
                $originalEndDate = $arrRow['endDate'];
                $endDate = (int) $arrRow['endDate'];
                $endTimeInt = $arrRow['endTime'] ?? 0;
                $formattedEndDate = \Contao\Date::parse($dateFormat, $endDate);
                $formattedEndTime = ($endTimeInt !== 0 && $endTimeInt !== '' && $endTimeInt !== null && $endTimeInt !== '0') ? \Contao\Date::parse($timeFormat, (int)$endTimeInt) : '';
                $arrRow['endDate'] = trim($formattedEndDate . ' ' . $formattedEndTime);
                $arrRow['endTime'] = $formattedEndTime;
                $arrRow['endDateInt'] = $arrRow['endDate'];
                $arrRow['endDate'] = $originalEndDate;
            } else {
                $arrRow['endDate'] = '';
                $arrRow['endTime'] = '';
                $arrRow['endDateInt'] = '';
            }

            $type = \con4gis\ReservationBundle\Classes\Models\C4gReservationTypeModel::findByPk($arrRow['reservation_type']);
            if ($type) {
                $arrRow['reservation_type'] = $type->caption;
            }

            $showOrganisation = false;
            if (Database::getInstance()->tableExists('tl_c4g_settings')) {
                $settingsFields = Database::getInstance()->listFields('tl_c4g_settings');
                $fieldNames = array_column($settingsFields, 'name');
                if (in_array('showOrganisationInsteadOfName', $fieldNames, true)) {
                    $settings = Database::getInstance()->prepare("SELECT showOrganisationInsteadOfName FROM tl_c4g_settings")->execute()->fetchAssoc();
                    $showOrganisation = !empty($settings['showOrganisationInsteadOfName']);
                }
            }

            if ($showOrganisation) {
                $result = [
                    $arrRow['beginDateInt'],
                    $arrRow['endDateInt'],
                    $arrRow['desiredCapacity'],
                    $arrRow['reservation_type'],
                    $arrRow['organisation'] ?? '',
                    $arrRow['reservation_object']
                ];
            } else {
                $result = [
                    $arrRow['beginDateInt'],
                    $arrRow['endDateInt'],
                    $arrRow['desiredCapacity'],
                    $arrRow['reservation_type'],
                    $arrRow['lastname'],
                    $arrRow['firstname'],
                    $arrRow['reservation_object']
                ];
            }
            if ($do && ($do == 'calendar')) {
                $checkedInYes = 'Ja';
                if ($arrRow['checkedIn'] && $arrRow['checkedIn'] > 1) {
                    $checkedInYes = 'Ja ('.$arrRow['checkedIn'].')';
                }
                $result[] = $arrRow['checkedIn'] ? $checkedInYes : 'nein';
            }

            return $result;
        }

        /**
         * @param DataContainer|array $dc
         * @return array
         */
        public function getActObjects($dc)
        {
            $return = [];
            if ($dc instanceof DataContainer) {
                if (!$dc->activeRecord) {
                    return $return;
                }
                $reservationObjectType = $dc->activeRecord->reservationObjectType;
            } elseif (is_array($dc)) {
                $reservationObjectType = $dc['reservationObjectType'] ?? false;
            } else {
                $reservationObjectType = false;
            }

            if (!$reservationObjectType) {
                $objects = $this->Database->prepare("SELECT id,caption FROM tl_c4g_reservation_object ORDER BY sorting, caption")
                    ->execute();

                while ($objects->next()) {
                    $return[$objects->id] = $objects->caption;
                }

                $events = $this->Database->prepare("SELECT id,title,startDate FROM tl_calendar_events")
                    ->execute();

                while ($events->next()) {
                    $dt = new \DateTime('@' . (int)$events->startDate);
                    $dt->setTimezone(new \DateTimeZone('Europe/Berlin'));
                    $return[$events->id] = $dt->format($GLOBALS['TL_CONFIG']['dateFormat']) . ': ' . $events->title;
                }
            } else {
                switch ($reservationObjectType) {
                    case '1':
                    case '3':
                        $objects = $this->Database->prepare("SELECT id,caption FROM tl_c4g_reservation_object ORDER BY sorting, caption")
                            ->execute();

                        while ($objects->next()) {
                            $return[$objects->id] = $objects->caption;
                        }
                        break;
                    case '2':
                        $events = $this->Database->prepare("SELECT id,title,startDate FROM tl_calendar_events")
                            ->execute();

                        while ($events->next()) {
                            $dt = new \DateTime('@' . (int)$events->startDate);
                            $dt->setTimezone(new \DateTimeZone('Europe/Berlin'));
                            $return[$events->id] = $dt->format($GLOBALS['TL_CONFIG']['dateFormat']) . ': ' . $events->title;
                        }
                        break;
                }
            }

            return $return;
        }

        /**
         * @param DataContainer $dc
         */
        public function doNotDeleteDataWithoutParent(DataContainer $dc)
        {
            //return;
        }

        /**
         * @param \Contao\DataContainer $dc
         */
        public function setParent(DC_Table $dc)
        {
            \Contao\Message::addInfo($GLOBALS['TL_LANG']['tl_c4g_reservation']['infoReservation']);
            
            $do = Input::get('do');
            $id = Input::get('id');
            
            $showOrganisation = false;
            $formSettingsId = 0;

            if (Database::getInstance()->tableExists('tl_c4g_settings')) {
                $settingsFields = Database::getInstance()->listFields('tl_c4g_settings');
                $fieldNames = array_column($settingsFields, 'name');

                $selectFields = [];
                if (in_array('showOrganisationInsteadOfName', $fieldNames, true)) {
                    $selectFields[] = 'showOrganisationInsteadOfName';
                }
                if (in_array('formSettingsSelection', $fieldNames, true)) {
                    $selectFields[] = 'formSettingsSelection';
                }

                if (!empty($selectFields)) {
                    $settings = Database::getInstance()->prepare("SELECT " . implode(',', $selectFields) . " FROM tl_c4g_settings")->execute()->fetchAssoc();
                    $showOrganisation = !empty($settings['showOrganisationInsteadOfName']);
                    $formSettingsId = intval($settings['formSettingsSelection'] ?? 0);
                }
            }

            if ($id && $do && ($do == 'calendar')) {
                if ($showOrganisation) {
                    $GLOBALS['TL_DCA']['tl_c4g_reservation']['list']['label']['fields'] =
                        ['beginDateInt','endDateInt','desiredCapacity','reservation_type','organisation','reservation_object','checkedIn'];
                } else {
                    $GLOBALS['TL_DCA']['tl_c4g_reservation']['list']['label']['fields'] =
                        ['beginDateInt','endDateInt','desiredCapacity','reservation_type','lastname','firstname','reservation_object','checkedIn'];
                }
                $GLOBALS['TL_DCA']['tl_c4g_reservation']['list']['label']['operations'] = ['edit', 'copy', 'delete', 'show', 'participants', 'confirmationEmail', 'toggle'];

                $GLOBALS['TL_DCA']['tl_c4g_reservation']['fields']['reservationObjectType']['default'] = '2';
                $GLOBALS['TL_DCA']['tl_c4g_reservation']['fields']['reservationObjectType']['eval']['disabled'] = true;
                $GLOBALS['TL_DCA']['tl_c4g_reservation']['fields']['reservation_object']['default'] = $id;
                $GLOBALS['TL_DCA']['tl_c4g_reservation']['fields']['reservation_object']['eval']['chosen'] = false;
                $GLOBALS['TL_DCA']['tl_c4g_reservation']['fields']['reservation_object']['eval']['disabled'] = true;

                $GLOBALS['TL_DCA']['tl_c4g_reservation']['fields']['beginDate']['eval']['disabled'] = true;
                $GLOBALS['TL_DCA']['tl_c4g_reservation']['fields']['beginTime']['eval']['disabled'] = true;
                $GLOBALS['TL_DCA']['tl_c4g_reservation']['fields']['endDate']['eval']['disabled'] = true;
                $GLOBALS['TL_DCA']['tl_c4g_reservation']['fields']['endTime']['eval']['disabled'] = true;
            } else {
                if ($showOrganisation) {
                    $GLOBALS['TL_DCA']['tl_c4g_reservation']['list']['label']['fields'] =
                        ['beginDateInt','endDateInt','desiredCapacity','reservation_type','organisation','reservation_object'];
                } else {
                    $GLOBALS['TL_DCA']['tl_c4g_reservation']['list']['label']['fields'] =
                        ['beginDateInt','endDateInt','desiredCapacity','reservation_type','lastname','firstname','reservation_object'];
                }
                $GLOBALS['TL_DCA']['tl_c4g_reservation']['list']['label']['operations'] = ['edit', 'copy', 'delete', 'show', 'participants', 'confirmationEmail', 'toggle'];
            }

            if (!$formSettingsId && $id && $do !== 'calendar' && Database::getInstance()->tableExists('tl_c4g_reservation') && Database::getInstance()->fieldExists('formular_id', 'tl_c4g_reservation')) {
                $resRow = Database::getInstance()->prepare("SELECT formular_id FROM tl_c4g_reservation WHERE id=?")->execute($id)->fetchAssoc();
                if (!empty($resRow['formular_id'])) {
                    $formSettingsId = intval($resRow['formular_id']);
                }
            }

            if ($formSettingsId > 0 && Database::getInstance()->tableExists('tl_c4g_reservation_settings')) {
                $fieldSelect = Database::getInstance()->prepare("SELECT fieldSelection FROM tl_c4g_reservation_settings WHERE id=?")->execute($formSettingsId)->fetchAssoc();
                if ($fieldSelect && !empty($fieldSelect['fieldSelection'])) {
                    $additionaldatas = StringUtil::deserialize($fieldSelect['fieldSelection'], true);
                    if (is_array($additionaldatas)) {
                        foreach ($additionaldatas as $rowdata) {
                            $rowField = $rowdata['additionaldatas'] ?? '';
                            $individualLabel = trim($rowdata['individualLabel'] ?? '');
                            if ($rowField && $individualLabel !== '' && isset($GLOBALS['TL_DCA']['tl_c4g_reservation']['fields'][$rowField])) {
                                $origLabel = $GLOBALS['TL_DCA']['tl_c4g_reservation']['fields'][$rowField]['label'] ?? null;
                                $desc = is_array($origLabel) ? ($origLabel[1] ?? '') : '';
                                $GLOBALS['TL_DCA']['tl_c4g_reservation']['fields'][$rowField]['label'] = [$individualLabel, $desc];
                            }
                        }
                    }
                }
            }

            // Check current action
            $key = Input::get('key');
            $reservationType = 0;
            if ($id && $key && ($key == 'sendNotification')) {
                \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('C4gReservation', 'Key sendNotification detected for ID: ' . $id);
                C4gReservationConfirmation::sendNotification($id);
                //delete key per redirect
                Controller::redirect(str_replace('&key='.$key, '', \Contao\Environment::get('request')));
            }
        }

        /**
         * @param $dc
         * @return array
         */
        public function loadMemberOptions($dc) {
            $options = [];

            if (!$dc->activeRecord) {
                return $options;
            }

            $options[$dc->activeRecord->id] = '';

            $stmt = $this->Database->prepare("SELECT id, firstname, lastname FROM tl_member WHERE `disable` != 1");
            $result = $stmt->execute()->fetchAllAssoc();

            foreach ($result as $row) {
                $options[$row['id']] = $row['lastname'] . ', ' . $row['firstname'];
            }
            return $options;
        }

        /**
         * @param $dc
         * @return array
         */
        public function loadGroupOptions($dc) {
            $options = [];

            if (!$dc->activeRecord) {
                return $options;
            }

            $options[$dc->activeRecord->id] = '';

            $stmt = $this->Database->prepare("SELECT id, name FROM tl_member_group WHERE `disable` != 1");
            $result = $stmt->execute()->fetchAllAssoc();

            foreach ($result as $row) {
                $options[$row['id']] = $row['name'];
            }
            return $options;
        }

        public function sendNotification($row, $href, $label, $title, $icon) {
            if (is_array($title)) {
                $title = $title[0] ?? '';
            }
            if (is_array($label)) {
                $label = $label[0] ?? '';
            }

            if (is_array($title)) {
                $title = $title[0] ?? '';
            }
            $rt = Input::get('rt');
            $do = Input::get('do');

            $icon = 'bundles/con4gisreservation/images/be-icons/con4gis_reservation_notification.svg';

            $attributes = 'style="margin-right:3px"';
            $imgAttributes = 'style="width: 18px; height: 18px"';

            $showButton = false;
            $emailSent = ($row['emailConfirmationSend'] === '1');

            if (($row['confirmed'] || $row['specialNotification']) && !$emailSent) {
                $type = $row['reservation_type'];
                if ($type) {
                    $reservationType = Database::getInstance()->prepare("SELECT * FROM tl_c4g_reservation_type WHERE `id`=? LIMIT 1")->execute($type)->fetchAssoc();

                    if ($reservationType) {
                        error_log('C4gReservation: Found reservation type for ID ' . $row['id']);
                        $confirmed = ($row['confirmed'] === '1' || $row['confirmed'] === 1);
                        $special = ($row['specialNotification'] === '1' || $row['specialNotification'] === 1);
                        $autoSend = $reservationType['auto_send'] ?? 'not set';
                        \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('C4gReservation', 'Checking notification types for reservation ' . $row['id'] . ': confirmed=' . ($confirmed ? '1' : '0') . ', special=' . ($special ? '1' : '0') . ', emailSent=' . ($emailSent ? '1' : '0') . ', autoSend=' . $autoSend);
                        if ($row['confirmed']) {
                            $notificationConifrmationType = StringUtil::deserialize($reservationType['notification_confirmation_type']);

                            if ($notificationConifrmationType && (count($notificationConifrmationType) > 0)) {
                                $showButton = true;
                            }
                        }

                        if ($row['specialNotification']) {
                            $notificationSpecialType = StringUtil::deserialize($reservationType['notification_special_type']);

                            if ($notificationSpecialType && (count($notificationSpecialType) > 0)) {
                                $showButton = true;
                            }
                        }
                    }


                }
            }

            if (!$showButton) {
                return '';
            }

            $href = System::getContainer()->get('router')->generate('contao_backend')."?do=$do&key=sendNotification&id=".$row['id'];
            return '<a href="' . $href . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>'.Image::getHtml($icon, $label, $imgAttributes).'</a> ';
        }

        /**
         * @param $varValue
         * @param DataContainer $dc
         * @return string
         */
        public function generateKey($value, $dc) {
            if (!$value) {
                $value = C4GBrickCommon::getUUID();
                $database = Database::getInstance();
                $reservations = $database->prepare("SELECT * FROM tl_c4g_reservation where `reservation_id`=?")
                    ->execute($value)->fetchAllAssoc();
                if ($reservations && count($reservations) > 0) {
                    $value = C4GBrickCommon::getUUID();
                }
            }

            return $value;
        }
    }
?>