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
        $param = C4gReservationParamsModel::findByPk($paramId);
        $published = $param->published;
        $price = $param->price;

        //Tax rate
        $taxOption = $param->taxOptions;
        if ($taxOption == 'tNone'){
            $taxIncl = '';
        } else {
            $taxIncl = $GLOBALS['TL_LANG']['fe_c4g_reservation']['taxIncl'];
        }

        if (!$param) {
            return null;
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
        if ($published) {
            if ($param && $caption && $published && ($price && $reservationSettings->showPrices)) {
                return ['id' => $paramId, 'name' => $caption . "<span class='price'> (+" . C4gReservationHandler::formatPrice($price).") ".$taxIncl." </span>"];
            } else if ($param && $caption && $published) {
                return ['id' => $paramId, 'name' => $caption];
            }
        }

        return null;
    }


}