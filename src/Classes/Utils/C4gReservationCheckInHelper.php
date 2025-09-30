<?php

namespace con4gis\ReservationBundle\Classes\Utils;

use con4gis\ProjectsBundle\Classes\QRCode\LinkToQRCode;

class C4gReservationCheckInHelper
{
    public function generateQRCode($content, $fileName)
    {
        if ($content && $fileName) {
            if (LinkToQRCode::linkToQRCode($content, $fileName)) {
                $fileArr['content'] = $content;
                $fileArr['fileName'] = $fileName;

                return $fileArr;
            };
        }

        return false;
    }

    public function generateBeforeSaving($params)
    {
        $key = $params['reservation_id'];
        //ToDo mkdir?
        $fileName = 'files/c4g_brick_data/qrcode/qrcode_' . $key . '.png';
        $content  = '?checkIn='.$key; //ToDo Check Content
        $linkArr = $this->generateQRCode($content, $fileName);
        if ($linkArr && is_array($linkArr)) {
            $params['qrContent'] = $linkArr['content'] ?: '';
            $params['qrFileName'] = $linkArr['fileName'] ?: '';
        }

        return $params;
    }

}