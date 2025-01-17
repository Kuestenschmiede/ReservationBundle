<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 10
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2025, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

use Contao\DC_Table;
use Contao\BackendUser;
use Contao\Input;
use Contao\StringUtil;
use Contao\Image;
use Contao\System;
use Contao\Versions;
use Contao\DataContainer;

$GLOBALS['TL_DCA']['tl_c4g_reservation_params'] = array
(
    //config
    'config' => array
    (
        'dataContainer'     => DC_Table::class,
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
            'fields'            => array('caption', 'price', 'taxOptions'),
            'panelLayout'       => 'filter;sort,search,limit',
//            'headerFields'      => array('lastname','firstname'),
        ),

        'label' => array
        (
            'fields'            => array('caption', 'price', 'taxOptions'),
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
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_params']['edit'],
                'href'          => 'act=edit',
                'icon'          => 'edit.gif',
            ),
            'copy' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_params']['copy'],
                'href'          => 'act=copy',
                'icon'          => 'copy.gif',
            ),
            'delete' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_params']['delete'],
                'href'          => 'act=delete',
                'icon'          => 'delete.gif',
                'attributes'    => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\')) return false;Backend.getScrollOffset()"',
            ),
            'show' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_params']['show'],
                'href'          => 'act=show',
                'icon'          => 'show.gif',
            ),
            'toggle' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation_params']['TOGGLE'],
                'icon'                => 'visible.gif',
                'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'     => array('tl_c4g_reservation_params', 'toggleIcon')
            )
        )
    ),

    //Palettes
    'palettes' => array
    (
        'default'   =>  'caption, language, feCaption, price, taxOptions, published;'
    ),


    //Fields
    'fields' => array
    (
        'id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_params']['id'],
            'sql'               => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp' => array
        (
            'sql'               => "int(10) unsigned NOT NULL default '0'"
        ),
        'caption' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_params']['caption'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'w50'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')

        ),
        'feCaption' => array
        (
            'label'			=> &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['feCaption'],
            'exclude' 		=> true,
            'inputType'     => 'multiColumnWizard',
            'eval' 			=> array
            (
                'columnFields' => array
                (
                    'caption' => array
                    (
                        'label'                 => &$GLOBALS['TL_LANG']['tl_c4g_reservation_params']['option'],
                        'exclude'               => true,
                        'inputType'             => 'text',
                        'eval' 			        => array('tl_class'=>'w50')
                    ),
                    'language' => array
                    (
                        'label'                 => &$GLOBALS['TL_LANG']['tl_c4g_reservation_params']['language'],
                        'exclude'               => true,
                        'inputType'             => 'select',
                        'options'               => ['de' => 'Deutsch', 'en' => 'Englisch'],
                        'eval'                  => array('chosen' => false, 'style'=>'width: 200px')
                    )
                ),
                'tl_class'=>'clr',
            ),

            'sql' => "blob NULL"
        ),
        'taxOptions' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_params']['taxOptions'],
            'exclude'                 => true,
            'inputType'               => 'radio',
            'default'                 => 'tNone',
            'options'                 => array( 'tNone', 'tStandard', 'tReduced'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation_params']['references'],
            'eval'                    => array('submitOnChange' => true, 'tl_class' => 'long clr', 'fieldType'=>'radio', 'tl_class'=>'w50 clr'),
            'sql'                     => array('type' => 'string', 'length' => 50, 'default' => 'tNone')
        ),
        'price' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_params']['price'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'default'                 => '0.00',
            'eval'                    => array('rgxp'=>'digit','mandatory'=>true, 'maxlength'=>10, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'w50 clr'),
            'sql'                     => "double(7,2) unsigned default '0'"

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
 * Class tl_c4G_reservation_params
 */
// class tl_c4g_reservation_params extends Backend
class tl_c4g_reservation_params extends \Contao\Backend
{
    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import(Contao\BackendUser::class, 'User');
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
        $this->import(Contao\BackendUser::class, 'User');

        if (strlen(Input::get('tid'))) {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') == ''));
            $this->redirect($this->getReferer());
        }

        $href .= '&amp;id=' . Input::get('id') . '&amp;tid=' . $row['id'] . '&amp;state='.($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.gif';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
    }
    public function toggleVisibility($intId, $blnPublished)
    {
        // Check permissions to publish
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_c4g_reservation_params::published', 'alexf')) {
            $this->log('Not enough permissions to show/hide record ID "' . $intId . '"', 'tl_c4g_reservation_params toggleVisibility', TL_ERROR);
            $this->redirect(System::getContainer()->get('router')->generate('contao_backend').'?act=error');
        }

        $objVersions = new Versions('tl_c4g_reservation_params', $intId);
		$objVersions->initialize();


        // Trigger the save_callback
        if (isset($GLOBALS['TL_DCA']['tl_c4g_reservation_params']['fields']['published']['save_callback']) && is_array($GLOBALS['TL_DCA']['tl_c4g_reservation_params']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_c4g_reservation_params']['fields']['published']['save_callback'] as $callback) {
                $this->import($callback[0]);
                $blnPublished = $this->$callback[0]->$callback[1]($blnPublished, $this);
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE tl_c4g_reservation_params SET tstamp=" . time() . ", published='" . ($blnPublished ? '0' : '1') . "' WHERE `id`=?")
            ->execute($intId);
        $objVersions = new Versions('tl_c4g_reservation_params', $intId);
		$objVersions->create();
    }
}