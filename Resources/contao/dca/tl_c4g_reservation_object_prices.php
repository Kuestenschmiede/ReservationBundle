<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 8
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2021, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

$GLOBALS['TL_DCA']['tl_c4g_reservation_object_prices'] = array
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
            'fields'            => array('priceoption','price'),
            'panelLayout'       => 'filter;sort,search,limit',
        ),

        'label' => array
        (
            'fields'            => array('priceoption','price'),
            'showColumns'       => true,
        ),

        'global_operations' => array
        (
            'all' => array
            (
                'label'         => $GLOBALS['TL_LANG']['MSC']['all'],
                'href'          => 'act=select',
                'class'         => 'header_edit_all',
                'attributes'    => 'onclick="Backend.getScrollOffSet()" accesskey="e"'
            )
        ),

        'operations' => array
        (
            'edit' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_object_prices']['edit'],
                'href'          => 'act=edit',
                'icon'          => 'edit.gif',
            ),
            'copy' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_object_prices']['copy'],
                'href'          => 'act=copy',
                'icon'          => 'copy.gif',
            ),
            'delete' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_object_prices']['delete'],
                'href'          => 'act=delete',
                'icon'          => 'delete.gif',
                'attributes'    => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false;Backend.getScrollOffset()"',
            ),
            'show' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_object_prices']['show'],
                'href'          => 'act=show',
                'icon'          => 'show.gif',
            ),
            'toggle' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object_prices']['TOGGLE'],
                'icon'                => 'visible.gif',
                'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'     => array('tl_c4g_reservation', 'toggleIcon')
            )
        )
    ),

    //Palettes
    'palettes' => array
    (
        'default'   =>  'price, priceoption, reservation_object, reservation_event, published;',
    ),


    //Fields
    'fields' => array
    (
        'id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object_prices']['id'],
            'sql'               => "int(10) unsigned NOT NULL auto_increment"
        ),

        'tstamp' => array
        (
            'sql'               => "int(10) unsigned NOT NULL default '0'"
        ),

        'price' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object_prices']['price'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('rpxp'=>'digit','mandatory'=>true, 'maxlength'=>6, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'w50 clr'),
            'sql'                     => "double(5,2) NOT NULL default '0.00'"
        ),

        'priceoption' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object_prices']['priceoption'],
            'exclude'                 => true,
            'inputType'               => 'radio',
            'options'                 => array('pMin','pHour','pDay','pEvent'),
            'default'                 => '',
            'reference'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object_prices'],
            'eval'                    => array('mandatory'=>true, 'tl_class' => 'long clr'),
            'sql'                     => "varchar(50) NOT NULL default ''"
        ),

        'reservation_object' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object_prices']['reservation_object'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'select',
            'foreignKey'              => 'tl_c4g_reservation_object.caption',
            'eval'                    => array('chosen'=>true,'mandatory'=>false,'multiple'=>true, 'disabled' => false, 'tl_class'=>'long clr'),
            'sql'                     => "blob NULL'"
        ),

        'reservation_event' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_object_prices']['reservation_event'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'select',
            'foreignKey'              => 'tl_calendar_events.title',
            'eval'                    => array('chosen'=>true,'mandatory'=>false,'multiple'=>true, 'false' => true, 'tl_class'=>'long clr'),
            'sql'                     => "blob NULL'"
        ),

        'published' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['published'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 clr','mandatory'=>false, 'multiple'=>false,'alwaysSave'=>true),
            'sql'                     => "int(1) unsigned NULL default 1"
        ),
    )
);

/**
 * Class tl_c4g_reservation_object_prices
 */
class tl_c4g_reservation_object_prices extends Backend
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

        $href .= '&amp;id=' . $this->Input->get('id') . '&amp;tid=' . $row['id'] . '&amp;state=' . $row[''];

        if (!$row['published']) {
            $icon = 'invisible.gif';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $this->generateImage($icon, $label) . '</a> ';
    }
    public function toggleVisibility($intId, $blnPublished)
    {
        // Check permissions to publish
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_c4g_reservation_object_prices::published', 'alexf')) {
            $this->log('Not enough permissions to show/hide record ID "' . $intId . '"', 'tl_c4g_reservation_object_prices toggleVisibility', TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        $this->createInitialVersion('tl_c4g_reservation_object_prices', $intId);

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_c4g_reservation_object_prices']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_c4g_reservation_object_prices']['fields']['published']['save_callback'] as $callback) {
                $this->import($callback[0]);
                $blnPublished = $this->$callback[0]->$callback[1]($blnPublished, $this);
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE tl_c4g_reservation_type SET tstamp=" . time() . ", published='" . ($blnPublished ? '0' : '1') . "' WHERE id=?")
            ->execute($intId);
        $this->createNewVersion('tl_c4g_reservation_object_prices', $intId);
    }

}