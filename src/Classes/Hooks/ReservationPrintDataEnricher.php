<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 10
 * @author con4gis contributors
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2025, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

namespace con4gis\ReservationBundle\Classes\Hooks;

use con4gis\ReservationBundle\Controller\C4gReservationController;
use con4gis\ReservationBundle\Classes\Models\C4gReservationTypeModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationEventModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationModel;

/**
 * Anreicherer für Print-Daten: Berechnet fehlende/inkonsistente Preisfelder
 * ausschließlich innerhalb des Reservation-Bundles.
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
            // Nur tätig werden, wenn priceSum fehlt oder leer wirkt
            // ODER wenn wir einen Verdacht auf unvollständige Summen haben
            $hasPriceSum = isset($data['priceSum']) && trim((string) $data['priceSum']) !== '';
            $priceSumValue = $hasPriceSum ? floatval(str_replace(',', '.', preg_replace('/[^0-9,.-]/', '', $data['priceSum']))) : 0;
            
            // Wenn priceSum vorhanden ist, prüfen wir, ob Optionen gewählt wurden, aber die Summe verdächtig niedrig ist
            // (z.B. gleich dem Basispreis). Im Zweifel rechnen wir lieber einmal neu nach.
            // Aber: Wir vermeiden Endlosschleifen oder unnötige Re-Calculations wenn möglich.
            $recalcRequired = !$hasPriceSum;

            // Nur arbeiten, wenn das übergebene Modul die benötigten Settings-Funktionen bietet
            if (!is_object($module) || !method_exists($module, 'getReservationSettings')) {
                return;
            }

            $reservationTypeId = isset($data['reservation_type']) ? (int) $data['reservation_type'] : 0;
            if ($reservationTypeId <= 0) {
                return;
            }

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
                $reservationObject = C4gReservationModel::findByPk($resObjectId);
            }
            if (!$reservationObject && !$reservationEventObject) {
                return;
            }

            // putVars aus den vorliegenden Daten rekonstruieren
            $putVars = (array) $data;
            if ($duration && !isset($putVars[$durationKey])) { $putVars[$durationKey] = $duration; }
            if ($desiredCapacity && !isset($putVars[$desiredCapacityKey])) { $putVars[$desiredCapacityKey] = $desiredCapacity; }

            // Härtung und Rekonstruktion von Optionen aus serialisierten Datenbankfeldern
            $objectId = $isEvent ? ($reservationEventObject->id ?? 0) : ($reservationObject->id ?? 0);
            $objectPid = $isEvent ? ($reservationEventObject->pid ?? 0) : ($reservationObject->pid ?? 0);
            
            // 1. Aus flachen Daten (Formular-Submit)
            foreach ($data as $key => $val) {
                if (is_string($val)) {
                    if (strpos($key, 'additional_params_') === 0 || strpos($key, 'participants_') === 0) {
                        if ($val === 'true') { $putVars[$key] = true; }
                        elseif ($val === 'false') { $putVars[$key] = false; }
                        elseif (is_numeric($val) && (float)$val == (int)$val) { $putVars[$key] = (int)$val; }
                    }
                }
            }

            // 2. Aus serialisierten Feldern (Datenbank-Datensatz)
            // Normalisierung der IDs für die Keys (wie im Calculator erwartet: -00{id})
            $suffix = $objectId ?: $objectPid;
            
            if (!empty($data['additional_params']) && is_string($data['additional_params'])) {
                $chosenAdd = \Contao\StringUtil::deserialize($data['additional_params'], true);
                if (!empty($chosenAdd)) {
                    foreach ($chosenAdd as $optId) {
                        $putVars['additional_params_' . $reservationTypeId . '-00' . $suffix . '|' . $optId] = true;
                        // Auch Radio-Format unterstützen
                        $putVars['additional_params_' . $reservationTypeId . '-00' . $suffix] = $optId;
                    }
                }
            }

            if (!empty($data['included_params']) && is_string($data['included_params'])) {
                $chosenInc = \Contao\StringUtil::deserialize($data['included_params'], true);
                if (!empty($chosenInc)) {
                    foreach ($chosenInc as $optId) {
                        $putVars['included_params_' . $reservationTypeId . '-00' . $suffix . '|' . $optId] = true;
                    }
                }
            }

            // 3. Teilnehmer-Optionen rekonstruieren (falls vorhanden)
            $resId = (int) ($data['id'] ?? 0);
            if ($resId > 0) {
                $participants = \Contao\Database::getInstance()->prepare("SELECT * FROM tl_c4g_reservation_participants WHERE pid = ?")
                    ->execute($resId)->fetchAllAssoc();
                if (!empty($participants)) {
                    $onlyParticipants = $settings->onlyParticipants ?: false;
                    $counter = $onlyParticipants ? $desiredCapacity : $desiredCapacity - 1;
                    foreach ($participants as $idx => $participant) {
                        if (!empty($participant['participant_params'])) {
                            $pParams = \Contao\StringUtil::deserialize($participant['participant_params'], true);
                            if (is_array($pParams)) {
                                foreach ($pParams as $pOptId) {
                                    // Wir müssen das Format des Calculators nachbilden:
                                    // participants_{typeId}-{counter}§participant_params§{idx}|{optId}
                                    $putVars['participants_' . $reservationTypeId . '-' . $counter . '§participant_params§' . $idx . '|' . $pOptId] = true;
                                    $putVars['participants_' . $reservationTypeId . '-' . $counter . '§participant_params§' . $idx] = $pOptId;
                                }
                            }
                        }
                    }
                }
            }

            // Wenn wir bereits eine Summe haben, schauen wir kurz, ob Optionen gewählt wurden
            if (!$recalcRequired) {
                $hasOptions = false;
                $includedParams = $reservationType->included_params ?? false;
                $additionalParams = $reservationType->additional_params ?? false;
                if ($includedParams || $additionalParams) {
                    $hasOptions = true;
                } else {
                    $participantParams = $reservationObject ? ($reservationObject->participant_params ?? false) : ($reservationEventObject->participant_params ?? false);
                    if ($participantParams) {
                        $hasOptions = true;
                    }
                }
                
                // Wenn Optionen möglich sind, rechnen wir zur Sicherheit immer neu, 
                // um sicherzustellen, dass sie in priceSum enthalten sind.
                if ($hasOptions) {
                    $recalcRequired = true;
                }
            }

            if (!$recalcRequired) {
                return;
            }

            // Preise berechnen (by ref)
            C4gReservationController::allPrices($settings, $putVars, $reservationObject, $reservationEventObject, $reservationType, $isEvent, $desiredCapacity);

            // Ergebnisse zurück in $data schreiben (nur setzen, wenn vorhanden)
            foreach ([
                'price','priceSum','priceOptionSum','priceDiscount',
                'priceNet','priceTax','priceOptionSumNet','priceOptionSumTax',
                'priceSumNet','priceSumTax','reservationTaxRate'
            ] as $k) {
                if (isset($putVars[$k])) {
                    $data[$k] = $putVars[$k];
                }
            }
        } catch (\Throwable $t) {
            // Keine Blockade bei Fehlern – lieber unverändert weiterlaufen
        }
    }
}
