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

                if ($auto_send === 'minutely' || $auto_send === '1' || $auto_send === 1 || $auto_send === 'true') {
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
