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
namespace con4gis\ReservationBundle\Classes\Cron;

use con4gis\ReservationBundle\Classes\Notifications\C4gReservationConfirmation;
use Contao\Backend;
use Contao\Database;
use Contao\File;
use Contao\FilesModel;
use Contao\StringUtil;

//ToDo replace for Contao 5
class Cron extends Backend
{
    //send confirmation emails if defined
    public function onMinutely(): void
    {
        $db = $this->Database->prepare('SELECT id, auto_send FROM tl_c4g_reservation_type ')
            ->execute()->fetchAllAssoc();

        foreach ($db as $entry) {
            $auto_send = $entry['auto_send'];

            if ($auto_send === 'minutely') {
                $database = Database::getInstance();
                $reservations = $database->prepare("SELECT * FROM tl_c4g_reservation where `reservation_type` = ? AND (`confirmed` = '1' OR `specialNotification` = '1') AND NOT `emailConfirmationSend` = '1'")
                    ->execute($entry['id'])->fetchAllAssoc();

                foreach ($reservations as $reservation) {
                    C4gReservationConfirmation::sendNotification($reservation['id']);
                    sleep(4); // buffer
                }
            }
        }
    }

    //Delete old data records by specifying the number of days
    public function onDaily(): void
    {
        $db = $this->Database->prepare('SELECT id, auto_del, del_time FROM tl_c4g_reservation_type ')
            ->execute()->fetchAllAssoc();

        foreach ($db as $entry) {
            $format = $entry['auto_del'];
            $value = $entry['del_time'];

            if ($value && ($value >= 1) && ($format === 'daily')) {
                $daytime = time();
                $reservations = $this->Database->prepare('SELECT * FROM tl_c4g_reservation where `reservation_type` = ?')
                    ->execute($entry['id'])->fetchAllAssoc();

                foreach ($reservations as $reservation) {
                    $begindate = $reservation['beginDate'];
                    $deletetime = $begindate + ($value * 60 * 60 * 24) ;
                    if ($daytime > $deletetime) {
                        if ($reservation['fileUpload']) {
                            $fileUuid = StringUtil::binToUuid($reservation['fileUpload']);
                            $file = FilesModel::findById($fileUuid);
                            if ($file) {
                                $file = new File($file->path);
                                $file->delete();
                            }
                        }
                        $db = $this->Database->prepare('DELETE FROM tl_c4g_reservation WHERE `id`=?')
                            ->execute($reservation['id']);
                    }
                }
            }
        }
    }
}
