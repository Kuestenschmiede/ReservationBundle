<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 10
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2025, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

namespace con4gis\ReservationBundle\Classes\Caches;

use con4gis\CoreBundle\Classes\C4GAutomator;
use con4gis\CoreBundle\Resources\contao\models\C4gLogModel;
use Contao\System;
use Psr\Cache\CacheItemPoolInterface;

class C4gReservationCacheAutomator extends C4GAutomator
{
    /**
     * Purge the reservation form cache that is used to speed up date exclusion and booked-days calculations.
     *
     * Note: These items are stored in Symfony's cache.app pool using keys prefixed with
     *       'c4g_res_date_excl_' and 'c4g_res_booked_days_'. As PSR-6 does not support
     *       deleting by prefix, we clear the pool to ensure consistent results.
     */
    public static function purgeReservationFormCache(): void
    {
        try {
            $container = System::getContainer();
            // Prefer dedicated reservation cache pool if available
            if ($container && $container->has('cache.c4g_reservation')) {
                /** @var CacheItemPoolInterface $pool */
                $pool = $container->get('cache.c4g_reservation');
                $pool->clear();
            } elseif ($container && $container->has('cache.app')) {
                /** @var CacheItemPoolInterface $pool */
                $pool = $container->get('cache.app');
                // Fallback: try to clear only tagged reservation items if TagAwareAdapter
                // is in use; otherwise, clear entire app cache as a last resort.
                if (method_exists($pool, 'invalidateTags')) {
                    try { $pool->invalidateTags(['c4g_reservation']); } catch (\Throwable $t2) { $pool->clear(); }
                } else {
                    $pool->clear();
                }
            }
        } catch (\Throwable $t) {
            // ignore
        }

        // Add an entry to the con4gis log
        C4gLogModel::addLogEntry('reservation', 'cleared reservation form cache');
    }
}
