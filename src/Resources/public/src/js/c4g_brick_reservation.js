/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 8
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2022, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

function getWeekdate(date) {

    //ToDo internationalize
    if (date.indexOf(".") > 0) {
        var arrDate = date.split(".")
        var y = arrDate[2];
        var m = arrDate[1];
        var d = arrDate[0];
        //important for safari browser
        m = m<10?"0"+m:m;
        d = d<10?"0"+d:d;

        date = y + "/" + m + "/" + d;
    } else if (date.indexOf("/") > 0) {
        var arrDate = date.split("/")
        var y = arrDate[2];
        var m = arrDate[1];
        var d = arrDate[0];
        //important for safari browser
        m = m<10?"0"+m:m;
        d = d<10?"0"+d:d;

        date = y + "/" + m + "/" + d;
    }

    var jsdate = new Date(date);
    return jsdate.getDay();
}

function isWeekday(datestr, fieldName) {
    var dateArr = datestr.split('--');
    if (dateArr && dateArr[1]) {
        if (getWeekdate(dateArr[0]) == dateArr[1]) {
            return true;
        }
    }
    return false;
}

function setObjectId(object, typeid, showDateTime = 0) {
    var objectParam = object ? jQuery(object).attr('data-object') : false;
    var typeId = typeid;
    var selectField = document.getElementById("c4g_reservation_object_"+typeId);
    var reservationObjects = jQuery(document.getElementsByClassName("displayReservationObjects"));
    var emptyOption = null;
    var objects = null;
    var values = '';

    if (selectField) {
        jQuery(selectField).show();
        reservationObjects ? reservationObjects.show() : false;
        if (objectParam) {
            //values = objectParam.split("_")[2];
            objects = objectParam.split('-');

            if (!jQuery(object).is(":disabled")) {
                jQuery(selectField).val(objects[0]);
                jQuery(selectField).attr('value',objects[0]);
                jQuery(selectField).change();
            }

            values = objects ? objects : values
        }
    }
    hideOptions(reservationObjects, typeId, values, showDateTime);
    return true;
}

