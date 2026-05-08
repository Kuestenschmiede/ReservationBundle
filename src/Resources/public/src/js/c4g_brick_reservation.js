/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @author con4gis contributors (see "authors.md")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2026, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

var callFromChangeCapacity = false;

function setObjectId(object, typeid, showDateTime) {
    if (showDateTime === undefined) { showDateTime = 0; }
    var objectParam = object ? object.getAttribute('data-object'): false;
    var typeId = typeid;
    var selectField = document.getElementById("c4g_reservation_object_"+typeId);
    var reservationObjects = document.getElementsByClassName('displayReservationObjects');
    var objects = null;
    var values = '';
    var oldValue = false;

    if (selectField) {
        selectField.style.display = 'block';
        oldValue = selectField.value && (parseInt(selectField.value) > 0) ? selectField.value : oldValue;

        if (reservationObjects) {
            for (var i=0; i < reservationObjects.length; i++) {
                var reservationObject = reservationObjects[i];
                reservationObject.style.display = 'block';
            }
        }

        if (objectParam) {
            objects = objectParam.split('-');
            var breakDance = false;
            for (var i=0; i < objects.length; i++) {
                for (var j = 0; j < selectField.options.length; j++) {
                    if (!selectField.options[j].getAttribute('hidden')) {
                        if (selectField.options[j].value == objects[i]) {
                            selectField.value = objects[i];
                            handleBrickConditions();
                            breakDance = true;
                            break;
                        }
                    }
                }
                if (breakDance) {
                    break;
                }
            }

            values = objects ? objects : values
        }
    }

    hideOptions(typeId, values, showDateTime);
    if (object) {
        var desc = object.getAttribute('data-desc');
        if (desc) {
            var descElement = object.parentNode.parentNode.parentNode.parentNode.parentNode.querySelector('.c4g__form-description');
            if (descElement) {
                descElement.innerHTML = desc;
            }
        }
    }

    if (oldValue) {
       for (var i = 0; i < selectField.options.length; i++) {
           if (!selectField.options[i].getAttribute('hidden') && !selectField.options[i].getAttribute('disabled')) {
               if (selectField.options[i].value == oldValue) {
                   selectField.value = oldValue;
                   handleBrickConditions();
                   break;
               }
           }
       }
    }

    if (selectField && (parseInt(selectField.value) !== -1)) {
        for (var i = 0; i < selectField.options.length; i++) {
            if ((parseInt(selectField.options[i].value) == -1) && selectField.options[i].style.display != "none") {
                selectField.options[i].style.display = "none";
                break;
            }
        }
    }

    return true;
}

