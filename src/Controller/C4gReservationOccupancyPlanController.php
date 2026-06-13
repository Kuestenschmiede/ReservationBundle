<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @author con4gis contributors (see "authors.md")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

namespace con4gis\ReservationBundle\Controller;

use con4gis\ProjectsBundle\Classes\Framework\C4GBaseController;
use con4gis\ProjectsBundle\Classes\Views\C4GBrickViewType;
use con4gis\ReservationBundle\Classes\Models\C4gReservationModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationObjectModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationSettingsModel;
use con4gis\ReservationBundle\Classes\Models\C4gReservationSuspensionModel;
use Contao\Controller;
use Contao\Date;
use Contao\Input;
use Contao\PageModel;
use Contao\StringUtil;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Contao\ModuleModel;
use Contao\Template;

class C4gReservationOccupancyPlanController extends C4GBaseController
{
    public const TYPE = 'occupancy_plan';

    public function __construct($projectDir, $requestStack, $framework)
    {
        parent::__construct($projectDir, $requestStack, $framework);
        $this->viewType = C4GBrickViewType::PUBLICBASED;
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $this->model = $model;
        foreach ($model->row() as $fieldName => $value) {
            $this->$fieldName = $value;
        }
        $this->loadLanguageFiles();

        if (class_exists('con4gis\CoreBundle\Classes\ResourceLoader')) {
            \con4gis\CoreBundle\Classes\ResourceLoader::loadJavaScriptResource('assets/jquery/js/jquery.min.js', \con4gis\CoreBundle\Classes\ResourceLoader::JAVASCRIPT, 'jquery');
        } elseif (!isset($GLOBALS['TL_JAVASCRIPT']) || !is_array($GLOBALS['TL_JAVASCRIPT']) || !in_array('assets/jquery/js/jquery.min.js', $GLOBALS['TL_JAVASCRIPT'])) {
            $GLOBALS['TL_JAVASCRIPT'][] = 'assets/jquery/js/jquery.min.js|static';
        }
        
        $response = new Response($this->run());
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    public function addFields(): array
    {
        return [];
    }

    public function generateAjax($request = null)
    {
        return parent::generateAjax($request);
    }

    public function run()
    {
        $objects = StringUtil::deserialize($this->occupancy_reservation_objects);
        if (empty($objects)) {
            return '';
        }

        $month = Input::get('month') ?: date('m');
        $year = Input::get('year') ?: date('Y');

        $time = strtotime("$year-$month-01");
        $daysInMonth = date('t', $time);
        $firstWeekday = date('N', $time);

        $prevMonth = date('m', strtotime("-1 month", $time));
        $prevYear = date('Y', strtotime("-1 month", $time));
        $nextMonth = date('m', strtotime("+1 month", $time));
        $nextYear = date('Y', strtotime("+1 month", $time));

        $reservations = $this->getReservations($objects, $month, $year);
        $occupancy = $this->calculateOccupancy($objects, $reservations, $daysInMonth, $month, $year);

        $settings = C4gReservationSettingsModel::findAll();
        if ($settings && $settings->current()) {
            $this->session->setSessionValue('reservationSettings', $settings->current()->id);
        }

        $html = '<div id="c4g_occupancy_plan" class="occupancy-plan">';
        $html .= '<div class="calendar-nav">';
        $html .= '<a class="c4g-calendar-link" href="' . Controller::addToUrl("month=$prevMonth&year=$prevYear", true, ['date']) . '" data-anchor="#c4g_occupancy_plan">&laquo;</a>';
        $html .= '<span>' . $GLOBALS['TL_LANG']['MONTHS'][intval($month)-1] . ' ' . $year . '</span>';
        $html .= '<a class="c4g-calendar-link" href="' . Controller::addToUrl("month=$nextMonth&year=$nextYear", true, ['date']) . '" data-anchor="#c4g_occupancy_plan">&raquo;</a>';
        $html .= '</div>';

        $html .= '<table class="calendar">';
        $html .= '<thead><tr>';
        foreach ($GLOBALS['TL_LANG']['DAYS_SHORT'] as $dayShort) {
            $html .= '<th>' . $dayShort . '</th>';
        }
        $html .= '</tr></thead>';
        $html .= '<tbody><tr>';

        for ($i = 1; $i < $firstWeekday; $i++) {
            $html .= '<td class="empty"></td>';
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            if (($day + $firstWeekday - 2) % 7 == 0 && $day != 1) {
                $html .= '</tr><tr>';
            }

            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $dateFormatted = Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], strtotime($dateStr));
            // Ensure no leading/trailing whitespace which might trip up the regex
            $dateFormatted = trim($dateFormatted);
            $occData = $occupancy[$day];
            $status = $occData['status']; // 'free', 'booked', 'partial'
            $text = $occData['text'];
            
            $class = "day $status";
            $link = '';
            if (($status === 'free' || $status === 'partial') && $this->reservation_form_site) {
                $page = PageModel::findByPk($this->reservation_form_site);
                if ($page) {
                    $link = $page->getFrontendUrl();
                    $link .= (str_contains($link, '?') ? '&' : '?') . 'date=' . $dateFormatted;
                    $anchor = '#c4g_reservation_form';
                }
            }

            $html .= '<td class="' . $class . '">';
            if ($link) {
                $html .= '<a class="c4g-calendar-link" href="' . $link . '" data-anchor="' . $anchor . '"><span class="day-num">' . $day . '</span>';
            } else {
                $html .= '<span><span class="day-num">' . $day . '</span>';
            }

            if ($text) {
                $html .= '<div class="day-text">' . $text . '</div>';
            }

            if ($link) {
                $html .= '</a>';
            } else {
                $html .= '</span>';
            }

            if ($status === 'partial') {
                $html .= '<div class="triangle"></div>';
            }
            $html .= '</td>';
        }

