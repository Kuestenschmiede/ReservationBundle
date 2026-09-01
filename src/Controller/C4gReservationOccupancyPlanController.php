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

        $jumpToNext = $this->jump_to_next_possible_date === null ? true : (bool)$this->jump_to_next_possible_date;

        $planMaxTimestamp = $this->getPlanMaxReservationTimestamp($objects);
        $planMaxYear = $planMaxTimestamp !== null ? (int)date('Y', $planMaxTimestamp) : null;
        $planMaxMonth = $planMaxTimestamp !== null ? (int)date('m', $planMaxTimestamp) : null;
        $planMaxMonthTime = $planMaxTimestamp !== null ? strtotime(date('Y-m-01', $planMaxTimestamp)) : null;

        $hasMonthParam = Input::get('month') !== null && Input::get('month') !== '';
        $hasYearParam = Input::get('year') !== null && Input::get('year') !== '';

        if (!$hasMonthParam && !$hasYearParam) {
            $dateParam = Input::get('date');
            if ($dateParam) {
                $dateParamStr = is_numeric($dateParam) ? date('Y-m-d', (int)$dateParam) : trim((string)$dateParam);
                if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $dateParamStr, $m)) {
                    $month = sprintf('%02d', (int)$m[2]);
                    $year = (string)(int)$m[3];
                } elseif (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $dateParamStr, $m)) {
                    $month = sprintf('%02d', (int)$m[2]);
                    $year = (string)(int)$m[1];
                }
            }
        }

        $meta = $this->prepareOccupancyMetadata($objects);

        if (!$hasMonthParam && !$hasYearParam && !isset($month) && $jumpToNext) {
            $freeDateMonthYear = $this->findFirstFreeMonth($objects, 12, $meta);
            if ($freeDateMonthYear) {
                $month = $freeDateMonthYear['month'];
                $year = $freeDateMonthYear['year'];
                $reservations = $freeDateMonthYear['reservations'];
                $occupancy = $freeDateMonthYear['occupancy'];
            }
        }

        if (!isset($month) || !isset($year)) {
            $month = Input::get('month') ?: date('m');
            $year = Input::get('year') ?: date('Y');
            $time = strtotime("$year-$month-01");
            if ($time < strtotime(date('Y-m-01'))) {
                $month = date('m');
                $year = date('Y');
                $time = strtotime("$year-$month-01");
            } elseif ($planMaxMonthTime !== null && $time > $planMaxMonthTime) {
                $month = sprintf('%02d', $planMaxMonth);
                $year = (string)$planMaxYear;
                $time = strtotime("$year-$month-01");
            }
            $daysInMonth = date('t', $time);
            $reservations = $this->getReservations($objects, $month, $year);
            $occupancy = $this->calculateOccupancy($objects, $reservations, $daysInMonth, $month, $year, $meta);
        } else {
            $time = strtotime("$year-$month-01");
            if ($time < strtotime(date('Y-m-01'))) {
                $month = date('m');
                $year = date('Y');
                $time = strtotime("$year-$month-01");
            } elseif ($planMaxMonthTime !== null && $time > $planMaxMonthTime) {
                $month = sprintf('%02d', $planMaxMonth);
                $year = (string)$planMaxYear;
                $time = strtotime("$year-$month-01");
            }
            $daysInMonth = date('t', $time);
            if (!isset($occupancy)) {
                $reservations = $this->getReservations($objects, $month, $year);
                $occupancy = $this->calculateOccupancy($objects, $reservations, $daysInMonth, $month, $year, $meta);
            }
        }

        $month = sprintf('%02d', (int)$month);
        $year = (string)(int)$year;

        $firstWeekday = date('N', $time);

        $prevMonth = date('m', strtotime("-1 month", $time));
        $prevYear = date('Y', strtotime("-1 month", $time));
        $nextMonth = date('m', strtotime("+1 month", $time));
        $nextYear = date('Y', strtotime("+1 month", $time));

        $settings = C4gReservationSettingsModel::findAll();
        if ($settings && $settings->current()) {
            $this->session->setSessionValue('reservationSettings', $settings->current()->id);
        }

        $monthLabel = $GLOBALS['TL_LANG']['MSC']['month'] ?? 'Monat';
        $yearLabel = $GLOBALS['TL_LANG']['MSC']['year'] ?? 'Jahr';

        $curYear = (int)date('Y');
        $curMonth = (int)date('m');
        $prevMonthTime = strtotime("$prevYear-$prevMonth-01");
        $curMonthTime = strtotime(date('Y-m-01'));
        $nextMonthTime = strtotime("$nextYear-$nextMonth-01");

        $html = '<div id="c4g_occupancy_plan" class="occupancy-plan">';
        $html .= '<div class="calendar-nav">';
        if ($prevMonthTime < $curMonthTime) {
            $html .= '<span class="c4g-calendar-link nav-prev disabled" aria-disabled="true" style="pointer-events: none; opacity: 0.35;">&laquo;</span>';
        } else {
            $html .= '<a class="c4g-calendar-link nav-prev" href="' . Controller::addToUrl("month=$prevMonth&year=$prevYear", true, ['date']) . '" data-anchor="#c4g_occupancy_plan">&laquo;</a>';
        }
        
        $html .= '<div class="calendar-nav-selectors">';
        $html .= '<select class="c4g-calendar-select month-select" aria-label="' . $monthLabel . '" onchange="if(this.value){if(typeof c4gLoadOccupancyPlanMonth===\'function\'){c4gLoadOccupancyPlanMonth(this.value);}else{window.location.href=this.value;}}">';
        for ($m = 1; $m <= 12; $m++) {
            $mPadded = sprintf('%02d', $m);
            $monthName = $GLOBALS['TL_LANG']['MONTHS'][$m - 1] ?? date('F', mktime(0, 0, 0, $m, 1));
            $url = Controller::addToUrl("month=$mPadded&year=$year", true, ['date']);
            $selected = ((int)$month === $m) ? ' selected="selected"' : '';
            $disabled = false;
            if ((int)$year === $curYear && $m < $curMonth) {
                $disabled = true;
            }
            if ($planMaxYear !== null) {
                if ((int)$year === $planMaxYear && $m > $planMaxMonth) {
                    $disabled = true;
                } elseif ((int)$year > $planMaxYear) {
                    $disabled = true;
                }
            }
            $disabledAttr = $disabled ? ' disabled="disabled"' : '';
            $html .= '<option value="' . $url . '"' . $selected . $disabledAttr . '>' . $monthName . '</option>';
        }
        $html .= '</select>';

        $minYear = $curYear;
        $maxYear = $planMaxYear !== null ? $planMaxYear : max($curYear + 15, (int)$year + 5);
        if ($maxYear < $minYear) {
            $maxYear = $minYear;
        }
        $html .= '<select class="c4g-calendar-select year-select" aria-label="' . $yearLabel . '" onchange="if(this.value){if(typeof c4gLoadOccupancyPlanMonth===\'function\'){c4gLoadOccupancyPlanMonth(this.value);}else{window.location.href=this.value;}}">';
        for ($y = $minYear; $y <= $maxYear; $y++) {
            $url = Controller::addToUrl("month=$month&year=$y", true, ['date']);
            $selected = ((int)$year === $y) ? ' selected="selected"' : '';
            $html .= '<option value="' . $url . '"' . $selected . '>' . $y . '</option>';
        }
        $html .= '</select>';
        $html .= '</div>';

        if ($planMaxMonthTime !== null && $nextMonthTime > $planMaxMonthTime) {
            $html .= '<span class="c4g-calendar-link nav-next disabled" aria-disabled="true" style="pointer-events: none; opacity: 0.35;">&raquo;</span>';
        } else {
            $html .= '<a class="c4g-calendar-link nav-next" href="' . Controller::addToUrl("month=$nextMonth&year=$nextYear", true, ['date']) . '" data-anchor="#c4g_occupancy_plan">&raquo;</a>';
        }
        $html .= '</div>';

        $html .= '<table class="calendar">';
        $html .= '<thead><tr>';
        if (empty($GLOBALS['TL_LANG']['DAYS_SHORT'])) {
            Controller::loadLanguageFile('default');
        }
        $daysShort = $GLOBALS['TL_LANG']['DAYS_SHORT'] ?? ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'];
        $daysShort[] = array_shift($daysShort);
        foreach ($daysShort as $dayShort) {
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
            $dayTimestamp = strtotime($dateStr);
            $dayWeekday = (int)date('N', $dayTimestamp);
            $isWeekend = ($dayWeekday === 6 || $dayWeekday === 7);

            $dateFormatted = Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], $dayTimestamp);
            // Ensure no leading/trailing whitespace which might trip up the regex
            $dateFormatted = trim($dateFormatted);
            $occData = $occupancy[$day];
            $status = $occData['status']; // 'free', 'booked', 'partial'
            $text = $occData['text'];
            $splitClass = !empty($occData['split']) ? ' ' . $occData['split'] : '';
            $weekendClass = $isWeekend ? ' weekend' : '';
            
            $class = "day $status" . $weekendClass . $splitClass;
            $link = '';
            if (($status === 'free' || $status === 'partial') && $this->reservation_form_site) {
                $page = PageModel::findByPk($this->reservation_form_site);
                if ($page) {
                    $link = $page->getFrontendUrl();
                    $link .= (str_contains($link, '?') ? '&' : '?') . 'date=' . $dateFormatted;
                    $anchor = '#c4g_reservation_form';
                }
            }

            $customStyle = '';
            if (!empty($occData['color'])) {
                $customStyle = ' style="background-color: ' . htmlspecialchars($this->formatColor($occData['color'])) . ' !important;"';
            }

            $html .= '<td class="' . $class . '"' . $customStyle . '>';
            if ($link) {
                $html .= '<a class="c4g-calendar-link" href="' . $link . '" data-date="' . $dateFormatted . '" data-anchor="' . $anchor . '"><span class="day-num">' . $day . '</span>';
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

            if ($status === 'partial' && empty($occData['split'])) {
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

        $weekendCss = '';
        if (!empty($this->colorize_weekends)) {
            $wColor = !empty($this->weekend_color) ? $this->formatColor($this->weekend_color) : '#eaeaea';
            $weekendCss = ".occupancy-plan td.weekend, .occupancy-plan td.weekend.free, .occupancy-plan td.weekend.booked { background-color: {$wColor}; }";
        }

        $style = '<style>
            .occupancy-plan { width: 100%; max-width: 800px; }
            .occupancy-plan .calendar-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; gap: 10px; }
            .occupancy-plan .calendar-nav-selectors { display: flex; gap: 8px; align-items: center; }
            .occupancy-plan .calendar-nav select { padding: 4px 8px; font-size: 1rem; border: 1px solid #ccc; border-radius: 4px; background-color: #fff; cursor: pointer; }
            .occupancy-plan .calendar-nav a.c4g-calendar-link, .occupancy-plan .calendar-nav span.c4g-calendar-link { font-size: 1.25rem; text-decoration: none; font-weight: bold; padding: 2px 8px; color: inherit; }
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
            .occupancy-plan td.split-morning-booked {
                background: linear-gradient(135deg, #f8d7da 50%, #d4edda 50%) !important;
                color: #155724;
            }
            .occupancy-plan td.split-afternoon-booked {
                background: linear-gradient(135deg, #d4edda 50%, #f8d7da 50%) !important;
                color: #155724;
            }
            ' . $weekendCss . '
            .occupancy-plan td .day-num { font-weight: bold; display: block; margin-bottom: 2px; }
            .occupancy-plan td .day-text { hyphens: auto; overflow-wrap: normal; word-break: normal; font-size: .75em; line-height: 1.1; /* word-wrap: break-word; */ }
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

        $script = '<script>
        (function() {
            window.c4gLoadOccupancyPlanMonth = function(url, skipPushState) {
                if (!url) return;
                var container = document.getElementById("c4g_occupancy_plan");
                if (!container) {
                    window.location.href = url;
                    return;
                }
                container.style.opacity = "0.5";
                container.style.pointerEvents = "none";
                fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" } })
                    .then(function(res) {
                        if (!res.ok) throw new Error("Network response was not ok");
                        return res.text();
                    })
                    .then(function(html) {
                        var parser = new DOMParser();
                        var doc = parser.parseFromString(html, "text/html");
                        var newContainer = doc.getElementById("c4g_occupancy_plan");
                        if (newContainer && container.parentNode) {
                            container.parentNode.replaceChild(newContainer, container);
                            if (!skipPushState && window.history && window.history.pushState) {
                                window.history.pushState({ c4g_occupancy_url: url }, "", url);
                            }
                            initOccupancyPlan();
                        } else {
                            window.location.href = url;
                        }
                    })
                    .catch(function(err) {
                        console.error("Error loading occupancy plan month:", err);
                        window.location.href = url;
                    });
            };

            function initOccupancyPlan() {
                var container = document.getElementById("c4g_occupancy_plan");
                if (!container) return;
                container.style.opacity = "1";
                container.style.pointerEvents = "auto";

                var links = container.querySelectorAll("td a.c4g-calendar-link");
                links.forEach(function(link) {
                    link.addEventListener("click", function(e) {
                        var dateVal = link.getAttribute("data-date");
                        if (!dateVal) return;
                        var formInputs = document.querySelectorAll(\'input[id^="c4g_beginDate_"]:not([id$="_picker"])\');
                        if (formInputs && formInputs.length > 0) {
                            e.preventDefault();
                            formInputs.forEach(function(input) {
                                input.value = dateVal;
                                var pickerId = input.id + "_picker";
                                var picker = document.getElementById(pickerId);
                                if (picker && picker.datepicker && typeof picker.datepicker.setDate === "function") {
                                    var tv = dateVal;
                                    if (dateVal.indexOf("-") !== -1 && dateVal.length === 10) {
                                        var pts = dateVal.split("-");
                                        tv = new Date(pts[0], pts[1] - 1, pts[2]);
                                    }
                                    picker.datepicker.setDate(tv);
                                }
                                var listId = input.id.replace("c4g_beginDate_", "");
                                if (typeof input.onchange === "function") {
                                    input.onchange();
                                } else if (typeof setTimeset === "function") {
                                    setTimeset(dateVal, listId, 0, 0);
                                } else {
                                    var evt = new Event("change", { bubbles: true });
                                    input.dispatchEvent(evt);
                                }
                            });
                            if (window.history && window.history.pushState) {
                                var newUrl = new URL(window.location.href);
                                newUrl.searchParams.set("date", dateVal);
                                window.history.pushState({ date: dateVal }, "", newUrl.toString());
                            }
                            var target = document.getElementById("c4g_reservation_form") || formInputs[0];
                            if (target) {
                                target.scrollIntoView({ behavior: "smooth", block: "start" });
                                if (typeof target.focus === "function") {
                                    target.focus({ preventScroll: true });
                                }
                            }
                        }
                    });
                });

                var navLinks = container.querySelectorAll(".calendar-nav a.nav-prev, .calendar-nav a.nav-next");
                navLinks.forEach(function(link) {
                    link.addEventListener("click", function(e) {
                        e.preventDefault();
                        var targetUrl = link.getAttribute("href");
                        if (targetUrl) {
                            window.c4gLoadOccupancyPlanMonth(targetUrl);
                        }
                    });
                });
            }

            if (!window.c4gOccupancyPlanPopstateBound) {
                window.c4gOccupancyPlanPopstateBound = true;
                window.addEventListener("popstate", function(e) {
                    if (e.state && e.state.c4g_occupancy_url) {
                        window.c4gLoadOccupancyPlanMonth(e.state.c4g_occupancy_url, true);
                    }
                });
            }

            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", initOccupancyPlan);
            } else {
                initOccupancyPlan();
            }
        })();
        </script>';

        return $style . $html . $script;
    }

    protected function findFirstFreeMonth($objects, $maxMonths = 12, ?array $meta = null): ?array
    {
        if ($meta === null) {
            $meta = $this->prepareOccupancyMetadata($objects);
        }
        if (empty($meta['validObjects'])) {
            return null;
        }

        $planMaxTimestamp = $this->getPlanMaxReservationTimestamp($objects, $meta['objectModels']);
        $planMaxMonthTime = $planMaxTimestamp !== null ? strtotime(date('Y-m-01', $planMaxTimestamp)) : null;

        $curTime = strtotime(date('Y-m-01'));
        for ($i = 0; $i < $maxMonths; $i++) {
            $checkTime = strtotime("+$i month", $curTime);
            if ($planMaxMonthTime !== null && $checkTime > $planMaxMonthTime) {
                break;
            }
            $checkMonth = date('m', $checkTime);
            $checkYear = date('Y', $checkTime);
            $daysInMonth = date('t', $checkTime);

            $reservations = $this->getReservations($objects, $checkMonth, $checkYear);
            $occupancy = $this->calculateOccupancy($objects, $reservations, $daysInMonth, $checkMonth, $checkYear, $meta);

            foreach ($occupancy as $dayData) {
                if (in_array($dayData['status'], ['free', 'partial'], true)) {
                    return [
                        'month' => $checkMonth,
                        'year' => $checkYear,
                        'reservations' => $reservations,
                        'occupancy' => $occupancy,
                    ];
                }
            }
        }

        return null;
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

    protected function prepareOccupancyMetadata(array $objects): array
    {
        $objectModels = [];
        $validObjects = [];
        foreach ($objects as $objId) {
            $model = C4gReservationObjectModel::findByPk($objId);
            if ($model) {
                $objectModels[$objId] = $model;
                $validObjects[] = $objId;
            }
        }

        if (empty($validObjects)) {
            return ['validObjects' => [], 'objectModels' => [], 'suspensionDates' => []];
        }

        usort($validObjects, function($a, $b) use ($objectModels) {
            $modelA = $objectModels[$a];
            $modelB = $objectModels[$b];
            $sortA = (int)($modelA->sorting ?? 0);
            $sortB = (int)($modelB->sorting ?? 0);
            if ($sortA !== $sortB) {
                return $sortA <=> $sortB;
            }
            $captionCmp = strcmp((string)($modelA->caption ?? ''), (string)($modelB->caption ?? ''));
            if ($captionCmp !== 0) {
                return $captionCmp;
            }
            return (int)$a <=> (int)$b;
        });

        $suspensionDates = [];
        $suspensionModels = [];

        $settings = C4gReservationSettingsModel::findAll();
        if ($settings) {
            foreach ($settings as $setting) {
                if ($setting->suspension_lists) {
                    $listIds = StringUtil::deserialize($setting->suspension_lists, true);
                    $models = C4gReservationSuspensionModel::findMultipleByIds($listIds);
                    if ($models) {
                        foreach ($models as $suspension) {
                            $suspensionModels[$suspension->id] = $suspension;
                        }
                    }
                }
            }
        }

        $allSuspensions = C4gReservationSuspensionModel::findAll();
        if ($allSuspensions) {
            foreach ($allSuspensions as $suspension) {
                $suspensionModels[$suspension->id] = $suspension;
            }
        }

        foreach ($suspensionModels as $suspension) {
            if ($suspension->suspension_dates) {
                $dates = StringUtil::deserialize($suspension->suspension_dates, true);
                foreach ($dates as $dateEntry) {
                    if (!empty($dateEntry['date'])) {
                        $exStart = $this->parseDateToTimestamp($dateEntry['date'], false);
                        if (!empty($dateEntry['date_end'])) {
                            $exEnd = $this->parseDateToTimestamp($dateEntry['date_end'], true);
                        } else {
                            $exEnd = $this->parseDateToTimestamp($dateEntry['date'], true);
                        }

                        if ($exStart !== null && $exEnd !== null) {
                            $comment = trim((string)($dateEntry['comment'] ?? ''));
                            $company = trim((string)($dateEntry['company'] ?? ''));
                            $caption = trim((string)($suspension->caption ?? ''));

                            $suspensionDates[] = [
                                'start' => $exStart,
                                'end' => $exEnd,
                                'caption' => $caption,
                                'showCaption' => (bool)$suspension->showCaption,
                                'showComment' => (bool)$suspension->showComment,
                                'showCompany' => (bool)$suspension->showCompany,
                                'comment' => $comment,
                                'company' => $company,
                                'color' => !empty($dateEntry['color']) ? $dateEntry['color'] : (!empty($suspension->suspension_color) ? $suspension->suspension_color : ''),
                                'priority' => 10
                            ];
                        }
                    }
                }
            }
        }

        foreach ($objectModels as $objId => $objModel) {
            if ($objModel && $objModel->days_exclusion) {
                $exclusions = StringUtil::deserialize($objModel->days_exclusion, true);
                $exclusionText = trim((string)($objModel->days_exclusion_text ?? ''));
                foreach ($exclusions as $exclusion) {
                    if (!empty($exclusion['date_exclusion'])) {
                        $exStart = $this->parseDateToTimestamp($exclusion['date_exclusion'], false);
                        if (!empty($exclusion['date_exclusion_end'])) {
                            $exEnd = $this->parseDateToTimestamp($exclusion['date_exclusion_end'], true);
                        } else {
                            $exEnd = $this->parseDateToTimestamp($exclusion['date_exclusion'], true);
                        }

                        if ($exStart !== null && $exEnd !== null) {
                            $reason = trim((string)($exclusion['reason_exclusion'] ?? ($exclusion['text'] ?? ($exclusion['comment'] ?? $exclusionText))));
                            if ($reason === '') {
                                $reason = $exclusionText ?: 'Gesperrt';
                            }
                            $suspensionDates[] = [
                                'start' => $exStart,
                                'end' => $exEnd,
                                'caption' => $reason,
                                'showCaption' => true,
                                'showComment' => true,
                                'showCompany' => false,
                                'comment' => $reason,
                                'company' => '',
                                'color' => '',
                                'priority' => 5
                            ];
                        }
                    }
                }
            }
        }

        return [
            'validObjects' => $validObjects,
            'objectModels' => $objectModels,
            'suspensionDates' => $suspensionDates,
        ];
    }

    protected function calculateOccupancy($objects, $reservations, $daysInMonth, $month, $year, ?array $meta = null)
    {
        if ($meta === null) {
            $meta = $this->prepareOccupancyMetadata($objects);
        }

        $validObjects = $meta['validObjects'];
        $objectModels = $meta['objectModels'];
        $suspensionDates = $meta['suspensionDates'];

        if (empty($validObjects)) {
            return [];
        }

        $occupancy = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateYmd = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $dayStart = strtotime("$dateYmd 00:00:00");
            $dayEnd = strtotime("$dateYmd 23:59:59");

            $isGlobalSuspended = false;
            $suspensionText = '';
            $suspensionColor = '';
            $reservationTexts = [];
            $maxPriority = -1;
            $maxColorPriority = -1;
            foreach ($suspensionDates as $sDate) {
                if ($dayStart <= $sDate['end'] && $dayEnd >= $sDate['start']) {
                    $isGlobalSuspended = true;
                    if (!empty($sDate['color']) && $sDate['priority'] > $maxColorPriority) {
                        $suspensionColor = $sDate['color'];
                        $maxColorPriority = $sDate['priority'];
                    }
                    if ($sDate['priority'] > $maxPriority || ($sDate['priority'] === $maxPriority && empty($suspensionText))) {
                        $currentText = '';
                        if ($sDate['showComment'] && !empty($sDate['comment'])) {
                            $currentText = $sDate['comment'];
                        } elseif ($sDate['showCompany'] && !empty($sDate['company'])) {
                            $currentText = $sDate['company'];
                        } elseif ($sDate['showCaption'] && !empty($sDate['caption'])) {
                            $currentText = $sDate['caption'];
                        }

                        if ($currentText === '') {
                            if (!empty($sDate['comment'])) {
                                $currentText = $sDate['comment'];
                            } elseif (!empty($sDate['caption'])) {
                                $currentText = $sDate['caption'];
                            } elseif (!empty($sDate['company'])) {
                                $currentText = $sDate['company'];
                            }
                        }

                        if ($currentText !== '') {
                            $suspensionText = $currentText;
                            $maxPriority = $sDate['priority'];
                        }
                    }
                }
            }

            $dayBookedCount = 0;
            $dayPartialCount = 0;
            $objectBookedMap = [];

            if ($isGlobalSuspended) {
                $dayBookedCount = count($validObjects);
                $occupancy[$day] = [
                    'status' => 'booked',
                    'text' => $suspensionText,
                    'color' => $suspensionColor,
                    'split' => ''
                ];
            } else {
                foreach ($validObjects as $objId) {
                    $objModel = $objectModels[$objId];
                    if (!$objModel) continue;

                    // Check opening hours / weekdays
                    $weekdayMap = [1 => 'oh_monday', 2 => 'oh_tuesday', 3 => 'oh_wednesday', 4 => 'oh_thursday', 5 => 'oh_friday', 6 => 'oh_saturday', 0 => 'oh_sunday'];
                    $currentWeekday = (int)date('w', $dayStart);
                    $weekdayField = $weekdayMap[$currentWeekday];

                    $openingHours = StringUtil::deserialize($objModel->$weekdayField, true);
                    $hasOpeningHours = false;

                    if (!empty($openingHours)) {
                        foreach ($openingHours as $period) {
                            $timeBegin = (isset($period['time_begin']) && is_numeric($period['time_begin']) && $period['time_begin'] !== '') ? (int)$period['time_begin'] : false;
                            $timeEnd = (isset($period['time_end']) && is_numeric($period['time_end']) && $period['time_end'] !== '') ? (int)$period['time_end'] : false;

                            if ($timeBegin !== false && $timeEnd !== false && !($timeBegin === 0 && $timeEnd === 0) && ($timeBegin !== $timeEnd)) {
                                $dateFrom = !empty($period['date_from']) ? (is_numeric($period['date_from']) ? (int)$period['date_from'] : strtotime($period['date_from'])) : 0;
                                $dateTo = !empty($period['date_to']) ? (is_numeric($period['date_to']) ? (int)$period['date_to'] : strtotime($period['date_to'])) : 0;

                                $fromValid = empty($dateFrom) || $dayEnd >= $dateFrom;
                                $toValid = empty($dateTo) || $dayStart <= $dateTo;

                                if ($fromValid && $toValid) {
                                    $hasOpeningHours = true;
                                    break;
                                }
                            }
                        }
                    }

                    if (!$hasOpeningHours) {
                        $dayBookedCount++;
                        $objectBookedMap[$objId] = true;
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
                        $objectBookedMap[$objId] = true;
                        continue;
                    }

                    $maxDays = isset($objModel->max_reservation_day) && is_numeric($objModel->max_reservation_day) ? (int)$objModel->max_reservation_day : 0;
                    if ($maxDays > 0) {
                        $objMaxDate = strtotime("+$maxDays days", strtotime(date('Y-m-d 23:59:59')));
                        if ($dayStart > $objMaxDate) {
                            $dayBookedCount++;
                            $objectBookedMap[$objId] = true;
                            continue;
                        }
                    }

                    $minDays = isset($objModel->min_reservation_day) && is_numeric($objModel->min_reservation_day) ? (int)$objModel->min_reservation_day : 0;
                    if ($minDays > 0) {
                        $objMinDate = strtotime("+$minDays days", strtotime(date('Y-m-d 00:00:00')));
                        if ($dayEnd < $objMinDate) {
                            $dayBookedCount++;
                            $objectBookedMap[$objId] = true;
                            continue;
                        }
                    }

                    $objQuantity = $objModel->quantity ?: 1;
                    $objReservations = array_filter($reservations, function($r) use ($objId, $dayStart, $dayEnd) {
                        return $r['reservation_object'] == $objId && $r['beginDate'] <= $dayEnd && $r['endDate'] >= $dayStart;
                    });
                    
                    $bookedCount = count($objReservations);
                    if ($bookedCount >= $objQuantity) {
                        $dayBookedCount++;
                        $objectBookedMap[$objId] = true;
                        if (!empty($this->show_occupancy_name)) {
                            foreach ($objReservations as $res) {
                                $org = isset($res['organisation']) ? trim((string)$res['organisation']) : '';
                                $lastname = isset($res['lastname']) ? trim((string)$res['lastname']) : '';
                                $name = $org !== '' ? $org : $lastname;
                                if ($name !== '' && !in_array($name, $reservationTexts, true)) {
                                    $reservationTexts[] = $name;
                                }
                            }
                        }
                    } elseif ($bookedCount > 0) {
                        $dayPartialCount++;
                        $objectBookedMap[$objId] = false;
                        if (!empty($this->show_occupancy_name)) {
                            foreach ($objReservations as $res) {
                                $org = isset($res['organisation']) ? trim((string)$res['organisation']) : '';
                                $lastname = isset($res['lastname']) ? trim((string)$res['lastname']) : '';
                                $name = $org !== '' ? $org : $lastname;
                                if ($name !== '' && !in_array($name, $reservationTexts, true)) {
                                    $reservationTexts[] = $name;
                                }
                            }
                        }
                    } else {
                        $objectBookedMap[$objId] = false;
                    }
                }
            }

            if (!isset($occupancy[$day])) {
                $reservationText = $suspensionText ?: implode(', ', $reservationTexts);
                $split = '';
                if (count($validObjects) === 2 && count($objectBookedMap) === 2) {
                    $firstId = $validObjects[0];
                    $secondId = $validObjects[1];
                    $firstBooked = !empty($objectBookedMap[$firstId]);
                    $secondBooked = !empty($objectBookedMap[$secondId]);
                    if ($firstBooked && !$secondBooked) {
                        $split = 'split-morning-booked';
                    } elseif (!$firstBooked && $secondBooked) {
                        $split = 'split-afternoon-booked';
                    }
                }

                if ($dayBookedCount >= count($validObjects) || $dayEnd < time()) {
                    $occupancy[$day] = ['status' => 'booked', 'text' => $reservationText, 'color' => $suspensionColor, 'split' => ''];
                } elseif ($dayBookedCount > 0 || $dayPartialCount > 0 || $split !== '') {
                    $occupancy[$day] = ['status' => 'partial', 'text' => $reservationText, 'color' => $suspensionColor, 'split' => $split];
                } else {
                    $occupancy[$day] = ['status' => 'free', 'text' => $reservationText, 'color' => $suspensionColor, 'split' => ''];
                }
            }
        }

        return $occupancy;
    }

    protected function formatColor($color): string
    {
        $color = trim((string)$color);
        if ($color === '') {
            return '';
        }
        if (preg_match('/^[0-9a-fA-F]{3,8}$/', $color)) {
            return '#' . $color;
        }
        return $color;
    }

    protected function parseDateToTimestamp($rawDate, bool $isEndOfDay = false): ?int
    {
        if (empty($rawDate)) {
            return null;
        }
        if (is_numeric($rawDate)) {
            $dateStr = date('Y-m-d', (int)$rawDate);
        } else {
            $rawDate = trim((string)$rawDate);
            if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $rawDate, $m)) {
                $dateStr = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
            } else {
                $ts = strtotime($rawDate);
                if ($ts === false) {
                    return null;
                }
                $dateStr = date('Y-m-d', $ts);
            }
        }
        $timeStr = $isEndOfDay ? '23:59:59' : '00:00:00';
        $res = strtotime($dateStr . ' ' . $timeStr);
        return $res !== false ? $res : null;
    }

    protected function getPlanMaxReservationTimestamp(array $objects, array $objectModels = []): ?int
    {
        $todayEnd = strtotime(date('Y-m-d 23:59:59'));
        $maxTimestamps = [];
        $hasUnrestricted = false;

        foreach ($objects as $objId) {
            $model = $objectModels[$objId] ?? C4gReservationObjectModel::findByPk($objId);
            if ($model) {
                $maxDays = isset($model->max_reservation_day) && is_numeric($model->max_reservation_day) ? (int)$model->max_reservation_day : 0;
                if ($maxDays > 0) {
                    $maxTimestamps[] = strtotime("+$maxDays days", $todayEnd);
                } else {
                    $hasUnrestricted = true;
                }
            }
        }

        if (empty($maxTimestamps) || $hasUnrestricted) {
            return null;
        }

        return max($maxTimestamps);
    }
}
