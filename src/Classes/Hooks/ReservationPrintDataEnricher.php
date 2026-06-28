<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @author con4gis contributors
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

namespace con4gis\ReservationBundle\Classes\Hooks;

use con4gis\ReservationBundle\Controller\C4gReservationController;
use con4gis\ReservationBundle\Classes\Models\C4gReservationTypeModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationEventModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationObjectModel;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationCheckInHelper;

/**
 * Anreicherer für Print-Daten: Berechnet fehlende/inkonsistente Preisfelder
 * und erzeugt QR-Codes ausschließlich innerhalb des Reservation-Bundles.
 */
class ReservationPrintDataEnricher
{
    /**
     * Listener für den Hook `c4gProjectsPreparePrintData`.
     *
     * @param object $module Beliebiges Frontend-Modul (kann, muss aber kein Reservierungsmodul sein)
     * @param array  $data   FieldData/PutVars, per Referenz veränderbar
     * @return void
     */
    public function enrich($module, array &$data): void
    {
        try {
            // error_log("ReservationPrintDataEnricher DEBUG Data START: " . json_encode($data));
            // Nur arbeiten, wenn das übergebene Modul die benötigten Settings-Funktionen bietet

            // 1. Map dynamic keys to standard keys for the helper and template
            $keysToSync = [
                'reservation_id', 'priceSum', 'priceSumTax', 'priceSumNet', 'bankName', 'bankIban', 'bankBic', 
                'qrFileName', 'bankQrFileName', 'documentId', 'firstname', 'lastname',
                'address', 'street', 'postal', 'city', 'email', 'phone', 'organisation', 'title', 'salutation',
                'firstname2', 'lastname2', 'address2', 'street2', 'postal2', 'city2', 'email2', 'phone2', 'organisation2', 'title2', 'salutation2',
                'beginDate', 'beginTime', 'endDate', 'endTime', 'reservation_type',
                'reservation_object', 'price', 'priceTax', 'priceNet', 'priceOptionSum',
                'priceOptionSumNet', 'priceOptionSumTax', 'priceDiscount', 'discountPercent',
                'discountCode', 'reservationTaxRate', 'documentId', 'dateOfBirth'
            ];

            foreach ($keysToSync as $standardKey) {
                if ($standardKey === 'reservation_id' && (!isset($data['reservation_id']) || !$data['reservation_id'])) {
                    if (isset($data['id']) && $data['id']) {
                        $data['reservation_id'] = $data['id'];
                    }
                }
                if (!isset($data[$standardKey]) || $data[$standardKey] === '' || $data[$standardKey] === null) {
                    foreach ($data as $dynamicKey => $value) {
                        if ((strpos($dynamicKey, $standardKey . '_') === 0 || strpos($dynamicKey, $standardKey . '|') === 0 || strpos($dynamicKey, $standardKey . '-') === 0) && ($value !== '' && $value !== null)) {
                            $data[$standardKey] = $value;
                            // error_log("ReservationPrintDataEnricher DEBUG Synced $standardKey from $dynamicKey: $value");
                            break;
                        }
                    }
                }
            }

            // Handle address/street alias
            if (isset($data['street']) && (!isset($data['address']) || !$data['address'])) {
                $data['address'] = $data['street'];
            }
            if (isset($data['address']) && (!isset($data['street']) || !$data['street'])) {
                $data['street'] = $data['address'];
            }
            if (isset($data['street2']) && (!isset($data['address2']) || !$data['address2'])) {
                $data['address2'] = $data['street2'];
            }
            if (isset($data['address2']) && (!isset($data['street2']) || !$data['street2'])) {
                $data['street2'] = $data['address2'];
            }

            // Fallback for beginTime if it's still empty and it's a reservation
            if (!isset($data['beginTime']) || $data['beginTime'] === '' || $data['beginTime'] === null) {
                if (isset($data['beginTimeInt']) && (int)$data['beginTimeInt'] === 0) {
                    $data['beginTime'] = '00:00';
                    // error_log("ReservationPrintDataEnricher DEBUG Fallback beginTime to 00:00 because beginTimeInt is 0");
                }
            }
            // error_log("ReservationPrintDataEnricher DEBUG Data MID: " . json_encode($data));

            $reservationTypeId = isset($data['reservation_type']) ? (int) $data['reservation_type'] : 0;
            if ($reservationTypeId <= 0) {
                return;
            }

            // 2. Preisberechnung falls nötig
            $hasPriceSum = isset($data['priceSum']) && trim((string) $data['priceSum']) !== '';
            if (!$hasPriceSum) {
                $this->recalculatePrices($module, $data, $reservationTypeId);
            }

            // 3. QR-Code-Generierung
            $settings = $module->getReservationSettings();
            $checkInHelper = new C4gReservationCheckInHelper($settings ? $settings->checkInPage : null);
            
            $data = $checkInHelper->generateBeforeSaving($data);

            // 4. Convert QR codes to Base64 to bypass path/permission issues in PDF engine
            $rootDir = \Contao\System::getContainer()->getParameter('kernel.project_dir');
            foreach (['qrFileName' => 'qrBase64', 'bankQrFileName' => 'bankQrBase64'] as $fileKey => $base64Key) {
                if (isset($data[$fileKey]) && $data[$fileKey]) {
                    $path = $data[$fileKey];
                    if (strpos($path, '/') !== 0 && !preg_match('/^[a-zA-Z]:/', $path)) {
                        $path = $rootDir . '/' . $path;
                    }
                    if (file_exists($path) && is_readable($path)) {
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        $imgData = file_get_contents($path);
                        if (strpos($imgData, '<svg') !== false) {
                            $type = 'svg+xml';
                        }
                        $data[$base64Key] = 'data:image/' . $type . ';base64,' . base64_encode($imgData);
                    }
                }
            }

            // 5. Mirror fresh results back to dynamic keys to be absolutely sure the template finds them
            $fieldsToMirror = [
                'qrFileName', 'bankQrFileName', 'qrContent', 'qrBase64', 'bankQrBase64',
                'price', 'priceTax', 'priceSum', 'priceSumTax', 'priceNet', 'priceSumNet',
                'priceOptionSum', 'priceOptionSumTax', 'priceOptionSumNet', 'priceDiscount',
                'discountPercent', 'discountCode', 'reservationTaxRate', 'documentId'
            ];
            foreach ($fieldsToMirror as $ktm) {
                if (isset($data[$ktm]) && $data[$ktm]) {
                    foreach ($data as $pk => $pv) {
                        if (strpos($pk, $ktm . '_') === 0 || strpos($pk, $ktm . '|') === 0 || strpos($pk, $ktm . '-') === 0) {
                            $data[$pk] = $data[$ktm];
                        }
                    }
                }
            }

        } catch (\Throwable $t) {
            // Fail silently to not block printing
            if (\Contao\System::getContainer()->has('monolog.logger.contao')) {
                \Contao\System::getContainer()->get('monolog.logger.contao')->error('ReservationPrintDataEnricher Error: ' . $t->getMessage());
            }
        }
    }

