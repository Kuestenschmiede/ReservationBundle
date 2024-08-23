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

$GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants'] = array
(
    //config
    'config' => array
    (
        'dataContainer'     => 'Table',
        'enableVersioning'  => true,
        'ptable'            => 'tl_calendar_events',
        'onload_callback'   => [['tl_c4g_reservation_event_participants', 'setLabel']],
        'doNotCopyRecords'  => true,
        'notCreatable'      => true,
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
            'fields'            => array('id' , /* 'title', */'lastname','firstname','email'),
            'panelLayout'       => 'filter;sort,search,limit',
        ),

        'label' => array
        (
            'label_callback'    => array('tl_c4g_reservation_event_participants', 'listFields'),
            'showColumns'       => true,
        ),

        'global_operations' => array
        (
            /* 'all' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'          => 'act=select',
                'class'         => 'header_edit_all',
                'attributes'    => 'onclick="Backend.getScrollOffSet()" accesskey="e"'
            ) */
        ), 

        'operations' => array
        (
            
            // 'edit' => array
            // (
            //     'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['edit'],
            //     'href'          => 'act=edit',
            //     'icon'          => 'edit.gif',
            // ),
            // 'copy' => array
            // (
            //     'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['copy'],
            //     'href'          => 'act=copy',
            //     'icon'          => 'copy.gif',
            // ),
            // 'delete' => array
            // (
            //     'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['delete'],
            //     'href'          => 'act=delete',
            //     'icon'          => 'delete.gif',
            //     'attributes'    => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\')) return false;Backend.getScrollOffset()"',
            // ),
            // 'show' => array
            // (
            //     'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['show'],
            //     'href'          => 'act=show',
            //     'icon'          => 'show.gif',
            // ),
            // 'toggle' => array
            // (
            //     'label'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['TOGGLE'],
            //     'icon'                => 'visible.gif',
            //     'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
            //     'button_callback'     => array('tl_c4g_reservation_event_participants', 'toggleIcon')
            // )
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
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['id'],
            'sql'               => "int(10) unsigned NOT NULL auto_increment",
            'sorting'           => true,
            'search'            => false
        ),

        'pid' => array
        (
            'sql'               => "int(10) unsigned NOT NULL default '0'"
        ),

        'reservation_id' => array
        (
            'sql'               => "int(10) unsigned NOT NULL default '0'"
        ),

        'participant_id' => array
        (
            'sql'               => "int(10) unsigned NOT NULL default '0'"
        ),

        'tstamp' => array
        (
            'sql'               => "int(10) unsigned NOT NULL default 0"
        ),

        'title' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['title'],
            'exclude'                 => true,
            'search'                  => false,
            'sorting'                 => false,
            //'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'clr'),
            'sql'                     => "varchar(50) NOT NULL default ''"
        ),

        'lastname' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['lastname'],
            'exclude'                 => true,
            'search'                  => true,
            'sorting'                 => true,
            'flag'                    => 11,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'clr'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'firstname' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['firstname'],
            'exclude'                 => true,
            'search'                  => false,
            'sorting'                 => false,
            // 'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'dateOfBirth' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['dateOfBirth'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            //'flag'                    => 6,
            'eval'                    => array('rgxp'=>'date', 'mandatory'=>false, 'doNotCopy'=>false, 'datepicker'=>true, 'tl_class'=>'w50 wizard clr'),
            'sql'                     => "int(10) signed NULL"
        ),

        'email' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['email'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'rgxp'=>'email', 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'phone' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['phone'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>64, 'rgxp'=>'phone', 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'long'),
            'sql'                     => "varchar(64) NOT NULL default ''"
        ),
       
        'address' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['address'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'postal' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['postal'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>32, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => "varchar(32) NOT NULL default ''"

        ),

        'city' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['city'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"

        ),

        'comment' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['comment'],
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
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['reservation_participant_option'],
            'exclude'           => true,
            'sorting'           => false,
            'filter'            => false,
            'inputType'         => 'select',
            'foreignKey'        => 'tl_c4g_reservation_params.caption',
            'eval'              => array('chosen'=>true,'mandatory'=>false,'multiple'=>true, 'tl_class'=>'long clr','alwaysSave'=> true),
            'sql'               => "blob NULL",
        ),

        'booker' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['booked_by'],
            'exclude'                 => true,
            'search'                  => false,
            'sorting'                 => false,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array(
                                            'mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'clr'),
            'sql'                     => "varchar(50) NOT NULL default ''"
        ),

        'additional1' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['additional1'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'additional2' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['additional2'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'additional3' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation']['additional3'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'maxlength'=>254, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'long'),
            'sql'                     => "varchar(254) NOT NULL default ''"
        ),

        'cancellation' => array(
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['cancellation'],
            'exclude'           => true,
            'filter'            => false,
            'inputType'         => 'checkbox',
            'eval'              => array('tl_class'=>'w50'),
            'sql'               => "char(1) NOT NULL default ''"
        )
    )
);


/**
 * Class tl_c4g_reservation_event_participants
 */