function hideOptions(reservationObjects, typeId, values, showDateTime) {
    if (reservationObjects) {
        if (typeId == -1) {
            var typeField = document.getElementById("c4g_reservation_type");
            typeId = typeField ? typeField.value : -1;
        }
        var selectField = document.getElementById("c4g_reservation_object_"+typeId);
        var first = -1;
        var firstValueParam = 0;
        if (selectField) {
            for (i = 0; i < selectField.options.length; i++) {
                var option = selectField.options[i];
                var min = option.getAttribute('min') ? parseInt(option.getAttribute('min')) : 1;
                var max = option.getAttribute('max') ? parseInt(option.getAttribute('max')) : 0;
                var desiredCapacity = document.getElementById("c4g_desiredCapacity_"+typeId);
                var capacity = desiredCapacity ? desiredCapacity.value : 0;

                //not in values
                var foundValue = false;
                if (jQuery.isArray(values)) {
                    for (j = 0; j < values.length; j++) {
                        if (values[j] == option.value) {
                            if (j == 0) {
                                firstValueParam = values[j];
                            }
                            foundValue = true;
                            break;
                        }
                    }
                } else if (parseInt(values) >= 0) {
                    if (values == option.value) {
                        firstValueParam = values;
                        foundValue = true;
                    }
                }

                if (!foundValue && (option.value != -1)) {
                    jQuery(selectField).children('option[value="'+option.value+'"]').attr('disabled','disabled');
                } else if (option.value != -1) {
                    jQuery(selectField).children('option[value="'+option.value+'"]').removeAttr('disabled');
                    if (min && capacity && (capacity > 0)) {
                        if ((capacity < min) || (max && (capacity > max))) {
                            jQuery(selectField).children('option[value="'+option.value+'"]').attr('disabled','disabled');
                        } else {
                            jQuery(selectField).children('option[value="'+option.value+'"]').removeAttr('disabled');

                            if ((firstValueParam >= 0)  && (option.value == firstValueParam)) {
                                first = firstValueParam;
                            } else {
                                if ((first == -1) && (option.value != -1)) {
                                    first = option.value;
                                }
                            }
                        }
                    } else {
                        jQuery(selectField).children('option[value="'+option.value+'"]').removeAttr('disabled');
                        if ((firstValueParam >= 0)  && (option.value == firstValueParam)) {
                            first = firstValueParam;
                        } else {
                            if ((first == -1) && (option.value != -1)) {
                                first = option.value;
                            }
                        }
                    }
                }

                if (showDateTime && (option.value != -1) && foundValue) {
                    var text = jQuery(selectField).children('option[value="'+option.value+'"]').text();
                    var date = '';
                    var time = '';

                    var dateFields = document.querySelectorAll('.c4g__form-date-container .c4g_beginDate_'+typeId);
                    if (dateFields) {
                        for (k = 0; k < dateFields.length; k++) {
                            var dateField = dateFields[k];
                            if (dateField && dateField.value) {
                                date = dateField.value;
                                break;
                            }
                        }
                    }

                    var radioButton = jQuery('.reservation_time_button_'+typeId+' input[type = "radio"]:checked');
                    if (radioButton) {
                        for (k = 0; k < radioButton.length; k++) {
                            var button = radioButton[k];
                            if (button) {
                                var label = jQuery('label[for="'+ jQuery(button).attr('id') +'"]');
                                time = label ? label[0].firstChild.nodeValue : '';
                                break;
                            }
                        }
                    }

                    if (text && (date != '') && (time != '')) {
                        var pos = text.lastIndexOf(' (');
                        if (pos != -1) {
                            text = text.substr(0, pos);
                        }

                        jQuery(selectField).children('option[value="'+option.value+'"]').text(
                            text + '\u00A0('+date+'\u00A0'+time+')'
                        );
                    }
                }
            }

            if (parseInt(first) >= 0) {
                jQuery(selectField).val(first).change();
                jQuery(selectField).children('option[value="'+first+'"]').removeAttr('disabled');
                jQuery(selectField).children('option[value="-1"]').attr('disabled','disabled');

                jQuery(selectField).removeAttr('disabled');
            } else {
                jQuery(selectField).children('option[value="-1"]').removeAttr('disabled');
                jQuery(selectField).val("-1").change();
                jQuery(selectField).prop("disabled", true);
            }
        }
    }

    checkEventFields();
}

function checkType(dateField, event) {
    if (event) {
        return jQuery(dateField).parent().parent().hasClass('begindate-event') ? true : false;
    } else {
        return jQuery(dateField).parent().parent().hasClass('begin-date') ? true : false
    }
}

function setReservationForm(typeId, showDateTime, event) {
    jQuery(".reservation-id").hide();

    if (typeId == -1) {
        var typeField = document.getElementById("c4g_reservation_type");
        typeId = typeField ? typeField.value : -1;

        if (!event) {
            var selectedIndex = typeField.selectedIndex;
            var selectedOption = typeField.options[selectedIndex];
            if (selectedOption) {
                event = selectedOption.getAttribute('type') == 2 ? true : false;
            }

        }
    }

    var capacityField = jQuery('.c4g__form-int--desiredCapacity input');
    if (capacityField) {
        var value = capacityField.val();
        if (capacityField.attr('max') && (value > capacityField.attr('max'))) {
            capacityField.val(capacityField.attr('max'));
        }
        if (capacityField.attr('min') && (value < capacityField.attr('min'))) {
            capacityField.val(capacityField.attr('min'));
        }
    }

    var dateId = 'c4g_beginDate_'+typeId;
    if (document.getElementById(dateId)) {
        setTimeset(document.getElementById(dateId), typeId, showDateTime);
    } else if (event) {
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        const eventId = urlParams.get('event')

        if (eventId) {
            var dateId = 'c4g_beginDateEvent_' + typeId + '-22' + eventId;
            if (document.getElementById(dateId)) {
                setTimeset(document.getElementById(dateId), typeId, showDateTime);
                checkEventFields();
            }
        } else {
            var dateFields = document.getElementsByClassName('c4g__form-date-input');
            if (dateFields) {
                for (i = 0; i < dateFields.length; i++) {
                    var dateField = dateFields[i];
                    if (dateField && checkType(dateField, event) && dateField.value) {
                        var fieldId = dateField.id;
                        if (fieldId && fieldId.indexOf('c4g_beginDateEvent_' + typeId + '-22')) {
                            setTimeset(dateField, typeId, showDateTime);
                            checkEventFields();
                            break;
                        }
                    }
                }
            }
        }
    }

    document.getElementsByClassName('c4g__spinner-wrapper')[0].style.display = "none";
}

