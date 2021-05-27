<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 8
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2021, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

namespace con4gis\ReservationBundle\Classes;


use con4gis\CoreBundle\Resources\contao\models\C4gLogModel;
use con4gis\ProjectsBundle\Classes\Notifications\C4GNotification;
use con4gis\ReservationBundle\Resources\contao\models\C4GReservationParamsModel;
use Contao\Controller;
use Contao\Database;
use Contao\StringUtil;

class C4gReservationConfirmation
{
    /**
     * @param int $reservationId
     */
    public static function sendNotification(int $reservationId) {
        $database = Database::getInstance();
        $reservation = $database->prepare("SELECT * FROM tl_c4g_reservation WHERE id=? LIMIT 1")->execute($reservationId)->fetchAssoc();
        $reservationType = $reservation ? $reservation['reservation_type'] : false;
        $reservationObjectType = $reservation ? $reservation['reservationObjectType'] : false;
        if ($reservationType &&  $reservation['email'] && !($reservation['emailConfirmationSend'])) {
            try {
                $type = $database->prepare("SELECT * FROM tl_c4g_reservation_type WHERE id=? LIMIT 1")->execute($reservationType)->fetchAssoc();
                if ($type) {
                    if ($type['location']) {
                        $location = $database->prepare("SELECT * FROM tl_c4g_reservation_location WHERE id=? LIMIT 1")->execute($type['location'])->fetchAssoc();
                    }

                    if ($reservationObjectType === '1') {
                        $reservationObject = $database->prepare("SELECT * FROM tl_c4g_reservation_object WHERE id=? LIMIT 1")->execute($reservation['reservation_object'])->fetchAssoc();
                    } else {
                        $reservationObject = $database->prepare("SELECT * FROM tl_calendar_events WHERE id=? LIMIT 1")->execute($reservation['reservation_object'])->fetchAssoc();
                    }

                    $notificationConifrmationType = StringUtil::deserialize($type['notification_confirmation_type']);
                    $notificationSpecialType = StringUtil::deserialize($type['notification_special_type']);

                    $configuration = $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['con4gis_reservation_bundle']['con4gis_reservation_confirmation'];
                    $c4gNotify = $configuration ? new C4GNotification($configuration) : false;

                    $arrNotificationIds = [];
                    if ($reservation['specialNotification'] && $notificationSpecialType && (count($notificationSpecialType) > 0)) {
                        $arrNotificationIds = $notificationSpecialType;
                    } else if ($reservation['confirmed'] && $notificationConifrmationType && (count($notificationConifrmationType) > 0)) {
                        $arrNotificationIds = $notificationConifrmationType;
                    }

                    if ($c4gNotify && is_array($arrNotificationIds) && (count($arrNotificationIds) > 0) && $reservationObject) {

                        if ($reservationObjectType == '2') {
                            $c4gNotify->setTokenValue('reservation_object', $reservationObject['title'] ? $reservationObject['title'] : '');
                        } else {
                            $c4gNotify->setTokenValue('reservation_object', $reservationObject['caption'] ? $reservationObject['caption'] : '');
                        }

                        $c4gNotify->setTokenValue('admin_email', $GLOBALS['TL_CONFIG']['adminEmail']);
                        $c4gNotify->setTokenValue('email', $reservation['email']);
                        $c4gNotify->setTokenValue('contact_email', $location && $location['contact_email'] ? $location['contact_email'] : false);
                        $c4gNotify->setTokenValue('reservation_type', $type['caption'] ? $type['caption'] : '');

                        $c4gNotify->setTokenValue('desiredCapacity', $reservation['desiredCapacity'] ? $reservation['desiredCapacity'] : '');

                        $dateFormat = $GLOBALS['TL_CONFIG']['dateFormat'];
                        //$datimFormat = $GLOBALS['TL_CONFIG']['datimFormat'];
                        $timeFormat = $GLOBALS['TL_CONFIG']['timeFormat'];
                        $c4gNotify->setTokenValue('beginDate', $reservation['beginDate'] ? date($dateFormat, $reservation['beginDate']) : '');
                        $c4gNotify->setTokenValue('beginTime', $reservation['beginTime'] ? date($timeFormat, $reservation['beginTime']) : '');
                        $c4gNotify->setTokenValue('endDate', $reservation['endDate'] ? date($dateFormat, $reservation['endDate']) : '');
                        $c4gNotify->setTokenValue('endTime', $reservation['endTime'] ? date($timeFormat, $reservation['endTime']) : '');

                        $params = $reservation['included_params'] ? unserialize($reservation['included_params']) : [];
                        $includedParamsArr = [];
                        foreach ($params as $param) {
                            $includedParam = C4gReservationParamsModel::findByPk($param);
                            if ($includedParam && $includedParam->caption) {
                                $includedParamsArr[$param] = $includedParam->caption;
                            }
                        }
                        $c4gNotify->setTokenValue('included_params', implode($includedParamsArr));

                        $params = $reservation['additional_params'] ? unserialize($reservation['additional_params']) : [];
                        $additionalParamsArr = [];
                        foreach ($params as $param) {
                            $additionalParam = C4gReservationParamsModel::findByPk($param);
                            if ($additionalParam && $additionalParam->caption) {
                                $additionalParamsArr[$param] = $additionalParam->caption;
                            }
                        }
                        $c4gNotify->setTokenValue('additional_params', implode($additionalParamsArr));

                        $participantsArr = [];
                        $participants = $database->prepare("SELECT * FROM tl_c4g_reservation_participants WHERE pid=?")->execute($reservation['id'])->fetchAllAssoc();
                        if ($participants && (count($participants) > 0)) {
                            foreach ($participants as $participant) {
                                //$paramCaption = C4gReservationParamsModel::findByPk($paramId)->caption;
                                $participantsArr[] = [$participant['firstname'], $participant['lastname'], $participant['email']];
                            }
                        }

                        $participants = '';
                        foreach ($participantsArr as $participantkey => $valueArray) {
                            $participants .= $participants ? '; ' . $participantkey . ': ' . trim(implode(', ', $valueArray)) : $participantkey . ': ' . trim(implode(', ', $valueArray));
                        }

                        $c4gNotify->setTokenValue('participantList', $participants);
                        $c4gNotify->setTokenValue('speaker', 'ToDo');
                        $c4gNotify->setTokenValue('topic', 'ToDo');
                        $c4gNotify->setTokenValue('audience', 'ToDo');

                        $salutation = array(
                            'man' => $GLOBALS['TL_LANG']['tl_c4g_reservation']['man'][0],
                            'woman' => $GLOBALS['TL_LANG']['tl_c4g_reservation']['woman'][0],
                            'various' => $GLOBALS['TL_LANG']['tl_c4g_reservation']['various'][0]
                        );
                        $c4gNotify->setTokenValue('salutation', $reservation['salutation'] ? $salutation[$reservation['salutation']] : '');
                        $c4gNotify->setTokenValue('title', $reservation['title'] ? $reservation['title'] : '');
                        $c4gNotify->setTokenValue('organisation', $reservation['organisation'] ? $reservation['organisation'] : '');
                        $c4gNotify->setTokenValue('firstname', $reservation['firstname'] ? $reservation['firstname'] : '');
                        $c4gNotify->setTokenValue('lastname', $reservation['lastname'] ? $reservation['lastname'] : '');
                        $c4gNotify->setTokenValue('phone', $reservation['phone'] ? $reservation['phone'] : '');
                        $c4gNotify->setTokenValue('address', $reservation['address'] ? $reservation['address'] : '');
                        $c4gNotify->setTokenValue('postal', $reservation['postal'] ? $reservation['postal'] : '');
                        $c4gNotify->setTokenValue('city', $reservation['city'] ? $reservation['city'] : '');
                        $c4gNotify->setTokenValue('dateOfBirth', $reservation['dateOfBirth'] ? date($dateFormat, $reservation['dateOfBirth']) : '');
                        $c4gNotify->setTokenValue('salutation2', $reservation['salutation2'] ? $salutation[$reservation['salutation2']] : '');
                        $c4gNotify->setTokenValue('title2', $reservation['title2'] ? $reservation['title2'] : '');
                        $c4gNotify->setTokenValue('organisation2', $reservation['organisation2'] ? $reservation['organisation2'] : '');
                        $c4gNotify->setTokenValue('firstname2', $reservation['firstname2'] ? $reservation['firstname2'] : '');
                        $c4gNotify->setTokenValue('lastname2', $reservation['lastname2'] ? $reservation['lastname2'] : '');
                        $c4gNotify->setTokenValue('email2', $reservation['email2'] ? $reservation['email2'] : '');
                        $c4gNotify->setTokenValue('phone2', $reservation['phone2'] ? $reservation['phone2'] : '');
                        $c4gNotify->setTokenValue('address2', $reservation['address2'] ? $reservation['address2'] : '');
                        $c4gNotify->setTokenValue('postal2', $reservation['postal2'] ? $reservation['postal2'] : '');
                        $c4gNotify->setTokenValue('city2', $reservation['city2'] ? $reservation['city2'] : '');
                        $c4gNotify->setTokenValue('comment', $reservation['comment'] ? unserialize($reservation['comment']) : '');
                        $c4gNotify->setTokenValue('internal_comment', $reservation['internal_comment'] ? unserialize($reservation['internal_comment']) : '');

                        $c4gNotify->setTokenValue('location', $location ? $location['name'] : '');
                        $c4gNotify->setTokenValue('contact_name', $location ? $location['contact_name'] : '');
                        $c4gNotify->setTokenValue('contact_phone', $location ? $location['contact_phone'] : '');
                        $c4gNotify->setTokenValue('contact_street', $location ? $location['contact_street'] : '');
                        $c4gNotify->setTokenValue('contact_postal', $location ? $location['contact_postal'] : '');
                        $c4gNotify->setTokenValue('contact_city', $location ? $location['contact_city'] : '');

                        $c4gNotify->setTokenValue('reservation_id', $reservation['reservation_id']);
                        $c4gNotify->setTokenValue('agreed', $reservation['agreed']);


                        $binFileUuid = $reservation['fileUpload'];
                        $filePath = '';
                        if ($binFileUuid) {
                            $filePath = Controller::replaceInsertTags("{{file::$binFileUuid}}");
                            if (!$filePath) {
                                C4gLogModel::addLogEntry('reservation', 'File not found: '.$binFileUuid.'. Try again in 5 seconds ...');
                                sleep(5);
                                $filePath = Controller::replaceInsertTags("{{file::$binFileUuid}}");
                                if (!$filePath) {
                                    C4gLogModel::addLogEntry('reservation', 'File not found again.: ' . $binFileUuid . '. Why?');
                                } else {
                                    C4gLogModel::addLogEntry('reservation', 'This time the file was there: ' . $binFileUuid . '.');
                                }
                            }
                        }

                        $c4gNotify->setTokenValue('uploadFile', $filePath);


                        $c4gNotify->setOptionalTokens(
                            [
                                'contact_email','desiredCapacity', 'endDate', 'endTime', 'included_params', 'additional_params', 'participantList', 'speaker', 'topic',
                                'audience', 'salutation', 'title', 'organisation', 'phone', 'address', 'postal', 'city', 'dateOfBirth', 'salutation2', 'title2', 'organisation2',
                                'firstname2', 'lastname2', 'email2', 'phone2', 'address2', 'postal2', 'city2', 'comment', 'internal_comment', 'location', 'contact_name',
                                'contact_phone', 'contact_street', 'contact_postal', 'contact_city', 'uploadFile', 'pdfnc_attachment', 'pdfnc_document', 'reservation_id', 'agreed'
                            ]
                        );

                        $sendingResult = $c4gNotify->send($arrNotificationIds);
                        if ($sendingResult) {
                            $database->prepare("UPDATE tl_c4g_reservation SET emailConfirmationSend='1' WHERE id=?")->execute($reservationId);
                        }
                    }
                }
            } catch (\Throwable $exception) {
                C4gLogModel::addLogEntry('reservation', $exception->getMessage());
            }
        }
    }
}