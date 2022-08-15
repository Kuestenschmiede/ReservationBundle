<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 8
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2022, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */
namespace con4gis\ReservationBundle\Classes\Models;


use con4gis\CoreBundle\Classes\C4GUtils;
use con4gis\CoreBundle\Classes\Helper\ArrayHelper;
use Contao\Model;

/**
 * Class C4gReservationModel
 * @package con4gis\reservation
 */
class C4gReservationModel extends Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_c4g_reservation';

    /**
     * @return mixed
     */
    public static function getListItems() {
        $db = \Database::getInstance();
        $pastDayNumber = 1;//intval($listParams->getModelListParams());
        $stmt = $db->prepare("SELECT * FROM tl_c4g_reservation WHERE `cancellation` <> '1' AND `beginDate` >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ".$pastDayNumber." DAY))");
        $dbResult = $stmt->execute();
        $dbResult = $dbResult->fetchAllAssoc();
        $result = $dbResult;

        return ArrayHelper::arrayToObject($result);
    }

    public static function getListItemsByMember($memberId, $tableName, $database, $fieldList, $listParams) {
        $db = \Database::getInstance();
        $pastDayNumber = intval($listParams->getModelListParams()[0]);
        $stmt = $db->prepare("SELECT * FROM tl_c4g_reservation WHERE `member_id`=? AND `cancellation` <> '1' AND `beginDate` >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ".$pastDayNumber." DAY))");
        $dbResult = $stmt->execute($memberId);
        $dbResult = $dbResult->fetchAllAssoc();

        $result = [];
        foreach ($dbResult as $dbResultItem) {
            $result[$dbResultItem['id']] = $dbResultItem;
        }

        return ArrayHelper::arrayToObject($result);
    }

    public static function getListItemsByGroup($groupId, $database, $listParams, $brickDatabase) {
        $db = \Database::getInstance();
        $pastDayNumber = intval($listParams->getModelListParams()[0]);
        $stmt = $db->prepare("SELECT * FROM tl_c4g_reservation WHERE `group_id`=? AND `cancellation` <> '1' AND beginDate >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ".$pastDayNumber." DAY))");
        $dbResult = $stmt->execute($groupId);
        $dbResult = $dbResult->fetchAllAssoc();

        $result = [];
        foreach ($dbResult as $dbResultItem) {
            $result[$dbResultItem['id']] = $dbResultItem;
        }

        return ArrayHelper::arrayToObject($result);
    }

    /**
     * @param $groupId
     * @param $database
     * @param $listParams
     * @param $brickDatabase
     * @return bool|\stdClass
     *
     * get special notifcations and special types
     */
    public static function getAddressListItemsByGroup($groupId, $database, $listParams, $brickDatabase) {
        $types = $listParams->getModelListParams();
        $db = \Database::getInstance();
        if ($types) {
            $inTypes = C4GUtils::buildInString($types);

            $stmt = $db->prepare("SELECT * FROM tl_c4g_reservation WHERE `group_id`=? AND `cancellation` <> '1' AND `beginDate` >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 DAY)) AND (`specialNotification` = '1' OR (`reservation_type` $inTypes))");
            $dbResult = $stmt->execute($groupId, ...$types);
        } else {
            $stmt = $db->prepare("SELECT * FROM tl_c4g_reservation WHERE `group_id`=? AND `cancellation` <> '1' AND `beginDate` >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 DAY)) AND `specialNotification` = '1'");
            $dbResult = $stmt->execute($groupId);
        }

        $dbResult = $dbResult->fetchAllAssoc();

        $result = [];
        foreach ($dbResult as $dbResultItem) {
            $result[$dbResultItem['id']] = $dbResultItem;
        }

        return ArrayHelper::arrayToObject($result);
    }
}