function checkTimelist(value, timeList) {
    var arrIndex = -1;

    if (value && timeList) {
        for (idx=0; idx < timeList.length; idx++) {
            let hits = 0;
            if (timeList[idx]) {
                let timeset = [];
                let timeidx = timeList[idx].toString();
                if (timeidx && timeidx.indexOf('#')) {
                    timeset = timeidx.split('#');
                } else {
                    timeset[0] = timeidx;
                }

                let valueset = [];
                value = value.toString();
                if (value.indexOf('#')) {
                    valueset = value.split('#');
                } else {
                    valueset[0] = value;
                }

                if (parseInt(timeset[0]) === parseInt(valueset[0])) {
                    arrIndex = idx;
                    hits++;
                }

                if (timeset[1] && valueset[1]) {
                    let beginTime = parseInt(timeset[0]);
                    let endTime = beginTime + parseInt(timeset[1]);

                    let beginValue = parseInt(valueset[0]);
                    let endValue = beginValue + parseInt(valueset[1]);

                    if ((beginValue >= beginTime) && (beginValue < endTime)) {
                        arrIndex = idx;
                        hits++;
                    }

                    if ((endValue > beginTime) && (endValue <= endTime)) {
                        arrIndex = idx;
                        hits++;
                    }

                    if (hits == 3) {
                        break;
                    }
                }
            } else if (hits == 1) {
                break;
            }
        }
    }

    return arrIndex;
}

function checkMax(objectList, arrindex, idx, value, timeList, capacity) {
    let result = true;
    let actCapacity = objectList[arrindex][idx]['act'] + parseInt(capacity);
    if (objectList[arrindex][idx]['max'] && (actCapacity <= objectList[arrindex][idx]['max'])) {
        for (y = 0; y < objectList.length; y++) {
            if (value && timeList && (y != arrindex)) {

                let timeset = [];
                let timeidx = timeList[y].toString();
                if (timeidx && timeidx.indexOf('#')) {
                    timeset = timeidx.split('#');
                } else {
                    timeset[0] = timeidx;
                }

                let valueset = [];
                value = value.toString();
                if (value.indexOf('#')) {
                    valueset = value.split('#');
                } else {
                    valueset[0] = value;
                }

                let doCheck = false;

                if (parseInt(timeset[0]) === parseInt(valueset[0])) {
                    doCheck = true;
                } else if (timeset[1] && valueset[1]) {
                    let beginTime = parseInt(timeset[0]);
                    let endTime = beginTime+parseInt(timeset[1]);

                    let beginValue = parseInt(valueset[0]);
                    let endValue = beginValue+parseInt(valueset[1]);

                    if ((beginValue >= beginTime) && (beginValue < endTime)) {
                        doCheck = true;
                    } else if ((endValue > beginTime) && (endValue <= endTime)) {
                        doCheck = true;
                    }
                }

                if (doCheck) {
                    for (z = 0; z < objectList[y].length; z++) {
                        if (objectList[y][z]['max'] && ((objectList[y][z]['act'] >= objectList[y][z]['max'])) ||
                            ((objectList[y][z]['act'] + objectList[arrindex][idx]['act']) >= objectList[arrindex][idx]['max'])) {
                            return false;
                        }
                    }
                }

                result = true;
            }
        }
    } else {
        result = !objectList[arrindex][idx]['max'];
    }

    return result;
}

function shuffle(array) {
    let counter = array.length;

    // While there are elements in the array
    while (counter > 0) {
        // Pick a random index
        let index = Math.floor(Math.random() * counter);

        // Decrease counter by 1
        counter--;

        // And swap the last element with it
        let temp = array[counter];
        array[counter] = array[index];
        array[index] = temp;
    }

    return array;
}

