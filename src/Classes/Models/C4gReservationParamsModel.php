<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @author con4gis contributors (see "authors.md")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */
namespace con4gis\ReservationBundle\Classes\Models;


use Contao\Model;
use Contao\StringUtil;
use con4gis\ReservationBundle\Classes\Utils\C4gReservationHandler;

/**
 * Class C4geservationParamsModel
 * @package c4g\projects
 */
class C4gReservationParamsModel extends Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_c4g_reservation_params';

    /**
     * @param string $paramId
     * @param object $reservationSettings
     * @return array|string[]|null
     */
    public static function feParamsCaptions(string $paramId, object $reservationSettings): ?array
    {
        // In-request memoization to avoid repeated DB hits within the same render
        static $memo = [];
        $lang = (string)($GLOBALS['TL_LANGUAGE'] ?? '');
        $showPrices = (bool)($reservationSettings->showPrices ?? false);
        $memoKey = $paramId . '|' . $lang . '|' . ($showPrices ? '1' : '0');

        if (array_key_exists($memoKey, $memo)) {
            return $memo[$memoKey];
        }

        // Optional Symfony cache, governed by reservation settings flags (enable + TTL)
        $useCache = false; $ttl = 43200; // default 12h
        try {
            $flag = (string)($reservationSettings->reservation_enable_cache ?? '');
            $useCache = ($flag === '1' || $flag === 1 || $flag === true);
            $ttlCandidate = (int)($reservationSettings->reservation_cache_ttl ?? 0);
            if ($ttlCandidate > 0) { $ttl = $ttlCandidate; }
        } catch (\Throwable $t) { /* ignore */ }

        $cached = null; $cache = null; $cacheKey = 'c4g_res_param_caption_' . md5($memoKey);
        if ($useCache) {
            try {
                $container = \Contao\System::getContainer();
                if ($container && $container->has('cache.app')) {
                    $cache = $container->get('cache.app');
                    $item = $cache->getItem($cacheKey);
                    if ($item->isHit()) {
                        $val = $item->get();
                        if (is_array($val) || $val === null) {
                            $cached = $val;
                        }
                    }
                }
            } catch (\Throwable $t) { $cache = null; }
        }

        if ($cached !== null || ($useCache && $cached === null)) {
            // Note: we intentionally allow caching of null (no published caption) to avoid re-hits.
            $memo[$memoKey] = $cached;
            return $cached;
        }

        $param = C4gReservationParamsModel::findByPk($paramId);
        if (!$param) {
            $memo[$memoKey] = null;
            // store null in cache to prevent thundering herd
            if ($cache) {
                try { $it = $cache->getItem($cacheKey); $it->set(null); if (method_exists($it,'expiresAfter')) { $it->expiresAfter($ttl); } $cache->save($it); } catch (\Throwable $t) {}
            }
            return null;
        }
        $published = $param->published;
        $price = $param->price;

        //Tax rate
        $taxOption = $param->taxOptions;
        if ($taxOption == 'tNone'){
            $taxIncl = '';
        } else {
            $taxIncl = $GLOBALS['TL_LANG']['fe_c4g_reservation']['taxIncl'];
        }

        $feCaptions = StringUtil::deserialize($param->feCaption);
        $caption = '';

        //Caotion language
        //Use str_contains for newer versions. strpos still in use for older versions
        if ($feCaptions) {
            foreach ($feCaptions as $feCaption) {
                if (strpos($GLOBALS['TL_LANGUAGE'], $feCaption['language']) !== false && $feCaption['caption']) {
                    $caption = $feCaption['caption'];
                    break;
                }
            }
        }

        if (empty($caption)) {
            $caption = $param->caption ?: '';
        }

        //Setting FE caption string up
        $result = null;
        if ($published) {
            if ($param && $caption && $published && ($price && $showPrices)) {
                $result = ['id' => $paramId, 'name' => $caption . "<span class='price'> (+" . C4gReservationHandler::formatPrice($price).") ".$taxIncl." </span>"];
            } else if ($param && $caption && $published) {
                $result = ['id' => $paramId, 'name' => $caption];
            }
        }

        // Memoize and optionally store in cache
        $memo[$memoKey] = $result;
        if ($cache) {
            try { $it = $cache->getItem($cacheKey); $it->set($result); if (method_exists($it,'expiresAfter')) { $it->expiresAfter($ttl); } $cache->save($it); } catch (\Throwable $t) {}
        }

        return $result;
    }


    /**
     * Bulk variant: load multiple parameter captions in one go.
     * Returns map [paramId => array|null] using same rules as feParamsCaptions().
     * Applies short in-request memoization and optional Symfony cache across requests.
     *
     * @param array $paramIds
     * @param object $reservationSettings
     * @return array<string, array|null>
     */
    public static function feParamsCaptionsBulk(array $paramIds, object $reservationSettings): array
    {
        // Normalize IDs
        $ids = array_values(array_filter(array_unique(array_map('strval', $paramIds))));
        if (empty($ids)) { return []; }

        $lang = (string)($GLOBALS['TL_LANGUAGE'] ?? '');
        $showPrices = (bool)($reservationSettings->showPrices ?? false);

        // In-request memoization store shared with single-call variant
        static $memo = [];
        $result = [];
        $missing = [];
        foreach ($ids as $id) {
            $mk = $id . '|' . $lang . '|' . ($showPrices ? '1' : '0');
            if (array_key_exists($mk, $memo)) {
                $result[$id] = $memo[$mk];
            } else {
                $missing[$id] = $mk;
            }
        }
        if (empty($missing)) {
            return $result;
        }

        // Cross-request cache controls
        $useCache = false; $ttl = 43200; // default 12h
        try {
            $flag = (string)($reservationSettings->reservation_enable_cache ?? '');
            $useCache = ($flag === '1' || $flag === 1 || $flag === true);
            $ttlCandidate = (int)($reservationSettings->reservation_cache_ttl ?? 0);
            if ($ttlCandidate > 0) { $ttl = $ttlCandidate; }
        } catch (\Throwable $t) { /* ignore */ }

        $cache = null; $cached = null;
        $bulkKey = 'c4g_res_param_caption_bulk_' . md5(implode(',', array_keys($missing)) . '|' . $lang . '|' . ($showPrices ? '1' : '0'));
        if ($useCache) {
            try {
                $container = \Contao\System::getContainer();
                if ($container && $container->has('cache.app')) {
                    $cache = $container->get('cache.app');
                    $item = $cache->getItem($bulkKey);
                    if ($item->isHit()) {
                        $val = $item->get();
                        if (is_array($val)) { $cached = $val; }
                    }
                }
            } catch (\Throwable $t) { $cache = null; }
        }

        if (is_array($cached)) {
            foreach ($missing as $id => $mk) {
                $memo[$mk] = $cached[$id] ?? null;
                $result[$id] = $memo[$mk];
            }
            return $result;
        }

        // Query all still-missing records at once
        $collection = self::findMultipleByIds(array_keys($missing));
        $byId = [];
        if ($collection) {
            while ($collection->next()) {
                $row = $collection->current();
                $byId[(string)$row->id] = $row;
            }
        }

        $taxInclLang = $GLOBALS['TL_LANG']['fe_c4g_reservation']['taxIncl'] ?? '';
        $store = [];
        foreach ($missing as $id => $mk) {
            $param = $byId[$id] ?? null;
            $value = null;
            if ($param && $param->published) {
                $feCaptions = StringUtil::deserialize($param->feCaption);
                $caption = '';
                if ($feCaptions) {
                    foreach ($feCaptions as $feCaption) {
                        if (strpos((string)$GLOBALS['TL_LANGUAGE'], (string)$feCaption['language']) !== false && $feCaption['caption']) {
                            $caption = $feCaption['caption'];
                            break;
                        }
                    }
                }
                if ($caption === '' || $caption === null) { $caption = $param->caption ?: ''; }
                if ($caption !== '') {
                    $price = (float)$param->price;
                    $taxOption = $param->taxOptions;
                    $taxIncl = ($taxOption && $taxOption !== 'tNone') ? $taxInclLang : '';
                    if ($showPrices && $price) {
                        $value = ['id' => (string)$id, 'name' => $caption . "<span class='price'> (+" . C4gReservationHandler::formatPrice($price) . ") " . $taxIncl . " </span>"];
                    } else {
                        $value = ['id' => (string)$id, 'name' => $caption];
                    }
                }
            }
            $store[$id] = $value;
            $memo[$mk] = $value;
            $result[$id] = $value;
        }

        if ($cache) {
            try { $it = $cache->getItem($bulkKey); $it->set($store); if (method_exists($it,'expiresAfter')) { $it->expiresAfter($ttl); } $cache->save($it); } catch (\Throwable $t) {}
        }

        return $result;
    }
}