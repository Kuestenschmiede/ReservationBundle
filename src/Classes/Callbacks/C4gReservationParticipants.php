<?php
    namespace con4gis\ReservationBundle\Classes\Callbacks;

    use Contao\Backend;
    use Contao\BackendUser;
    use Contao\Input;
    use Contao\StringUtil;
    use Contao\Image;
    use Contao\Versions;
    use Contao\DataContainer;
    use con4gis\ProjectsBundle\Classes\Common\C4GBrickCommon;

    class C4gReservationParticipants extends Backend
    {
        /**
         * Import the back end user object
         */
        public function __construct()
        {
            parent::__construct();
            $this->import(BackendUser::class, 'User');
        }

        public function generateUuid($varValue, DataContainer $dc)
        {
            if ($varValue == '') {
                return C4GBrickCommon::getGUID();
            }
            else {
                return $varValue;
            }
        }

        public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
        {
            $this->import(BackendUser::class, 'User');
            
            if (strlen(Input::get('tid')))
            {
                $this->toggleVisibility(Input::get('tid'), (Input::get('state') == ''));
                $this->redirect($this->getReferer());
            }

            $href .= '&amp;id='.Input::get('id').'&amp;tid='.$row['id'].'&amp;state='.($row['cancellation'] ? '' : '1');


            if ($row['cancellation'])
            {
                $icon = 'invisible.gif';
            }

            return '<a href="' . $this->addToUrl($href) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
        }

        public function toggleVisibility($intId, $blnCancellation)
        {
            $objVersions = new Versions('tl_c4g_reservation_object', $intId);
            $objVersions->initialize();
                
            // Trigger the save_callback
            if (is_array($GLOBALS['TL_DCA']['tl_c4g_reservation_participants']['fields']['cancellation']['save_callback']))
            {
                foreach ($GLOBALS['TL_DCA']['tl_c4g_reservation_participants']['fields']['cancellation']['save_callback'] as $callback)
                {
                    $object = $this->import($callback[0]);
                    if (isset($object)) {
                        $blnCancellation = $object->{$callback[1]}(!$blnCancellation, $this);
                    }
                }
            }

            // Update the database
            $this->Database->prepare("UPDATE tl_c4g_reservation_participants SET tstamp=". time() .", cancellation='" . ($blnCancellation ? '' : '1') . "' WHERE `id`=?")
                ->execute($intId);
            $objVersions->create();
        }

        public function listFields($arrRow)
        {
            $result = [
                $arrRow['id'],
                $arrRow['title'],
                $arrRow['lastname'],
                $arrRow['firstname'],
                $arrRow['email'],
                $arrRow['cancellation'] ? $GLOBALS['TL_LANG']['tl_c4g_reservation_participants']['yes'] : ''
            ];
            return $result;
        }
    }
?>