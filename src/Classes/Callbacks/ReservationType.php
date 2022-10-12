<?php

namespace con4gis\ReservationBundle\Classes\Callbacks;

use Contao\Database;

class ReservationType {

    /**
     * @return mixed
     */
    public function getAllTypes()
    {
        \Contao\System::loadLanguageFile('tl_c4g_reservation_settings');
        $database = Database::getInstance();
        $types = $database->prepare("SELECT id,caption,reservationObjectType FROM tl_c4g_reservation_type ORDER BY caption")
            ->execute();
        while ($types->next()) {
            $objectType = $types->reservationObjectType;
            $return[$types->id] = $types->caption.' ['.$GLOBALS["TL_LANG"]["tl_c4g_reservation_settings"]["referencesObjectType"][$objectType].']';
        }
        return $return;
    }
}
