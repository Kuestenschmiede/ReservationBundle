<?php

namespace con4gis\ReservationBundle\Classes\Utils;

use con4gis\ProjectsBundle\Classes\QRCode\LinkToQRCode;

class C4gReservationCheckInHelper
{
    public function generateQRCode($fileName)
    {
        $link = $fileName; //ToDo
        if ($link) {
            $hash = array_key_first($link);
            if (LinkToQRCode::linkToQRCode($link, $fileName)) {
                $fileArr['hash'] = $hash;
                $fileArr['link'] = $link;
                $fileArr['file'] = $fileName;

                return $fileArr;
            };
        }

        return false;
    }

}