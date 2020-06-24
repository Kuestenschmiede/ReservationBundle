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

$GLOBALS['TL_DCA']['tl_c4g_reservation_type'] = array
(
    //config
    'config' => array
    (
        'dataContainer'     => 'Table',
        'enableVersioning'  => 'true',
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
            'fields'            => array('caption','periodType','objectCount'),
            'panelLayout'       => 'filter;sort,search,limit',
            'headerFields'      => array('caption','periodType','objectCount'),
        ),

        'label' => array
        (
            'fields'            => array('caption','periodType','objectCount'),
            //'label_callback'    => array('tl_c4g_reservation_type', 'listTypes'),
            'showColumns'       => true
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
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['edit'],
                'href'          => 'act=edit',
                'icon'          => 'edit.gif',
            ),
            'copy' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['copy'],
                'href'          => 'act=copy',
                'icon'          => 'copy.gif',
            ),
            'delete' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['delete'],
                'href'          => 'act=delete',
                'icon'          => 'delete.gif',
                'attributes'    => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false;Backend.getScrollOffset()"',
            ),
            'show' => array
            (
                'label'         => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['show'],
                'href'          => 'act=show',
                'icon'          => 'show.gif',
            ),
             'toggle' => array
             (
                'label'               => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['TOGGLE'],
                'icon'                => 'visible.gif',
                'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'     => array('tl_c4g_reservation_type', 'toggleIcon')
            )
        )
    ),

    //Palettes
    'palettes' => array
    (
        'default'   =>  '{type_legend},caption,description,options,business_name,business_phone,business_email,business_street,business_postal,business_city,periodType,objectCount,additional_params,published;{expert_legend:hide},auto_del;',
       '__selector__' => array('periodType','auto_del')
    ),

    //Subpalettes
   'subpalettes' => array(
        'periodType_md'                 =>'beginDate, endDate;',
        'periodType_event'              =>'{event_legend},event_dayBegin, event_timeBegin, event_dayEnd, event_timeEnd;',
        'periodType_contao_event'       =>'{event_legend},event_id;',
        'auto_del_daily'                =>'del_time;',

    ),


    //Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'               => "int(10) unsigned NOT NULL auto_increment"
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
            'search'            => true,
            'eval'              => array('doNotCopy'=>true, 'maxlength'=>128),
            'save_callback'     => array(array('tl_c4g_reservation_type','generateUuid')),
            'sql'               => "varchar(128) COLLATE utf8_bin NOT NULL default ''"
        ),

        'caption' => array
        (
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['caption'],
            'sorting'           => true,
            'flag'              => 1,
            'search'            => true,
            'inputType'         => 'text',
            'eval'              => array('mandatory' => true, 'tl_class' => 'long', 'maxlength' => 255),
            'sql'               => "varchar(255) NOT NULL default ''"
        ),


        'event_caption' => array
        (
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event_caption'],
            'sorting'           => true,
            'flag'              => 1,
            'search'            => true,
            'inputType'         => 'text',
            'eval'              => array('mandatory' => true, 'tl_class' => 'long', 'maxlength' => 255),
            'sql'               => "varchar(255) NOT NULL default ''"
        ),

        'options' => array
        (
            'label'			=> &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['options'],
            'exclude' 		=> true,
            'inputType'     => 'multiColumnWizard',
            'eval' 			=> array
            (
                'columnFields' => array
                (
                    'caption' => array
                    (
                        'label'                 => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['option'],
                        'exclude'               => true,
                        'inputType'             => 'text',
                        'eval' 			        => array('tl_class'=>'w50')
                    ),
                    'language' => array
                    (
                        'label'                 => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['language'],
                        'exclude'               => true,
                        'inputType'             => 'select',
                        'options'               => ['de' => 'Deutsch', 'en' => 'Englisch'],
                        'eval'                  => array('chosen' => false, 'style'=>'width: 200px')
                    )
                )
            ),

            'sql' => "blob NULL "
        ),

       'business_name' => array
       (
           'label'             =>$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['business_name'],
           'exclude'           => true,
           'inputType'         => 'text',
           'eval'              =>array('mandatory'=>false, 'tl_class'=>'w50 clr'),
           'sql'               =>"varchar(255) NOT NULL default ''"
       ),
        'business_phone' => array
        (
            'label'             =>$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['business_phone'],
            'exclude'           => true,
            'inputType'         => 'text',
            'eval'              =>array('rgxp'=>'digit','mandatory'=>false, 'tl_class'=>'w50 clr '),
            'sql'               =>"varchar(255) NOT NULL default ''"
        ),
        'business_email' => array
        (
            'label'             =>$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['business_email'],
            'exclude'           => true,
            'inputType'         => 'text',
            'eval'              =>array('rgxp'=>'email','mandatory'=>false, 'tl_class'=>'w50 clr'),
            'sql'               =>"varchar(255) NOT NULL default ''"
        ),
        'business_street' => array
        (
            'label'             =>$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['business_street'],
            'exclude'           => true,
            'inputType'         => 'text',
            'eval'              =>array('mandatory'=>false, 'tl_class'=>'w50 clr'),
            'sql'               =>"varchar(255) NOT NULL default ''"
        ),
        'business_postal' => array
        (
            'label'             =>$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['business_postal'],
            'exclude'           => true,
            'inputType'         => 'text',
            'eval'              =>array('rgxp'=>'digit','mandatory'=>false, 'tl_class'=>'w50 clr'),
            'sql'               =>"varchar(255) NOT NULL default ''"
        ),
        'business_city' => array
        (
            'label'             =>$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['business_city'],
            'exclude'           => true,
            'inputType'         => 'text',
            'eval'              =>array('mandatory'=>false, 'tl_class'=>'w50 clr'),
            'sql'               =>"varchar(255) NOT NULL default ''"
        ),

       'periodType' => array
       (
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['periodType'],
            'exclude'           => true,
            'inputType'         => 'select',
            'options'           => array('minute',/*'minute_period'*/'hour',/*'hour_period','md','event','contao_event'*/),
            'reference'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type'],
            'eval'              => array('tl_class'=>'w50', 'feEditable'=>true, 'feViewable'=>true, 'mandatory'=>true, 'submitOnChange' => true),
            'sql'               => "char(25) NOT NULL default ''"
       ),

        'objectCount' => array
        (
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['objectCount'],
            'inputType'         => 'text',
            'eval'              => array('rgxp'=>'digit', 'mandatory'=>false, 'tl_class'=>'w50 clr'),
            'sql'               => "smallint(5) unsigned NULL"
        ),

        'auto_del' => array
        (
            'label'                 => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['auto_del'],
            'exclude'               => true,
            'inputType'             => 'select',
            'reference'             => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type'],
            'default'               =>'no_delete',
            'options'               => array('daily','no_delete'),
            'eval'                  => array('tl_class'=>'w50', 'feEditable'=>true, 'feViewable'=>true, 'mandatory'=>false, 'submitOnChange' => true),
            'sql'                   => "char(25) default ''"
        ),
        'del_time' => array
        (
            'label'                    =>&$GLOBALS['TL_LANG']['tl_c4g_reservations_type']['del_time'],
            'exclude'                  =>true,
            'inputType'                =>'text',
            'default'                  =>'30',
            'eval'                     =>array('rgxp'=>'digit', 'mandatory'=>false, 'tl_class'=>'w50 wizard'),
            'sql'                      =>"smallint(5) unsigned NULL"

        ),

        'beginDate' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['beginDate'],
            'default'                 => time(),
            'filter'                  => true,
            'sorting'                 => true,
            'search'                  => false,
            'exclude'                 => true,
            'inputType'               => 'text',
            'flag'                    => 6,
            'eval'                    => array('rgxp'=>'date', 'mandatory'=>true, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                     => "int(10) unsigned NULL default 0"
        ),

        'endDate' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['endDate'],
            'default'                 => time(),
            'filter'                  => true,
            'sorting'                 => true,
            'search'                  => false,
            'exclude'                 => true,
            'inputType'               => 'text',
            'flag'                    => 6,
            'eval'                    => array('rgxp'=>'date', 'mandatory'=>true, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                     => "int(10) unsigned NULL default 0"
        ),

        'event_id' => array
        (
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event_id'],
            'inputType'         => 'select',
            'foreignKey'        => 'tl_calendar_events.title',
            'eval'              => array('mandatory' => false, 'tl_class' => 'long'),
            'sql'               => "int(10) unsigned NOT NULL default 0",
            'relation'          => array('type' => 'hasOne', 'load' => 'lazy'),
        ),

        'event_dayBegin' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event_dayBegin'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'date', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                     => "int(10) unsigned NULL default 0"
        ),

        'event_timeBegin' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event_timeBegin'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'tl_class'=>'w50 wizard', 'datepicker'=>true),
            'sql'                     => "int(10) unsigned NULL default 0"
        ),

        'event_dayEnd' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event_dayEnd'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'date', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                     => "int(10) unsigned NULL default 0"
        ),

        'event_timeEnd' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['event_timeEnd'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'tl_class'=>'w50 wizard', 'datepicker'=>true),
            'sql'                     => "int(10) unsigned NULL default 0"
        ),

        'additional_params' => array
            (
            'label'                   => $GLOBALS['TL_LANG']['ts_c4g_reservation_type']['additional_params'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'foreignKey'              => 'tl_c4g_reservation_params.caption',
            'eval'                    => array('mandatory'=>false,'multiple'=>true, 'tl_class'=>'long clr','alwaysSave'=> true),
            'sql'                     =>"blob NULL "
            ),

        'description' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['description'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => false,
            'sorting'                 => false,
            'inputType'               => 'textarea',
            'default'                 => '',
            'eval'                    => array('mandatory'=>false, 'rte'=>'tinyMCE', 'feEditable'=>true, 'feViewable'=>true, 'tl_class'=>'long'),
            'sql'                     => "text NULL"
        ),

        'published' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_type']['published'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('mandatory'=>false, 'multiple'=>false,'alwaysSave'=>true),
            'sql'                     => "int(1) unsigned NULL default 1"
        ),

   /*     'minute_interval' => array
        (
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['minute_interval'],
            'inputType'         => 'text',
            'eval'              => array('rgxp'=>'digit', 'maxval'=> 60, 'mandatory'=>'true', 'tl_class'=>'long clr'),
            'sql'               => "smallint(5) unsigned NOT NULL default '0'"
        ),

        'hour_interval' => array
        (
            'label'             => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['hour_interval'],
            'inputType'         => 'text',
            'eval'              => array('rgxp'=>'digit', 'maxval'=> 24, 'mandatory'=>'true', 'tl_class'=>'long clr'),
            'sql'               => "smallint(5) unsigned NOT NULL default '0'"
        ),
*/

