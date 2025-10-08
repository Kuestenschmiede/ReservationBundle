<?php

namespace con4gis\ReservationBundle\Classes\Utils;

use con4gis\CoreBundle\Classes\C4GUtils;
use con4gis\ProjectsBundle\Classes\QRCode\LinkToQRCode;
use Contao\PageModel;

class C4gReservationCheckInHelper
{
    private $checkInPage = "";

    public function __construct($checkInPage)
    {
        $this->checkInPage = $checkInPage;
    }

    public function generateQRCode($content, $fileName)
    {
        if ($content && $fileName) {
            $rootDir = \Contao\System::getContainer()->getParameter('kernel.project_dir');
            if (LinkToQRCode::linkToQRCode($content, $rootDir.'/'.$fileName)) {
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
        $fileName = 'files/c4g_brick_data/qrcode/qrcode_' . $key . '.png';

        $checkInPage = $this->checkInPage;
        if ($checkInPage) {
            $checkInUrl = '/';
            $jumpTo = PageModel::findByPk($checkInPage);

            if ($jumpTo) {
                $checkInUrl = C4GUtils::replaceInsertTags("{{env::url}}").$jumpTo->getFrontendUrl();
            }

            $content  = $checkInUrl.'?checkIn='.$key;
            $linkArr = $this->generateQRCode($content, $fileName);
            if ($linkArr && is_array($linkArr)) {
                $params['qrContent'] = $linkArr['content'] ?: '';
                $params['qrFileName'] = $linkArr['fileName'] ?: '';
            }
        }

        return $params;
    }

}