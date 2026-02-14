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
        $database = Database::getInstance();
        $reservation = $database->prepare('SELECT * FROM tl_c4g_reservation WHERE `id`=? LIMIT 1')->execute($reservationId)->fetchAssoc();
        $reservationType = $reservation ? $reservation['reservation_type'] : false;
        $reservationObjectType = $reservation ? $reservation['reservationObjectType'] : false;
        if ($reservationType && $reservation['email'] && !($reservation['emailConfirmationSend'])) {
            try {
                $type = $database->prepare('SELECT * FROM tl_c4g_reservation_type WHERE `id`=? LIMIT 1')->execute($reservationType)->fetchAssoc();
                if ($type) {
                    if (($reservationObjectType === '1') || ($reservationObjectType === '3')) {
                        $reservationObject = $database->prepare('SELECT * FROM tl_c4g_reservation_object WHERE `id`=? LIMIT 1')->execute($reservation['reservation_object'])->fetchAssoc();
                    } else {
                        $reservationObject = $database->prepare('SELECT * FROM tl_calendar_events WHERE `id`=? LIMIT 1')->execute($reservation['reservation_object'])->fetchAssoc();
                    }

                    $notificationConfirmationType = StringUtil::deserialize($type['notification_confirmation_type']);
                    $notificationSpecialType = StringUtil::deserialize($type['notification_special_type']);

                    $configuration = $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['con4gis_reservation_bundle']['con4gis_reservation_confirmation'];
                    $c4gNotify = $configuration ? new C4GNotification($configuration) : false;

                    $arrNotificationIds = [];
                    if ($reservation['specialNotification'] && $notificationSpecialType && (count($notificationSpecialType) > 0)) {
                        $arrNotificationIds = $notificationSpecialType;
                    } elseif ($reservation['confirmed'] && $notificationConfirmationType && (count($notificationConfirmationType) > 0)) {
                        $arrNotificationIds = $notificationConfirmationType;
                    }

                    if ($c4gNotify && is_array($arrNotificationIds) && (count($arrNotificationIds) > 0) && $reservationObject) {
                        if ($reservationObjectType == '2') {
                            $c4gNotify->setTokenValue('reservation_object', $reservationObject['title'] ? $reservationObject['title'] : '');
                            $c4gNotify->setTokenValue('reservation_title', $reservationObject['title'] ? $reservationObject['title'] : '');
                        } else {
                            $c4gNotify->setTokenValue('reservation_object', $reservationObject['caption'] ? $reservationObject['caption'] : '');
                            $c4gNotify->setTokenValue('reservation_title', $reservationObject['caption'] ? $reservationObject['caption'] : '');
                        }

                        $locationId = $reservationObject['location'] ?: $type['location'];
                        $location = false;
                        if ($locationId) {
                            $location = $database->prepare('SELECT * FROM tl_c4g_reservation_location WHERE `id`=? LIMIT 1')->execute($locationId)->fetchAssoc();
                        }

                        $organizerId = $reservationObject['organizer'];
                        $organizer = false;
                        if ($organizerId) {
                            $organizer = $database->prepare('SELECT * FROM tl_c4g_reservation_location WHERE `id`=? LIMIT 1')->execute($organizerId)->fetchAssoc();
                        }

                        $c4gNotify->setTokenValue('admin_email', $GLOBALS['TL_CONFIG']['adminEmail']);
                        $c4gNotify->setTokenValue('email', $reservation['email']);

                        if ($organizer) {
                            $c4gNotify->setTokenValue('contact_email', $organizer && $organizer['contact_email'] ? $organizer['contact_email'] : false);
                            $c4gNotify->setTokenValue('contact_website', $organizer && $organizer['contact_website'] ? $organizer['contact_website'] : false);
                        } else {
                            $c4gNotify->setTokenValue('contact_email', $location && $location['contact_email'] ? $location['contact_email'] : false);
                            $c4gNotify->setTokenValue('contact_website', $organizer && $organizer['contact_website'] ? $organizer['contact_website'] : false);
                        }

                        $c4gNotify->setTokenValue('reservation_type', $type['caption'] ? $type['caption'] : '');

                        $memberId = $reservationObject['member_id'] ?: $reservation['member_id'];
                        if ($memberId) {
                            $member = MemberModel::findByPk($memberId);
                            $c4gNotify->setTokenValue('member_email', $member->email);
                        }

                        $c4gNotify->setTokenValue('desiredCapacity', $reservation['desiredCapacity'] ? $reservation['desiredCapacity'] : '');

                        $dateFormat = $GLOBALS['TL_CONFIG']['dateFormat'];
                        //$datimFormat = $GLOBALS['TL_CONFIG']['datimFormat'];
                        $timeFormat = $GLOBALS['TL_CONFIG']['timeFormat'];
                        $c4gNotify->setTokenValue('beginDate', $reservation['beginDate'] ? date($dateFormat, $reservation['beginDate']) : '');
                        $c4gNotify->setTokenValue('beginTime', $reservation['beginTime'] ? date($timeFormat, $reservation['beginTime']) : '');
                        $c4gNotify->setTokenValue('endDate', $reservation['endDate'] ? date($dateFormat, $reservation['endDate']) : '');
                        $c4gNotify->setTokenValue('endTime', $reservation['endTime'] ? date($timeFormat, $reservation['endTime']) : '');

                        $c4gNotify->setTokenValue('description', $reservationObject['description'] ?: ($reservationObject['details'] ?: ''));

                        $params = $reservation['included_params'] ? \Contao\StringUtil::deserialize($reservation['included_params']) : [];
                        $includedParamsArr = [];
                        foreach ($params as $param) {
                            $includedParam = C4gReservationParamsModel::findByPk($param);
                            if ($includedParam && $includedParam->caption) {
                                $includedParamsArr[$param] = $includedParam->caption;
                            }
                        }
                        $c4gNotify->setTokenValue('included_params', implode($includedParamsArr));

                        $params = $reservation['additional_params'] ? \Contao\StringUtil::deserialize($reservation['additional_params']) : [];
                        $additionalParamsArr = [];
                        foreach ($params as $param) {
                            $additionalParam = C4gReservationParamsModel::findByPk($param);
                            if ($additionalParam && $additionalParam->caption) {
                                $additionalParamsArr[$param] = $additionalParam->caption;
                            }
                        }
                        $c4gNotify->setTokenValue('additional_params', implode($additionalParamsArr));

                        $participantsArr = [];
                        $participants = $database->prepare('SELECT * FROM tl_c4g_reservation_participants WHERE `pid`=?')->execute($reservation['id'])->fetchAllAssoc();
                        if ($participants && (count($participants) > 0)) {
                            foreach ($participants as $participant) {
                                //$paramCaption = C4gReservationParamsModel::findByPk($paramId)->caption;
                                $participantsArr[] = [$participant['lastname'], $participant['firstname'], $participant['email']];
                            }
                        }

                        $participants = '';
                        $count = 0;
                        foreach ($participantsArr as $participantkey => $valueArray) {
                            $count++;
                            $participants .= $participants ? '; ' . $count . '. ' . trim(implode(', ', $valueArray)) : $count . '. ' . trim(implode(', ', $valueArray));
                        }

                        $c4gNotify->setTokenValue('participantList', $participants);

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
                                if (($eventObject && $eventObject['targetAudience']) || ($calendarObject && $calendarObject['reservationTargetAudience'])) {
                                    $audiences = $eventObject && $eventObject['targetAudience'] ? $eventObject['targetAudience'] : $calendarObject['reservationTargetAudience'];
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
                                $c4gNotify->setTokenValue('conferenceLink', $eventObject['conferenceLink'] ?: '');
                            }

                            if (($eventObject && $eventObject['price']) || ($calendarObject && $calendarObject['reservationPrice'])) {
                                $price = C4gReservationHandler::formatPrice($eventObject && $eventObject['price'] ? $eventObject['price'] : $calendarObject['reservationPrice']);
                                $priceOptionSum = C4gReservationHandler::formatPrice($eventObject && $eventObject['priceOptionSum']);
                                $priceSum = C4gReservationHandler::formatPrice($eventObject && $eventObject['priceSum']);
                                $c4gNotify->setTokenValue('price', $price);
                                $c4gNotify->setTokenValue('priceOptionSum', $priceOptionSum);
                                $c4gNotify->setTokenValue('priceSum', $priceSum);
                            }
                            //ToDo set dbkey and all price tokens
                            $c4gNotify->setTokenValue('dbkey', $reservation['id'] ? $reservation['dbkey'] : '');

                            if ($eventObject && $eventObject['priceSumTax']) {
                                $c4gNotify->setTokenValue('priceTax', $reservation['priceTax'] ? $reservation['priceTax'] : '');
                                $c4gNotify->setTokenValue('priceNet', $reservation['priceNet'] ? $reservation['priceNet'] : '');

                                $c4gNotify->setTokenValue('priceOptionSumTax', $reservation['priceOptionSumTax'] ? $reservation['priceOptionSumTax'] : '');
                                $c4gNotify->setTokenValue('priceOptionSumNet', $reservation['priceOptionSumNet'] ? $reservation['priceOptionSumNet'] : '');

                                $c4gNotify->setTokenValue('priceSumNet', $reservation['priceSumNet'] ? $reservation['priceSumNet'] : '');
                                $c4gNotify->setTokenValue('priceSumTax', $reservation['priceSumTax'] ? $reservation['priceSumTax'] : '');

                                $c4gNotify->setTokenValue('reservationTaxRate', $reservation['reservationTaxRate'] ? $reservation['reservationTaxRate'] : '');
                            }
                        }

                        $salutation = [
                            'man' => $GLOBALS['TL_LANG']['tl_c4g_reservation']['man'][0],
                            'woman' => $GLOBALS['TL_LANG']['tl_c4g_reservation']['woman'][0],
                            'various' => $GLOBALS['TL_LANG']['tl_c4g_reservation']['various'][0],
                        ];
                        $c4gNotify->setTokenValue('salutation', $reservation['salutation'] && $salutation[$reservation['salutation']] ? $salutation[$reservation['salutation']] : '');
                        $c4gNotify->setTokenValue('title', $reservation['title'] ? $reservation['title'] : '');
                        $c4gNotify->setTokenValue('organisation', $reservation['organisation'] ? $reservation['organisation'] : '');
                        $c4gNotify->setTokenValue('firstname', $reservation['firstname'] ? $reservation['firstname'] : '');
                        $c4gNotify->setTokenValue('lastname', $reservation['lastname'] ? $reservation['lastname'] : '');
                        $c4gNotify->setTokenValue('phone', $reservation['phone'] ? $reservation['phone'] : '');
                        $c4gNotify->setTokenValue('address', $reservation['address'] ? $reservation['address'] : '');
                        $c4gNotify->setTokenValue('postal', $reservation['postal'] ? $reservation['postal'] : '');
                        $c4gNotify->setTokenValue('city', $reservation['city'] ? $reservation['city'] : '');
                        $c4gNotify->setTokenValue('dateOfBirth', $reservation['dateOfBirth'] ? date($dateFormat, $reservation['dateOfBirth']) : '');
                        $c4gNotify->setTokenValue('salutation2', $reservation['salutation2'] && $salutation[$reservation['salutation2']] ? $salutation[$reservation['salutation2']] : '');
                        $c4gNotify->setTokenValue('title2', $reservation['title2'] ? $reservation['title2'] : '');
                        $c4gNotify->setTokenValue('organisation2', $reservation['organisation2'] ? $reservation['organisation2'] : '');
                        $c4gNotify->setTokenValue('firstname2', $reservation['firstname2'] ? $reservation['firstname2'] : '');
                        $c4gNotify->setTokenValue('lastname2', $reservation['lastname2'] ? $reservation['lastname2'] : '');
                        $c4gNotify->setTokenValue('email2', $reservation['email2'] ? $reservation['email2'] : '');
                        $c4gNotify->setTokenValue('phone2', $reservation['phone2'] ? $reservation['phone2'] : '');
                        $c4gNotify->setTokenValue('address2', $reservation['address2'] ? $reservation['address2'] : '');
                        $c4gNotify->setTokenValue('postal2', $reservation['postal2'] ? $reservation['postal2'] : '');
                        $c4gNotify->setTokenValue('city2', $reservation['city2'] ? $reservation['city2'] : '');
                        $c4gNotify->setTokenValue('comment', $reservation['comment'] ? \Contao\StringUtil::deserialize($reservation['comment']) : '');
                        $c4gNotify->setTokenValue('internal_comment', $reservation['internal_comment'] ? \Contao\StringUtil::deserialize($reservation['internal_comment']) : '');
                        $c4gNotify->setTokenValue('additional1', $reservation['additional1'] ?: '');
                        $c4gNotify->setTokenValue('additional2', $reservation['additional2'] ?: '');
                        $c4gNotify->setTokenValue('additional3', $reservation['additional3'] ?: '');

                        $c4gNotify->setTokenValue('location', $location ? $location['name'] : '');
                        $c4gNotify->setTokenValue('contact_name', $location ? $location['contact_name'] : '');
                        $c4gNotify->setTokenValue('contact_phone', $location ? $location['contact_phone'] : '');
                        $c4gNotify->setTokenValue('contact_street', $location ? $location['contact_street'] : '');
                        $c4gNotify->setTokenValue('contact_postal', $location ? $location['contact_postal'] : '');
                        $c4gNotify->setTokenValue('contact_city', $location ? $location['contact_city'] : '');

                        $c4gNotify->setTokenValue('reservation_id', $reservation['reservation_id']);
                        $c4gNotify->setTokenValue('agreed', $reservation['agreed']);

                        if ($reservationObject['price']) {
                            $price = C4gReservationHandler::formatPrice($reservationObject['price']);
                            $priceOptionSum = C4gReservationHandler::formatPrice($reservationObject['priceOptionSum']);
                            $priceSum = C4gReservationHandler::formatPrice($reservationObject['priceSum']);
                            $c4gNotify->setTokenValue('price', $price);
                            $c4gNotify->setTokenValue('priceOptionSum', $priceOptionSum);
                            $c4gNotify->setTokenValue('priceSum', $priceSum);
                        }

                        if ($eventObject && $eventObject['discountCode'] && $eventObject['discountPercent'] && $reservation['discountCode'] &&
                            ($reservation['discountCode'] == $eventObject['discountCode'])) {
                            $c4gNotify->setTokenValue('discountPercent', $eventObject['discountPercent']);
                            $c4gNotify->setTokenValue('discountCode', $reservation['discountCode']);
                            $c4gNotify->setTokenValue('priceDiscount', $reservation['priceDiscount'] ? $reservation['priceDiscount'] : '');
                        }

                        if ($reservationObject['documentId']) {
                            $c4gNotify->setTokenValue('documentId', $reservation['documentId']);
                        }

                        if ($reservationObject && $reservationObject['priceSumTax']) {
                            $c4gNotify->setTokenValue('priceTax', $reservation['priceTax'] ? $reservation['priceTax'] : '');
                            $c4gNotify->setTokenValue('priceNet', $reservation['priceNet'] ? $reservation['priceNet'] : '');

                            $c4gNotify->setTokenValue('priceOptionSumTax', $reservation['priceOptionSumTax'] ? $reservation['priceOptionSumTax'] : '');
                            $c4gNotify->setTokenValue('priceOptionSumNet', $reservation['priceOptionSumNet'] ? $reservation['priceOptionSumNet'] : '');

                            $c4gNotify->setTokenValue('priceSumNet', $reservation['priceSumNet'] ? $reservation['priceSumNet'] : '');
                            $c4gNotify->setTokenValue('priceSumTax', $reservation['priceSumTax'] ? $reservation['priceSumTax'] : '');

                            $c4gNotify->setTokenValue('reservationTaxRate', $reservation['reservationTaxRate'] ? $reservation['reservationTaxRate'] : '');
                        }

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
                                'contact_phone', 'contact_street', 'contact_postal', 'contact_city', 'uploadFile', 'pdfnc_attachment', 'pdfnc_document', 'reservation_id', 'agreed',
                                'description', 'additional1', 'additional2', 'additional3','member_email',
                                'price','priceTax','priceSum','priceSumTax','priceNet','priceSumNet', 'priceOptionSum', 'priceOptionSumNet', 'priceOptionSumTax',
                                'priceDiscount', 'discountCode', 'discountPercent', 'documentId', 'reservationTaxRate', 'dbkey'
                            ]
                        );

                        $sendingResult = $c4gNotify->send($arrNotificationIds);
                        if ($sendingResult) {
                            $database->prepare("UPDATE tl_c4g_reservation SET emailConfirmationSend='1' WHERE `id`=?")->execute($reservationId);
                        }
                    }
                }
            } catch (\Throwable $exception) {
                C4gLogModel::addLogEntry('reservation', $exception->getMessage());
            }
        }
    }
}
