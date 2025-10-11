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
            $this->import(BackendUser::class, 'User');

            if (strlen(Input::get('tid')))
            {
                $this->toggleVisibility(Input::get('tid'), Input::get('state'));
                $this->redirect($this->getReferer());
            }

            $href .= '&amp;id='.Input::get('id').'&amp;tid='.$row['id'].'&amp;state='.($row['cancellation']);

            if ($row['cancellation'])
            {
                $icon = 'invisible.gif';
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

            if ($arrRow['beginDate']) {
                $arrRow['beginDate'] = $arrRow['beginDate'] ? date($GLOBALS['TL_CONFIG']['dateFormat'],$arrRow['beginDate']). ' ' .date($GLOBALS['TL_CONFIG']['timeFormat'],$arrRow['beginTime']) : date($GLOBALS['TL_CONFIG']['dateFormat'],$arrRow['beginDate']);
            } else {
                $arrRow['beginDate'] = '';
            }

            if ($arrRow['endDate']) {
                $arrRow['endDate'] = $arrRow['endDate'] ? date($GLOBALS['TL_CONFIG']['dateFormat'],$arrRow['endDate']). ' ' .date($GLOBALS['TL_CONFIG']['timeFormat'],$arrRow['endTime']) : date($GLOBALS['TL_CONFIG']['dateFormat'],$arrRow['endDate']);
            } else {
                $arrRow['endDate'] = '';
            }

            $type = \con4gis\ReservationBundle\Classes\Models\C4gReservationTypeModel::findByPk($arrRow['reservation_type']);
            if ($type) {
                $arrRow['reservation_type'] = $type->caption;
            }

            $do = Input::get('do');
            if ($do && ($do == 'calendar')) {
                $checkedInYes = 'Ja';
                if ($arrRow['checkedIn'] && $arrRow['checkedIn'] > 1) {
                    $checkedInYes = 'Ja ('.$arrRow['checkedIn'].')';
                }

                $result = [
                    $arrRow['beginDate'],
                    $arrRow['endDate'],
                    $arrRow['desiredCapacity'],
                    $arrRow['reservation_type'],
                    $arrRow['lastname'],
                    $arrRow['firstname'],
                    $arrRow['reservation_object'],
                    $arrRow['checkedIn'] ? $checkedInYes : 'nein'
                ];
            } else {
                $result = [
                    $arrRow['beginDate'],
                    $arrRow['endDate'],
                    $arrRow['desiredCapacity'],
                    $arrRow['reservation_type'],
                    $arrRow['lastname'],
                    $arrRow['firstname'],
                    $arrRow['reservation_object']
                ];
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
            if (!$dc->activeRecord) {
                return $return;
            }
            if ($dc instanceof DataContainer && $dc->activeRecord->reservationObjectType) {
                $reservationObjectType = $dc->activeRecord->reservationObjectType;
            } elseif (is_array($dc)) {
                $reservationObjectType = $dc['reservationObjectType'];
            } else {
                $reservationObjectType = false;
            }

            if (!$reservationObjectType) {
                $objects = $this->Database->prepare("SELECT id,caption FROM tl_c4g_reservation_object")
                    ->execute();

                while ($objects->next()) {
                    $return[$objects->id] = $objects->caption;
                }

                $events = $this->Database->prepare("SELECT id,title,startDate FROM tl_calendar_events")
                    ->execute();

                while ($events->next()) {
                    $return[$events->id] = date($GLOBALS['TL_CONFIG']['dateFormat'],$events->startDate).': '.$events->title;
                }
            } else {
                switch ($reservationObjectType) {
                    case '1':
                    case '3':
                        $objects = $this->Database->prepare("SELECT id,caption FROM tl_c4g_reservation_object")
                            ->execute();

                        while ($objects->next()) {
                            $return[$objects->id] = $objects->caption;
                        }
                        break;
                    case '2':
                        $events = $this->Database->prepare("SELECT id,title,startDate FROM tl_calendar_events")
                            ->execute();

                        while ($events->next()) {
                            $return[$events->id] = date($GLOBALS['TL_CONFIG']['dateFormat'],$events->startDate).': '.$events->title;
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
            


            if ($id && $do && ($do == 'calendar')) {
                $GLOBALS['TL_DCA']['tl_c4g_reservation']['list']['label']['fields'] =
                    ['beginDate','endDate','desiredCapacity','reservation_type:tl_c4g_reservation_type.caption','lastname','firstname','reservation_object','checkedIn'];

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
                $GLOBALS['TL_DCA']['tl_c4g_reservation']['list']['label']['fields'] =
                    ['beginDate','endDate','desiredCapacity','reservation_type:tl_c4g_reservation_type.caption','lastname','firstname','reservation_object'];
            }

            // Check current action
            $key = Input::get('key');
            $reservationType = 0;
            if ($id && $key && ($key == 'sendNotification')) {
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
            $rt = Input::get('rt');
            $do = Input::get('do');

            $attributes = 'style="margin-right:3px"';
            $imgAttributes = 'style="width: 18px; height: 18px"';

            $showButton = false;

            if (($row['confirmed'] || $row['specialNotification']) && (!$row['emailConfirmationSend'])) {
                $type = $row['reservation_type'];
                if ($type) {
                    $reservationType = Database::getInstance()->prepare("SELECT * FROM tl_c4g_reservation_type WHERE `id`=? LIMIT 1")->execute($type)->fetchAssoc();

                    if ($reservationType) {
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