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

/**
 * Table tl_module
 */

$GLOBALS['TL_DCA']['tl_c4g_reservation_participants'] = array
(
    //config
    'config' => array
    (
        'dataContainer'     => 'Table',
        'enableVersioning'  => true,
        'ptable'            => 'tl_c4g_reservation',
        'sql'               => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'pid' => 'index'
            )
        )
    ),


    //List
    'list' => array
    (
        'sorting' => array
        (
            'mode'              => 2,
            'fields'            => array('id','title','lastname','firstname','email'),
            'panelLayout'       => 'filter;sort,search,limit',
        ),

        'label' => array
        (
            'fields'            => array('id','title','lastname','firstname','email','cancellation'),
            'label_callback'    => array('tl_c4g_reservation_participants', 'listFields'),
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
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_participants']['edit'],
                'href'          => 'act=edit',
                'icon'          => 'edit.gif',
            ),
            'copy' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_participants']['copy'],
                'href'          => 'act=copy',
                'icon'          => 'copy.gif',
            ),
            'delete' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_participants']['delete'],
                'href'          => 'act=delete',
                'icon'          => 'delete.gif',
                'attributes'    => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\')) return false;Backend.getScrollOffset()"',
            ),
            'show' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_participants']['show'],
                'href'          => 'act=show',
                'icon'          => 'show.gif',
            ),
            'toggle' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation_participants']['TOGGLE'],
                'icon'                => 'visible.gif',
                'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'     => array('tl_c4g_reservation_participants', 'toggleIcon')
            )
        )
    ),

    //Palettes
    'palettes' => array
    (
        'default'   =>  '{participants_legend}, title, lastname, firstname, email, comment, participant_params, cancellation;',
    ),


    //Fields
    'fields' => array
    (
        'id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_participants']['id'],
            'sql'               => "int(10) unsigned NOT NULL auto_increment",
            'sorting'           => true,
        ),
        'pid' => array
        (
            'sql'               => "int(10) unsigned NOT NULL default '0'"
        ),
        'tstamp' => array
        (
            'sql'               => "int(10) unsigned NOT NULL default 0"
        ),

//        'uuid' => array
//        (
//            'label'             => array('uuid','uuid'),
//            'exclude'           => true,
//            'inputType'         => 'text',
//            'search'            => false,
//            'eval'              => array('doNotCopy'=>true, 'maxlength'=>128),
//            'save_callback'     => array(array('tl_c4g_reservation_participants','generateUuid')),
//            'sql'               => "varchar(128) COLLATE utf8_bin NOT NULL default ''"
//        ),

        'title' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_participants']['title'],
            'exclude'                 => true,
            'search'                  => true,
            'sorting'                 => true,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'clr'),
            'sql'                     => "varchar(50) NOT NULL default ''"
        ),

        'lastname' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_participants']['lastname'],
            'exclude'                 => true,
            'search'                  => true,
            'sorting'                 => true,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'clr'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'firstname' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_participants']['firstname'],
            'exclude'                 => true,
            'search'                  => false,
            'sorting'                 => false,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'email' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_participants']['email'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'rgxp'=>'email', 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'phone' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_participants']['phone'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>64, 'rgxp'=>'phone', 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'long'),
            'sql'                     => "varchar(64) NOT NULL default ''"
        ),
       
        'address' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_participants']['address'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'postal' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_participants']['postal'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>32, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => "varchar(32) NOT NULL default ''"

        ),

        'city' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_participants']['city'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"

        ),

        'comment' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_participants']['comment'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'textarea',
            'default'                 => '',
            'eval'                    => array('mandatory'=>false, 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'long'),
            'sql'                     => "text NULL"
        ),

        'participant_params' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_participants']['reservation_participant_option'],
            'exclude'           => true,
            'inputType'         => 'select',
            'foreignKey'        => 'tl_c4g_reservation_params.caption',
            'eval'              => array('chosen'=>true,'mandatory'=>false,'multiple'=>true, 'tl_class'=>'long clr','alwaysSave'=> true),
            'sql'               => "blob NULL",
        ),

        'cancellation' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_participants']['cancellation'],
            'exclude'           => true,
            'filter'            => true,
            'inputType'         => 'checkbox',
            'eval'              => array('tl_class'=>'w50'),
            'sql'               => "char(1) NOT NULL default ''"
        )
    )
);


/**
 * Class tl_c4g_reservation_participants
 */
class tl_c4g_reservation_participants extends Backend
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
        }
        else {
            return $varValue;
        }
    }

    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $this->import('BackendUser', 'User');

        if (strlen($this->Input->get('tid')))
        {
            $this->toggleVisibility($this->Input->get('tid'), ($this->Input->get('state') == 1));
            $this->redirect($this->getReferer());
        }

        $href .= '&amp;id='.$this->Input->get('id').'&amp;tid='.$row['id'].'&amp;state='.$row[''];

        if ($row['cancellation'])
        {
            $icon = 'invisible.gif';
        }

        return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';

    }

    public function toggleVisibility($intId, $blnCancellation)
    {

        $this->createInitialVersion('tl_c4g_reservation_participants', $intId);

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_c4g_reservation_participants']['fields']['cancellation']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_c4g_reservation_participants']['fields']['cancellation']['save_callback'] as $callback)
            {
                $this->import($callback[0]);
                $blnCancellation = $this->$callback[0]->$callback[1](!$blnCancellation, $this);
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE tl_c4g_reservation_participants SET tstamp=". time() .", cancellation='" . ($blnCancellation ? '0' : '1') . "' WHERE `id`=?")
            ->execute($intId);
        $this->createNewVersion('tl_c4g_reservation_participants', $intId);
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
