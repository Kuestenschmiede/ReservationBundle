<?php
    namespace con4gis\ReservationBundle\Classes\Callbacks;

    use con4gis\CoreBundle\Classes\C4GVersionProvider;
    use con4gis\ReservationBundle\Classes\Models\C4gReservationLocationModel;
    use Contao\Backend;
    use Contao\BackendUser;
    use Contao\Database;
    use Contao\DataContainer;
    use Contao\System;

    class C4gReservationLocation extends Backend
    {
        /**
         * Import the back end user object
         */
        public function __construct()
        {
            parent::__construct();
            $this->import(Backenduser::class, 'User');
        }

        public function generateUuid($varValue, DataContainer $dc)
        {
            if ($varValue == '') {
                return \c4g\projects\C4GBrickCommon::getGUID();
            } else {
                return $varValue;
            }
        }

        /** Validate Center Lon*/
        public function setCenterLon($varValue, DataContainer $dc)
        {
            if (C4GVersionProvider::isInstalled('con4gis/maps') && !\con4gis\MapsBundle\Classes\Utils::validateLon($varValue)) {
                throw new Exception($GLOBALS['TL_LANG']['tl_c4g_reservation_location']['geox_invalid']);
            }
            return $varValue;
        }

        /** Validate Center Lat*/
        public function setCenterLat($varValue, DataContainer $dc)
        {
            if (C4GVersionProvider::isInstalled('con4gis/maps') && !\con4gis\MapsBundle\Classes\Utils::validateLat($varValue)) {
                throw new Exception($GLOBALS['TL_LANG']['tl_c4g_reservation_location']['geoy_invalid']);
            }
            return $varValue;
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
         * Auto-generate the object alias if it has not been set yet
         *
         * @param mixed         $varValue
         * @param DataContainer $dc
         *
         * @return mixed
         *
         * @throws Exception
         */
        public function generateAlias($varValue, DataContainer $dc)
        {
            $aliasExists = function (string $alias) use ($dc): bool
            {
                return $this->Database->prepare("SELECT id FROM tl_c4g_reservation_location WHERE alias=? AND id!=?")->execute($alias, $dc->id)->numRows > 0;
            };

            // Generate the alias if there is none
            if (!$varValue)
            {
                $varValue = System::getContainer()->get('contao.slug')->generate($dc->activeRecord->name, C4gReservationLocationModel::findByPk($dc->activeRecord->id)->jumpTo ?: 0, $aliasExists);
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