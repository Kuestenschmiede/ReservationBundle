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

namespace con4gis\ReservationBundle\Classes;

use con4gis\ReservationBundle\Resources\contao\models\C4gReservationObjectModel;

class C4gReservationHelper
{
    /**
     * How many objects are booked for the current time?
     * @param $countArr
     * @param $date
     * @param $time
     * @param $interval
     * @return int|mixed
     */
    public static function getObjectCountPerTime($countArr, $date, $time, $interval)
    {
        $actCount = 0;
        if (!empty($countArr)) {
            foreach ($countArr[$date] as $beginTime => $count) {
                if ($beginTime == $time) {
                    $actCount = $actCount + $count;
                } elseif (($time > $beginTime) && ($time < ($beginTime + $interval))) {
                    $actCount = $actCount + $count;
                }
            }
        }

        return $actCount;
    }
}
