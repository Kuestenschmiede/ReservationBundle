<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @author con4gis contributors (see "authors.md")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */
namespace con4gis\ReservationBundle\Classes\Objects;

/**
 * Class C4gReservationFrontendObject
 * @package c4g\projects
 */
class C4gReservationFrontendObject
{
    private $id = -1;
    private $type = 1; //1=default, 2=event
    private $caption = '';
    private $quantity = 1;
    private $allTypesQuantity = 1;
    private $allTypesValidity = 0;
    private $timeinterval = '30';
    private $periodType = 'minute';
    private $duration = '30';
    private $reservationTypes = [];
    private $opening_hours = ['mo','tu','we','th','fr','sa','su'];
    private $dates_exclusion = [];
    private $weekday_exclusion = [];
    private $min_reservation_day = '1';
    private $max_reservation_day = '365';
    private $maxBeginTime = '';
    private $desiredCapacity = [];
    private $beginDate = 0;
    private $beginTime = 0;
    private $endDate = 0;
    private $endTime = 0;
    private $typeOfObject = 'standard';
    private $dateTimeBegin = 0;
    private $typeOfObjectDuration = 0;
    private $almostFullyBookedAt = 0;
    private $number = '';
    private $topic = [];
    private $audience = [];
    private $speaker = [];
    private $location = 0;
    private $organizer = 0;
    private $eventDuration = ''; //ToDo
    private $priority = 0;
    private $switchAllTypes = null;
    private $description = '';
    private $image = null;
    private $price = 0.00;
    private $taxOptions = 'tNone';
    private $standardTaxRate = 0.0;
    private $reducedTaxRate = 0.0;
    private $priceOption = 'pReservation';
    private $allTypesEvents = null;
    private $conferenceLink = '';

    private $currentReservations = null;
    private $severalBookings = null;

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
     * @return int
     */
    public function getAllTypesQuantity(): int
    {
        return $this->allTypesQuantity;
    }

    /**
     * @param int $allTypesQuantity
     */
    public function setAllTypesQuantity(int $allTypesQuantity): void
    {
        $this->allTypesQuantity = $allTypesQuantity;
    }

    /**
     * @return int
     */
    public function getAllTypesValidity(): int
    {
        return $this->allTypesValidity;
    }

    /**
     * @param int $allTypesValidity
     */
    public function setAllTypesValidity(int $allTypesValidity): void
    {
        $this->allTypesValidity = $allTypesValidity;
    }

    /**
     * @return string
     */
    public function getTypeOfObject(): string
    {
        return $this->typeOfObject;
    }

    /**
     * @param string $typeOfObject
     */
    public function setTypeOfObject(string $typeOfObject): void
    {
        $this->typeOfObject = $typeOfObject;
    }

    /**
     * @return int
     */
    public function getDateTimeBegin(): int
    {
        return $this->dateTimeBegin;
    }

    /**
     * @param int $dateTimeBegin
     */
    public function setDateTimeBegin(int $dateTimeBegin): void
    {
        $this->dateTimeBegin = $dateTimeBegin;
    }

    /**
     * @return int
     */
    public function getTypeOfObjectDuration(): int
    {
        return $this->typeOfObjectDuration;
    }

