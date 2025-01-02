<?php

namespace con4gis\ReservationBundle\Classes\Callbacks;

use con4gis\ReservationBundle\Classes\Models\C4gReservationTypeModel;
use Contao\Backend;
use Contao\BackendUser;
use Contao\Input;
use Contao\StringUtil;
use Contao\Image;
use Contao\Versions;
use Contao\System;

class C4gReservationType extends Backend
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

        if (strlen(Input::get('tid'))) {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') == ''));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_c4g_reservation_type::published', 'alexf')) {
            return '';
        }

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
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_c4g_reservation_type::published', 'alexf'))
        {
            $this->log('Not enough permissions to show/hide record ID "'.$intId.'"', 'tl_c4g_reservation_type toggleVisibility', TL_ERROR);
            $this->redirect(System::getContainer()->get('router')->generate('contao_backend').'/main.php?act=error');
        }
        $objVersions = new Versions('tl_c4g_reservation_type', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (isset($GLOBALS['TL_DCA']['tl_c4g_reservation_type']['fields']['published']['save_callback']) && is_array($GLOBALS['TL_DCA']['tl_c4g_reservation_type']['fields']['published']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_c4g_reservation_type']['fields']['published']['save_callback'] as $callback)
            {
                $this->import($callback[0]);
                $blnPublished = $this->$callback[0]->$callback[1]($blnPublished, $this);
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE tl_c4g_reservation_type SET tstamp=". time() .", published='" . ($blnPublished ? 0 : 1) . "' WHERE id=?")
            ->execute($intId);
        $objVersions = new Versions('tl_c4g_reservation_type', $intId);
        $objVersions->create();
    }

    /**
     * @param $dc
     * @return array
     */
    public function loadMemberOptions($dc) {
        $options = [];

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

        $stmt = $this->Database->prepare("SELECT id, name FROM tl_member_group WHERE `disable` != 1");
        $result = $stmt->execute()->fetchAllAssoc();

        foreach ($result as $row) {
            $options[$row['id']] = $row['name'];
        }
        return $options;
    }

    /**
     * @param \Contao\DataContainer $dc
     */
    public function showInfoMessage($dc)
    {
        \Contao\Message::addInfo($GLOBALS['TL_LANG']['tl_c4g_reservation_type']['infotext']);
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
            return $this->Database->prepare("SELECT id FROM tl_c4g_reservation_type WHERE alias=? AND id!=?")->execute($alias, $dc->id)->numRows > 0;
        };

        // Generate the alias if there is none
        if (!$varValue)
        {
            $varValue = System::getContainer()->get('contao.slug')->generate($dc->activeRecord->caption, C4gReservationTypeModel::findByPk($dc->activeRecord->id)->jumpTo ?: 0, $aliasExists);
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