function hideOptions(typeId, values, showDateTime) {
    if (typeId == -1) {
        var typeField = document.getElementById("c4g_reservation_type");
        typeId = typeField ? typeField.value : -1;
    }

    var selectField = document.getElementById("c4g_reservation_object_"+typeId);
    var firstKey = false;
    var firstValueParam = 0;
    var emptyKey = false;
    if (selectField) {
        //var distance = 0;

        for (var i = 0; i < selectField.options.length; i++) {
            var option = selectField.options[i];
            var min = option.getAttribute('min') ? parseInt(option.getAttribute('min')) : 1;
            var max = option.getAttribute('max') ? parseInt(option.getAttribute('max')) : 0;
            var desiredCapacity = document.getElementById("c4g_desiredCapacity_"+typeId);
            var capacity = desiredCapacity ? desiredCapacity.value : 0;

            // var actMin = capacity - min;
            // var actMax = max - capacity;
            // var actCapacity = (actMin > actMax) ? actMin : actMax;

            if (option['value'] && (parseInt(option['value']) == -1)) {
                emptyKey = i;
            }

            //not in values
            var foundValue = false;
            if (Array.isArray(values)) {
                if (min && capacity && (capacity > 0) && (capacity >= min) && (capacity <= max)) {
                    for (var j = 0; j < values.length; j++) {
                        if (values[j] == option.value) {
                            // if (!distance || (distance == 0) || (actCapacity && (actCapacity > 0) && (distance >= actCapacity))) {
                            //     distance = actCapacity ? actCapacity : distance;
                            firstValueParam = values[j];
                            foundValue = true;
                            //}
                        }
                    }
                } else {
                    for (var j = 0; j < values.length; j++) {
                        if (values[j] == option.value) {
                            if (j == 0) {
                                firstValueParam = values[j];
                            }
                            foundValue = true;
                            break;
                        }
                    }
                }
            } else if (parseInt(values) >= 0) {
                if (values == option.value) {
                    firstValueParam = values;
                    foundValue = true;
                }
            }


            if (!foundValue && (option.value != -1)) {
                option.setAttribute('disabled','disabled');
                option.setAttribute('hidden','hidden');
            } else if (option.value != -1) {
                option.removeAttribute('disabled');
                option.removeAttribute ('hidden');
                if (min && capacity && (capacity > 0)) {
                    if ((capacity < min) || (max && (capacity > max))) {
                        option.setAttribute('disabled','disabled');
                        option.setAttribute('hidden','hidden');
                    } else {
                        option.removeAttribute('disabled');
                        option.removeAttribute('hidden');

                        if ((firstValueParam >= 0)  && (option.value == firstValueParam)) {
                            firstKey = i;
                        } else {
                            if ((firstKey == -1) && (option.value != -1)) {
                                firstKey = i;
                            }
                        }
                    }
                } else {
                    option.removeAttribute('disabled');
                    option.removeAttribute('hidden');
                    if ((firstValueParam >= 0)  && (option.value == firstValueParam)) {
                        firstKey = i;
                    } else {
                        var first = selectField.value;
                        if ((first == -1) && (option.value != -1)) {
                            firstKey = i;
                        }
                    }
                }
            }

            if (showDateTime && (option.value != -1) && foundValue) {
                var text = option.textContent;
                var date = '';
                var time = '';

                var radioButtons = document.querySelectorAll('.reservation_time_button_'+typeId+' input[type = "radio"]:checked');
                if (radioButtons && radioButtons[0]) {
                    var begin =  radioButtons[0].getAttribute('data-stamp') ? parseInt( radioButtons[0].getAttribute('data-stamp')) : 0;
                    var labels = document.getElementsByClassName('c4g__form-check-label');
                    for (var k = 0; k < labels.length; k++) {
                        if (labels[k].htmlFor == radioButtons[0].id) {
                            time = labels[k].textContent;
                            break;
                        }
                    }

                    var dateFields = document.querySelectorAll('.c4g__form-date-container .c4g_beginDate_'+typeId);
                    if (dateFields) {
                        for (var k = 0; k < dateFields.length; k++) {
                            var dateField = dateFields[k];
                            if (dateField && dateField.value) {
                                var org = dateField.getAttribute('data-org');
                                if (org !== null) {
                                    dateField.value = org;
                                }

                                //ToDo international
                                if (begin >= 82800) {
                                    var pattern = /(\d{2})\.(\d{2})\.(\d{4})/;
                                    var newDate = new Date(dateField.value.replace(pattern,'$3-$2-$1'));
                                    newDate.setSeconds(newDate.getSeconds() + 86400);
                                    var ye = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(newDate);
                                    var mo = new Intl.DateTimeFormat('en', { month: '2-digit' }).format(newDate);
                                    var da = new Intl.DateTimeFormat('en', { day: '2-digit' }).format(newDate);
                                    date = da+'.'+mo+'.'+ye;
                                    dateField.setAttribute('data-org', dateField.value);
                                    dateField.value = date;
                                } else {
                                    date = dateField.value;
                                }

                                break;
                            }
                        }
                    }

                    if (text && (date != '') && (time != '')) {
                        var pos_price = text.lastIndexOf(')');
                        var pos = text.lastIndexOf('\u00A0(');
                        var priceText = '';

                        if (pos != -1 && pos_price != -1 && pos_price > pos) {
                            var pos_semicolon = text.indexOf(';');
                            if (pos_semicolon !== -1) {
                                priceText = text.substr(pos+2, pos_semicolon-pos-2);
                            } else {
                                priceText = text.substr(pos+2, pos_price-pos-2);
                            }
                        }

                        if (pos != -1) {
                            text = text.substr(0 , pos);
                        }

                        var pos_date = time.indexOf(date);
                        if (pos_date != -1) {
                            //ToDo other currencies
                            if (priceText && priceText.search(/€/) !== -1) {
                                option.textContent = text + '\u00A0('+priceText+';\u00A0'+time+')';
                            } else {
                                option.textContent = text + '\u00A0('+time+')';
                            }
                        } else {
                            //ToDo other currencies
                            if (priceText && priceText.search(/€/) !== -1) {
                                option.textContent = text + '\u00A0('+priceText+';\u00A0'+date+'\u00A0'+time+')';
                            } else {
                                option.textContent = text + '\u00A0('+date+'\u00A0'+time+')';
                            }

                        }
                    }
                }
            }
        }

        if ((parseInt(firstKey) !== -1) && selectField.options[firstKey]) {
          selectField.value = selectField.options[firstKey].value;
          selectField.options[firstKey].removeAttribute('disabled');
          selectField.options[firstKey].removeAttribute('hidden');
          if (emptyKey != false) {
              selectField.options[emptyKey].setAttribute('disabled','disabled');
              selectField.options[emptyKey].setAttribute('hidden','hidden');
          }
          selectField.removeAttribute('disabled');
        } else {
            if (emptyKey != false) {
              selectField.options[emptyKey].removeAttribute('disabled');
              selectField.options[emptyKey].removeAttribute('hidden');
            }
            selectField.value = -1;
            selectField.setAttribute('disabled','disabled');
        }

        eventFire(selectField,'change');
    }

    checkEventFields();
}

function checkType(dateField, event) {
    if (event) {
        return dateField.parentNode.parentNode.classList.contains('begindate-event') ? true : false;
    } else {
        return dateField.parentNode.parentNode.classList.contains('begin-date') ? true : false;
    }
}

