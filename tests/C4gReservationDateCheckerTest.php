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
use PHPUnit\Framework\TestCase;

class C4gReservationDateCheckerTest extends TestCase
{
    public function testBeginOfDate() {
        $now = time();
        $beginOfDay = C4gReservationDateChecker::getBeginOfDate($now);
        $hour = date('H', $beginOfDay);
        self::assertEquals(0,$hour);

        $minute = date('i', $beginOfDay);
        self::assertEquals(0,$minute);

        $second = date('s', $beginOfDay);
        self::assertEquals(0,$second);
    }

    public function testEndOfDate() {
        $now = time();
        $endOfDay = C4gReservationDateChecker::getEndOfDate($now);
        $hour = date('H', $endOfDay);
        self::assertEquals(23,$hour);

        $minute = date('i', $endOfDay);
        self::assertEquals(59,$minute);

        $second = date('s', $endOfDay);
        self::assertEquals(59,$second);
    }
}