        $lastWeekday = date('N', strtotime("$year-$month-$daysInMonth"));
        for ($i = $lastWeekday; $i < 7; $i++) {
            $html .= '<td class="empty"></td>';
        }

        $html .= '</tr></tbody>';
        $html .= '</table>';
        
        if ($this->show_occupancy_legend) {
            $html .= '<div class="legend">';
            $html .= '<strong>' . (($GLOBALS['TL_LANG']['fe_c4g_reservation']['occupancy_legend'] ?? '') ?: 'Legende') . ':</strong>';
            $html .= '<ul>';
            $html .= '<li><span class="box free"></span> ' . (($GLOBALS['TL_LANG']['fe_c4g_reservation']['occupancy_free'] ?? '') ?: 'Frei') . '</li>';
            $html .= '<li><span class="box partial"></span> ' . (($GLOBALS['TL_LANG']['fe_c4g_reservation']['occupancy_partial'] ?? '') ?: 'Teilweise belegt') . '</li>';
            $html .= '<li><span class="box booked"></span> ' . (($GLOBALS['TL_LANG']['fe_c4g_reservation']['occupancy_booked'] ?? '') ?: 'Belegt') . '</li>';
            $html .= '</ul>';
            $html .= '</div>';
        }

        $html .= '</div>';

