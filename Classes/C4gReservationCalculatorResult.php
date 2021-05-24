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

class C4gReservationCalculatorResult
{
    private $dbBookings = 0;
    private $dbPersons = 0;
    private $dbPercent = 0;
    private $timeArray = [];

    /**
     * @return int
     */
    public function getDbBookings(): int
    {
        return $this->dbBookings;
    }

    /**
     * @param int $dbBookings
     */
    public function setDbBookings(int $dbBookings): void
    {
        $this->dbBookings = $dbBookings;
    }

    /**
     * @return int
     */
    public function getDbPersons(): int
    {
        return $this->dbPersons;
    }

    /**
     * @param int $dbPersons
     */
    public function setDbPersons(int $dbPersons): void
    {
        $this->dbPersons = $dbPersons;
    }

    /**
     * @return int
     */
    public function getDbPercent(): int
    {
        return $this->dbPercent;
    }

    /**
     * @param int $dbPercent
     */
    public function setDbPercent(int $dbPercent): void
    {
        $this->dbPercent = $dbPercent;
    }

    /**
     * @return array
     */
    public function getTimeArray(): array
    {
        return $this->timeArray;
    }

    /**
     * @param array $timeArray
     */
    public function setTimeArray(array $timeArray): void
    {
        $this->timeArray = $timeArray;
    }
}
