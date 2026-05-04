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

#[AsCronjob('minutely')]
class CronMinutely
{
    //send confirmation emails if defined
    public function __invoke(): void
    {
        try {
            error_log('con4gis Reservation Cron: onMinutely started');
            if (class_exists('\con4gis\CoreBundle\Resources\contao\models\C4gLogModel')) {
                \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('Cron', 'CronMinutely started via __invoke');
            }
            
            \Contao\System::loadLanguageFile('tl_c4g_reservation');
            $database = \Contao\Database::getInstance();
            $db = $database->prepare('SELECT id, auto_send FROM tl_c4g_reservation_type ')
                ->execute()->fetchAllAssoc();
            
            \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('Cron', "Found " . count($db) . " reservation types");

            if (empty($db)) {
                \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('Cron', 'No reservation types found in tl_c4g_reservation_type');
            }

            foreach ($db as $entry) {
                $auto_send = $entry['auto_send'];
                \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('Cron', "Processing type ID {$entry['id']} with auto_send '$auto_send'");

                if ($auto_send === 'minutely' || $auto_send === '1' || $auto_send === 1) {
                    \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('Cron', "Type ID {$entry['id']} has auto_send enabled, searching for pending reservations...");
                    $reservations = $database->prepare("SELECT id, confirmed, specialNotification, emailConfirmationSend FROM tl_c4g_reservation where `reservation_type` = ? AND (`confirmed` = '1' OR `specialNotification` = '1') AND (emailConfirmationSend IS NULL OR emailConfirmationSend = '' OR emailConfirmationSend = '0')")
                        ->execute($entry['id'])->fetchAllAssoc();

                    if (empty($reservations)) {
                        \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('Cron', "No pending reservations found for type ID {$entry['id']}");
                    }

                    foreach ($reservations as $reservation) {
                        \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('Cron', "Found pending reservation ID {$reservation['id']} for type ID {$entry['id']}");
                        \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('Cron', "Calling sendNotification for reservation ID {$reservation['id']}");
                        \con4gis\ReservationBundle\Classes\Notifications\C4gReservationConfirmation::sendNotification(intval($reservation['id']));
                        sleep(2); // reduced buffer
                    }
                } else {
                    \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('Cron', "Type ID {$entry['id']} has auto_send NOT enabled (current value: '$auto_send')");
                }
            }
        } catch (\Throwable $e) {
            \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('Cron', 'Error in CronMinutely: ' . $e->getMessage());
        }
    }
}

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
