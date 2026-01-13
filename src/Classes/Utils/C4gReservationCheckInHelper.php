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
        $key = $params['reservation_id'] ?? '';
        if (!$key) {
            return $params;
        }
        $path = 'files/c4g_brick_data/qrcode/';
        $rootDir = \Contao\System::getContainer()->getParameter('kernel.project_dir');
        if (!is_dir($rootDir.'/'.$path)) {
            mkdir($rootDir.'/'.$path);
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