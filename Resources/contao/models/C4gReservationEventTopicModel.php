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
 * Class C4gReservationEventTopicModel
 * @package con4gis/reservation
 */
class C4gReservationEventTopicModel extends \Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_c4g_reservation_event_topic';
}
