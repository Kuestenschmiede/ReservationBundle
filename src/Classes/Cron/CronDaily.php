<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @author con4gis contributors (see "authors.md")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */
namespace con4gis\ReservationBundle\Classes\Cron;
use Contao\CoreBundle\Cron\AsCronjob;

#[AsCronjob('daily')]
class CronDaily
{
    //Delete old data records by specifying the number of days
    public function __invoke(): void
    {
        $database = \Contao\Database::getInstance();
        $db = $database->prepare('SELECT id, auto_del, del_time FROM tl_c4g_reservation_type ')
            ->execute()->fetchAllAssoc();

        foreach ($db as $entry) {
            $format = $entry['auto_del'];
            $value = $entry['del_time'];

            if ($value && ($value >= 1) && ($format === 'daily')) {
                $daytime = time();
                $reservations = $database->prepare('SELECT * FROM tl_c4g_reservation where `reservation_type` = ?')
                    ->execute($entry['id'])->fetchAllAssoc();

                foreach ($reservations as $reservation) {
                    $begindate = $reservation['beginDate'];
                    $deletetime = $begindate + ($value * 60 * 60 * 24) ;
                    if ($daytime > $deletetime) {
                        if ($reservation['fileUpload']) {
                            $fileUuid = \Contao\StringUtil::binToUuid($reservation['fileUpload']);
                            $file = \Contao\FilesModel::findById($fileUuid);
                            if ($file) {
                                $file = new \Contao\File($file->path);
                                $file->delete();
                            }
                        }
                        $database->prepare('DELETE FROM tl_c4g_reservation WHERE `id`=?')
                            ->execute($reservation['id']);
                    }
                }
            }
        }
    }
}
