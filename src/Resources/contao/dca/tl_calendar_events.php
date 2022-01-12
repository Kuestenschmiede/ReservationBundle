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

use con4gis\CoreBundle\Resources\contao\models\C4gLogModel;
use Contao\Image;
use Contao\StringUtil;

$str = 'tl_calendar_events';

$GLOBALS['TL_DCA'][$str]['config']['ctable'][] = 'tl_c4g_reservation_event';
$GLOBALS['TL_DCA'][$str]['config']['onload_callback'][] = ['tl_c4g_reservation_event_bridge', 'c4gLoadReservationData'];

$GLOBALS['TL_DCA'][$str]['list']['operations']['c4gEditEvent'] = [
    'label'               => &$GLOBALS['TL_LANG'][$str]['c4gEditEvent'],
    'icon'                => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation_types.svg',
    'button_callback'     => ['tl_c4g_reservation_event_bridge', 'c4gEditEvent'],
    'exclude'             => true
];

$GLOBALS['TL_DCA'][$str]['list']['operations']['c4gEditReservations'] = [
    'label'               => &$GLOBALS['TL_LANG'][$str]['c4gEditReservations'],
    'icon'                => 'bundles/con4gisreservation/images/be-icons/con4gis_reservation.svg',
    'button_callback'     => ['tl_c4g_reservation_event_bridge', 'c4gShowReservations'],
    'exclude'             => true
];

$GLOBALS['TL_DCA'][$str]['fields']['c4g_reservation_number'] = [
    'label'                   => &$GLOBALS['TL_LANG'][$str]['c4g_reservation_number'],
    'default'                 => '',
    'sorting'                 => true,
    'search'                  => true,
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('doNotCopy' => true),
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
        $reservationEvents = $this->Database->prepare("SELECT pid, number FROM tl_c4g_reservation_event WHERE `pid`<>0 AND `number`<>''")->execute()->fetchAllAssoc();
        if ($reservationEvents) {
            foreach ($reservationEvents as $reservationEvent) {
                $this->Database->prepare("UPDATE tl_calendar_events SET c4g_reservation_number=? WHERE `id`=?")->execute($reservationEvent['number'], $reservationEvent['pid']);
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
        $calendar = Database::getInstance()->prepare("SELECT activateEventReservation FROM tl_calendar WHERE `id`=?")->execute($row['pid'])->fetchAssoc();
        if ($calendar['activateEventReservation']) {
            $rt = Input::get('rt');
            $ref = Input::get('ref');
            $do = Input::get('do');

            $attributes = 'style="margin-right:3px"';
            $imgAttributes = 'style="width: 18px; height: 18px"';

            $result = Database::getInstance()->prepare("SELECT id FROM tl_c4g_reservation_event WHERE `pid`=?")->execute($row['id'])->fetchAllAssoc();

            if ($result && count($result) > 1) {
                C4gLogModel::addLogEntry('reservation', 'There are more than one event connections. Check Event: '. $row['id']);
            } else if ($result && count($result) == 1) {
                $href = "/contao?do=$do&table=tl_c4g_reservation_event&amp;act=edit&amp;id=".$result[0]['id']."&amp;pid=".$row['id']."&amp;rt=".$rt;
            } else {
                $href = "/contao?do=$do&table=tl_c4g_reservation_event&amp;act=create&amp;mode=2&amp;pid=".$row['id']."&amp;rt=".$rt;
            }

            $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['pid']['default'] = $row['id'];

            return '<a href="' . $href . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, $imgAttributes) . '</a> ';
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
    public function c4gShowReservations($row, $href, $label, $title, $icon)
    {
        $calendar = Database::getInstance()->prepare("SELECT activateEventReservation FROM tl_calendar WHERE `id`=?")->execute($row['pid'])->fetchAssoc();
        if ($calendar['activateEventReservation']) {
            $rt = Input::get('rt');
            $ref = Input::get('ref');
            $do = Input::get('do');

            $attributes = 'style="margin-right:3px"';
            $imgAttributes = 'style="width: 18px; height: 18px"';

            $href = "/contao?do=$do&table=tl_c4g_reservation&amp&id=" . $row['id'] . "&pid=" . $row['pid'] . "&rt=" . $rt . "&ref=" . $ref;

            $state = InsertTags::replaceInsertTags('{{c4gevent::' . $row['id'] . '::state_raw}}');
            switch ($state) {
                case '1':
                    $icon = 'bundles/con4gisreservation/images/circle_green.svg';
                    break;
                case '2':
                    $icon = 'bundles/con4gisreservation/images/circle_orange.svg';
                    break;
                case '3':
                    $icon = 'bundles/con4gisreservation/images/circle_red.svg';
                    break;
                default:
                    $icon = 'bundles/con4gisreservation/images/circle_red.svg';
                    break;
            }

            return '<a href="' . $href . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, $imgAttributes) . '</a> ';
        }
    }
}