    /**
     * Re-calculates prices based on data
     */
    private function recalculatePrices($module, array &$data, int $reservationTypeId): void
    {
        try {
            $eventKey = 'reservation_object_event_' . $reservationTypeId;
            $objKey   = 'reservation_object_' . $reservationTypeId;
            $isEvent = !empty($data[$eventKey]);
            $resObjectId = $isEvent ? (int) ($data[$eventKey] ?? 0) : (int) ($data[$objKey] ?? 0);
            if ($resObjectId <= 0) {
                return;
            }

            $desiredCapacityKey = 'desiredCapacity_' . $reservationTypeId;
            $desiredCapacity = isset($data[$desiredCapacityKey]) ? (int) $data[$desiredCapacityKey] : (isset($data['desiredCapacity']) ? (int) $data['desiredCapacity'] : 1);

            $durationKey = 'duration_' . $reservationTypeId;
            $duration = isset($data[$durationKey]) ? (int) $data[$durationKey] : (isset($data['duration']) ? (int) $data['duration'] : 0);

            // Modelle laden
            $settings = $module->getReservationSettings();
            $reservationType = C4gReservationTypeModel::findByPk($reservationTypeId);
            if (!$settings || !$reservationType) {
                return;
            }

            $reservationObject = null;
            $reservationEventObject = null;
            if ($isEvent) {
                $reservationEventObject = C4gReservationEventModel::findByPk($resObjectId);
            } else {
                $reservationObject = C4gReservationObjectModel::findByPk($resObjectId);
            }
            if (!$reservationObject && !$reservationEventObject) {
                return;
            }

            $putVars = (array) $data;
            if ($duration && !isset($putVars[$durationKey])) { $putVars[$durationKey] = $duration; }
            if ($desiredCapacity && !isset($putVars[$desiredCapacityKey])) { $putVars[$desiredCapacityKey] = $desiredCapacity; }

            // Optionen rekonstruieren
            $suffix = $resObjectId;
            if (!empty($data['additional_params']) && is_string($data['additional_params'])) {
                $chosenAdd = \Contao\StringUtil::deserialize($data['additional_params'], true);
                if (!empty($chosenAdd)) {
                    foreach ($chosenAdd as $optId) {
                        $putVars['additional_params_' . $reservationTypeId . '-00' . $suffix . '|' . $optId] = true;
                    }
                }
            }

            C4gReservationController::allPrices($settings, $putVars, $reservationObject, $reservationEventObject, $reservationType, $isEvent, $desiredCapacity);

            foreach ([
                'price','priceSum','priceOptionSum','priceDiscount',
                'priceNet','priceTax','priceOptionSumNet','priceOptionSumTax',
                'priceSumNet','priceSumTax','reservationTaxRate',
                'priceParticipantOptionSum', 'priceParticipantOptionSumNet', 'priceParticipantOptionSumTax'
            ] as $k) {
                if (isset($putVars[$k])) {
                    $data[$k] = $putVars[$k];
                }
            }
        } catch (\Throwable $t) { /* ignore */ }
    }
}
