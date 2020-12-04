<?php

use Contao\Image;
use Contao\StringUtil;

$str = 'tl_calendar_events';

$GLOBALS['TL_DCA'][$str]['config']['ctable'][] = 'tl_c4g_reservation_event';
//$GLOBALS['TL_DCA'][$str]['config']['ctable'][] = 'tl_c4g_reservation';

$GLOBALS['TL_DCA'][$str]['list']['operations']['c4gEditEvent'] = [
    'label'               => &$GLOBALS['TL_LANG'][$str]['c4gEditEvent'],
    'icon'                => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation_types.svg',
    'href'                => 'table=tl_c4g_reservation_event&amp;act=create',
    'button_callback'     => ['tl_c4g_reservation_event_bridge', 'c4gEditEvent'],
    'exclude'             => true
];

$GLOBALS['TL_DCA'][$str]['list']['operations']['c4gEditReservations'] = [
    'label'               => &$GLOBALS['TL_LANG'][$str]['c4gEditReservations'],
    'icon'                => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation.svg',
    'href'                => 'table=tl_c4g_reservation',
    'button_callback'     => ['tl_c4g_reservation_event_bridge', 'c4gShowReservations'],
    'exclude'             => true
];

/**
 * Class tl_c4g_reservation
 */
class tl_c4g_reservation_event_bridge extends tl_calendar_events
{
    public function c4gEditEvent($row, $href, $label, $title, $icon)
    {
        $rt = Input::get('rt');
        $ref = Input::get('ref');
        $do = Input::get('do');

        $attributes = 'style="margin-right:3px"';
        $imgAttributes = 'style="width: 18px; height: 18px"';

        $result = Database::getInstance()->prepare("SELECT id FROM tl_c4g_reservation_event WHERE pid=? LIMIT 1")->execute($row['id'])->fetchAssoc();
        if ($result) {
            $href = "/contao?do=$do&table=tl_c4g_reservation_event&amp;act=edit&id=".$result['id']."&pid=".$row['id']."&rt=".$rt;
        } else {
            $href = "/contao?do=$do&table=tl_c4g_reservation_event&amp;act=create&id=".$row['id']."&pid=".$row['id']."&rt=".$rt;
        }

        $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['pid']['default'] = $row['id'];

        return '<a href="' . $href . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, $imgAttributes) . '</a> ';
    }

    public function c4gShowReservations($row, $href, $label, $title, $icon)
    {
        $rt = Input::get('rt');
        $ref = Input::get('ref');
        $do = Input::get('do');

        $attributes = 'style="margin-right:3px"';
        $imgAttributes = 'style="width: 18px; height: 18px"';

        $href = "/contao?do=$do&table=tl_c4g_reservation&amp&id=".$row['id']."&pid=".$row['pid']."&rt=".$rt."&ref=".$ref;

        return '<a href="' . $href . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, $imgAttributes) . '</a> ';
    }
}