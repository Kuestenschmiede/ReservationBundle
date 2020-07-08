<?php
/**
 * This file is part of con4gis,
 * the gis-kit for Contao CMS.
 *
 * @package    con4gis
 * @version    7
 * @author     con4gis contributors (see "authors.txt")
 * @license    LGPL-3.0-or-later
 * @copyright  Küstenschmiede GmbH Software & Design
 * @link       https://www.con4gis.org
 */
namespace con4gis\ReservationBundle\Resources\contao\models;


use Contao\Database;
use Contao\Model;

/**
 * Class C4gReservationModel
 * @package c4g\projects
 */
class C4gReservationModel extends Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_c4g_reservation';

    public static function getDialog($id)
    {
        $database=Database::getInstance();

        $database->prepare("SELECT * FROM tl_c4g_reservation_object WHERE id=?")
            ->execute($objectId);




    }

}