    /**
     * @param int $typeOfObjectDuration
     */
    public function setTypeOfObjectDuration(int $typeOfObjectDuration): void
    {
        $this->typeOfObjectDuration = $typeOfObjectDuration;
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
    public function getDuration(): string
    {
        return $this->duration;
    }

    /**
     * @param string $duration
     */
    public function setDuration(string $duration): void
    {
        $this->duration = $duration;
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

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getBeginDate(): int
    {
        return $this->beginDate;
    }

    /**
     * @param int $beginDate
     */
    public function setBeginDate(int $beginDate): void
    {
        $this->beginDate = $beginDate;
    }

    /**
     * @return int
     */
    public function getBeginTime(): int
    {
        return $this->beginTime;
    }

    /**
     * @param int $beginTime
     */
    public function setBeginTime(int $beginTime): void
    {
        $this->beginTime = $beginTime;
    }

    /**
     * @return int
     */
    public function getEndDate(): int
    {
        return $this->endDate;
    }

    /**
     * @param int $endDate
     */
    public function setEndDate(int $endDate): void
    {
        $this->endDate = $endDate;
    }

    /**
     * @return int
     */
    public function getEndTime(): int
    {
        return $this->endTime;
    }

    /**
     * @param int $endTime
     */
    public function setEndTime(int $endTime): void
    {
        $this->endTime = $endTime;
    }

    /**
     * @return int
     */
    public function getAlmostFullyBookedAt(): int
    {
        return $this->almostFullyBookedAt;
    }

    /**
     * @param int $almostFullyBookedAt
     */
    public function setAlmostFullyBookedAt(int $almostFullyBookedAt): void
    {
        $this->almostFullyBookedAt = $almostFullyBookedAt;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber(string $number): void
    {
        $this->number = $number;
    }

    /**
     * @return array
     */
    public function getTopic(): array
    {
        return $this->topic;
    }

    /**
     * @param array $topic
     */
    public function setTopic(array $topic): void
    {
        $this->topic = $topic;
    }

    /**
     * @return array
     */
    public function getAudience(): array
    {
        return $this->audience;
    }

    /**
     * @param array $audience
     */
    public function setAudience(array $audience): void
    {
        $this->audience = $audience;
    }

    /**
     * @return array
     */
    public function getSpeaker(): array
    {
        return $this->speaker;
    }

    /**
     * @param array $speaker
     */
    public function setSpeaker(array $speaker): void
    {
        $this->speaker = $speaker;
    }

    /**
     * @return int
     */
    public function getLocation(): int
    {
        return $this->location;
    }

    /**
     * @param int $location
     */
    public function setLocation(int $location): void
    {
        $this->location = $location;
    }

    /**
     * @return int
     */
    public function getOrganizer(): int
    {
        return $this->organizer;
    }

    /**
     * @param int $organizer
     */
    public function setOrganizer(int $organizer): void
    {
        $this->organizer = $organizer;
    }

    /**
     * @return string
     */
    public function getEventDuration(): string
    {
        return $this->eventDuration;
    }

    /**
     * @param string $eventDuration
     */
    public function setEventDuration(string $eventDuration): void
    {
        $this->eventDuration = $eventDuration;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @return null
     */
    public function getSwitchAllTypes()
    {
        return $this->switchAllTypes;
    }

    /**
     * @param null $switchAllTypes
     */
    public function setSwitchAllTypes($switchAllTypes): void
    {
        $this->switchAllTypes = $switchAllTypes;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return null
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param null $image
     */
    public function setImage($image): void
    {
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function getPeriodType(): string
    {
        return $this->periodType;
    }

    /**
     * @param string $periodType
     */
    public function setPeriodType(string $periodType): void
    {
        $this->periodType = $periodType;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    /**
    * @return string
    */
    public function getTaxOptions(): string
    {
        return $this->taxOptions;
    }

    /**
     * @param string $taxOptions
     */
    public function setTaxOptions(string $taxOptions): void
    {
        $this->taxOptions = $taxOptions;
    }

    /**
     * @return float
     */
    public function getStandardTaxRate(): float
    {
        return $this->standardTaxRate;
    }

    /**
     * @param float $standardTaxRate
     */
    public function setStandardTaxRate(float $standardTaxRate): void
    {
        $this->standardTaxRate = $standardTaxRate;
    }

    /**
     * @return float
     */
    public function getReducedTaxRate(): float
    {
        return $this->reducedTaxRate;
    }

    /**
     * @param float $reducedTaxRate
     */
    public function setReducedTaxRate(float $reducedTaxRate): void
    {
        $this->reducedTaxRate = $reducedTaxRate;
    }

    /**
     * @return string
     */
    public function getPriceOption(): string
    {
        return $this->priceOption;
    }

    /**
     * @param string $priceOptions
     */
    public function setPriceOption(string $priceOptions): void
    {
        $this->priceOption = $priceOptions;
    }

    /**
     * @return null
     */
    public function getAllTypesEvents()
    {
        return $this->allTypesEvents;
    }

    /**
     * @param null $allTypesEvents
     */
    public function setAllTypesEvents($allTypesEvents): void
    {
        $this->allTypesEvents = $allTypesEvents;
    }

    /**
     * @return string
     */
    public function getMaxBeginTime(): string
    {
        return $this->maxBeginTime;
    }

    /**
     * @param string $maxBeginTime
     */
    public function setMaxBeginTime(string $maxBeginTime): void
    {
        $this->maxBeginTime = $maxBeginTime;
    }

    public function setCurrentReservations($currentReservations)
    {
        $this->currentReservations = $currentReservations;
    }

    public function getCurrentReservations()
    {
        return $this->currentReservations;
    }

    public function setSeveralBookings($severalBookings)
    {
        $this->severalBookings = $severalBookings;
    }

    public function getSeveralBookings()
    {
        return $this->severalBookings;
    }

    public function getConferenceLink(): string
    {
        return $this->conferenceLink;
    }

    public function setConferenceLink(string $conferenceLink): void
    {
        $this->conferenceLink = $conferenceLink;
    }
}
