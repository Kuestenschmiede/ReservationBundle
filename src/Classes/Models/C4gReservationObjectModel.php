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
use con4gis\CoreBundle\Resources\contao\models\C4gLogModel;
use con4gis\ReservationBundle\Classes\Calculator\C4gReservationCalculator;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationDateChecker;
use con4gis\ReservationBundle\Classes\C4gReservationFrontendObject;
use Contao\Database;
use Contao\Date;
use Contao\Model;

/**
 * Class C4gReservationObjectModel
 * @package c4g\projects
 */
class C4gReservationObjectModel extends Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_c4g_reservation_object';


}
