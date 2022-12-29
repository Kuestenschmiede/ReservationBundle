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

declare(strict_types=1);

namespace con4gis\ReservationBundle\Tests;

use con4gis\CoreBundle\Classes\C4GUtils;
use con4gis\ReservationBundle\Classes\Calculator\C4gReservationCalculator;
use con4gis\ReservationBundle\Classes\Models\C4gReservationModel;
use con4gis\ReservationBundle\Classes\Objects\C4gReservationFrontendObject;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\TestCase;

class C4gReservationCalculatorTest extends ContaoTestCase
{
    const RESERVATION_OBJECT_TYPE_DEFAULT = 1;

    private function getTableType() {
        $type = [
            'id' => 99000,
            'tstamp' => time(),
            'caption' => 'Tischreservierung',
            'periodType' => 'hour',
            'reservationObjectType' => $this::RESERVATION_OBJECT_TYPE_DEFAULT,
            'published' => 1
        ];
        return $type;
    }

    private function getTableObject() {
        $object = new C4gReservationFrontendObject();
        $object->setId(99000);
        $object->setCaption('6er Tisch');
        return $object;
    }

    private function getTableReservations() {
        $reservationLists = [
            [
                'id' => 99000,
                'tstamp' => time(),
                'reservation_type' => 99000,
                'desiredCapacity' => 1,
                'duration' => 1,
                'beginDate' => 1703286000,
                'endDate' => 1703286000,
                'beginTime' => 32400,
                'endTime' => 36000,
                'reservation_object' => 1,
//                'reservation_id' => C4GUtils::getGUID(),
                'published' => 1
            ],
            [
                'id' => 99001,
                'tstamp' => time(),
                'reservation_type' => 3,
                'desiredCapacity' => 1,
                'duration' => 1,
                'beginDate' => 1672441200,
                'endDate' => 1672527600,
                'beginTime' => 32400,
                'endTime' => 36000,
                'reservation_object' => 1,
//                'reservation_id' => C4GUtils::getGUID(),
                'published' => 1
            ],
            [
                'id' => 99001,
                'tstamp' => time(),
                'reservation_type' => 3,
                'desiredCapacity' => 1,
                'duration' => 1,
                'beginDate' => 1672441200,
                'endDate' => 1672527600,
                'beginTime' => 36000,
                'endTime' => 43200,
                'reservation_object' => 1,
//                'reservation_id' => C4GUtils::getGUID(),
                'published' => 1
            ]
        ];

        return $reservationLists;
    }

    public function testCalculator() {
        $framework = $this->mockContaoFramework();
//        $framework->expects($this->atLeastOnce())
//            ->method('initialize');
        $container = $this->getContainerWithContaoConfiguration();
        \Contao\System::setContainer($container);

        $reservationLists = $this->getTableReservations();

        $c4gReservationCalculator = new C4gReservationCalculator(1703286000+32400, 1, $reservationLists);

        $object = $this->getTableObject();
        $type   = $this->getTableType();

        $c4gReservationCalculator->calculateAll(1703286000, 32400, 36000, $object, $type, 1, [], 1);
        $this->assertNotEmpty($c4gReservationCalculator->getCalculatorResult());
        $this->assertEquals(0, $c4gReservationCalculator->getCalculatorResult()->getDbPersons());

        $this->assertEquals(true, 36000 && (36000 >= 86400) ? 36000 - 86400 : 36000);
//        $this->assertEquals(true, ((32400 >= $timeBeginDb) && ($timeBegin < $timeEndDb)) ||
//            (($timeEnd > $timeBeginDb) && ($timeEnd <= $timeEndDb)));


    }
}