function setReservationForm(typeId, showDateTime) {
    var callFromChangeCapacity = false;
    if (document.getElementsByClassName("reservation-id")[0]) {
        document.getElementsByClassName("reservation-id")[0].style.display = "none";
    }

    var event = false;
    var object = false;

    var typeField = document.getElementById("c4g_reservation_type");
    if (!typeField) {
        // If no type selector is present (e.g. object-first), use the provided typeId
        if (!typeId) return;
    } else {
        typeId = typeField.value || -1;

        var selectedIndex = typeField.selectedIndex;
        var selectedOption = typeField.options[selectedIndex];
        if (selectedOption) {
            event = selectedOption.getAttribute('type') == 2 ? true : false;
            object = selectedOption.getAttribute('type') == 3 ? true : false;
        }
    }

    if (typeId > 0) {
        var capacityField = document.getElementById("c4g_desiredCapacity_"+typeId);
        if (capacityField) {
            var value = capacityField.value;
            if (capacityField.getAttribute('max') && (value > parseInt(capacityField.getAttribute('max')))) {
                capacityField.value = capacityField.getAttribute('max');
            }
            if (capacityField.getAttribute('min') && (value < parseInt(capacityField.getAttribute('min')))) {
                capacityField.value = capacityField.getAttribute('min');
            }
        }

        var durationField = document.getElementById("c4g_duration_"+typeId);
        if (durationField && durationField.style.display !== "none") {
           durationField.style.display = "block";

            var value = durationField.value;
            if (durationField && durationField.getAttribute('max') && (value > parseInt(durationField.getAttribute('max')))) {
                durationField.value = durationField.getAttribute('max');
            }
            if (durationField && durationField.getAttribute('min') && (value < parseInt(durationField.getAttribute('min')))) {
                durationField.value = durationField.getAttribute('min');
            }
        }

        var dateId = 'c4g_beginDate_'+typeId;
        if (object) {
            var objectElement = document.getElementById("c4g_reservation_object_"+typeId);
            if (objectElement) {
                dateId = dateId + '-33' +objectElement.value;
                if (document.getElementById(dateId)) {
                    setTimeset(document.getElementById(dateId).value, typeId, showDateTime,objectElement.value);
                }
            }
        } else if (event) {
            var queryString = window.location.search;
            var urlParams = new URLSearchParams(queryString);
            var eventIdFromUrl = urlParams.get('event');

            if (!eventIdFromUrl) {
                // Try to get from URL path (auto_item)
                var pathParts = window.location.pathname.split('/');
                var eventIndex = pathParts.indexOf('event');
                if (eventIndex !== -1 && pathParts[eventIndex + 1]) {
                    eventIdFromUrl = pathParts[eventIndex + 1].replace('.html', '');
                }
            }

            if (eventIdFromUrl) {
                var eventSelect = document.getElementById("c4g_reservation_object_event_" + typeId);
                if (eventSelect) {
                    eventSelect.value = eventIdFromUrl;
                }
                
                var dateIdEvent = 'c4g_beginDateEvent_' + typeId + '-22' + eventIdFromUrl;
                if (document.getElementById(dateIdEvent)) {
                    setTimeset(document.getElementById(dateIdEvent).value, typeId, showDateTime,0);
                    checkEventFields();
                }
            } else {
                var dateFields = document.getElementsByClassName('c4g__form-date-input');
                if (dateFields) {
                    for (var i = 0; i < dateFields.length; i++) {
                        var dateField = dateFields[i];
                        if (dateField && checkType(dateField, event) && dateField.value) {
                            var fieldId = dateField.id;
                            if (fieldId && fieldId.indexOf('c4g_beginDateEvent_' + typeId + '-22') !== -1) {
                                setTimeset(dateField.value, typeId, showDateTime,0);
                                checkEventFields();
                                break;
                            }
                        }
                    }
                }
            }
        } else if (document.getElementById(dateId)) {
            setTimeset(document.getElementById(dateId).value, typeId, showDateTime, 0);
        }
    }
    handleBrickConditions();

    if (document.getElementsByClassName('c4g__spinner-wrapper')[0]) {
        document.getElementsByClassName('c4g__spinner-wrapper')[0].style.display = "none";
    }
}

