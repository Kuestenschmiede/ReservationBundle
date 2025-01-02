<?php

    namespace con4gis\ReservationBundle\Classes\Callbacks;

    use con4gis\CoreBundle\Classes\Helper\ArrayHelper;
    use con4gis\ReservationBundle\Classes\Models\C4gReservationObjectModel;
    use con4gis\ReservationBundle\Classes\Models\C4gReservationTypeModel;
    use Contao\CalendarBundle\Security\ContaoCalendarPermissions;
    use Contao\Config;
    use Contao\DC_Table;
    use Contao\DataContainer;
    use Contao\Backend;
    use Contao\BackendUser;
    use Contao\Input;
    use Contao\StringUtil;
    use Contao\Image;
    use Contao\Versions;
    use Contao\System;

    class C4gReservationObject extends Backend
    {

        private $dataContainer;
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
            $test = $this->dataContainer;
            if (strlen(Input::get('tid')))
            {
                $state = Input::get('state');
                $tid = Input::get('tid');
                $this->toggleVisibility(Input::get('tid'), (Input::get('state') === ''));
                $this->redirect($this->getReferer());
            }
            
            // Check permissions AFTER checking the tid, so hacking attempts are logged
            if (!$this->User->isAdmin && !$this->User->hasAccess('tl_c4g_reservation_object::published', 'alexf'))
            {
                return '';
            }
            
            $href .= '&amp;id='.Input::get('id').'&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);
            
            if (!$row['published'])
            {
                $icon = 'invisible.svg';
            }
            
            return '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
        }
        
    
        /**
         * Disable/enable a user group
         *
         * @param integer $intId
         * @param boolean $blnVisible
         * @param DataContainer $dc
         *
         * @throws Contao\CoreBundle\Exception\AccessDeniedException
         */
       
        public function toggleVisibility($intId, $blnPublished)
        {
            // Check permissions to publish
            if (!$this->User->isAdmin && !$this->User->hasAccess('tl_c4g_reservation_object::published', 'alexf'))
            {
                $this->log('Not enough permissions to show/hide record ID "'.$intId.'"', 'tl_c4g_reservation_object toggleVisibility', TL_ERROR);
                $this->redirect(System::getContainer()->get('router')->generate('contao_backend').'/main.php?act=error');
            }
            $objVersions = new Versions('tl_c4g_reservation_object', $intId);
            $objVersions->initialize();
    
            // Trigger the save_callback
            if (isset($GLOBALS['TL_DCA']['tl_c4g_reservation_object']['fields']['published']['save_callback']) && is_array($GLOBALS['TL_DCA']['tl_c4g_reservation_object']['fields']['published']['save_callback']))
            {
                foreach ($GLOBALS['TL_DCA']['tl_c4g_reservation_object']['fields']['published']['save_callback'] as $callback)
                {
                    $this->import($callback[0]);
                    $blnPublished = $this->$callback[0]->$callback[1]($blnPublished, $this);
                }
            }
    
            // Update the database
            $this->Database->prepare("UPDATE tl_c4g_reservation_object SET tstamp=". time() .", published='" . ($blnPublished ? 0 : 1) . "' WHERE id=?")
                ->execute($intId);
            $objVersions = new Versions('tl_c4g_reservation_object', $intId);
            $objVersions->create();
        }
    
    
        public function listFields($arrRow)
        {
            $type_ids = StringUtil::deserialize($arrRow['viewableTypes']);
    
            $reservationTypes = '';
            foreach ($type_ids as $type_id) {
                $reservation_type = C4gReservationTypeModel::findByPk($type_id);
                if ($reservation_type) {
                    if ($reservationTypes == '') {
                        $reservationTypes .= $reservation_type->caption;
                    } else {
                        $reservationTypes .= ','.$reservation_type->caption;
                    }
                }
            }
    
            $arrRow['viewableTypes'] = $reservationTypes;
    
            $result = [
                $arrRow['caption'],
                $arrRow['quantity'],
                $arrRow['desiredCapacityMin'],
                $arrRow['desiredCapacityMax'],
                $arrRow['viewableTypes'],
                $arrRow['time_interval']
            ];
            return $result;
        }
    
        public function getTypes(DataContainer $dc)
        {
            $return = [];
    
            $types = $this->Database->prepare("SELECT id, caption, reservationObjectType, published FROM tl_c4g_reservation_type")
                ->execute()->fetchAllAssoc();
            foreach ($types as $type) {
                if ($type['reservationObjectType'] != '2') {
                    $key = $type['id'];
                    $return[$key] = $type['caption'];
                }
            }
    
            asort($return);
            return $return;
        }
    
        /**
         * @param $dc
         * @return array
         */
        public function loadMemberOptions($dc) {
            $this->dataContainer = $dc;
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
         * Return all speaker as array
         * @return array
         */
        public function getSpeakerName(DataContainer $dc)
        {
            $return = [];
    
            $objects = $this->Database->prepare("SELECT id,title,firstname,lastname FROM tl_c4g_reservation_event_speaker ORDER BY lastname")
                ->execute();
    
            while ($objects->next()) {
                $name = '';
                if ($objects->title) {
                    $name = $objects->lastname.','.$objects->firstname.','.$objects->title;
                } else {
                    $name = $objects->lastname.','.$objects->firstname;
                }
    
                $return[$objects->id] = $name;
            }
    
            return $return;
        }
    
        /**
         * Auto-generate the object alias if it has not been set yet
         *
         * @param mixed         $varValue
         * @param DataContainer $dc
         *
         * @return mixed
         *
         * @throws Exception
         */
        public function generateAlias($varValue, $dc)
        {
            $aliasExists = function (string $alias) use ($dc): bool
            {
                return $this->Database->prepare("SELECT id FROM tl_c4g_reservation_object WHERE alias=? AND id!=?")->execute($alias, $dc->id)->numRows > 0;
            };
    
            // Generate the alias if there is none
            if (!$varValue)
            {
                $varValue = System::getContainer()->get('contao.slug')->generate($dc->activeRecord->caption, C4gReservationObjectModel::findByPk($dc->activeRecord->id)->jumpTo ?: 0, $aliasExists);
            }
            elseif (preg_match('/^[1-9]\d*$/', $varValue))
            {
                throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasNumeric'], $varValue));
            }
            elseif ($aliasExists($varValue))
            {
                throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
            }
    
            return $varValue;
        }
    }
?>
