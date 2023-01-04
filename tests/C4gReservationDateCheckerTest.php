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

namespace con4gis\ReservationBundle\Tests;

use con4gis\ReservationBundle\Classes\Utils\C4gReservationDateChecker;
use Contao\Config;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\TestCase;

class C4gReservationDateCheckerTest extends ContaoTestCase
{
    public function testMergeDateWithTime(){
//        $framework = $this->mockContaoFramework();
//        $config = $framework->getAdapter(Config::class);

        $now = time();
        $date = C4gReservationDateChecker::getBeginOfDate($now, 'GMT');
        $time = $now - $date;

        $mergedDateTime = C4gReservationDateChecker::mergeDateWithTime($date,$time, 'GMT');
        self::assertEquals($now,$mergedDateTime);

        $mergedDateTime = C4gReservationDateChecker::mergeDateWithTime($date,$now, 'GMT');
        self::assertEquals($now,$mergedDateTime);
    }
    /*
     * Test Begin of Date (00:00:00)
    */
    public function testBeginOfDate() {
        $now = time();
        $beginOfDay = C4gReservationDateChecker::getBeginOfDate($now, 'GMT');
        $hour = date('H', $beginOfDay);
        self::assertEquals(0,$hour);

        $minute = date('i', $beginOfDay);
        self::assertEquals(0,$minute);

        $second = date('s', $beginOfDay);
        self::assertEquals(0,$second);
    }
    /*
     * Test End of Date (23:59:59)
    */
    public function testEndOfDate() {
        $now = time();
        $endOfDay = C4gReservationDateChecker::getEndOfDate($now, 'GMT');
        $hour = date('H', $endOfDay);
        self::assertEquals(23,$hour);

        $minute = date('i', $endOfDay);
        self::assertEquals(59,$minute);

        $second = date('s', $endOfDay);
        self::assertEquals(59,$second);
    }
    /*
     * Test Merge Date with Time (Both are Timestamps)
    */
//    public function testMergeDateWithTimeForIcs(){
//        $now = time();
//        $date = C4gReservationDateChecker::getBeginOfDate($now);
//        $time = $now - $date;
//
//        $mergedDateTimeIcs = C4gReservationDateChecker::mergeDateWithTimeForIcs($date,$time);
//        self::assertEquals($now,$mergedDateTimeIcs);
//    }
    /*
     * Test Weekday (Su-Sa)
    */
    public function testWeekdayStr(){
        date_default_timezone_set('Europe/Berlin');
        $sylvester = 1672441200;
        $weekday = date('w', $sylvester);

        $weekdayStr = C4gReservationDateChecker::getWeekdayStr($weekday);
        self::assertEquals('sa',$weekdayStr);

    }
    /*
     * Test Weekday (Sunday-Saturday)
    */
    public function testWeekdayFullStr(){
        date_default_timezone_set('Europe/Berlin');
        $sylvester = 1672441200;
        $weekday = date('w', $sylvester);

        $weekdayStr = C4gReservationDateChecker::getWeekdayFullStr($weekday);
        self::assertEquals('saturday',$weekdayStr);
    }
    /*
     * Test Weekday Number (0-6)
    */
    public function testWeekdayNumber(){
        date_default_timezone_set('Europe/Berlin');
        $sylvester = 1672441200;
        $weekday = date('w', $sylvester);

        $weekdayNumber = C4gReservationDateChecker::getWeekdayNumber(C4gReservationDateChecker::getWeekdayStr($weekday));
        self::assertEquals('6',$weekdayNumber);
    }
    /*
     * Test Individual Days
    */
    public function testIndividualDays(){
        $sylvester = 1672441200;
        //$GLOBALS['TL_CONFIG']['timeZone'] = "Europe/Berlin";

        $weekdayStr = C4gReservationDateChecker::isSunday($sylvester, "Europe/Berlin");
        self::assertEquals(false,$weekdayStr);

        $weekdayStr = C4gReservationDateChecker::isMonday($sylvester, "Europe/Berlin");
        self::assertEquals(false,$weekdayStr);

        $weekdayStr = C4gReservationDateChecker::isTuesday($sylvester, "Europe/Berlin");
        self::assertEquals(false,$weekdayStr);

        $weekdayStr = C4gReservationDateChecker::isWednesday($sylvester, "Europe/Berlin");
        self::assertEquals(false,$weekdayStr);

        $weekdayStr = C4gReservationDateChecker::isThursday($sylvester, "Europe/Berlin");
        self::assertEquals(false,$weekdayStr);

        $weekdayStr = C4gReservationDateChecker::isFriday($sylvester, "Europe/Berlin");
        self::assertEquals(false,$weekdayStr);

        $weekdayStr = C4gReservationDateChecker::isSaturday($sylvester, "Europe/Berlin");
        self::assertEquals(true, $weekdayStr);

    }

}