function changeCapacity(typeId, showDateTime) {
    callFromChangeCapacity = true;
    if (document.getElementsByClassName("reservation-id")[0]) {
        document.getElementsByClassName("reservation-id")[0].style.display = "none";
    }

    var event = false;
    var object = false;

    var typeField = document.getElementById("c4g_reservation_type");
    if (!typeField) return; // Safety first
    typeId = typeField.value || -1;

    var selectedIndex = typeField.selectedIndex;
    var selectedOption = typeField.options[selectedIndex];
    if (selectedOption) {
        event = selectedOption.getAttribute('type') == 2 ? true : false;
        object = selectedOption.getAttribute('type') == 3 ? true : false;
    }

    if (typeId > 0) {
        var capacityField = document.getElementById("c4g_desiredCapacity_"+typeId);
        if (capacityField) {
            var value = capacityField.value;
            if (capacityField.getAttribute('max') && (value > parseInt(capacityField.getAttribute('max')))) {
                capacityField.value = capacityField.getAttribute('max');
            }
            if (capacityField.getAttribute('min') && (value < parseInt(capacityField.getAttribute('min')))) {
                capacityField.value = capacityField.getAttribute('min');
            }
        }

        var durationField = document.getElementById("c4g_duration_"+typeId);
        if (durationField && durationField.style.display !== "none") {
           durationField.style.display = "block";

            var value = durationField.value;
            if (durationField && durationField.getAttribute('max') && (value > parseInt(durationField.getAttribute('max')))) {
                durationField.value = durationField.getAttribute('max');
            }
            if (durationField && durationField.getAttribute('min') && (value < parseInt(durationField.getAttribute('min')))) {
                durationField.value = durationField.getAttribute('min');
            }
        }

        var dateId = 'c4g_beginDate_'+typeId;
        if (object) {
            var objectElement = document.getElementById("c4g_reservation_object_"+typeId);
            if (objectElement) {
                dateId = dateId + '-33' +objectElement.value;
                if (document.getElementById(dateId)) {
                    setTimeset(document.getElementById(dateId).value, typeId, showDateTime,objectElement.value);
                }
            }
        } else if (event) {
            var queryString = window.location.search;
            var urlParams = new URLSearchParams(queryString);
            var eventIdFromUrl = urlParams.get('event');

            if (!eventIdFromUrl) {
                // Try to get from URL path (auto_item)
                var pathParts = window.location.pathname.split('/');
                var eventIndex = pathParts.indexOf('event');
                if (eventIndex !== -1 && pathParts[eventIndex + 1]) {
                    eventIdFromUrl = pathParts[eventIndex + 1].replace('.html', '');
                }
            }

            if (eventIdFromUrl) {
                var eventSelect = document.getElementById("c4g_reservation_object_event_" + typeId);
                if (eventSelect) {
                    eventSelect.value = eventIdFromUrl;
                }
                
                var dateIdEvent = 'c4g_beginDateEvent_' + typeId + '-22' + eventIdFromUrl;
                if (document.getElementById(dateIdEvent)) {
                    setTimeset(document.getElementById(dateIdEvent).value, typeId, showDateTime,0);
                    checkEventFields();
                }
            } else {
                var dateFields = document.getElementsByClassName('c4g__form-date-input');
                if (dateFields) {
                    for (var j = 0; j < dateFields.length; j++) {
                        var dateField = dateFields[j];
                        if (dateField && checkType(dateField, event) && dateField.value) {
                            var fieldId = dateField.id;
                            if (fieldId && fieldId.indexOf('c4g_beginDateEvent_' + typeId + '-22') !== -1) {
                                setTimeset(dateField.value, typeId, showDateTime,0);
                                checkEventFields();
                                break;
                            }
                        }
                    }
                }
            }
        } else if (document.getElementById(dateId)) {
            setTimeset(document.getElementById(dateId).value, typeId, showDateTime, 0);
        }
    }
    handleBrickConditions();

    if (document.getElementsByClassName('c4g__spinner-wrapper')[0]) {
        document.getElementsByClassName('c4g__spinner-wrapper')[0].style.display = "none";
    }
}