/*        'oh_monday' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['oh_monday'],
            'default'                 => time(),
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('columnFields'	=> array
            (
                'time_begin' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['time_begin'],
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                ),
                'time_end' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['time_end'],
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                )
            )),
            'sql'                     => "blob NULL"
        ),


        'oh_tuesday' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['oh_tuesday'],
            'default'                 => time(),
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('columnFields'	=> array
            (
                'time_begin' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['time_begin'],
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                ),
                'time_end' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['time_end'],
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                )
            )),
            'sql'                     => "blob NULL"
        ),


        'oh_wednesday' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['oh_wednesday'],
            'default'                 => time(),
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('columnFields'	=> array
            (
                'time_begin' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['time_begin'],
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                ),
                'time_end' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['time_end'],
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                )
            )),
            'sql'                     => "blob NULL"
        ),


        'oh_thursday' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['oh_thursday'],
            'default'                 => time(),
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('columnFields'	=> array
            (
                'time_begin' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['time_begin'],
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                ),
                'time_end' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['time_end'],
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                )
            )),
            'sql'                     => "blob NULL"
        ),


        'oh_friday' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['oh_friday'],
            'default'                 => time(),
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('columnFields'	=> array
            (
                'time_begin' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['time_begin'],
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                ),
                'time_end' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['time_end'],
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                )
            )),
            'sql'                     => "blob NULL"
        ),


        'oh_saturday' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['oh_saturday'],
            'default'                 => time(),
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('columnFields'	=> array
            (
                'time_begin' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['time_begin'],
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                ),
                'time_end' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['time_end'],
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                )
            )),
            'sql'                     => "blob NULL"
        ),


        'oh_sunday' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['oh_sunday'],
            'default'                 => time(),
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array('columnFields'	=> array
            (
                'time_begin' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['time_begin'],
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                ),
                'time_end' => array
                (
                    'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['time_end'],
                    'exclude'                 => true,
                    'inputType'               => 'text',
                    'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
                    'sql'                     => "int(10) unsigned NULL"
                )
            )),
            'sql'                     => "blob NULL"
        ),

*/
/*      'dayBegin' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['dayBegin'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'date', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                     => "int(10) unsigned NULL"
        ),

        'timeBegin' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['timeBegin'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'tl_class'=>'w50 wizard','datepicker'=>true),
            'sql'                     => "int(10) unsigned NULL"
        ),

        'dayEnd' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['dayEnd'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'date', 'mandatory'=>false, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                     => "int(10) unsigned NULL"
        ),

        'timeEnd' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_c4g_reservation_type']['timeEnd'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'time', 'mandatory'=>false, 'doNotCopy'=>true, 'tl_class'=>'w50 wizard','datepicker'=>true),
            'sql'                     => "int(10) unsigned NULL"
        ),
*/
    )

);


