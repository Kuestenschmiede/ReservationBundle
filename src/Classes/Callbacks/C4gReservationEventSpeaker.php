<?php
    namespace con4gis\ReservationBundle\Classes\Callbacks;
    use con4gis\ReservationBundle\Classes\Models\C4gReservationSettingsModel;
    use Contao\DC_Table;
    use Contao\DataContainer;
    use Contao\Input;
    use Contao\Image;
    use Contao\Backend;
    use Contao\BackendUser;
    use Contao\Model;
    use Contao\System;
    use Contao\Versions;
    use Contao\StringUtil;

    //class tl_c4g_reservation_event_speaker extends Backend
    class C4gReservationEventSpeaker extends Backend
    {
        /**
        * Import the back end user object
        */
        public function __construct()
        {
            parent::__construct();
            $this->import(BackendUser::class, 'User');
            // $this->User = BackendUser::getInstance();
        }

        public function generateUuid($varValue, DataContainer $dc)
        {
            if ($varValue == '') {
                return \c4g\projects\C4GBrickCommon::getGUID();
            } else {
                return $varValue;
            }
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
                return $this->Database->prepare("SELECT id FROM tl_c4g_reservation_event_speaker WHERE alias=? AND id!=?")->execute($alias, $dc->id)->numRows > 0;
            };

            // Generate the alias if there is none
            if (!$varValue)
            {
                $alias = $dc->activeRecord->title ? $dc->activeRecord->title.'-'.$dc->activeRecord->lastname.'-'.$dc->activeRecord->firstname : $dc->activeRecord->lastname.'-'.$dc->activeRecord->firstname;
                $varValue = System::getContainer()->get('contao.slug')->generate($alias, C4gReservationSettingsModel::findByPk($dc->activeRecord->id)->jumpTo ?: 0, $aliasExists);
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

        public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
        {
            $this->import(BackendUser::class, 'User');
            
            if (strlen(Input::get('tid'))) {
                $this->toggleVisibility(Input::get('tid'), (Input::get('state') == ''));
                $this->redirect($this->getReferer());
            }

            // Check permissions AFTER checking the tid, so hacking attempts are logged
            /* if (!$this->User->isAdmin && !$this->User->hasAccess('tl_c4g_reservation_event_speaker::published', 'alexf')) {
                return '';
            } */

            $href .= '&amp;id=' . Input::get('id') . '&amp;tid=' . $row['id'] . '&amp;state='.($row['published'] ? '' : 1);

            if (!$row['published']) {
                $icon = 'invisible.gif';
            }

            return '<a href="' . $this->addToUrl($href) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
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
            if (!$this->User->isAdmin && !$this->User->hasAccess('tl_c4g_reservation_event_speaker::published', 'alexf')) {
                $this->log('Not enough permissions to show/hide record ID "' . $intId . '"', 'tl_c4g_reservation_event_speaker toggleVisibility', TL_ERROR);
                $this->redirect(System::getContainer()->get('router')->generate('contao_backend').'?act=error');
            }

            $objVersions = new Versions('tl_c4g_reservation_event_speaker', $intId);
            $objVersions->initialize();
            
            // Trigger the save_callback
            if (isset($GLOBALS['TL_DCA']['tl_c4g_reservation_event_speaker']['fields']['published']['save_callback']) && is_array($GLOBALS['TL_DCA']['tl_c4g_reservation_event_speaker']['fields']['published']['save_callback'])) {
                foreach ($GLOBALS['TL_DCA']['tl_c4g_reservation_event_speaker']['fields']['published']['save_callback'] as $callback) {
                    $this->import($callback[0]);
                    $blnPublished = $this->$callback[0]->$callback[1]($blnPublished, $this);
                }
            }

            // Update the database
            $this->Database->prepare("UPDATE tl_c4g_reservation_event_speaker SET tstamp=" . time() . ", published='" . ($blnPublished ? 0 : 1) . "' WHERE `id`=?")
                ->execute($intId);
            $objVersions = new Versions('tl_c4g_reservation_event_speaker', $intId);
            $objVersions->create();
        }
    }
?>