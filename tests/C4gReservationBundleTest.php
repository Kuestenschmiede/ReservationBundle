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

use con4gis\ReservationBundle\con4gisReservationBundle;
use Contao\TestCase\ContaoTestCase;

class C4gReservationBundleTest extends ContaoTestCase
{
    public function testBundle() {
        $bundle = new con4gisReservationBundle();
        $this->assertInstanceOf('con4gis\ReservationBundle\con4gisReservationBundle', $bundle);
    }
}