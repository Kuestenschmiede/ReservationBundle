<?php

namespace Reservation\Test\Classes;

use con4gis\ReservationBundle\Classes\C4gReservationInsertTags;
use PHPUnit\Framework\TestCase;

class C4gReservationInsertTagsTest extends TestCase
{
    public function testDatabase($request, $queryToAdd, $expectedResult)
    {
        $insertTags = new C4gReservationInsertTags();
        $this->assertNotEmpty($insertTags);
    }
}