/**
 * Class tl_c4g_reservation
 */
class tl_c4g_reservation_type extends Backend
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
            return \con4gis\ProjectsBundle\Classes\Common\C4GBrickCommon::getGUID();
        }
        else {
            return $varValue;
        }
    }

    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $this->import('BackendUser', 'User');

        if (strlen($this->Input->get('tid'))) {
            $this->toggleVisibility($this->Input->get('tid'), ($this->Input->get('state') == '0'));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_c4g_reservation_type::published', 'alexf')) {
            return '';
        }

        $href .= '&amp;id=' . $this->Input->get('id') . '&amp;tid=' . $row['id'] . '&amp;state=' . $row[''];

        if (!$row['published']) {
            $icon = 'invisible.gif';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $this->generateImage($icon, $label) . '</a> ';
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
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_c4g_reservation_type::published', 'alexf')) {
            $this->log('Not enough permissions to show/hide record ID "' . $intId . '"', 'tl_c4g_reservation_type toggleVisibility', TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        $this->createInitialVersion('tl_c4g_reservation_type', $intId);

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_c4g_reservation_type']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_c4g_reservation_type']['fields']['published']['save_callback'] as $callback) {
                $this->import($callback[0]);
                $blnPublished = $this->$callback[0]->$callback[1]($blnPublished, $this);
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE tl_c4g_reservation_type SET tstamp=" . time() . ", published='" . ($blnPublished ? '0' : '1') . "' WHERE id=?")
            ->execute($intId);
        $this->createNewVersion('tl_c4g_reservation_type', $intId);
    }

//    public function listTypes($arrRow)
//    {
//        $period_ids = unserialize($arrRow['periodType']);
//
//        $reservationTypes = '';
//        foreach ($type_ids as $type_id) {
//            $reservation_type = \con4gis\ReservationBundle\Resources\contao\models\C4gReservationTypeModel::findByPk($type_id);
//            if ($reservation_type) {
//                if ($reservationTypes == '') {
//                    $reservationTypes .= $reservation_type->caption;
//                } else {
//                    $reservationTypes .= ','.$reservation_type->caption;
//                }
//            }
//        }
//
//        $arrRow['viewableTypes'] = $reservationTypes;
//
//        $result = [
//            $arrRow['caption'],
//            $arrRow['quantity'],
//            $arrRow['desiredCapacityMin'],
//            $arrRow['desiredCapacityMax'],
//            $arrRow['viewableTypes']
//        ];
//        return $result;
//    }

}
