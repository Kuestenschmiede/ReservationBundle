<?php

namespace con4gis\ReservationBundle\Classes\Utils;

use con4gis\CoreBundle\Classes\C4GUtils;
use con4gis\ProjectsBundle\Classes\QRCode\LinkToQRCode;
use con4gis\ReservationBundle\Classes\Models\C4gReservationLocationModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationObjectModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationTypeModel;
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
        $key = $params['reservation_id'] ?? '';
        if (!$key) {
            return $params;
        }

        $locationId = 0;
        if (isset($params['reservation_object']) && $params['reservation_object']) {
            $obj = C4gReservationObjectModel::findByPk($params['reservation_object']);
            if ($obj && $obj->location_id) {
                $locationId = $obj->location_id;
            }
        }

        if (!$locationId && isset($params['reservation_type']) && $params['reservation_type']) {
            $type = C4gReservationTypeModel::findByPk($params['reservation_type']);
            if ($type && $type->location_id) {
                $locationId = $type->location_id;
            }
        }

        if ($locationId) {
            $loc = C4gReservationLocationModel::findByPk($locationId);
            if ($loc) {
                $params['bankName'] = $loc->bankName ?: '';
                $params['bankIban'] = $loc->bankIban ?: '';
                $params['bankBic'] = $loc->bankBic ?: '';

                if ($params['bankIban'] && $params['bankName']) {
                    $bankContent = "BCD\n001\n1\nSCT\n" . $loc->bankBic . "\n" . $loc->bankName . "\n" . $loc->bankIban . "\nEUR" . $params['priceSum'] . "\n\n\n" . $key;
                    $bankPath = 'files/c4g_brick_data/qrcode/';
                    $bankFileName = $bankPath . 'bank_qrcode_' . $key . '.png';
                    $bankLinkArr = $this->generateQRCode($bankContent, $bankFileName);
                    if ($bankLinkArr) {
                        $params['bankQrFileName'] = $bankLinkArr['fileName'];
                    }
                }
            }
        }

        $path = 'files/c4g_brick_data/qrcode/';
        $rootDir = \Contao\System::getContainer()->getParameter('kernel.project_dir');
        if (!is_dir($rootDir.'/'.$path)) {
            mkdir($rootDir.'/'.$path, 0777, true);
        }
        $fileName = $path.'qrcode_' . $key . '.png';

        $checkInPage = $this->checkInPage;
        if ($checkInPage) {
            $checkInUrl = '/';
            $jumpTo = PageModel::findByPk($checkInPage);

            if ($jumpTo) {
                $jumpToUrl = C4GUtils::replaceInsertTags("{{env::url}}");
                if (substr($jumpToUrl, -1) !== '/') {
                    $jumpToUrl .= '/';
                }
                $checkInUrl = $jumpToUrl.$jumpTo->getFrontendUrl();
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

    public static function removeQRCodeFile() {
        $rootDir = \Contao\System::getContainer()->getParameter('kernel.project_dir');
        $path = $rootDir.'/files/c4g_brick_data/qrcode';

        if (is_dir($path)) {
            $files = array_diff(scandir($path), array('.','..'));

            foreach ($files as $file) {
                $file_path = "$path/$file";
                unlink($file_path);
            }

            rmdir($path);
        }
    }
}