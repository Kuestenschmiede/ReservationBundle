<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 8
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2022, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

$GLOBALS['TL_DCA']['tl_c4g_reservation_team_role'] = array
(
    //config
    'config' => array
    (
        'dataContainer'     => 'Table',
        'enableVersioning'  => true,
        'sql'               => array
        (
            'keys' => array
            (
                'id' => 'primary'
            )
        )
    ),


    //List
    'list' => array
    (
        'sorting' => array
        (
            'mode'              => 2,
            'fields'            => array('caption','language'),
            'panelLayout'       => 'filter;sort,search,limit',
//            'headerFields'      => array('lastname','firstname'),
        ),

        'label' => array
        (
            'fields'            => array('caption','language'),
            //'format'            => '<span class="reservation_date" style="color:#E30518">%s</span><span class="reservation_time" style="color:#E30518">%s</span><span class="reservation_id" style="color:#E30518">%s</span><span class="lastname" style="color:#E30518">%s</span><span class="firstname" style="color:#E30518">%s</span>',
            //'label_callback'    => array('tl_c4g_reservation', 'listDates'),
            'showColumns'       => true,
        ),

        'global_operations' => array
        (
            'all' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'          => 'act=select',
                'class'         => 'header_edit_all',
                'attributes'    => 'onclick="Backend.getScrollOffSet()" accesskey="e"'
            )
        ),

        'operations' => array
        (
            'edit' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_team_role']['edit'],
                'href'          => 'act=edit',
                'icon'          => 'edit.gif',
            ),
            'copy' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_team_role']['copy'],
                'href'          => 'act=copy',
                'icon'          => 'copy.gif',
            ),
            'delete' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_team_role']['delete'],
                'href'          => 'act=delete',
                'icon'          => 'delete.gif',
                'attributes'    => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\')) return false;Backend.getScrollOffset()"',
            ),
            'show' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_team_role']['show'],
                'href'          => 'act=show',
                'icon'          => 'show.gif',
            ),
            'toggle' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation_team_role']['TOGGLE'],
                'icon'                => 'visible.gif',
                'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'     => array('tl_c4g_reservation_team_role', 'toggleIcon')
            )
        )
    ),

    //Palettes
    'palettes' => array
    (
        'default'   =>  'caption, language, published;'
    ),


    //Fields
    'fields' => array
    (
        'id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_team_role']['id'],
            'sql'               => "int(10) unsigned NOT NULL auto_increment"
        ),

        'tstamp' => array
        (
            'sql'               => "int(10) unsigned NOT NULL default '0'"
        ),

        'caption' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_team_role']['caption'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(254) NOT NULL default ''"

        ),

        'language' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_c4g_reservation_team_role']['language'],
            'exclude'               => true,
            'inputType'             => 'select',
            'options'               => ['de'=>'Deutsch', 'en'=>'Englisch'],
            'eval'                  => array('chosen' => false, 'tl_class' => "w50"),
            'sql'                   => "char(5) NOT NULL default ''"
        ),

        'published' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['published'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 clr'),
            'sql'                     => "int(1) unsigned NULL default 1"
        ),

    )
);


/**
 * Class tl_c4g_reservation_team_role
 */
class tl_c4g_reservation_team_role extends Backend
{
    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    public function generateUuid($varValue, DataContainer $dc)
    {
        if ($varValue == '') {
            return \c4g\projects\C4GBrickCommon::getGUID();
        } else {
            return $varValue;
        }
    }

    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $this->import('BackendUser', 'User');

        if (strlen($this->Input->get('tid'))) {
            $this->toggleVisibility($this->Input->get('tid'), ($this->Input->get('state') == 0));
            $this->redirect($this->getReferer());
        }

        $href .= '&amp;id=' . $this->Input->get('id') . '&amp;tid=' . $row['id'] . '&amp;state='.($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.gif';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $this->generateImage($icon, $label) . '</a> ';
    }
    public function toggleVisibility($intId, $blnPublished)
    {
        // Check permissions to publish
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_c4g_reservation_team_role::published', 'alexf')) {
            $this->log('Not enough permissions to show/hide record ID "' . $intId . '"', 'tl_c4g_reservation_team_role toggleVisibility', TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        $this->createInitialVersion('tl_c4g_reservation_team_role', $intId);

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_c4g_reservation_team_role']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_c4g_reservation_team_role']['fields']['published']['save_callback'] as $callback) {
                $this->import($callback[0]);
                $blnPublished = $this->$callback[0]->$callback[1]($blnPublished, $this);
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE tl_c4g_reservation_team_role SET tstamp=" . time() . ", published='" . ($blnPublished ? '0' : '1') . "' WHERE `id`=?")
            ->execute($intId);
        $this->createNewVersion('tl_c4g_reservation_team_role', $intId);
    }
}