function checkTimelist(value, timeList) {
    var arrIndex = -1;

    if (value && timeList) {
        for (idx=0; idx < timeList.length; idx++) {
            var hits = 0;
            if (timeList[idx]) {
                var timeset = [];
                var timeidx = timeList[idx].toString();
                if (timeidx && timeidx.indexOf('#')) {
                    timeset = timeidx.split('#');
                } else {
                    timeset[0] = timeidx;
                }

                var valueset = [];
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
                    var beginTime = parseInt(timeset[0]);
                    var endTime = beginTime + parseInt(timeset[1]);

                    var beginValue = parseInt(valueset[0]);
                    var endValue = beginValue + parseInt(valueset[1]);

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
                } else if (timeset[1] && valueset[0]) { //direct booking
                    var beginTime = parseInt(timeset[0]);
                    var endTime = beginTime + parseInt(timeset[1]);

                    var beginValue = parseInt(valueset[0]);

                    if ((beginTime <= beginValue) && (endTime >= beginValue)) {
                        arrIndex = idx;
                        hits++;
                    }

                    if (hits == 3) {
                        break;
                    }
                } else if (timeset[0] && valueset[1]) { //direct booking
                    var beginTime = parseInt(timeset[0]);

                    var beginValue = parseInt(valueset[0]);
                    var endValue = beginValue + parseInt(valueset[1]);

                    if ((beginValue <= beginTime) && (endValue >= beginTime)) {
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
    var result = true;
    var actCapacity = objectList[arrindex][idx]['act'] + parseInt(capacity);
    if (objectList[arrindex][idx]['max'] && (actCapacity <= objectList[arrindex][idx]['max'])) {
        for (y = 0; y < objectList.length; y++) {
            if (value && timeList && (y != arrindex)) {

                var timeset = [];
                var timeidx = timeList[y].toString();
                if (timeidx && timeidx.indexOf('#')) {
                    timeset = timeidx.split('#');
                } else {
                    timeset[0] = timeidx;
                }

                var valueset = [];
                value = value.toString();
                if (value.indexOf('#')) {
                    valueset = value.split('#');
                } else {
                    valueset[0] = value;
                }

                var doCheck = false;

                if (parseInt(timeset[0]) === parseInt(valueset[0])) {
                    doCheck = true;
                } else if (timeset[1] && valueset[1]) {
                    var beginTime = parseInt(timeset[0]);
                    var endTime = beginTime+parseInt(timeset[1]);

                    var beginValue = parseInt(valueset[0]);
                    var endValue = beginValue+parseInt(valueset[1]);

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
    var counter = array.length;

    // While there are elements in the array
    while (counter > 0) {
        // Pick a random index
        var index = Math.floor(Math.random() * counter);

        // Decrease counter by 1
        counter--;

        // And swap the last element with it
        var temp = array[counter];
        array[counter] = array[index];
        array[index] = temp;
    }

    return array;
}

function addRadioFieldSet(radioGroup, data, additionalId, capacity, showDateTime, objId) {
    if (!data || !data['times']) {
        return;
    }
    var times = data['times'];

    //delete all childs from radioGroup
    
    if (radioGroup) {
        while (radioGroup.firstChild) {
            radioGroup.firstChild.remove();
        }
    }

    //add new childs to radioGroup
    Object.keys(times)
    .sort(function(a, b) {
        // Split the keys by '#'
        var timeA = a.split('#')[0];
        var timeB = b.split('#')[0];

        // Compare the beginning time to determine the order
        return timeA - timeB;
    })
    .forEach(function(key) {
        var name = times[key]['name'];
        var interval = times[key]['interval'];
        var time = times[key]['time'];
        var objects = times[key]['objects'];
        var begin = times[key]['begin'];
        var description = times[key]['description'];
        var percent = 0;
        var priority = 0;
        var value = '';
        var selectField = document.getElementById("c4g_reservation_object_"+additionalId);
        var disabled = '';
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

        var objArr = [];
        var objstr = '';
        if (!capacity || (capacity == -1) || (capacity >= capMin) && (!capMax || (capacity <= capMax))) {
            objects = shuffle(objects);
            var noObj = true;
            for (j = 0; j < objects.length; j++) {
                var object = objects[j];
                if (parseInt(object['id']) != -1) {
                    percent = object['percent'];
                    if (object['priority'] && (object['priority'] == 1)) {
                        objArr.splice(0, 0, object['id']);
                    } else {
                        objArr.push(object['id']);
                    }
                    noObj = false;
                }
            }

            disabled = noObj;
        } else {
            disabled = true;
        }
        if (!callFromChangeCapacity) {
            
        }
        for (j = 0; j < objArr.length; j++) {
            if (j == 0) {
                objstr = objstr + objArr[j];
            } else {
                objstr = objstr + '-' + objArr[j];
            }
        }

        if(!callFromChangeCapacity) {}

        var c4gFormCheck = document.createElement('div');
        c4gFormCheck.className = "c4g__form-check";

        var c4gFormCheckInput = document.createElement('input');
        c4gFormCheckInput.type = 'radio';
        c4gFormCheckInput.className = "c4g__form-check-input c4g__btn-check";
        if (objId) {
            c4gFormCheckInput.setAttribute('name', '_c4g_beginTime_'+additionalId+"-33"+objId);
            c4gFormCheckInput.id = 'beginTime_'+additionalId+"-33"+objId+'-'+time+'#'+interval;
            //c4gFormCheckInput.setAttribute("onchange", "setObjectId(this,"+additionalId+","+showDateTime+");",true);
            c4gFormCheckInput.setAttribute("onclick", "var target = document.getElementById('c4g_beginTime_" + additionalId + "-33" + objId + "'); if (target) target.value = this.value;");
        } else {
            c4gFormCheckInput.setAttribute('name', '_c4g_beginTime_'+additionalId);
            c4gFormCheckInput.id = 'beginTime_'+additionalId+'-'+time+'#'+interval;
            c4gFormCheckInput.setAttribute("onchange", "setObjectId(this,"+additionalId+","+showDateTime+");");
            c4gFormCheckInput.setAttribute("onclick", "var target = document.getElementById('c4g_beginTime_" + additionalId + "'); if (target) target.value = this.value;");
        }
        c4gFormCheckInput.setAttribute('data-object', objstr);
        c4gFormCheckInput.setAttribute("value", time+'#'+interval);
        c4gFormCheckInput.setAttribute("data-stamp", begin);
        c4gFormCheckInput.setAttribute("data-desc", description);
        c4gFormCheckInput.style = "display: block;";
        if (disabled) {
            c4gFormCheckInput.setAttribute('disabled', disabled);
            c4gFormCheckInput.setAttribute('hidden', disabled);
        }

        if (percent > 0) {
            c4gFormCheckInput.className = c4gFormCheckInput.className+" radio_object_hurry_up";
        }
        c4gFormCheck.appendChild(c4gFormCheckInput);

        var c4gFormCheckLabel = document.createElement('label');
        c4gFormCheckLabel.className = "c4g__form-check-label c4g__btn c4g__btn-radio";
        c4gFormCheckLabel.innerText = name;
        c4gFormCheckLabel.htmlFor = c4gFormCheckInput.id;
        c4gFormCheck.appendChild(c4gFormCheckLabel);

        if (radioGroup) {
            radioGroup.appendChild(c4gFormCheck);
        }

        if (radioGroup && radioGroup.parentNode && radioGroup.parentNode.parentNode) {
            var descriptions = radioGroup.parentNode.parentNode.getElementsByClassName('c4g__form-description');
            if (descriptions && descriptions.length > 0) {
                descriptions[0].innerText = description;
            }
        }
    });

    //return objstr;
}

function setTimeset(date, additionalId, showDateTime, objectId) {
    var elementId = 0;
    var duration = -1;
    var capacity = -1;

    var dateFields = document.querySelectorAll('.c4g__form-date-container .c4g_beginDate_'+additionalId);
    if (dateFields) {
        for (k = 0; k < dateFields.length; k++) {
            var dateField = dateFields[k];
            if (dateField) {
                dateField.removeAttribute('data-org');
            }
        }
    }

    var selectField = document.getElementById("c4g_reservation_object_"+additionalId);
    if (objectId) {
        selectField.setAttribute('value',objectId);
        for (i=0;i<selectField.options.length;i++) {
            var option = selectField.options[i];
            if (option.value == selectField.value) {
                option.setAttribute("selected","true");
            } else {
                option.removeAttribute("selected");
            }
        }
    } else if (selectField) {
        selectField.value = -1;
        eventFire(selectField, 'change');
    }

    var durationNode = document.getElementById("c4g_duration_"+additionalId);
    if (durationNode && durationNode.style && durationNode.display !== "none"){
        var duration = durationNode.value;
    }

    var capacityNode = document.getElementById("c4g_desiredCapacity_"+additionalId);
    if (capacityNode && capacityNode.style && capacityNode.display !== "none"){
        var capacity = capacityNode.value;
    }

    //hotfix dates with slashes
    if (date && (typeof date === 'string') && date.indexOf("/")) {
        date = date.replace(/\//g, "~");
    }


    if (date && additionalId) {
        duration = duration ? duration : -1;
        capacity = capacity ? capacity : -1;
        var spinners = document.getElementsByClassName('c4g__spinner-wrapper');
        if (spinners && spinners.length > 0) {
            spinners[0].style.display = "flex";
        }
        
        // con4gis_reservation_values sync
        if (typeof con4gis_reservation_values === 'undefined') { window.con4gis_reservation_values = {}; }
        window.con4gis_reservation_values[additionalId] = date.replace(/~/g, "/");

        var url = "/reservation-api/currentTimeset/" + date + "/" + additionalId + "/" + duration + "/" + capacity + "/" + objectId;
        // Anti-Cache-Param for the API call to ensure fresh data
        url += (url.indexOf('?') === -1 ? '?' : '&') + 't=' + new Date().getTime();
        
        var targetButton = false;
        fetch(url)
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                var contentType = response.headers.get('content-type');
                if (!contentType || (contentType.indexOf('application/json') === -1 && contentType.indexOf('text/javascript') === -1)) {
                    throw new TypeError("Oops, we haven't got JSON!");
                }
                return response.json();
            })
            .then(function(data) {
                var addId = additionalId;
                if (objectId) {
                    addId += '-33'+objectId;
                }
                
                // Ensure targetDateField still has the correct value before rendering radio buttons
                // This helps if something else reset it while fetch was running
                var targetDateField = document.getElementById('c4g_beginDate_' + additionalId + (objectId ? '-33' + objectId : ''));
                var urlParams = new URLSearchParams(window.location.search);
                var urlDate = urlParams.get('date');
                var expectedDate = (urlDate || date || '').replace(/~/g, "/");
                if (targetDateField && expectedDate && targetDateField.value !== expectedDate) {
                    // console.log("Restoring dateValue from setTimeset parameter to prevent overwrite");
                    targetDateField.value = expectedDate;
                    var pickerField = document.getElementById(targetDateField.id + '_picker');
                    if (pickerField && pickerField.datepicker && typeof pickerField.datepicker.setDate === 'function') {
                        var tv = expectedDate;
                        if (expectedDate.indexOf('-') !== -1 && expectedDate.length === 10) {
                            var pts = expectedDate.split('-');
                            tv = new Date(pts[0], pts[1] - 1, pts[2]);
                        }
                        pickerField.datepicker.setDate(tv);
                    }
                }

                var radioGroup = document.querySelector(".radio-group-beginTime_"+addId);
                //if (!callFromChangeCapacity) {
                    addRadioFieldSet(radioGroup, data, additionalId, capacity, showDateTime, objectId); 
                //}
                var selectField = document.getElementById("c4g_reservation_object_"+additionalId);
                var objCaptions = data['captions'];

                var resIdFields = document.getElementById("c4g_reservation_id");
                if (resIdFields) {
                    if (!resIdFields.value || (resIdFields.value != data['reservationId'])) {
                        resIdFields.value = data['reservationId']; //Force regeneration
                    }
                }
                var resIdContainers = document.getElementsByClassName("reservation-id");
                if (resIdContainers && resIdContainers.length > 0) {
                    resIdContainers[0].style.display = "block";
                }

                var timeButtons = document.getElementsByClassName('reservation_time_button_'+addId);

                if (objCaptions && objCaptions[objectId]) {
                    var objField = document.getElementById("c4g_reservation_object_"+additionalId);
                    if (objField && objField.length) {
                        for (z=0; z < objField.options.length; z++) {
                            if (objField.options[z].value == objectId) {
                                objField.options[z].innerHtml = objCaptions[objectId];
                                break;
                            }

                        }
                    }
                }

                handleBrickConditions();

                if (additionalId != -1) {
                    var timeGroups = document.querySelectorAll('.radio-group-beginTime_'+additionalId+' input[type = "hidden"]');
                    var timeValueField = document.getElementById('c4g_beginTime_'+additionalId);
                    var timeValue = timeValueField ? timeValueField.value : false;
                    
                    if (!timeValue && timeGroups) {
                        for (z = 0; z < timeGroups.length; z++) {
                            if (timeGroups[z].style.display != "none") {
                                timeValue = timeGroups[z].value;
                                break;
                            }
                        }
                    }

                    var radioButton = document.querySelectorAll('.radio-group-beginTime_'+additionalId+' input[type = "radio"]');
                    var visibleButtons = [];
                    var targetButton = null;
                    if (radioButton && radioButton.length) {
                        for (z = 0; z < radioButton.length; z++) {
                            var button = radioButton[z];
                            if (button && !button.getAttribute('disabled') && !button.getAttribute('hidden')) {
                if (timeValue && button.value === timeValue) {
                    targetButton = button;
                } else if (button.value && !timeValue && (!visibleButtons || (visibleButtons.indexOf(button) === -1))) {
                    visibleButtons.push(button);
                }
                            }
                        }
                    }

                    if (!targetButton && visibleButtons && visibleButtons.length >= 1) {
                        targetButton = visibleButtons[0];
                    }

                    if (!objectId && selectField) {
                        setObjectId(0,additionalId,showDateTime);
                        selectField.value = -1;
                        eventFire(selectField,'change');
                        selectField.disabled = true;
                    }

                    if (!objectId) {
                        if (targetButton && !targetButton.disabled && !targetButton.classList.contains("radio_object_disabled")) {
                            targetButton.setAttribute("checked", "checked");
                            document.getElementById('c4g_beginTime_'+additionalId).value=targetButton.value;
                            setObjectId(targetButton,additionalId,showDateTime);
                        } else {
                            for (z=0; z<visibleButtons.length; z++) {
                                visibleButtons[z].removeAttribute("checked");

                            }
                            if (selectField) {
                                selectField.value = -1;
                                eventFire(selectField,'change');
                                selectField.disabled = true;
                            }
                        }                                    
                    } else {
                        if (targetButton && !targetButton.disabled && !targetButton.classList.contains("radio_object_disabled")) {
                            targetButton.click();
                        }
                    }
                }
            }).finally(function() {
                var spinners = document.getElementsByClassName("c4g__spinner-wrapper");
                if (spinners && spinners.length > 0) {
                    spinners[0].style.display = "none";
                }
            });
    }
}


/**
 *
 * @param object
 */
function checkEventFields(typeId, selectField) {
    if (!typeId) {
        var typeField = document.getElementById("c4g_reservation_type");
        typeId = typeField ? typeField.value : -1;
    }
    if (!selectField) {
        selectField = document.querySelectorAll('#c4g_reservation_object_event_' + typeId)[0];
    }
    var isSelectHidden = false;
    if (selectField && selectField.style && selectField.style.display === 'none') {
        isSelectHidden = true;
    }
    var eventData = document.getElementsByClassName('eventdata');
    if (eventData) {
        for (var i = 0; i < eventData.length; i++) {
            eventData[i].hidden = true;
        }
    }

    if (selectField && !isSelectHidden) {
        //document.getElementsByClassName("reservation-id").hidden != true;
        for (var i = 0; i < selectField.options.length; i++) {
            var option = selectField.options[i];
            if (option.value && (option.selected || selectField.type !== 'select-one')) {
                var additional = -1;
                if (option.value) {
                    additional = typeId.toString() + "-22" + option.value.toString();

                    var eventDataWithType = document.querySelectorAll('.eventdata_' + additional);
                    if (eventDataWithType) {
                        for (var k = 0; k < eventDataWithType.length; k++) {
                            eventDataWithType[k].style.visibility = "hidden";
                            eventDataWithType[k].hidden = true;
                        }
                    }
                }

                var dateFields = document.getElementsByClassName('begindate-event');
                if (dateFields) {
                    for (var j = 0; j < dateFields.length; j++) {
                        var dateContainer = dateFields[j].querySelector('.c4g__form-date-container');
                        if (dateContainer && (additional != -1) && dateContainer.querySelector('.c4g_beginDateEvent_' + additional)) {
                            dateFields[j].style.visibility = "visible";
                            dateFields[j].hidden = false;
                        } else {
                            dateFields[j].hidden = true;
                        }
                    }
                }

                var timeFields = document.getElementsByClassName('reservation_time_event_button');
                if (timeFields) {
                    for (var j = 0; j < timeFields.length; j++) {
                        if (timeFields[j].classList.contains('reservation_time_event_button_' + additional)) {
                            timeFields[j].style.visibility = "visible";
                            timeFields[j].hidden = false;
                            var parentNode = timeFields[j].parentNode;
                            while (parentNode && parentNode.className && (typeof parentNode.className === 'string') && parentNode.className.indexOf('c4g__form-field') === -1) {
                                parentNode.hidden = false;
                                parentNode = parentNode.parentNode;
                            }
                            if (parentNode) parentNode.hidden = false;
                        } else {
                            timeFields[j].hidden = true;
                            var parentNode = timeFields[j].parentNode;
                            while (parentNode && parentNode.className && (typeof parentNode.className === 'string') && parentNode.className.indexOf('c4g__form-field') === -1) {
                                parentNode.hidden = true;
                                parentNode = parentNode.parentNode;
                            }
                            if (parentNode) parentNode.hidden = true;
                        }
                    }
                }
            }
        }
    } else {
        var dateFields = document.getElementsByClassName('begindate-event');
        if (dateFields) {
            for (var i = 0; i < dateFields.length; i++) {
                dateFields[i].hidden = true;
            }
        }

        var timeFields = document.getElementsByClassName('reservation_time_event_button');
        if (timeFields) {
            for (var i = 0; i < timeFields.length; i++) {
                timeFields[i].hidden = true;
            }
        }
    }
}

function onObjectChangeFirst(typeId, showDateTime, initialDate) {
    if (!typeId) return;
    var objField = document.getElementById('c4g_reservation_object_' + typeId);
    var actValue = objField ? String(objField.value) : '';
    var dateValue = String(initialDate || '');
    if (typeof con4gis_reservation_values !== 'undefined' && con4gis_reservation_values[typeId]) {
        dateValue = String(con4gis_reservation_values[typeId]);
    }
    if (!dateValue || dateValue === 'null' || dateValue === 'undefined') {
        dateValue = '';
        var dateFields = document.querySelectorAll('.c4g_beginDate_' + typeId);
        if (dateFields && dateFields.length > 0) {
            for (var i = 0; i < dateFields.length; i++) {
                if (dateFields[i] && dateFields[i].value && (dateFields[i].offsetParent !== null || dateFields[i].type === 'hidden')) {
                    dateValue = String(dateFields[i].value);
                    if (dateValue) { break; }
                }
            }
        }
    }
    if (!dateValue || dateValue === 'null' || dateValue === 'undefined') {
        var cookieValue = document.cookie.match('(^|;)\\s*reservationInitialDateCookie\\s*=\\s*([^;]+)');
        if (cookieValue) { dateValue = decodeURIComponent(cookieValue.pop()); }
    }
    if (dateValue && actValue) {
        var targetId = 'c4g_beginDate_' + typeId + '-33' + actValue;
        var targetDateField = document.getElementById(targetId);
        
        var urlParams = new URLSearchParams(window.location.search);
        var urlDate = urlParams.get('date');
        if (urlDate) {
            // Prioritize URL date and clear/update cookie to prevent jumping
            dateValue = String(urlDate);
            document.cookie = 'reservationInitialDateCookie=' + encodeURIComponent(dateValue) + '; path=/; SameSite=Lax';
        }

        if (targetDateField && dateValue && targetDateField.value !== dateValue) {
            targetDateField.value = dateValue;
            var pickerField = document.getElementById(targetId + '_picker');
            if (pickerField && pickerField.datepicker && typeof pickerField.datepicker.setDate === 'function') {
                try {
                    pickerField.datepicker.setDate(dateValue);
                } catch (pe) {
                    console.error('Error setting date on picker:', pe);
                }
            }
            if (typeof eventFire === 'function') { eventFire(targetDateField, 'change'); }
        }
        
        if (!urlDate || (dateValue === initialDate)) {
            document.cookie = 'reservationInitialDateCookie=' + encodeURIComponent(dateValue) + '; path=/; SameSite=Lax';
            if (typeof con4gis_reservation_values === 'undefined') { window.con4gis_reservation_values = {}; }
            window.con4gis_reservation_values[typeId] = dateValue;
        }
    }
    if (typeof setTimeset === 'function') {
        try { setTimeset(String(dateValue || ''), String(typeId), parseInt(showDateTime || 0), String(actValue || '')); } catch(e1){console.error(e1);}
    }
    if (typeof handleBrickConditions === 'function') {
        try { handleBrickConditions(); } catch(e2){console.error(e2);}
    }
        setTimeout(function() {
            try {
                var retryId = 'c4g_beginDate_' + typeId + '-33' + actValue;
                var retryField = document.getElementById(retryId);
                var urlParamsRetry = new URLSearchParams(window.location.search);
                var urlDateRetry = urlParamsRetry.get('date');
                var retryDateValue = dateValue;
                if (urlDateRetry) {
                    retryDateValue = String(urlDateRetry);
                }

                if (retryField && retryDateValue && retryField.value !== retryDateValue) {
                    retryField.value = retryDateValue;
                    var retryPicker = document.getElementById(retryId + '_picker');
                    if (retryPicker && retryPicker.datepicker && typeof retryPicker.datepicker.setDate === 'function') {
                        try {
                            retryPicker.datepicker.setDate(retryDateValue);
                        } catch (rpe) {
                            console.error('Error setting date on retry picker:', rpe);
                        }
                    }
                    if (typeof eventFire === 'function') { eventFire(retryField, 'change'); }
                    if (typeof setTimeset === 'function') { setTimeset(String(retryDateValue || ''), String(typeId), parseInt(showDateTime || 0), String(actValue || '')); }
                }
        } catch(e3){console.error(e3);}
    }, 500);
}

function eventFire(el, etype) {
    if (el && typeof el.dispatchEvent === 'function' && typeof etype === 'string' && etype.length > 0) {
        try {
            var evObj = null;
            if (typeof Event === 'function') {
                evObj = new Event(etype, { bubbles: true, cancelable: false });
            } else if (document.createEvent) {
                evObj = document.createEvent('Events');
                evObj.initEvent(etype, true, false);
            }
            if (evObj) {
                try {
                    el.dispatchEvent(evObj);
                } catch (de_err) {
                    if (typeof console !== 'undefined' && console.error) {
                        console.error('dispatchEvent failed for element:', el, 'event:', etype, de_err);
                    }
                }
            }
        } catch (e) {
            if (typeof console !== 'undefined' && console.error) {
                console.error('eventFire failed:', e);
            }
        }
    }
}
