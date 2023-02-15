<?php

namespace con4gis\ReservationBundle\Classes\Callbacks;

use Contao\Database;

class ReservationCalendarEvents {
    /**
     * Return all event types as array
     * @return array
     */
    public function getReservationTypes()
    {
        $return = [];

        $objects = Database::getInstance()->prepare("SELECT id,caption FROM tl_c4g_reservation_type WHERE `reservationObjectType` = 2 ORDER BY caption")
            ->execute();

        while ($objects->next()) {
            $return[$objects->id] = $objects->caption;
        }

        return $return;
    }

    /**
     * Return all speaker as array
     * @return array
     */
    public function getSpeakerName()
    {
        $return = [];

        $objects = Database::getInstance()->prepare("SELECT id,title,firstname,lastname FROM tl_c4g_reservation_event_speaker ORDER BY lastname")
            ->execute();

        while ($objects->next()) {
            $name = '';
            if ($objects->title) {
                $name = $objects->lastname.','.$objects->firstname.','.$objects->title;
            } else {
                $name = $objects->lastname.','.$objects->firstname;
            }

            $return[$objects->id] = $name;
        }

        return $return;
    }
}
