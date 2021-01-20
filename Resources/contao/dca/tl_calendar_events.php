<?php

use Contao\Image;
use Contao\StringUtil;

$str = 'tl_calendar_events';

$GLOBALS['TL_DCA'][$str]['config']['ctable'][] = 'tl_c4g_reservation_event';
//$GLOBALS['TL_DCA'][$str]['config']['ctable'][] = 'tl_c4g_reservation';
$GLOBALS['TL_DCA'][$str]['config']['onload_callback'][] = ['tl_c4g_reservation_event_bridge', 'c4gLoadReservationData'];

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

$GLOBALS['TL_DCA'][$str]['fields']['c4g_reservation_number'] = [
    'label'                   => $GLOBALS['TL_LANG'][$str]['c4g_reservation_number'],
    'default'                 => '',
    'sorting'                 => true,
    'search'                  => true,
    'exclude'                 => true,
    'inputType'               => 'text',
    'sql'                     => "varchar(128) NOT NULL default ''"
];

/**
 * Class tl_c4g_reservation
 */
class tl_c4g_reservation_event_bridge extends tl_calendar_events
{

    public function c4gLoadReservationData()
    {
        $noReservationEvents = true;
        $reservationEvents = $this->Database->prepare("SELECT pid, number FROM tl_c4g_reservation_event WHERE pid!=0 AND number!=''")->execute()->fetchAllAssoc();
        if ($reservationEvents) {
            foreach ($reservationEvents as $reservationEvent) {
                $this->Database->prepare("UPDATE tl_calendar_events SET c4g_reservation_number=? WHERE id=?")->execute($reservationEvent['number'], $reservationEvent['pid']);
                $noReservationEvents = false;
            }
        }
        if ($noReservationEvents) {
            $GLOBALS['TL_DCA']['tl_calendar_events']['fields']['c4g_reservation_number']['sorting'] = false;
            $GLOBALS['TL_DCA']['tl_calendar_events']['fields']['c4g_reservation_number']['search'] = false;
        }
    }

    /**
     * @param $row
     * @param $href
     * @param $label
     * @param $title
     * @param $icon
     * @return string
     */
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

    /**
     * @param $row
     * @param $href
     * @param $label
     * @param $title
     * @param $icon
     * @return string
     */
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