function isElementReallyShowed (el) {
    var elementReallyShowed = !jQuery(el).is(":disabled") && !jQuery(el).is(":hidden") && !(jQuery(el).css("visibility") == "hidden");
    jQuery(el).parents().each(function () {
        elementReallyShowed = elementReallyShowed && !jQuery(el).is(":disabled") && !jQuery(this).is(":hidden") && !(jQuery(this).css("visibility") == "hidden");
    });

    return elementReallyShowed;
}

function setTimeset(dateField, additionalId, showDateTime) {
    var elementId = 0;
    var date = 0;
    var val = -1;
    var nameField = '';

    if (additionalId == -1) {
        jQuery(document.getElementsByClassName('reservation_time_button')) ? jQuery(document.getElementsByClassName('reservation_time_button')).hide() : false;
        jQuery(document.getElementsByClassName('displayReservationObjects')) ? jQuery(document.getElementsByClassName('displayReservationObjects')).hide() : false;
    } else {
        if (!dateField) {
            dateField = document.getElementById('c4g_beginDate_'+additionalId);
        }

        date = dateField ? dateField.value : 0;
    }

    var durationNode = document.getElementById("c4g_duration");
    if (durationNode) {
        var duration = durationNode.value;
    }

    C4GCallOnChangeMethodswitchFunction(document.getElementById("c4g_reservation_object_"+additionalId));
    C4GCallOnChange(document.getElementById("c4g_reservation_object_"+additionalId));

    //hotfix dates with slashes
    if (date && date.indexOf("/")) {
        date = date.replace("/", "~");
        date = date.replace("/", "~");
    }

    if (date && additionalId) {
        duration = duration ? duration : -1;
        document.getElementsByClassName('c4g__spinner-wrapper')[0].style.display = "flex";
        let url = "/reservation-api/currentTimeset/" + date + "/" + additionalId + "/" + duration;
        var targetButton = false;
        fetch(url)
            .then(response => response.json())
            .then((data) => {
                var timeGroup = document.getElementById("c4g_beginTime_"+additionalId+"-00"+getWeekdate(date));
                var radioGroups = timeGroup ? timeGroup.parentElement.getElementsByClassName("c4g__form-radio-group") : document.querySelectorAll(".reservation_time_button .c4g__form-radio-group");
                var timeList = [];
                var intervalList = [];
                var objectList = [];
                var times = data['times'];
                var size = times.length;
                document.getElementById("c4g_reservation_id").value = data['reservationId']; //Force regeneration
                jQuery(document.getElementsByClassName("reservation-id")).show();
                jQuery(document.getElementsByClassName('reservation_time_button_'+additionalId)) ? jQuery(document.getElementsByClassName('reservation_time_button_'+additionalId)).show() : false;
                var iterator = 0;
                for (let key in times) {
                    var dataTime = times[key]['time'];
                    if (parseInt(times[key]['interval']) > 0) {
                        dataTime = times[key]['time']+'#'+times[key]['interval'];
                    }
                    var dataInterval = times[key]['interval'];
                    var dataObjects = times[key]['objects'];

                    timeList[iterator] = dataTime;
                    intervalList[iterator] = dataInterval;
                    objectList[iterator] = dataObjects;
                    iterator++;
                }

                var selectField = document.getElementById("c4g_reservation_object_"+additionalId);
                var capMin = 1;
                var capMax = 0;
                if (selectField) {
                    for (i = 0; i < selectField.options.length; i++) {
                        var option = selectField.options[i];
                        var min = option.getAttribute('min') ? parseInt(option.getAttribute('min')) : 1;
                        if ((min == -1) || (min < capMin)) {
                            capMin = min;
                        }
                        var max = option.getAttribute('max') ? parseInt(option.getAttribute('max')) : 0;
                        if ((max == -1) || (max > capMax)) {
                            capMax = max;
                        }
                    }
                }

                var desiredCapacity = document.getElementById("c4g_desiredCapacity_"+additionalId);
                var capacity = desiredCapacity ? desiredCapacity.value : 0;

                if (radioGroups) {
                    for (i = 0; i < radioGroups.length; i++) {
                        for (j = 0; j < radioGroups[i].children.length; j++) {
                            if (radioGroups[i].children[j].style && radioGroups[i].children[j].style == "display:none") {
                                continue;
                            }

                            for (k = 0; k < radioGroups[i].children[j].children.length; k++) {
                                var value = jQuery(radioGroups[i].children[j].children[k]).val();

                                if (value) {
                                    namefield = radioGroups[i].children[j].children[k].getAttribute('name').substr(1);
                                    var arrindex = checkTimelist(value, timeList);

                                    var activateTimeButton = -1
                                    var percent = 0;
                                    if (arrindex !== -1) {
                                        for (l = 0; l < objectList[arrindex].length; l++) {
                                            if (objectList[arrindex][l]['id'] != -1) {
                                                if (checkMax(objectList, arrindex, l, value, timeList, capacity)) {
                                                    if (!capMax) {
                                                        activateTimeButton = 0;
                                                        percent = 0;
                                                    } else {
                                                        activateTimeButton = (activateTimeButton < objectList[arrindex][l]['act']) ? objectList[arrindex][l]['act'] : activateTimeButton;
                                                        percent = objectList[arrindex][l]['percent'];
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    if ((activateTimeButton >= 0) && (!capMax || (activateTimeButton < capMax)) && (!capacity || (capacity >= capMin) && (!capMax || (capacity <= capMax)))) {
                                        let objstr = '';
                                        let withPriority = false;
                                        let objArr = [];
                                        for (l = 0; l < objectList[arrindex].length; l++) {
                                            let listObj = objectList[arrindex][l];
                                            if (listObj['priority'] && (listObj['priority'] == 1)) {
                                                withPriority = true;
                                                break;
                                            }
                                        }

                                        objectList[arrindex] = shuffle(objectList[arrindex]); //random object selection
                                        for (l = 0; l < objectList[arrindex].length; l++) {
                                            let listObj = objectList[arrindex][l];
                                            let interval = intervalList[arrindex];
                                            let time = timeList[arrindex];

                                            if (withPriority && listObj['priority'] && (listObj['priority'] == 1)) {
                                                objArr.splice( 0, 0, listObj['id'] );
                                            } else {
                                                objArr.push(listObj['id']);
                                            }

                                            var optionidx = -1;
                                            for (var m = 0; m < jQuery(selectField).length; m++) {
                                                if (selectField[m].value == listObj['id']) {
                                                    if (withPriority && listObj['priority'] && (listObj['priority'] == 1)) {
                                                        optionidx = m;
                                                        val = listObj['id'];
                                                        break;
                                                    } else if (!withPriority) {
                                                        optionidx = m;
                                                        val = listObj['id'];
                                                        break;
                                                    }
                                                }
                                            }
                                        }

                                        for (l = 0; l < objArr.length; l++) {
                                            if (l == 0) {
                                                objstr = objstr + objArr[l];
                                            } else {
                                                objstr = objstr + '-' + objArr[l];
                                            }
                                        }

                                        //jQuery(radioGroups[i].children[j].children[k]).removeClass().addClass("radio_object_" + objstr);
                                        jQuery(radioGroups[i].children[j].children[k]).attr('data-object', objstr);
                                        jQuery(radioGroups[i].children[j].children[k]).attr('disabled', false);

                                        if (percent > 0) {
                                            jQuery(radioGroups[i].children[j].children[k]).addClass("radio_object_hurry_up");
                                        }

                                        if (!val || (val == -1)) {
                                            val = objArr[0]; //first valid option
                                        }

                                        hideOptions(reservationObjects,additionalId, objstr, showDateTime);
                                    } else {
                                        //jQuery(radioGroups[i].children[j].children[k]).removeClass().addClass("radio_object_disabled");
                                        jQuery(radioGroups[i].children[j].children[k]).attr('disabled', true);
                                    }
                                }
                            }
                        }
                    }
                }

                if (nameField) {
                    var valueElement = document.getElementById(nameField);
                    if (valueElement) {
                        valueElement.value = '';
                    }

                    var reservation_time_button = jQuery('.reservation_time_button_'+additionalId+' input[type = "radio"]');
                    reservation_time_button.prop( "checked", false );
                }

                var reservationObjects = document.getElementsByClassName("displayReservationObjects");

                if (additionalId != -1) {
                    var timeGroups = jQuery('.reservation_time_button_'+additionalId+'.formdata input[type = "hidden"]');
                    var timeValue = false;
                    if (timeGroups) {
                        for (i = 0; i < timeGroups.length; i++) {
                            if (timeGroups[i].style.display != "none") {
                                timeValue = timeGroups[i].value;
                                break;
                            }
                        }
                    }

                    var radioButton = jQuery('.reservation_time_button_'+additionalId+' input[type = "radio"]');
                    var visibleButtons = [];
                    if (radioButton) {
                        for (i = 0; i < radioButton.length; i++) {
                            var button = radioButton[i];
                            if (button && isElementReallyShowed(button)) {

                                if (timeValue && button.value === timeValue) {
                                    targetButton = button;
                                } else if (button.value) {
                                    visibleButtons.push(button);
                                }
                            }
                        }
                    }

                    //if there are just one time button then select automaticly.
                    if (!targetButton && visibleButtons && visibleButtons.length === 1) {
                        for (i = 0; i < visibleButtons.length; i++) {
                                targetButton = visibleButtons[i];
                                break;
                        }
                    }

                    if (targetButton && !jQuery(targetButton).is(":disabled") && !(jQuery(targetButton).hasClass("radio_object_disabled"))) {
                        jQuery(targetButton).click();
                    }

                }
            }).finally(function() {
                document.getElementsByClassName("c4g__spinner-wrapper")[0].style.display = "none";
            });
    }
}

/**
 *
 * @param object
 */
function checkEventFields() {
    var typeField = document.getElementById("c4g_reservation_type");
    var typeId = typeField ? typeField.value : -1;
    var selectField = jQuery('.reservation-event-object select');
    jQuery('.eventdata').hide();

    if (selectField && selectField.is(":visible")) {
        jQuery(document.getElementsByClassName("reservation-id")).show();
        for (i = 0; i < selectField.length; i++) {
            if (selectField[i]) {
                var additional = -1;
                if (selectField[i].value) {
                    additional = typeId.toString() + "-22" + selectField[i].value.toString();
                    jQuery('.eventdata_' + additional).show();
                    jQuery('.eventdata_' + additional).children().show();
                }

                var dateFields = document.getElementsByClassName('begindate-event');
                if (dateFields) {
                    for (j = 0; j < dateFields.length; j++) {
                        if ((additional != -1) && jQuery(dateFields[j]).children('.c4g__form-date-container').children('input').hasClass('c4g_beginDateEvent_' + additional)) {
                            jQuery(dateFields[j]).show();
                            jQuery(dateFields[j]).children('label').show();
                            jQuery(dateFields[j]).children('.c4g__form-date-container').show();
                            jQuery(dateFields[j]).children('.c4g__form-date-container').children('input').show();
                        } else {
                            jQuery(dateFields[j]).hide();
                            jQuery(dateFields[j]).children('label').hide();
                            jQuery(dateFields[j]).children('.c4g__form-date-container').hide();
                            jQuery(dateFields[j]).children('.c4g__form-date-container').children('input').hide();
                        }
                    }
                }

                var timeFields = jQuery('.reservation_time_event_button');
                if (timeFields) {
                    for (j = 0; j < timeFields.length; j++) {
                        if ((additional != -1) && jQuery(timeFields[j]).hasClass('reservation_time_event_button_' + additional)) {
                            jQuery(timeFields[j]).show();
                            jQuery(timeFields[j]).children('label').show();
                            jQuery(timeFields[j]).parent().show();
                            jQuery(timeFields[j]).parent().parent().show();
                            jQuery(timeFields[j]).parent().parent().parent().show();
                        } else {
                            jQuery(timeFields[j]).hide();
                            jQuery(timeFields[j]).children('label').hide();
                            jQuery(timeFields[j]).parent().hide();
                            jQuery(timeFields[j]).parent().parent().hide();
                            jQuery(timeFields[j]).parent().parent().parent().hide();
                        }
                    }
                }
            }
        }
    } else {
        var dateFields = jQuery('.begindate-event');
        if (dateFields) {
            for (i = 0; i < dateFields.length; i++) {
                jQuery(dateFields[i]).hide();
            }
        }

        var timeFields = jQuery('.reservation_time_event_button');
        if (timeFields) {
            for (i = 0; i < timeFields.length; i++) {
                jQuery(timeFields[i]).hide();
            }
        }
    }
}