        $style = '<style>
            .occupancy-plan { width: 100%; max-width: 800px; }
            .occupancy-plan .calendar-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
            .occupancy-plan table { width: 100%; border-collapse: collapse; margin-bottom: 15px; table-layout: fixed; }
            .occupancy-plan th, .occupancy-plan td { border: 1px solid #ccc; text-align: center; padding: 5px; width: 14.28%; height: 60px; vertical-align: top; overflow: hidden; }
            .occupancy-plan td.free { background-color: #d4edda; color: #155724; }
            .occupancy-plan td.booked { background-color: #f8d7da; color: #721c24; }
            .occupancy-plan td.partial { background-color: #d4edda; position: relative; overflow: hidden; }
            .occupancy-plan td.partial .triangle { 
                position: absolute; top: 0; right: 0; width: 0; height: 0; 
                border-style: solid; border-width: 0 20px 20px 0; border-color: transparent #f8d7da transparent transparent;
                pointer-events: none;
            }
            .occupancy-plan td .day-num { font-weight: bold; display: block; margin-bottom: 2px; }
            .occupancy-plan td .day-text { font-size: 0.75em; line-height: 1.1; word-wrap: break-word; }
            .occupancy-plan td a { display: block; text-decoration: none; color: inherit; position: relative; z-index: 1; height: 100%; }
            .occupancy-plan .legend ul { list-style: none; padding: 0; margin: 5px 0 0 0; display: flex; flex-wrap: wrap; gap: 10px; }
            .occupancy-plan .legend li { display: flex; align-items: center; font-size: 0.9em; }
            .occupancy-plan .legend .box { width: 15px; height: 15px; border: 1px solid #ccc; margin-right: 5px; display: inline-block; }
            .occupancy-plan .legend .box.free { background-color: #d4edda; }
            .occupancy-plan .legend .box.booked { background-color: #f8d7da; }
            .occupancy-plan .legend .box.partial { 
                background-color: #d4edda; position: relative; overflow: hidden;
            }
            .occupancy-plan .legend .box.partial::after {
                content: ""; position: absolute; top: 0; right: 0; width: 0; height: 0;
                border-style: solid; border-width: 0 15px 15px 0; border-color: transparent #f8d7da transparent transparent;
            }
        </style>';

        return $style . $html;
    }

    protected function getReservations($objects, $month, $year)
    {
        $start = strtotime("$year-$month-01 00:00:00");
        $end = strtotime("last day of $year-$month 23:59:59");

        $objIn = implode(',', array_map('intval', $objects));
        $db = \Contao\Database::getInstance();
        $res = $db->prepare("SELECT * FROM tl_c4g_reservation 
            WHERE reservation_object IN ($objIn) 
            AND cancellation != '1' 
            AND ((beginDate BETWEEN ? AND ?) OR (endDate BETWEEN ? AND ?) OR (beginDate < ? AND endDate > ?))")
            ->execute($start, $end, $start, $end, $start, $end);

        return $res->fetchAllAssoc();
    }

    protected function calculateOccupancy($objects, $reservations, $daysInMonth, $month, $year)
    {
        $occupancy = [];
        $objectModels = [];
        foreach ($objects as $objId) {
            $objectModels[$objId] = C4gReservationObjectModel::findByPk($objId);
        }

        $suspensionDates = [];
        $settings = C4gReservationSettingsModel::findAll();
        if ($settings) {
            foreach ($settings as $setting) {
                if ($setting->suspension_lists) {
                    $listIds = StringUtil::deserialize($setting->suspension_lists, true);
                    $models = C4gReservationSuspensionModel::findMultipleByIds($listIds);
                    if ($models) {
                        foreach ($models as $suspension) {
                            if ($suspension->suspension_dates) {
                                $dates = StringUtil::deserialize($suspension->suspension_dates, true);
                                foreach ($dates as $dateEntry) {
                                    if ($dateEntry['date']) {
                                        $dateStartStr = is_numeric($dateEntry['date']) ? date('Y-m-d', (int)$dateEntry['date']) : $dateEntry['date'];
                                        $exStart = strtotime($dateStartStr . ' 00:00:00');
                                        if (isset($dateEntry['date_end']) && $dateEntry['date_end']) {
                                            $dateEndStr = is_numeric($dateEntry['date_end']) ? date('Y-m-d', (int)$dateEntry['date_end']) : $dateEntry['date_end'];
                                            $exEnd = strtotime($dateEndStr . ' 23:59:59');
                                        } else {
                                            $exEnd = strtotime($dateStartStr . ' 23:59:59');
                                        }
                                        $suspensionDates[] = [
                                            'start' => $exStart,
                                            'end' => $exEnd,
                                            'caption' => $suspension->caption,
                                            'showCaption' => (bool)$suspension->showCaption,
                                            'showComment' => (bool)$suspension->showComment,
                                            'showCompany' => (bool)$suspension->showCompany,
                                            'comment' => $dateEntry['comment'] ?? '',
                                            'company' => $dateEntry['company'] ?? '',
                                            'priority' => 10
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach ($objectModels as $objId => $objModel) {
            if ($objModel && $objModel->days_exclusion) {
                $exclusions = StringUtil::deserialize($objModel->days_exclusion, true);
                foreach ($exclusions as $exclusion) {
                    if ($exclusion['date_exclusion']) {
                        $dateStartStr = is_numeric($exclusion['date_exclusion']) ? date('Y-m-d', (int)$exclusion['date_exclusion']) : $exclusion['date_exclusion'];
                        $exStart = strtotime($dateStartStr . ' 00:00:00');
                        if (isset($exclusion['date_exclusion_end']) && $exclusion['date_exclusion_end']) {
                            $dateEndStr = is_numeric($exclusion['date_exclusion_end']) ? date('Y-m-d', (int)$exclusion['date_exclusion_end']) : $exclusion['date_exclusion_end'];
                            $exEnd = strtotime($dateEndStr . ' 23:59:59');
                        } else {
                            $exEnd = strtotime($dateStartStr . ' 23:59:59');
                        }
                        $suspensionDates[] = [
                            'start' => $exStart,
                            'end' => $exEnd,
                            'caption' => $exclusion['reason_exclusion'] ?? '',
                            'showCaption' => true,
                            'showComment' => false,
                            'showCompany' => false,
                            'comment' => '',
                            'company' => '',
                            'priority' => 5
                        ];
                    }
                }
            }
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateYmd = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $dayStart = strtotime("$dateYmd 00:00:00");
            $dayEnd = strtotime("$dateYmd 23:59:59");

            $isGlobalSuspended = false;
            $suspensionText = '';
            $reservationTexts = [];
            $maxPriority = -1;
            foreach ($suspensionDates as $sDate) {
                if ($dayStart <= $sDate['end'] && $dayEnd >= $sDate['start']) {
                    $isGlobalSuspended = true;
                    if ($sDate['priority'] > $maxPriority) {
                        $currentText = '';
                        if ($sDate['showComment'] && $sDate['comment']) {
                            $currentText = $sDate['comment'];
                        } elseif ($sDate['showCompany'] && $sDate['company']) {
                            $currentText = $sDate['company'];
                        } elseif ($sDate['showCaption'] && $sDate['caption']) {
                            $currentText = $sDate['caption'];
                        }

                        if ($currentText) {
                            $suspensionText = $currentText;
                            $maxPriority = $sDate['priority'];
                        }
                    }
                }
            }

            $dayBookedCount = 0;
            $dayPartialCount = 0;
            if ($isGlobalSuspended) {
                $dayBookedCount = count($objects);
                $occupancy[$day] = [
                    'status' => 'booked',
                    'text' => $suspensionText
                ];
            } else {
                foreach ($objects as $objId) {
                    $objModel = $objectModels[$objId];
                    if (!$objModel) continue;

                    // Check opening hours / weekdays
                    $weekdayMap = [1 => 'oh_monday', 2 => 'oh_tuesday', 3 => 'oh_wednesday', 4 => 'oh_thursday', 5 => 'oh_friday', 6 => 'oh_saturday', 0 => 'oh_sunday'];
                    $currentWeekday = (int)date('w', $dayStart);
                    $weekdayField = $weekdayMap[$currentWeekday];
                    if (empty($objModel->$weekdayField)) {
                        $dayBookedCount++;
                        continue;
                    }

                    $isObjectExcluded = false;
                    if ($objModel->days_exclusion) {
                        $exclusions = StringUtil::deserialize($objModel->days_exclusion, true);
                        foreach ($exclusions as $exclusion) {
                            if ($exclusion['date_exclusion']) {
                                $dateStartStr = is_numeric($exclusion['date_exclusion']) ? date('Y-m-d', (int)$exclusion['date_exclusion']) : $exclusion['date_exclusion'];
                                $exStart = strtotime($dateStartStr . ' 00:00:00');
                                if (isset($exclusion['date_exclusion_end']) && $exclusion['date_exclusion_end']) {
                                    $dateEndStr = is_numeric($exclusion['date_exclusion_end']) ? date('Y-m-d', (int)$exclusion['date_exclusion_end']) : $exclusion['date_exclusion_end'];
                                    $exEnd = strtotime($dateEndStr . ' 23:59:59');
                                } else {
                                    $exEnd = strtotime($dateStartStr . ' 23:59:59');
                                }
                                    
                                if ($dayStart <= $exEnd && $dayEnd >= $exStart) {
                                    $isObjectExcluded = true;
                                    break;
                                }
                            }
                        }
                    }

                    if ($isObjectExcluded) {
                        $dayBookedCount++;
                        continue;
                    }

                    $objQuantity = $objModel->quantity ?: 1;
                    $objReservations = array_filter($reservations, function($r) use ($objId, $dayStart, $dayEnd) {
                        return $r['reservation_object'] == $objId && $r['beginDate'] <= $dayEnd && $r['endDate'] >= $dayStart;
                    });
                    
                    $bookedCount = count($objReservations);
                    if ($bookedCount >= $objQuantity) {
                        // Check if they cover the whole day
                        $coversWholeDay = false;
                        foreach ($objReservations as $res) {
                            if ($res['beginDate'] <= $dayStart && $res['endDate'] >= $dayEnd) {
                                $coversWholeDay = true;
                                if ($res['organisation'] && !in_array($res['organisation'], $reservationTexts)) {
                                    $reservationTexts[] = $res['organisation'];
                                }
                            }
                        }
                        if ($coversWholeDay) {
                            $dayBookedCount++;
                        } else {
                            $dayPartialCount++;
                        }
                    } elseif ($bookedCount > 0) {
                        $dayPartialCount++;
                        foreach ($objReservations as $res) {
                            if ($res['organisation'] && !in_array($res['organisation'], $reservationTexts)) {
                                $reservationTexts[] = $res['organisation'];
                            }
                        }
                    }
                }
            }

            if (!isset($occupancy[$day])) {
                $reservationText = implode(', ', $reservationTexts);
                if ($dayBookedCount >= count($objects) || $dayEnd < time()) {
                    $occupancy[$day] = ['status' => 'booked', 'text' => $reservationText];
                } elseif ($dayBookedCount > 0 || $dayPartialCount > 0) {
                    $occupancy[$day] = ['status' => 'partial', 'text' => $reservationText];
                } else {
                    $occupancy[$day] = ['status' => 'free', 'text' => ''];
                }
            }
        }

        return $occupancy;
    }
}
