<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 10
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2025, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */
namespace con4gis\ReservationBundle\Classes\Models;


use Contao\Model;

/**
 * Class C4gReservationModel
 * @package c4g\projects
 */
class C4gReservationCancellationModel extends Model
{
    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_c4g_reservation_cancellation';

    public static function cancellation($lastname, $key) {
        if ($lastname && $key) {
            $t = 'tl_c4g_reservation';
            $arrColumns = array("$t.lastname='$lastname' AND $t.reservation_id='$key' AND $t.cancellation <> '1' AND beginDate >= UNIX_TIMESTAMP(CURRENT_DATE())");
            $arrValues = array();
            $arrOptions = array();

            $reservations = C4gReservationModel::findBy($arrColumns, $arrValues, $arrOptions);
            if ($reservations) {
                $result = false;
                foreach($reservations as $reservation) {
                    $reservation->cancellation = 1;
                    $intAffected = $reservation->save();
                    $result = ($intAffected || $result);
                }

                if ($result) {
                    return true;
                }
            }
        }

        return false;
    }
}
