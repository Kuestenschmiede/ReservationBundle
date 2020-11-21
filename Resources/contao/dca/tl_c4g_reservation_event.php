<?php
/**
 * This file is part of con4gis,
 * the gis-kit for Contao CMS.
 *
 * @package    con4gis
 * @version    7
 * @author     con4gis contributors (see "authors.txt")
 * @license    LGPL-3.0-or-later
 * @copyright  Küstenschmiede GmbH Software & Design
 * @link       https://www.con4gis.org
 */

/**
 * Table tl_module
 */

use Contao\Image;
use Contao\StringUtil;

$GLOBALS['TL_DCA']['tl_c4g_reservation_event'] = array
(
    //config
    'config' => array
    (
        'dataContainer'     => 'Table',
        'enableVersioning'  => 'true',
        'ptable'            => 'tl_calendar_events',
        'onload_callback'   => [['tl_c4g_reservation_event', 'setParent']],
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
            'fields'            => array('number','pid','reservationType'),
            'panelLayout'       => 'filter;sort,search,limit',
        ),

        'label' => array
        (
            'fields'            => array('number','pid:tl_calendar_events.title','reservationType:tl_c4g_reservation_type.caption'),
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
            ),
            'back' => [
                //'href'                => 'key=back',
                'class'               => 'header_back',
                //'button_callback'     => ['\con4gis\CoreBundle\Classes\Helper\DcaHelper', 'back'],
                'href'                => $this->Input->get('pid') ? 'do=calendar&table=tl_calendar_events&id='.$this->Input->get('pid') : 'do=calendar&table=tl_calendar_events&id='.$this->Input->get('id'),
                //'button_callback'     => ['\con4gis\CoreBundle\Classes\Helper\DcaHelper', 'back'],
                'icon'                => 'back.svg',
                'label'               => &$GLOBALS['TL_LANG']['MSC']['backBT'],
            ]
        ),

        'operations' => array
        (
            'edit' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_event']['edit'],
                'href'          => 'act=edit',
                'icon'          => 'edit.gif',
            ),
            'copy' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_event']['copy'],
                'href'          => 'act=copy',
                'icon'          => 'copy.gif',
            ),
            'delete' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_event']['delete'],
                'href'          => 'act=delete',
                'icon'          => 'delete.gif',
                'attributes'    => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false;Backend.getScrollOffset()"',
            ),
            'show' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_event']['show'],
                'href'          => 'act=show',
                'icon'          => 'show.gif',
            )
        )
    ),

    //Palettes
    'palettes' => array
    (
        'default'   =>  '{event_legend}, pid, number, speaker, topic, targetAudience; {reservation_legend}, reservationType, minParticipants, maxParticipants;',
    ),


    //Fields
    'fields' => array
    (
        'id' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['id'],
            'sql'               => "int(10) unsigned NOT NULL auto_increment",
            'sorting'           => true,
        ),
        'pid' => array
        (
            'inputType'         => 'select',
            //'foreignKey'        => 'tl_calendar_events.title',
            'options_callback'  => ['tl_c4g_reservation_event', 'getActEvent'],
            'eval'              => array('mandatory' => false, 'disabled' => true, 'tl_class' => 'long clr'),
            'sql'               => "int(10) unsigned NOT NULL default 0"/*,
            'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),*/
        ),
        'tstamp' => array
        (
            'sql'               => "int(10) unsigned NOT NULL default 0"
        ),

        'uuid' => array
        (
            'label'             => array('uuid','uuid'),
            'exclude'           => true,
            'inputType'         => 'text',
            'search'            => false,
            'eval'              => array('doNotCopy'=>true, 'maxlength'=>128),
            'save_callback'     => array(array('tl_c4g_reservation_event','generateUuid')),
            'sql'               => "varchar(128) COLLATE utf8_bin NOT NULL default ''"
        ),

        'number' => array(
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_event']['number'],
            'default'                 => '',
            'sorting'                 => true,
            'flag'                    => 1,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'tl_class'=>'w50 clr'),
            'sql'                     => "varchar(128) NOT NULL default ''"
        ),

        'reservationType' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['reservationType'],
            'inputType'         => 'select',
            'foreignKey'        => 'tl_c4g_reservation_type.caption',
            'eval'              => array('mandatory' => true, 'tl_class' => 'long clr'),
            'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),
            'sql'               => "int(10) unsigned NOT NULL default 0"
        ),

        'minParticipants' => array(
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_event']['minParticipants'],
            'sorting'                 => false,
            'flag'                    => 1,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                     => "smallint(3) NOT NULL default 1"
        ),

        'maxParticipants' => array(
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_event']['maxParticipants'],
            'sorting'                 => false,
            'flag'                    => 1,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'rgxp'=>'digit', 'tl_class'=>'w50'),
            'sql'                     => "smallint(3) NOT NULL default 0"
        ),

        'speaker' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['speaker'],
            'inputType'         => 'checkbox',
            'foreignKey'        => 'tl_c4g_reservation_event_speaker.lastname', //ToDo
            'eval'              => array('mandatory' => false, 'tl_class' => 'long clr', 'multiple' => true, 'chosen' => true),
            'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),
            'sql'               => "blob NULL"
        ),

        'topic' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['topic'],
            'inputType'         => 'checkbox',
            'foreignKey'        => 'tl_c4g_reservation_event_topic.topic',
            'eval'              => array('mandatory' => false, 'tl_class' => 'long clr', 'multiple' => true, 'chosen' => true),
            'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),
            'sql'               => "blob NULL"
        ),

        'targetAudience' => array
        (
            'label'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_event']['targetAudience'],
            'inputType'         => 'checkbox',
            'foreignKey'        => 'tl_c4g_reservation_event_audience.targetAudience',
            'eval'              => array('mandatory' => false, 'tl_class' => 'long clr', 'multiple' => true, 'chosen' => true),
            'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),
            'sql'               => "blob NULL"
        ),
        'state' => array(
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_event']['state'],
            'exclude'           => true,
            'filter'            => true,
            'options'           => [0,1,2,3],
            'inputType'         => 'select',
            'eval'              => array('tl_class'=>'w50', 'feEditable'=>true, 'feViewable'=>true,),
            'sql'               => "char(1) NOT NULL default '0'"
        )
    )
);