class tl_c4g_reservation_event_participants extends Backend
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

        $href .= '&amp;id='.$this->Input->get('id').'&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

        if ($row['cancellation'])
        {
            $icon = 'invisible.gif';
        }

        return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';

    }

    public function toggleVisibility($intId, $blnCancellation)
    {

        $this->createInitialVersion('tl_c4g_reservation_event_participants', $intId);

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['cancellation']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['cancellation']['save_callback'] as $callback)
            {
                $this->import($callback[0]);
                $blnCancellation = $this->$callback[0]->$callback[1](!$blnCancellation, $this);
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE tl_c4g_reservation_event_participants SET tstamp=". time() .", cancellation='" . ($blnCancellation ? '0' : '1') . "' WHERE `id`=?")
            ->execute($intId);
        $this->createNewVersion('tl_c4g_reservation_event_participants', $intId);
    }

    public function setLabel(Contao\DataContainer $dc)
    {
        $id = intval(Input::get('id'));
        
        $formularId = Database::getInstance()->prepare("SELECT formular_id FROM tl_c4g_reservation WHERE reservation_object=?")->execute($id)->fetchAssoc();
        $formularId = intval($formularId['formular_id']);
        $fieldSelect = Database::getInstance()->prepare("SELECT fieldSelection FROM tl_c4g_reservation_settings WHERE id=?")->execute($formularId)->fetchAllAssoc(); 

        $additionaldatas = StringUtil::deserialize($fieldSelect[0]['fieldSelection']);

        //Default Labels
        $firstname = $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['firstname'][0];
        $lastname = $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['lastname'][0];
        $email = $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['email'][0];

        foreach ($additionaldatas as $rowdata)
        {
            $rowField = $rowdata['additionaldatas'];
            
            switch($rowField) {
                case "salutation": $salutation = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['salutation'][0];
                    break;
                case "firstname": $firstname = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $firstname;
                    break;
                case "lastname": $lastname = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $lastname;
                    break;
                case "email": $email = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $email;
                    break;
                case "dateOfBirth": $dateOfBirth = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['dateOfBirth'][0];
                    break;
                case "phone": $phone = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['phone'][0];
                    break;
                case "address": $address = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['address'][0];
                    break;
                case "postal": $postal = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['postal'][0];
                    break;
                case "city": $city = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['city'][0];
                    break;
                case "comment": $comment = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['comment'][0];
                    break;
                case "additional1": $additional1 = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['additional1'][0];
                    break;
                case "additional2": $additional2 = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['additional2'][0];
                    break;
                case "additional3": $additional3 = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['additional3'][0];
                    break;
            }
        }

        $showParticipantInfoFields = Database::getInstance()->prepare("SELECT showParticipantInfoFields FROM tl_c4g_reservation_event WHERE pid=?")->execute($id)->fetchAssoc(); 
        $additionalFields = StringUtil::deserialize($showParticipantInfoFields['showParticipantInfoFields']);

        $fields = [$lastname,$firstname];
        foreach ($additionalFields as $addFields) {
            switch($addFields) {
                case 'dateOfBirth': array_push($fields,$dateOfBirth);
                                    $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['city']['search'] = true; 
                                    $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['city']['sorting'] = true;
                                    break;
                case 'email':   array_push($fields,$email); 
                                break; 
                case 'phone':   array_push($fields,$phone); 
                                break;
                case 'address': array_push($fields,$address); 
                                break;
                case 'postal':  array_push($fields,$postal); 
                                break;
                case 'city':    array_push($fields,$city);
                                $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['city']['search'] = true; 
                                $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['city']['sorting'] = true;
                                break;
                case 'additional1': array_push($fields,$additional1); 
                                    $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['additional1']['search'] = true; 
                                    $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['additional1']['sorting'] = true;
                                    break;
                case 'additional2': array_push($fields,$additional2);
                                    $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['additional2']['search'] = true; 
                                    $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['additional2']['sorting'] = true;
                                    break;
                case 'additional3': array_push($fields,$additional3); 
                                    $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['additional3']['search'] = true; 
                                    $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['additional3']['sorting'] = true;
                                    break;
                case 'comment':     array_push($fields,$comment); 
                                    break;
                case 'reservation_participant_option':  array_push($fields,'participant_params');
                                                        $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['participant_params']['sorting'] = true; 
                                                        break;
                case 'booker':  array_push($fields,'booker'); 
                                break;
            }
        }
        $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['list']['label']['fields'] = $fields;
    } 

    public function listFields($arrRow)
    {
        $id = $arrRow['pid'];
        $showParticipantInfoFields = Database::getInstance()->prepare("SELECT showParticipantInfoFields FROM tl_c4g_reservation_event WHERE pid=?")->execute($id)->fetchAssoc(); 
        $additionalFields = StringUtil::deserialize($showParticipantInfoFields['showParticipantInfoFields']);

        $participantParams = StringUtil::deserialize($arrRow['participant_params']);
        if ($participantParams) {
            $i = 0;
            foreach ($participantParams as $p) {
                $params = Database::getInstance()->prepare("SELECT caption FROM `tl_c4g_reservation_params` WHERE id=?")->execute(intval($p))->fetchAssoc();
                $participant_params = $participant_params ? $participant_params . ", " . $params['caption'] : $params['caption']; 
            }
        }

        $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['lastname']['sorting'];

        $result = [$arrRow['lastname'],$arrRow['firstname']];
        foreach ($additionalFields as $addFields) {
            switch($addFields) {
                case 'dateOfBirth': $dateOfBirth = $arrRow['dateOfBirth'] ? date('d.m.Y',$arrRow['dateOfBirth']) : "";
                                    array_push($result,$dateOfBirth); break;
                case 'email':       array_push($result,$arrRow['email']); break;
                case 'phone':       array_push($result,$arrRow['phone']); break;
                case 'address':     array_push($result,$arrRow['address']); break;
                case 'postal':      array_push($result,$arrRow['postal']); break;
                case 'city':        array_push($result,$arrRow['city']); break;
                case 'comment':     array_push($result,$arrRow['comment']); break;
                case 'additional1': array_push($result,$arrRow['additional1']); break;
                case 'additional2': array_push($result,$arrRow['additional2']); break;
                case 'additional3': array_push($result,$arrRow['additional3']); break;                                 
                case 'reservation_participant_option': array_push($result,$participant_params); break;
                case 'booker': array_push($result,$arrRow['booker']); break;
            }
        }
        return $result;         
    }
}
