<?php

namespace con4gis\ReservationBundle\Classes;

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
