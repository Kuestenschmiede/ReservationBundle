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
namespace con4gis\ReservationBundle\Classes;

/**
 * Class C4gReservationFrontendObject
 * @package c4g\projects
 */
class C4gReservationFrontendObject
{
    private $id = -1;
    private $caption = '';
    private $quantity = 1;
    private $timeinterval = '30';
    private $reservationTypes = [];
    private $opening_hours = ['mo','tu','we','th','fr','sa','su'];
    private $dates_exclusion = [];
    private $weekday_exclusion = [];
    private $min_reservation_day = '1';
    private $max_reservation_day = '365';
    private $desiredCapacity = [];

    /**
     * C4gReservationFrontendObject constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * @param string $caption
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * @return string
     */
    public function getTimeinterval(): string
    {
        return $this->timeinterval;
    }

    /**
     * @param string $timeinterval
     */
    public function setTimeinterval(string $timeinterval): void
    {
        $this->timeinterval = $timeinterval;
    }

    /**
     * @return array
     */
    public function getReservationTypes(): array
    {
        return $this->reservationTypes;
    }

    /**
     * @param array $reservationTypes
     */
    public function setReservationTypes(array $reservationTypes): void
    {
        $this->reservationTypes = $reservationTypes;
    }

    /**
     * @return array
     */
    public function getOpeningHours()
    {
        return $this->opening_hours;
    }

    /**
     * @param array $opening_hours
     */
    public function setOpeningHours($opening_hours)
    {
        $this->opening_hours = $opening_hours;
    }

    /**
     * @return array
     */
    public function getDatesExclusion()
    {
        return $this->dates_exclusion;
    }

    /**
     * @param array $days_exclusion
     */
    public function setDatesExclusion($dates_exclusion)
    {
        $this->dates_exclusion = $dates_exclusion;
    }

    /**
     * @return string
     */
    public function getWeekdayExclusion()
    {
        return $this->weekday_exclusion;
    }

    /**
     * @param string $weekday_exclusion
     */
    public function setWeekdayExclusion($weekday_exclusion)
    {
        $this->weekday_exclusion = $weekday_exclusion;
    }

    /**
     * @return string
     */
    public function getMinReservationDay()
    {
        return $this->min_reservation_day;
    }

    /**
     * @param string $min_reservation_day
     */
    public function setMinReservationDay($min_reservation_day)
    {
        $this->min_reservation_day = $min_reservation_day;
    }

    /**
     * @return string
     */
    public function getMaxReservationDay()
    {
        return $this->max_reservation_day;
    }

    /**
     * @param string $max_reservation_day
     */
    public function setMaxReservationDay($max_reservation_day)
    {
        $this->max_reservation_day = $max_reservation_day;
    }

    /**
     * @return array
     */
    public function getDesiredCapacity(): array
    {
        return $this->desiredCapacity;
    }

    /**
     * @param array $desiredCapacity
     */
    public function setDesiredCapacity(array $desiredCapacity): void
    {
        $this->desiredCapacity = $desiredCapacity;
    }
}
