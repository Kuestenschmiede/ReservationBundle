<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @author con4gis contributors (see "authors.md")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

use con4gis\ReservationBundle\Classes\Callbacks\C4gReservationSuspension;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_c4g_reservation_suspension'] = array
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
            'fields'            => array('caption'),
            'panelLayout'       => 'filter;sort,search,limit'
        ),

        'label' => array
        (
            'fields'            => array('caption'),
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
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_suspension']['edit'],
                'href'          => 'act=edit',
                'icon'          => 'edit.gif',
            ),
            'copy' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_suspension']['copy'],
                'href'          => 'act=copy',
                'icon'          => 'copy.gif',
            ),
            'delete' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_suspension']['delete'],
                'href'          => 'act=delete',
                'icon'          => 'delete.gif',
                'attributes'    => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\')) return false;Backend.getScrollOffset()"',
            ),
            'show' => array
            (
                'label'         => &$GLOBALS['TL_LANG']['tl_c4g_reservation_suspension']['show'],
                'href'          => 'act=show',
                'icon'          => 'show.gif',
            )
        )
    ),

    //Palettes
    'palettes' => array
    (
        'default'   =>  '{suspension_legend}, caption; {suspension_dates_legend}, date_range_wizard, suspension_dates;'
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
            'sql'               => "int(10) unsigned NOT NULL default '0'"
        ),

        'caption' => array (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_suspension']['caption'],
            'exclude'                 => true,
            'filter'                  => false,
            'search'                  => true,
            'sorting'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'tl_class'=>'long'),
            'sql'                     => array('type' => 'string', 'length' => 254, 'default' => '')
        ),

        'date_range_wizard' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_suspension']['date_range_wizard'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'input_field_callback'    => array(C4gReservationSuspension::class, 'dateRangeWizard'),
            'eval'                    => array('tl_class' => 'w50 wizard')
        ),

        'suspension_dates' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_suspension']['suspension_dates'],
            'exclude'                 => true,
            'inputType'               => 'multiColumnWizard',
            'eval'                    => array
            (
                'tl_class'     => 'clr',
                'hideWizard'   => false,
                'doNotSaveEmpty'=> true,
                'columnFields' => array
                (
                    'date' => array
                    (
                        'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_suspension']['date'],
                        'exclude'                 => true,
                        'inputType'               => 'text',
                        'eval'                    => array('rgxp'=>'date', 'datepicker'=>true, 'mandatory'=>true, 'style'=>'width: 140px')
                    ),
                    'comment' => array
                    (
                        'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_reservation_suspension']['comment'],
                        'exclude'                 => true,
                        'inputType'               => 'text',
                        'eval'                    => array('mandatory'=>false, 'style'=>'width: 400px')
                    )
                )
            ),
            'sql' => "blob NULL"
        )
    )
);
