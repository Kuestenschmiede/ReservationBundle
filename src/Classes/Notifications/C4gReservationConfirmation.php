<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @author con4gis contributors (see "authors.md")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

namespace con4gis\ReservationBundle\Classes\Notifications;
use con4gis\CoreBundle\Classes\C4GUtils;
use con4gis\CoreBundle\Resources\contao\models\C4gLogModel;
use con4gis\ProjectsBundle\Classes\Notifications\C4GNotification;
use con4gis\ReservationBundle\Classes\Models\C4gReservationEventAudienceModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationEventSpeakerModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationEventTopicModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationParamsModel;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationHandler;
use Contao\Controller;
use Contao\Database;
use Contao\MemberModel;
use Contao\StringUtil;

class C4gReservationConfirmation
{
    /**
     * @param int $reservationId
     */
    public static function sendNotification(int $reservationId)
    {
        \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('C4gReservationConfirmation', 'sendNotification called for ID: ' . $reservationId);
        $objReservation = \con4gis\ReservationBundle\Classes\Models\C4gReservationModel::findByPk($reservationId);
        if (!$objReservation) {
            \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('C4gReservationConfirmation', "Reservation ID $reservationId not found in database.");
            return;
        }
        $reservation = $objReservation->row();
        \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('C4gReservationConfirmation', "DB Data for ID $reservationId: " . json_encode($reservation));
        $reservationType = $reservation['reservation_type'];
        $reservationObjectType = $reservation['reservationObjectType'];
        
        $emailConfirmationSend = $reservation['emailConfirmationSend'];
        $email = $reservation['email'];
        
        \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('C4gReservationConfirmation', "Evaluating reservation ID $reservationId: email='$email', emailConfirmationSend='$emailConfirmationSend'");

        if ($reservationType && $email && !($emailConfirmationSend === '1')) {
            try {
                $database = Database::getInstance();
                $type = $database->prepare('SELECT * FROM tl_c4g_reservation_type WHERE `id`=? LIMIT 1')->execute($reservationType)->fetchAssoc();
                if ($type) {
                    $isSpecial = ($reservation['specialNotification'] === '1' || $reservation['specialNotification'] === 1 || $reservation['specialNotification'] === true);
                    $isConfirmed = ($reservation['confirmed'] === '1' || $reservation['confirmed'] === 1 || $reservation['confirmed'] === true);
                    \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('C4gReservationConfirmation', "Processing reservation ID $reservationId: reservationObjectType={$reservationObjectType}, specialNotification={$reservation['specialNotification']}, confirmed={$reservation['confirmed']}, isSpecial=" . ($isSpecial ? '1' : '0') . ", isConfirmed=" . ($isConfirmed ? '1' : '0'));
                    $notificationConfirmationType = StringUtil::deserialize($type['notification_confirmation_type']);
                    $notificationSpecialType = StringUtil::deserialize($type['notification_special_type']);
                    \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('C4gReservationConfirmation', "Notification IDs for ID $reservationId: special=" . (is_array($notificationSpecialType) ? implode(',', $notificationSpecialType) : var_export($notificationSpecialType, true)) . ", confirmation=" . (is_array($notificationConfirmationType) ? implode(',', $notificationConfirmationType) : var_export($notificationConfirmationType, true)) . ", raw_spec=" . var_export($type['notification_special_type'], true) . ", raw_conf=" . var_export($type['notification_confirmation_type'], true));

                    if (($reservationObjectType === '1') || ($reservationObjectType === '3') || ($reservationObjectType === 1) || ($reservationObjectType === 3)) {
                        $reservationObject = $database->prepare('SELECT * FROM tl_c4g_reservation_object WHERE `id`=? LIMIT 1')->execute($reservation['reservation_object'])->fetchAssoc();
                        \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('C4gReservationConfirmation', "Loaded reservationObject from tl_c4g_reservation_object (ID: {$reservation['reservation_object']}): " . ($reservationObject ? 'found' : 'not found'));
                    } else {
                        $reservationObject = $database->prepare('SELECT * FROM tl_calendar_events WHERE `id`=? LIMIT 1')->execute($reservation['reservation_object'])->fetchAssoc();
                        \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('C4gReservationConfirmation', "Loaded reservationObject from tl_calendar_events (ID: {$reservation['reservation_object']}): " . ($reservationObject ? 'found' : 'not found'));
                    }

                    $configuration = [
                        'con4gis_reservation_confirmation' => [
                            'admin_email', 'email', 'contact_email', 'contact_website', 'reservation_type', 'member_email',
                            'desiredCapacity', 'beginDate', 'beginTime', 'endDate', 'endTime', 'description', 'included_params',
                            'additional_params', 'participantList', 'speaker', 'topic', 'audience', 'conferenceLink', 'price',
                            'priceOptionSum', 'priceSum', 'dbkey', 'priceTax', 'priceNet', 'priceOptionSumTax', 'priceOptionSumNet',
                            'priceSumNet', 'priceSumTax', 'reservationTaxRate', 'salutation', 'title', 'organisation', 'firstname',
                            'lastname', 'phone', 'address', 'postal', 'city', 'dateOfBirth', 'salutation2', 'title2', 'organisation2',
                            'firstname2', 'lastname2', 'email2', 'phone2', 'address2', 'postal2', 'city2', 'comment', 'internal_comment',
                            'additional1', 'additional2', 'additional3', 'location', 'contact_name', 'contact_phone', 'contact_street',
                            'contact_postal', 'contact_city', 'reservation_id', 'agreed', 'discountPercent', 'discountCode',
                            'priceDiscount', 'documentId', 'uploadFile', 'icsFilename'
                        ]
                    ];
                    $c4gNotify = new C4GNotification($configuration);

                    $arrNotificationIds = [];
                    if ($isSpecial && $notificationSpecialType && (count($notificationSpecialType) > 0)) {
                        $arrNotificationIds = $notificationSpecialType;
                    } elseif ($isConfirmed && $notificationConfirmationType && (count($notificationConfirmationType) > 0)) {
                        $arrNotificationIds = $notificationConfirmationType;
                    } else {
                        \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('C4gReservationConfirmation', "No valid notification IDs found for reservation $reservationId. isSpecial: " . ($isSpecial ? 'true' : 'false') . ", count(notificationSpecialType): " . (is_array($notificationSpecialType) ? count($notificationSpecialType) : 'NaN') . ", isConfirmed: " . ($isConfirmed ? 'true' : 'false') . ", count(notificationConfirmationType): " . (is_array($notificationConfirmationType) ? count($notificationConfirmationType) : 'NaN'));
                    }

                    if ($c4gNotify && is_array($arrNotificationIds) && (count($arrNotificationIds) > 0) && $reservationObject) {
                        \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('C4gReservationConfirmation', 'Attempting to send notifications: ' . implode(',', $arrNotificationIds) . ' for reservation ' . $reservationId);
                        $c4gNotify->setOptionalTokens([
                            'admin_email', 'email', 'contact_email', 'contact_website', 'reservation_type', 'member_email',
                            'desiredCapacity', 'beginDate', 'beginTime', 'endDate', 'endTime', 'description', 'included_params',
                            'additional_params', 'participantList', 'speaker', 'topic', 'audience', 'conferenceLink', 'price',
                            'priceOptionSum', 'priceSum', 'dbkey', 'priceTax', 'priceNet', 'priceOptionSumTax', 'priceOptionSumNet',
                            'priceSumNet', 'priceSumTax', 'reservationTaxRate', 'salutation', 'title', 'organisation', 'firstname',
                            'lastname', 'phone', 'address', 'postal', 'city', 'dateOfBirth', 'salutation2', 'title2', 'organisation2',
                            'firstname2', 'lastname2', 'email2', 'phone2', 'address2', 'postal2', 'city2', 'comment', 'internal_comment',
                            'additional1', 'additional2', 'additional3', 'location', 'contact_name', 'contact_phone', 'contact_street',
                            'contact_postal', 'contact_city', 'reservation_id', 'agreed', 'discountPercent', 'discountCode',
                            'priceDiscount', 'documentId', 'uploadFile', 'reservation_object', 'reservation_title', 'reservation_type_id', 'icsFilename'
                        ]);
                        if (($reservationObjectType == '2') || ($reservationObjectType == 2)) {
                            $c4gNotify->setTokenValue('reservation_object', ($reservationObject['title'] ?? '') ?: ($reservationObject['caption'] ?? ''));
                            $c4gNotify->setTokenValue('reservation_title', ($reservationObject['title'] ?? '') ?: ($reservationObject['caption'] ?? ''));
                        } else {
                            $c4gNotify->setTokenValue('reservation_object', ($reservationObject['caption'] ?? '') ?: ($reservationObject['title'] ?? ''));
                            $c4gNotify->setTokenValue('reservation_title', ($reservationObject['caption'] ?? '') ?: ($reservationObject['title'] ?? ''));
                        }

                        $locationId = ($reservationObject['location'] ?? '') ?: ($type['location'] ?? '');
                        $location = false;
                        if ($locationId) {
                            $location = $database->prepare('SELECT * FROM tl_c4g_reservation_location WHERE `id`=? LIMIT 1')->execute($locationId)->fetchAssoc();
                        }

                        $organizerId = ($reservationObject['organizer'] ?? '') ?: '';
                        $organizer = false;
                        if ($organizerId) {
                            $organizer = $database->prepare('SELECT * FROM tl_c4g_reservation_location WHERE `id`=? LIMIT 1')->execute($organizerId)->fetchAssoc();
                        }

                        $adminEmail = ($reservation['admin_email'] ?? '') ?: (($organizer && ($organizer['admin_email'] ?? '')) ? $organizer['admin_email'] : (($location && ($location['admin_email'] ?? '')) ? $location['admin_email'] : (\Contao\Config::get('adminEmail') ?: ($GLOBALS['TL_CONFIG']['adminEmail'] ?? ''))));
                        if (!$adminEmail || (strpos($adminEmail, '@') === false)) {
                            $adminEmail = \Contao\Config::get('adminEmail') ?: ($GLOBALS['TL_CONFIG']['adminEmail'] ?? '');
                        }
                        
                        if (!$adminEmail || (strpos($adminEmail, '@') === false)) {
                            \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('C4gReservationConfirmation', "Error: No valid admin email found for reservation $reservationId. Skipping notification.");
                            return;
                        }
                        
                        $c4gNotify->setTokenValue('admin_email', $adminEmail);
                        $c4gNotify->setTokenValue('email', ($reservation['email'] ?? '') ?: ' ');

                        $c4gNotify->setTokenValue('contact_email', ($reservation['contact_email'] ?? '') ?: (($organizer && ($organizer['contact_email'] ?? '')) ? $organizer['contact_email'] : (($location && ($location['contact_email'] ?? '')) ? $location['contact_email'] : ' ')));
                        $c4gNotify->setTokenValue('contact_website', ($reservation['contact_website'] ?? '') ?: (($organizer && ($organizer['contact_website'] ?? '')) ? $organizer['contact_website'] : (($location && ($location['contact_website'] ?? '')) ? $location['contact_website'] : ' ')));

                        $c4gNotify->setTokenValue('reservation_type', ($type['caption'] ?? '') ?: ($type['name'] ?? ' '));
                        $c4gNotify->setTokenValue('reservation_type_id', $reservationType);

                        if (($reservationObjectType == '2') || ($reservationObjectType == 2)) {
                            $c4gNotify->setTokenValue('reservation_object', ($reservationObject['title'] ?? '') ?: (($reservationObject['caption'] ?? '') ?: ' '));
                            $c4gNotify->setTokenValue('reservation_title', ($reservationObject['title'] ?? '') ?: (($reservationObject['caption'] ?? '') ?: ' '));
                        } else {
                            $c4gNotify->setTokenValue('reservation_object', ($reservationObject['caption'] ?? '') ?: (($reservationObject['title'] ?? '') ?: ' '));
                            $c4gNotify->setTokenValue('reservation_title', ($reservationObject['caption'] ?? '') ?: (($reservationObject['title'] ?? '') ?: ' '));
                        }

                        $memberId = ($reservationObject['member_id'] ?? '') ?: ($reservation['member_id'] ?? '');
                        if ($memberId) {
                            $member = MemberModel::findByPk($memberId);
                            if ($member) {
                                $c4gNotify->setTokenValue('member_email', $member->email ?: ' ');
                            }
                        }

                        $c4gNotify->setTokenValue('desiredCapacity', ($reservation['desiredCapacity'] ?? '') ?: '');
                        foreach ($reservation as $key => $val) {
                            if (str_starts_with($key, 'desiredCapacity_')) {
                                $c4gNotify->setTokenValue('desiredCapacity', $val ?: '');
                                $c4gNotify->setTokenValue($key, $val ?: '');
                            }
                        }

                        // Map raw database fields to tokens if they are not already set
                        $fieldMap = [
                            'salutation', 'title', 'organisation', 'firstname', 'lastname',
                            'email', 'phone', 'address', 'postal', 'city', 'dateOfBirth',
                            'salutation2', 'title2', 'organisation2', 'firstname2', 'lastname2',
                            'email2', 'phone2', 'address2', 'postal2', 'city2',
                            'price', 'priceNet', 'priceTax', 'priceSum', 'priceSumNet', 'priceSumTax',
                            'priceDiscount', 'discountPercent', 'discountCode', 'priceOptionSum',
                            'priceOptionSumNet', 'priceOptionSumTax', 'reservationTaxRate',
                            'conferenceLink', 'speaker', 'topic', 'audience', 'internal_comment',
                            'included_params', 'additional_params', 'participant_params',
                            'documentId', 'dbkey', 'icsFilename'
                        ];

                        foreach ($fieldMap as $field) {
                            if (isset($reservation[$field]) && $reservation[$field] !== '') {
                                $c4gNotify->setTokenValue($field, $reservation[$field]);
                            }
                        }

                        // Also check if admin_email is set in the tokens, if not, set a default
                        if (!$c4gNotify->getTokenValue('admin_email')) {
                            $c4gNotify->setTokenValue('admin_email', $GLOBALS['TL_CONFIG']['adminEmail'] ?: 'info@kuestenschmiede.de');
                        }
                        
                        // Map calculated price and discount values explicitly if available in $reservation
                        $c4gNotify->setTokenValue('priceSum', !empty($reservation['priceSum']) ? $reservation['priceSum'] : ($reservation['priceSum_base'] ?? '0,00 €'));
                        $c4gNotify->setTokenValue('priceDiscount', (!empty($reservation['priceDiscount']) && $reservation['priceDiscount'] !== '0' && $reservation['priceDiscount'] !== 0) ? $reservation['priceDiscount'] : '0,00 €');
                        $c4gNotify->setTokenValue('discountPercent', (!empty($reservation['discountPercent']) && $reservation['discountPercent'] !== '0' && $reservation['discountPercent'] !== 0) ? $reservation['discountPercent'] : ' ');
                        $c4gNotify->setTokenValue('discountCode', (!empty($reservation['discountCode']) && $reservation['discountCode'] !== '0' && $reservation['discountCode'] !== 0) ? $reservation['discountCode'] : ' ');
                        
                        $c4gNotify->setTokenValue('price', !empty($reservation['price']) ? $reservation['price'] : ($reservation['price_base'] ?? ' '));
                        $c4gNotify->setTokenValue('priceNet', !empty($reservation['priceNet']) ? $reservation['priceNet'] : ($reservation['priceNet_base'] ?? ' '));
                        $c4gNotify->setTokenValue('priceTax', !empty($reservation['priceTax']) ? $reservation['priceTax'] : ($reservation['priceTax_base'] ?? ' '));
                        $c4gNotify->setTokenValue('priceSumNet', !empty($reservation['priceSumNet']) ? $reservation['priceSumNet'] : ($reservation['priceSumNet_base'] ?? ' '));
                        $c4gNotify->setTokenValue('priceSumTax', !empty($reservation['priceSumTax']) ? $reservation['priceSumTax'] : ($reservation['priceSumTax_base'] ?? ' '));
                        $c4gNotify->setTokenValue('reservationTaxRate', !empty($reservation['reservationTaxRate']) ? $reservation['reservationTaxRate'] : ($reservation['reservationTaxRate_base'] ?? ' '));
                        $c4gNotify->setTokenValue('priceOptionSum', !empty($reservation['priceOptionSum']) ? $reservation['priceOptionSum'] : ($reservation['priceOptionSum_base'] ?? '0,00 €'));
                        $c4gNotify->setTokenValue('priceOptionSumNet', !empty($reservation['priceOptionSumNet']) ? $reservation['priceOptionSumNet'] : ($reservation['priceOptionSumNet_base'] ?? ' '));
                        $c4gNotify->setTokenValue('priceOptionSumTax', !empty($reservation['priceOptionSumTax']) ? $reservation['priceOptionSumTax'] : ($reservation['priceOptionSumTax_base'] ?? ' '));
                        
                        $c4gNotify->setTokenValue('type', ($reservation['type'] ?? (($reservationType['name'] ?? '') ?: ' ')));
                        $c4gNotify->setTokenValue('object', ($reservation['object'] ?? (($reservationObject['caption'] ?? '') ?: ' ')));

                        $dateFormat = ($GLOBALS['TL_CONFIG']['dateFormat'] ?? '') ?: 'd.m.Y';
                        $timeFormat = ($GLOBALS['TL_CONFIG']['timeFormat'] ?? '') ?: 'H:i';
                        $c4gNotify->setTokenValue('beginDate', (($reservation['beginDate'] ?? '') !== '') ? date($dateFormat, (int)$reservation['beginDate']) : ' ');
                        $c4gNotify->setTokenValue('endDate', (($reservation['endDate'] ?? '') !== '') ? date($dateFormat, (int)$reservation['endDate']) : ' ');
                        
                        $beginTimeValue = $reservation['beginTime'] ?? 'NOTSET';
                        $beginTimeInt = (int)($reservation['beginTimeInt'] ?? (isset($reservation['beginTime']) && is_numeric($reservation['beginTime']) ? $reservation['beginTime'] : 0));
                        if (isset($reservation['beginTime']) && is_numeric($reservation['beginTime']) && (int)$reservation['beginTime'] === 0) {
                            $beginTimeInt = 0;
                        }
                        // FORCE 00:00 if beginTime is numeric 0
                        if (isset($reservation['beginTime']) && is_numeric($reservation['beginTime']) && (int)$reservation['beginTime'] === 0) {
                            $formattedBeginTime = "00:00";
                        } else {
                            $formattedBeginTime = '';
                            if (isset($reservation['beginTime']) && $reservation['beginTime'] !== '') {
                                if (is_numeric($reservation['beginTime'])) {
                                    if ((int)$reservation['beginTime'] % 86400 === 0) {
                                        $formattedBeginTime = "00:00";
                                    } else {
                                        $formattedBeginTime = date($timeFormat, strtotime('1970-01-01 ' . gmdate('H:i', (int)$reservation['beginTime'] % 86400) . ' UTC'));
                                        if (($formattedBeginTime === '01:00' || $formattedBeginTime === '1:00') && ((int)$reservation['beginTime'] % 86400 === 0)) {
                                            $formattedBeginTime = "00:00";
                                        }
                                    }
                                } else {
                                    $formattedBeginTime = $reservation['beginTime'];
                                    // If the system formatted it to 01:00 but it was likely 00:00
                                    if (($formattedBeginTime === '01:00' || $formattedBeginTime === '1:00') && ($beginTimeInt % 86400 === 0)) {
                                        $formattedBeginTime = "00:00";
                                    }
                                }
                            } else {
                                $formattedBeginTime = "00:00";
                            }
                            
                            // Last resort check for 01:00/1:00 if beginTimeInt is 0
                            if (($formattedBeginTime === '01:00' || $formattedBeginTime === '1:00') && $beginTimeInt === 0) {
                                $formattedBeginTime = "00:00";
                            }
                            
                            if ((int)($reservation['beginTime'] ?? -1) === 0) {
                                $formattedBeginTime = "00:00";
                            }
                        }
                        // file_put_contents('var/logs/debug_time.log', "ID: $reservationId | Final formattedBeginTime: $formattedBeginTime\n", FILE_APPEND);
                        if ($formattedBeginTime === '0' || $formattedBeginTime === 0) {
                            $formattedBeginTime = '00:00';
                        }
                        $c4gNotify->setTokenValue('beginTime', $formattedBeginTime);

                        $c4gNotify->setTokenValue('endDate', (($reservation['endDate'] ?? '') !== '') ? date($dateFormat, (int)$reservation['endDate']) : ' ');
                        $c4gNotify->setTokenValue('bookedAt', (($reservation['bookedAt'] ?? '') !== '') ? date($dateFormat . ' ' . $timeFormat, (int)$reservation['bookedAt']) : ' ');
                        $endTimeInt = (int)($reservation['endTimeInt'] ?? (isset($reservation['endTime']) && is_numeric($reservation['endTime']) ? $reservation['endTime'] : 0));
                        if (isset($reservation['endTime']) && is_numeric($reservation['endTime']) && (int)$reservation['endTime'] === 0) {
                            $endTimeInt = 0;
                        }
                        $formattedEndTime = '';
                        if (isset($reservation['endTime']) && $reservation['endTime'] !== '') {
                            if (is_numeric($reservation['endTime'])) {
                                if ((int)$reservation['endTime'] % 86400 === 0) {
                                    $formattedEndTime = "00:00";
                                } else {
                                    $formattedEndTime = date($timeFormat, strtotime('1970-01-01 ' . gmdate('H:i', (int)$reservation['endTime'] % 86400) . ' UTC'));
                                    if (($formattedEndTime === '01:00' || $formattedEndTime === '1:00') && ((int)$reservation['endTime'] % 86400 === 0)) {
                                        $formattedEndTime = "00:00";
                                    }
                                }
                            } else {
                                $formattedEndTime = $reservation['endTime'];
                                // If the system formatted it to 01:00 but it was likely 00:00
                                if (($formattedEndTime === '01:00' || $formattedEndTime === '1:00') && ($endTimeInt % 86400 === 0)) {
                                    $formattedEndTime = "00:00";
                                }
                            }
                        } else {
                            $formattedEndTime = "00:00";
                        }
                        
                        // Last resort check for 01:00/1:00 if endTimeInt is 0
                        if (($formattedEndTime === '01:00' || $formattedEndTime === '1:00') && $endTimeInt === 0) {
                            $formattedEndTime = "00:00";
                        }
                        
                        if ((int)($reservation['endTime'] ?? -1) === 0) {
                            $formattedEndTime = "00:00";
                        }

                        if ($formattedEndTime === '0' || $formattedEndTime === 0) {
                            $formattedEndTime = '00:00';
                        }
                        $c4gNotify->setTokenValue('endTime', $formattedEndTime);
                        
                        $c4gNotify->setTokenValue('priceSum', !empty($reservation['priceSum']) ? $reservation['priceSum'] : '0,00 €');
                        $c4gNotify->setTokenValue('priceDiscount', (!empty($reservation['priceDiscount']) && $reservation['priceDiscount'] !== '0' && $reservation['priceDiscount'] !== 0) ? $reservation['priceDiscount'] : '0,00 €');
                        $c4gNotify->setTokenValue('discountPercent', (!empty($reservation['discountPercent']) && $reservation['discountPercent'] !== '0' && $reservation['discountPercent'] !== 0) ? $reservation['discountPercent'] : ' ');
                        $c4gNotify->setTokenValue('discountCode', (!empty($reservation['discountCode']) && $reservation['discountCode'] !== '0' && $reservation['discountCode'] !== 0) ? $reservation['discountCode'] : ' ');

                        $c4gNotify->setTokenValue('description', (string)(($reservationObject['description'] ?? '') ?: (($reservationObject['details'] ?? '') ?: (($reservationObject['teaser'] ?? '') ?: ''))));

                        $params = $reservation['included_params'] ? \Contao\StringUtil::deserialize($reservation['included_params']) : [];
                        $includedParamsArr = [];
                        foreach ($params as $param) {
                            $includedParam = C4gReservationParamsModel::findByPk($param);
                            if ($includedParam && $includedParam->caption) {
                                $includedParamsArr[$param] = $includedParam->caption;
                            }
                        }
                        $c4gNotify->setTokenValue('included_params', implode(', ', $includedParamsArr) ?: ' ');

                        $params = $reservation['additional_params'] ? \Contao\StringUtil::deserialize($reservation['additional_params']) : [];
                        $additionalParamsArr = [];
                        foreach ($params as $param) {
                            $additionalParam = C4gReservationParamsModel::findByPk($param);
                            if ($additionalParam && $additionalParam->caption) {
                                $additionalParamsArr[$param] = $additionalParam->caption;
                            }
                        }
                        $c4gNotify->setTokenValue('additional_params', implode(', ', $additionalParamsArr) ?: ' ');

                        $participantsArr = [];
                        $participants = $database->prepare('SELECT * FROM tl_c4g_reservation_participants WHERE `pid`=?')->execute($reservation['id'])->fetchAllAssoc();
                        if ($participants && (count($participants) > 0)) {
                            foreach ($participants as $participant) {
                                $participantsArr[] = trim($participant['firstname'] . ' ' . $participant['lastname']) . ($participant['email'] ? ' (' . $participant['email'] . ')' : '');
                            }
                        }

                        $participants = '';
                        $count = 0;
                        foreach ($participantsArr as $val) {
                            $count++;
                            $participants .= $participants ? '; ' . $count . '. ' . $val : $count . '. ' . $val;
                        }

                        $c4gNotify->setTokenValue('participantList', $participants ?: ' ');

                        if ($reservationObjectType == '2') {
                            $calendarObject = $database->prepare('SELECT * FROM tl_calendar WHERE id=? AND activateEventReservation="1"')->execute($reservationObject['pid'])->fetchAssoc();
                            $eventObject = $database->prepare('SELECT * FROM tl_c4g_reservation_event WHERE `pid`=? LIMIT 1')->execute($reservation['reservation_object'])->fetchAssoc();
                            if ($eventObject || $calendarObject) {
                                $speaker = '';

                                if (($eventObject && $eventObject['speaker']) || ($calendarObject && $calendarObject['reservationSpeaker'])) {
                                    $speakers = $eventObject && $eventObject['speaker'] ? $eventObject['speaker'] : $calendarObject['reservationSpeaker'];
                                    $speakerList = StringUtil::deserialize($speakers);
                                    foreach ($speakerList as $speakerId) {
                                        $speakerObject = C4gReservationEventSpeakerModel::findByPk($speakerId);
                                        if ($speakerObject) {
                                            if ($speaker) {
                                                $speaker .= ', ' . $speakerObject->title ? $speakerObject->title . ' ' . $speakerObject->firstname . ' ' . $speakerObject->lastname : $speakerObject->firstname . ' ' . $speakerObject->lastname;

                                            } else {
                                                $speaker = $speakerObject->title ? $speakerObject->title . ' ' . $speakerObject->firstname . ' ' . $speakerObject->lastname : $speakerObject->firstname . ' ' . $speakerObject->lastname;
                                            }
                                        }
                                    }
                                }

                                $topic = '';
                                if (($eventObject && $eventObject['topic']) || ($calendarObject && $calendarObject['reservationTopic'])) {
                                    $topic = $eventObject && $eventObject['topic'] ? $eventObject['topic'] : $calendarObject['reservationTopic'];
                                    $topicList = StringUtil::deserialize($topic);
                                    foreach ($topicList as $topicId) {
                                        $topicObject = C4gReservationEventTopicModel::findByPk($topicId);
                                        if ($topicObject) {
                                            if ($topic) {
                                                $topic .= ', ' . $topicObject->topic;
                                            } else {
                                                $topic = $topicObject->topic;
                                            }

                                        }
                                    }
                                }

                                $audience = '';
                                if (($eventObject && $eventObject['targetAudience']) || ($calendarObject && $calendarObject['reservationtargetAudience'])) {
                                    $audiences = $eventObject && $eventObject['targetAudience'] ? $eventObject['targetAudience'] : $calendarObject['reservationtargetAudience'];
                                    $audienceList = StringUtil::deserialize($audiences);
                                    foreach ($audienceList as $audienceId) {
                                        $audienceObject = C4gReservationEventAudienceModel::findByPk($audienceId);
                                        if ($audienceObject) {
                                            if ($audience) {
                                                $audience .= ', ' . $audienceObject->targetAudience;
                                            } else {
                                                $audience = $audienceObject->targetAudience;
                                            }

                                        }
                                    }
                                }

                                $c4gNotify->setTokenValue('speaker', $speaker);
                                $c4gNotify->setTokenValue('topic', $topic);
                                $c4gNotify->setTokenValue('audience', $audience);

                            }

                        if ($eventObject && $eventObject['conferenceLink']) {
                            $c4gNotify->setTokenValue('conferenceLink', $eventObject['conferenceLink'] ?: ' ');
                        } else {
                            $c4gNotify->setTokenValue('conferenceLink', $reservation['conferenceLink'] ?? ' ');
                        }

                        $c4gNotify->setTokenValue('price', ($reservation['price'] ?? '') ?: '0,00 €');
                        $c4gNotify->setTokenValue('priceOptionSum', ($reservation['priceOptionSum'] ?? '') ?: '0,00 €');
                        $c4gNotify->setTokenValue('priceSum', ($reservation['priceSum'] ?? '') ?: '0,00 €');
                        //ToDo set dbkey and all price tokens
                        $c4gNotify->setTokenValue('dbkey', $reservation['id'] ? ($reservation['dbkey'] ?: $reservation['id']) : ' ');

                        $c4gNotify->setTokenValue('priceTax', ($reservation['priceTax'] ?? '') ?: '0,00 €');
                        $c4gNotify->setTokenValue('priceNet', ($reservation['priceNet'] ?? '') ?: '0,00 €');

                        $c4gNotify->setTokenValue('priceOptionSumTax', ($reservation['priceOptionSumTax'] ?? '') ?: '0,00 €');
                        $c4gNotify->setTokenValue('priceOptionSumNet', ($reservation['priceOptionSumNet'] ?? '') ?: '0,00 €');

                        $c4gNotify->setTokenValue('priceSumNet', ($reservation['priceSumNet'] ?? '') ?: '0,00 €');
                        $c4gNotify->setTokenValue('priceSumTax', ($reservation['priceSumTax'] ?? '') ?: '0,00 €');
                        }

                        $salutation = [
                            'man' => $GLOBALS['TL_LANG']['tl_c4g_reservation']['man'][0],
                            'woman' => $GLOBALS['TL_LANG']['tl_c4g_reservation']['woman'][0],
                            'various' => $GLOBALS['TL_LANG']['tl_c4g_reservation']['various'][0],
                        ];
                        $c4gNotify->setTokenValue('salutation', ($reservation['salutation'] && ($salutation[$reservation['salutation']] ?? '')) ? $salutation[$reservation['salutation']] : ($reservation['salutation'] ?? ' '));
                        $c4gNotify->setTokenValue('title', ($reservation['title'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('organisation', ($reservation['organisation'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('firstname', ($reservation['firstname'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('lastname', ($reservation['lastname'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('email', ($reservation['email'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('phone', ($reservation['phone'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('address', ($reservation['address'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('postal', ($reservation['postal'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('city', ($reservation['city'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('dateOfBirth', ($reservation['dateOfBirth'] && is_numeric($reservation['dateOfBirth'])) ? date($dateFormat, $reservation['dateOfBirth']) : (($reservation['dateOfBirth'] ?? '') ?: ' '));
                        $c4gNotify->setTokenValue('salutation2', ($reservation['salutation2'] && ($salutation[$reservation['salutation2']] ?? '')) ? $salutation[$reservation['salutation2']] : ($reservation['salutation2'] ?? ' '));
                        $c4gNotify->setTokenValue('title2', ($reservation['title2'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('organisation2', ($reservation['organisation2'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('firstname2', ($reservation['firstname2'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('lastname2', ($reservation['lastname2'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('email2', ($reservation['email2'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('phone2', ($reservation['phone2'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('address2', ($reservation['address2'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('postal2', ($reservation['postal2'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('city2', ($reservation['city2'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('additional1', ($reservation['additional1'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('additional2', ($reservation['additional2'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('additional3', ($reservation['additional3'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('comment', ($reservation['comment'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('internal_comment', ($reservation['internal_comment'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('participant_params', ($reservation['participant_params'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('included_params', ($reservation['included_params'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('additional_params', ($reservation['additional_params'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('conferenceLink', ($reservation['conferenceLink'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('speaker', ($reservation['speaker'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('topic', ($reservation['topic'] ?? '') ?: ' ');
                        $c4gNotify->setTokenValue('audience', ($reservation['audience'] ?? '') ?: ' ');
 
                        $c4gNotify->setTokenValue('location', ($reservation['location'] ?? '') ?: (($location['name'] ?? '') ?: ' '));
                        $c4gNotify->setTokenValue('contact_name', ($reservation['contact_name'] ?? '') ?: (($location['contact_name'] ?? '') ?: ' '));
                        $c4gNotify->setTokenValue('contact_phone', ($reservation['contact_phone'] ?? '') ?: (($location['contact_phone'] ?? '') ?: ' '));
                        $c4gNotify->setTokenValue('contact_street', ($reservation['contact_street'] ?? '') ?: (($location['contact_street'] ?? '') ?: ' '));
                        $c4gNotify->setTokenValue('contact_postal', ($reservation['contact_postal'] ?? '') ?: (($location['contact_postal'] ?? '') ?: ' '));
                        $c4gNotify->setTokenValue('contact_city', ($reservation['contact_city'] ?? '') ?: (($location['contact_city'] ?? '') ?: ' '));

                        $c4gNotify->setTokenValue('reservation_id', (string)$reservationId);
                        $c4gNotify->setTokenValue('reservationId', (string)$reservationId);
                        $c4gNotify->setTokenValue('id', (string)$reservationId);
                        $c4gNotify->setTokenValue('agreed', $reservation['agreed']);

                        $c4gNotify->setTokenValue('price', ($reservation['price'] ?? '') ?: '0,00 €');
                        $c4gNotify->setTokenValue('priceOptionSum', ($reservation['priceOptionSum'] ?? '') ?: '0,00 €');
                        $c4gNotify->setTokenValue('priceSum', ($reservation['priceSum'] ?? '') ?: '0,00 €');

                        $c4gNotify->setTokenValue('discountPercent', ($reservation['discountPercent'] ?? '') ?: (($eventObject['discountPercent'] ?? '') ?: ' '));
                        $c4gNotify->setTokenValue('discountCode', ($reservation['discountCode'] ?? '') ?: (($eventObject['discountCode'] ?? '') ?: ' '));
                        $c4gNotify->setTokenValue('priceDiscount', ($reservation['priceDiscount'] ?? '') ?: '0,00 €');
                        $c4gNotify->setTokenValue('reservationTaxRate', ($reservation['reservationTaxRate'] ?? '') ?: ' ');

                        if ($reservationObject && ($reservationObject['documentId'] ?? '')) {
                            $c4gNotify->setTokenValue('documentId', ($reservation['documentId'] ?? '') ?: ' ');
                        }

                        $c4gNotify->setTokenValue('priceTax', ($reservation['priceTax'] ?? '') ?: '0,00 €');
                        $c4gNotify->setTokenValue('priceNet', ($reservation['priceNet'] ?? '') ?: '0,00 €');

                        $c4gNotify->setTokenValue('priceOptionSumTax', ($reservation['priceOptionSumTax'] ?? '') ?: '0,00 €');
                        $c4gNotify->setTokenValue('priceOptionSumNet', ($reservation['priceOptionSumNet'] ?? '') ?: '0,00 €');

                        $c4gNotify->setTokenValue('priceSumNet', ($reservation['priceSumNet'] ?? '') ?: '0,00 €');
                        $c4gNotify->setTokenValue('priceSumTax', ($reservation['priceSumTax'] ?? '') ?: '0,00 €');

                        $binFileUuid = $reservation['fileUpload'];
                        $filePath = '';
                        if ($binFileUuid) {
                            $filePath = $binFileUuid ? StringUtil::binToUuid($binFileUuid) : '';
                        }

                        if ($filePath) {
                          $c4gNotify->setTokenValue('uploadFile', $filePath);
                        }

                        $c4gNotify->setOptionalTokens(
                            [
                                'contact_email','contact_website','desiredCapacity', 'endDate', 'endTime', 'included_params', 'additional_params', 'participantList', 'speaker', 'topic',
                                'audience', 'salutation', 'title', 'organisation', 'phone', 'address', 'postal', 'city', 'dateOfBirth', 'salutation2', 'title2', 'organisation2',
                                'firstname2', 'lastname2', 'email2', 'phone2', 'address2', 'postal2', 'city2', 'comment', 'internal_comment', 'location', 'contact_name',
                                'contact_phone', 'contact_street', 'contact_postal', 'contact_city', 'uploadFile', 'pdfnc_attachment', 'pdfnc_document', 'reservation_id', 'reservationId', 'id', 'agreed',
                                'description', 'additional1', 'additional2', 'additional3','member_email', 'conferenceLink',
                                'price','priceTax','priceSum','priceSumTax','priceNet','priceSumNet', 'priceOptionSum', 'priceOptionSumNet', 'priceOptionSumTax',
                                'priceDiscount', 'discountCode', 'discountPercent', 'documentId', 'reservationTaxRate', 'dbkey', 'reservation_type_id', 'icsFilename'
                            ]
                        );

                        $sendingResult = $c4gNotify->send($arrNotificationIds);
                        \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('C4gReservationConfirmation', "Sending result for reservation $reservationId: " . ($sendingResult ? 'success' : 'failure'));
                        if ($sendingResult) {
                            $database->prepare("UPDATE tl_c4g_reservation SET emailConfirmationSend='1' WHERE `id`=?")->execute($reservationId);
                        }
                    } else {
                        \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('C4gReservationConfirmation', "Notification could NOT be prepared for reservation $reservationId. Possible reasons: c4gNotify empty, no notification IDs found, or reservationObject missing. notificationIds: " . (is_array($arrNotificationIds) ? implode(',', $arrNotificationIds) : 'not an array') . ", reservationObject found: " . ($reservationObject ? 'yes' : 'no'));
                    }
                } else {
                    \con4gis\CoreBundle\Resources\contao\models\C4gLogModel::addLogEntry('C4gReservationConfirmation', "Reservation type not found for ID: $reservationType");
                }
            } catch (\Throwable $exception) {
                C4gLogModel::addLogEntry('reservation', $exception->getMessage());
            }
        }
    }
}
