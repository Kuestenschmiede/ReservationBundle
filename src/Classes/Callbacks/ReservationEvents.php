<?php

namespace con4gis\ReservationBundle\Classes\Callbacks;

use Contao\Database;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;

class ReservationEvents {

    /**
     * @param $row
     * @param $href
     * @param $label
     * @param $title
     * @param $icon
     * @return void
     */
    public function runExport($row, $href, $label, $title, $icon, $attributes)
    {
        $exportExists = class_exists('con4gis\ExportBundle\con4gisExportBundle');
        if ($exportExists) {
            $settings = Database::getInstance()->prepare("SELECT exportSelection FROM tl_c4g_settings")->execute()->fetchAssoc();
            if ($settings && $settings['exportSelection']) {
                $rt = Input::get('rt');
                $ref = Input::get('ref');
                $do = Input::get('do');
                $where = "reservationObjectType = 2 AND reservation_object = ".$row['id'];

                $link = '<a href="/contao?do=c4g_export&table=tl_c4g_export&calendar='.$row['pid'].'&where='.$where.'&' . $href . '&id=' . $settings['exportSelection'].'&rt='.$rt. "&ref=" . $ref;
                $link .= '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label);
                $link .= '</a> ';
                return $link;
            }
        }
    }
}