/**
 * Class tl_c4g_reservation
 */
class tl_c4g_reservation_event extends Backend
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

//    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
//    {
//        $this->import('BackendUser', 'User');
//
//        if (strlen($this->Input->get('tid')))
//        {
//            $this->toggleVisibility($this->Input->get('tid'), ($this->Input->get('state') == 1));
//            $this->redirect($this->getReferer());
//        }
//
//        $href .= '&amp;id='.$this->Input->get('id').'&amp;tid='.$row['id'].'&amp;state='.$row[''];
//
//        if ($row['cancellation'])
//        {
//            $icon = 'invisible.gif';
//        }
//
//        return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
//
//    }
//
//    public function toggleVisibility($intId, $blnCancellation)
//    {
//
//        $this->createInitialVersion('tl_c4g_reservation_event', $intId);
//
//        // Trigger the save_callback
//        if (is_array($GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['cancellation']['save_callback']))
//        {
//            foreach ($GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['cancellation']['save_callback'] as $callback)
//            {
//                $this->import($callback[0]);
//                $blnCancellation = $this->$callback[0]->$callback[1](!$blnCancellation, $this);
//            }
//        }
//
//        // Update the database
//        $this->Database->prepare("UPDATE tl_c4g_reservation_event SET tstamp=". time() .", cancellation='" . ($blnCancellation ? '0' : '1') . "' WHERE id=?")
//            ->execute($intId);
//        $this->createNewVersion('tl_c4g_reservation_event', $intId);
//    }

    /**
     * @param \Contao\DataContainer $dc
     */
    public function setParent(Contao\DataContainer $dc)
    {
        \Contao\Message::addInfo('Hier kommt ein Infotext!!!'); //ToDO

        if (!$db->id) {
            return;
        }

        $dc->pid = $dc->id;
        $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['pid']['default'] = $dc->id;

        return $dc;
    }

    /**
     * Return all themes as array
     * @return array
     */
    public function getActEvent(DataContainer $dc)
    {
        $return = [];

        $do = Input::get('do');
        $id = Input::get('id');

        if ($do && $id) {
            $events = $this->Database->prepare("SELECT id,title FROM tl_calendar_events WHERE id=".$id)
                ->execute();
        } else {
            $events = $this->Database->prepare("SELECT id,title FROM tl_calendar_events")
                ->execute();
        }

        while ($events->next()) {
            $return[$events->id] = $events->title;
        }

        return $return;
    }
}
