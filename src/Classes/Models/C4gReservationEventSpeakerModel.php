<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @author con4gis contributors (see "authors.md")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */
namespace con4gis\ReservationBundle\Classes\Models;


use con4gis\CoreBundle\Classes\Helper\ArrayHelper;
use Contao\Model;
use Contao\Database;

/**
 * Class C4gReservationEventSpeakerModel
 * @package con4gis/reservation
 */
class C4gReservationEventSpeakerModel extends Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_c4g_reservation_event_speaker';

    /**
     * @return mixed
     */
    public static function getListItems($listParams = null) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM tl_c4g_reservation_event_speaker WHERE published = '1' ORDER BY sorting DESC, id ASC");
        $dbResult = $stmt->execute();
        $dbResult = $dbResult->fetchAllAssoc();
        $result = $dbResult;

        return ArrayHelper::arrayToObject($result);
    }

}
