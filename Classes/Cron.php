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

use Contao\Backend;

class Cron extends Backend
//Delete old data records by specifying the number of days
{
    public function onDaily(): void
    {
        $db = $this->Database->prepare('SELECT id, auto_del, del_time FROM tl_c4g_reservation_type ')
            ->execute()->fetchAllAssoc();

        foreach ($db as $entry) {
            $format = $entry['auto_del'];
            $value = $entry['del_time'];

            if ($value && ($value >= 1) && ($format === 'daily')) {
                $daytime = time();
                $reservations = $this->Database->prepare('SELECT * FROM tl_c4g_reservation where reservation_type = ?')
                    ->execute($entry['id'])->fetchAllAssoc();

                foreach ($reservations as $reservation) {
                    $begindate = $reservation['beginDate'];
                    $deletetime = $begindate + ($value * 60 * 60 * 24) ;
                    if ($daytime > $deletetime) {
                        $db = $this->Database->prepare('DELETE FROM tl_c4g_reservation WHERE id=?')
                            ->execute($reservation['id']);
                    }
                }
            }
        }
    }
}
