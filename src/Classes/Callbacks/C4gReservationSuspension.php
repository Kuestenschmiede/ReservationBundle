<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @author con4gis contributors (see "authors.md")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

namespace con4gis\ReservationBundle\Classes\Callbacks;

use Contao\Backend;
use Contao\DataContainer;

class C4gReservationSuspension extends Backend
{
    /**
     * Renders a wizard to add a date range to the MultiColumnWizard.
     * @param DataContainer $dc
     * @return string
     */
    public function dateRangeWizard(DataContainer $dc)
    {
        $strLabel = $GLOBALS['TL_LANG']['tl_c4g_reservation_suspension']['date_range_wizard'][0] ?: "Zeitraum hinzufügen";
        $strPromptStart = $GLOBALS['TL_LANG']['tl_c4g_reservation_suspension']['date_range_start'];
        $strPromptEnd = $GLOBALS['TL_LANG']['tl_c4g_reservation_suspension']['date_range_end'];
        $strFormat = $GLOBALS['TL_CONFIG']['dateFormat'] ?: 'Y-m-d';

        $image = \Contao\Image::getHtml('pagedata.gif', $strLabel, 'style="vertical-align:middle;"');
        if (strpos($image, 'src=""') !== false || strpos($image, 'src=" "') !== false) {
            $image = '📅';
        }

        return '
        <div class="widget">
            <h3><label>'.$strLabel.'</label></h3>
            <div style="margin-top:5px;">
                <button type="button" class="tl_submit" onclick="C4gReservation.addDateRange(\'ctrl_suspension_dates\', \''. $strPromptStart .'\', \''. $strPromptEnd .'\', \''. $strFormat .'\'); return false;">'.$image.' '.$strLabel.'</button>
            </div>
        </div>
        <script>(function(){if(typeof C4gReservation==="undefined"){window.C4gReservation={};}C4gReservation.addDateRange=function(id,startPrompt,endPrompt,format){var parseDate=function(str,fmt){if(!str)return null;var parts=str.match(/(\d+)/g);if(!parts||parts.length<3)return new Date(str);var day,month,year;if(fmt==="d.m.Y"){day=parseInt(parts[0],10);month=parseInt(parts[1],10)-1;year=parseInt(parts[2],10);}else if(fmt==="m/d/Y"){month=parseInt(parts[0],10)-1;day=parseInt(parts[1],10);year=parseInt(parts[2],10);}else{year=parseInt(parts[0],10);month=parseInt(parts[1],10)-1;day=parseInt(parts[2],10);}var d=new Date(year,month,day);return(d.getFullYear()===year&&d.getMonth()===month&&d.getDate()===day)?d:null;};var start=prompt(startPrompt+" ("+format+")","");if(!start)return;var end=prompt(endPrompt+" ("+format+")","");if(!end)return;var startDate=parseDate(start,format);var endDate=parseDate(end,format);if(!startDate||!endDate||isNaN(startDate.getTime())||isNaN(endDate.getTime())){alert("Ungültiges Datum. Bitte Format "+format+" verwenden.");return;}if(startDate>endDate){alert("Startdatum muss vor dem Enddatum liegen.");return;}var table=document.getElementById(id);if(!table){table=document.querySelector('table[id^="ctrl_suspension_dates"]');}if(!table){var wrapper=document.getElementById(id);if(wrapper){table=wrapper.querySelector('table');}}if(!table){alert("Konnte die Tabelle suspension_dates nicht finden ("+id+").");return;}var currentDate=new Date(startDate);var processNextDay=function(){if(currentDate>endDate)return;var dateStr;var d=("0"+currentDate.getDate()).slice(-2);var m=("0"+(currentDate.getMonth()+1)).slice(-2);var y=currentDate.getFullYear();if(format==="d.m.Y"){dateStr=d+"."+m+"."+y;}else if(format==="m/d/Y"){dateStr=m+"/"+d+"/"+y;}else{dateStr=y+"-"+m+"-"+d;}var rows=table.querySelectorAll('tbody tr');if(rows.length===0){var globalAdd=table.querySelector('.add, .mcwAdd, a[data-command="add"], a[data-operations="new"], a[onclick*="add"], button[data-command="add"]');if(!globalAdd){globalAdd=document.querySelector('a[onclick*="'+id+'"][onclick*="add"]');}if(globalAdd){globalAdd.click();setTimeout(function(){processNextDay();},200);return;}}var lastRow=rows[rows.length-1];var dateInput=lastRow?lastRow.querySelector('input[id$="_date"]'):null;if(dateInput&&dateInput.value!==""&&dateInput.value!==null){var addButton=lastRow.querySelector('.add, a[href*="act=add"], .mcwAdd, a[data-command="add"], a[data-operations="new"], .op-new, button[data-command="add"]');if(!addButton){var buttons=lastRow.querySelectorAll('a, button, img');for(var i=0;i<buttons.length;i++){var el=buttons[i];var className=el.className||"";var onclickAttr=el.getAttribute('onclick')||"";var dataCommand=el.getAttribute('data-command')||"";var dataOps=el.getAttribute('data-operations')||"";if(className.indexOf('add')!==-1||className.indexOf('new')!==-1||onclickAttr.indexOf('add')!==-1||dataCommand==='add'||dataOps==='new'){addButton=el;break;}}}if(addButton){addButton.click();var attempts=0;var checkAndFill=function(){var newRows=table.querySelectorAll('tbody tr');var newLastRow=newRows[newRows.length-1];var newDateInput=newLastRow?newLastRow.querySelector('input[name*="[date]"]'):null;if(!newDateInput&&newLastRow){newDateInput=newLastRow.querySelector('input[id$="_date"]');}if(newDateInput&&(newRows.length>rows.length||(newDateInput.value===""||newDateInput.value===null))){newDateInput.value=dateStr;try{newDateInput.dispatchEvent(new Event('input',{bubbles:true}));newDateInput.dispatchEvent(new Event('change',{bubbles:true}));}catch(e){}currentDate.setDate(currentDate.getDate()+1);setTimeout(processNextDay,100);}else if(attempts<30){attempts++;setTimeout(checkAndFill,150);}else{console.error("MCW row could not be added after "+attempts+" attempts.");}};setTimeout(checkAndFill,100);}else{console.error("Add button not found in MCW row.");}}else if(dateInput){dateInput.value=dateStr;currentDate.setDate(currentDate.getDate()+1);setTimeout(processNextDay,50);}else{console.error("Date input not found in last row.");}};processNextDay();};})();</script>';
    }
}
