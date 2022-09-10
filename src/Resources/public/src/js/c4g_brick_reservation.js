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

        if (y && y.length == 2) {
            y = '20'+y;
        }

        date = y + "/" + m + "/" + d;
    } else if (date.indexOf("/") > 0) {
        var arrDate = date.split("/")
        var y = arrDate[2];
        var m = arrDate[1];
        var d = arrDate[0];
        //important for safari browser
        m = m<10?"0"+m:m;
        d = d<10?"0"+d:d;

        if (y && y.length == 2) {
            y = '20'+y;
        }
        date = y + "/" + m + "/" + d;
    }

    var jsdate = new Date(date);
    return jsdate.getDay();
}

function isWeekday(datestr, fieldName) {
    var dateArr = datestr.split('--');
    if (dateArr && dateArr[0] && dateArr[1]) {
        if (getWeekdate(dateArr[0]) == dateArr[1]) {
            return true;
        }
    }
    return false;
}

function setObjectId(object, typeid, showDateTime = 0) {
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
            for (i=0; i < reservationObjects.length; i++) {
                var reservationObject = reservationObjects[i];
                reservationObject.style.display = 'block';
            }
        }

        if (objectParam) {
            objects = objectParam.split('-');
            var breakDance = false;
            for (i=0; i < objects.length; i++) {
                for (j = 0; j < selectField.options.length; j++) {
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

    if (oldValue) {
       for (i = 0; i < selectField.options.length; i++) {
           if (!selectField.options[i].getAttribute('hidden')) {
               if (selectField.options[i].value == oldValue) {
                   selectField.value = oldValue;
                   handleBrickConditions();
                   break;
               }
           }
       }
    }

    if (selectField && parseInt(selectField.value) != -1) {
        for (i = 0; i < selectField.options.length; i++) {
            if (parseInt(selectField.options[i].value) == -1 && selectField.options[i].style.display != "none") {
                selectField.options[i].style.display = "none";
                break;
            }
        }
    }

    hideOptions(typeId, values, showDateTime);
    return true;
}

function hideOptions(typeId, values, showDateTime) {
    if (typeId == -1) {
        var typeField = document.getElementById("c4g_reservation_type");
        typeId = typeField ? typeField.value : -1;
    }

    var selectField = document.getElementById("c4g_reservation_object_"+typeId);
    var first = -1;
    var firstValueParam = 0;
    var emptyKey = false;
    if (selectField) {
        var distance = 0;

        for (i = 0; i < selectField.options.length; i++) {
            var option = selectField.options[i];
            var min = option.getAttribute('min') ? parseInt(option.getAttribute('min')) : 1;
            var max = option.getAttribute('max') ? parseInt(option.getAttribute('max')) : 0;
            var desiredCapacity = document.getElementById("c4g_desiredCapacity_"+typeId);
            var capacity = desiredCapacity ? desiredCapacity.value : 0;
            var actMinDistance = capacity - min;
            var actMaxDistance = max - capacity;
            var actDistance = (actMinDistance > actMaxDistance) ? actMinDistance : actMaxDistance;

            if (option['value'] && (option['value'] == -1)) {
                emptyKey = i;
            }

            //not in values
            var foundValue = false;
            if (Array.isArray(values)) {
                if (min && capacity && (capacity > 0) && (capacity >= min) && (capacity <= max)) {
                    for (j = 0; j < values.length; j++) {
                        if (values[j] == option.value) {
                            if (!distance || (distance == 0) || (actDistance && (actDistance > 0) && (distance > actDistance))) {
                                distance = actDistance ? actDistance : distance;
                                firstValueParam = values[j];
                                foundValue = true;
                            }
                        }
                    }
                } else {
                    for (j = 0; j < values.length; j++) {
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
                            first = firstValueParam;
                        } else {
                            if ((first == -1) && (option.value != -1)) {
                                first = option.value;
                            }
                        }
                    }
                } else {
                    option.removeAttribute('disabled');
                    option.removeAttribute('hidden');
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
                var text = option.textContent;
                var date = '';
                var time = '';

                var dateFields = document.querySelectorAll('.c4g__form-date-container .c4g_beginDate_'+typeId); //ToDo +'-'+option.value
                //var dateFields = document.getElementsByClassName('.c4g__form-date-container .c4g_beginDate_'+typeId); //ToDo +'-'+option.value


                if (dateFields) {
                    for (k = 0; k < dateFields.length; k++) {
                        var dateField = dateFields[k];
                        if (dateField && dateField.value) {
                            date = dateField.value;
                            break;
                        }
                    }
                }


                var radioButton = document.querySelectorAll('.reservation_time_button_'+typeId+' input[type = "radio"]:checked');
                if (radioButton) {
                    for (k = 0; k < radioButton.length; k++) {
                        var button = radioButton[k];
                        if (button) {

                            var buttonLabel = button.parentNode.getElementsByTagName('label')[0];
                            time = buttonLabel ? buttonLabel.value : '';
                            break;
                        }
                    }
                }

                if (text && (date != '') && (time != '')) {
                    var pos = text.lastIndexOf('\u00A0(');
                    if (pos != -1) {
                        text = text.substr(0, pos);
                    }

                    option.textContent = text + '\u00A0('+date+'\u00A0'+time+')';
                }
            }
        }

        if (!selectField.disabled && selectField.value && (selectField.value >= 0)) {
            first = selectField.value;

        }

        if (parseInt(first) >= 0) {
            if (selectField.options[first]) {
              selectField.value = first;
              selectField.options[first].removeAttribute('disabled');
              selectField.options[first].removeAttribute('hidden');
            } else if (emptyKey != false) {
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
        return dateField.parent.parent.classList.contains('begindate-event') ? true : false;
    } else {
        return dateField.parent.parent.classList.contains('begin-date') ? true : false
    }
}

function setReservationForm(typeId, showDateTime) {
    document.getElementsByClassName("reservation-id")[0].style.display = "none";

    var event = false;
    var object = false;

    var typeField = document.getElementById("c4g_reservation_type");
    typeId = typeField ? typeField.value : -1;

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
                setTimeset(document.getElementById(dateId), typeId, showDateTime);
            }
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
        } else if (document.getElementById(dateId)) {
            setTimeset(document.getElementById(dateId), typeId, showDateTime);
        }
    }

    handleBrickConditions();

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
                } else if (timeset[1] && valueset[0]) { //direct booking
                    let beginTime = parseInt(timeset[0]);
                    let endTime = beginTime + parseInt(timeset[1]);

                    let beginValue = parseInt(valueset[0]);

                    if ((beginTime <= beginValue) && (endTime >= beginValue)) {
                        arrIndex = idx;
                        hits++;
                    }

                    if (hits == 3) {
                        break;
                    }
                } else if (timeset[0] && valueset[1]) { //direct booking
                    let beginTime = parseInt(timeset[0]);

                    let beginValue = parseInt(valueset[0]);
                    let endValue = beginValue + parseInt(valueset[1]);

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
    var elementReallyShowed = !el.disabled && (el.style.display != "none");

    var timeButtonParents = el && el.parent ? el.parent.parent.children : [];
    if (timeButtonParents && timeButtonParents.length) {
        for (i=0; i < timeButtonParents.length; i++) {
          elementReallyShowed = elementReallyShowed && !timeButtonParents[i].disabled && (timeButtonParents[i].style.display != "none") && (document.querySelector("#"+timeButtonParents[i].getAttribute('id')+":not([hidden])"));
        }
    }

    return elementReallyShowed;
}

function setTimeset(dateField, additionalId, showDateTime) {
    var elementId = 0;
    var date = 0;
    var val = -1;
    var nameField = '';
    var objectId = 0;

    if (additionalId == -1) {
        document.getElementsByClassName('reservation_time_button') ? document.getElementsByClassName('reservation_time_button').style.display = "none" : false;
        document.getElementsByClassName('displayReservationObjects') ? document.getElementsByClassName('displayReservationObjects').style.display = "none" : false;
    } else {
        if (!dateField) {
            dateField = document.getElementById('c4g_beginDate_'+additionalId);
        } else {
            if (dateField.id && dateField.id.indexOf("-33") && (dateField.id.indexOf("-33") != -1)) {
                objectId = dateField.id.substr(dateField.id.indexOf("-33")+3);
            }
        }

        date = dateField ? dateField.value : 0;
    }

    var durationNode = document.getElementById("c4g_duration_"+additionalId);
    if (durationNode && durationNode.style && durationNode.style !== "none"){
        var duration = durationNode.value;
    }

    var capacityNode = document.getElementById("c4g_desiredCapacity_"+additionalId);
    if (capacityNode && capacityNode.style && capacityNode.style !== "none"){
        var capacity = capacityNode.value;
    }

    //hotfix dates with slashes
    if (date && date.indexOf("/")) {
        date = date.replace("/", "~");
        date = date.replace("/", "~");
    }

    if (date && additionalId) {
        duration = duration ? duration : -1;
        capacity = capacity ? capacity : -1;
        document.getElementsByClassName('c4g__spinner-wrapper')[0].style.display = "flex";
        let url = "/reservation-api/currentTimeset/" + date + "/" + additionalId + "/" + duration + "/" + capacity + "/" + objectId;
        var targetButton = false;
        fetch(url)
            .then(response => response.json())
            .then((data) => {
                var timeGroup = document.getElementById("c4g_beginTime_"+additionalId+"-00"+getWeekdate(date));
                var radioGroups = timeGroup ? timeGroup.parentElement.getElementsByClassName("c4g__form-radio-group") : document.querySelectorAll(".reservation_time_button .c4g__form-radio-group");
                //var radioGroups = timeGroup ? timeGroup.parentElement.getElementsByClassName("c4g__form-radio-group") : document.getElementsByClassName(".reservation_time_button .c4g__form-radio-group");


                var timeList = [];
                var intervalList = [];
                var objectList = [];
                var nameList = [];
                var times = data['times'];
                var size = times.length;
                var objCaptions = data['captions'];
                if (!document.getElementById("c4g_reservation_id").value || (document.getElementById("c4g_reservation_id").value != data['reservationId'])) {
                    document.getElementById("c4g_reservation_id").value = data['reservationId']; //Force regeneration
                }

                document.getElementsByClassName("reservation-id")[0].style.display = "block";

                var timeButtons = document.getElementsByClassName('reservation_time_button_'+additionalId);
                for (i=0; i<timeButtons.length;i++) {
                    timeButtons[i].style.display = "block"
                }

                var iterator = 0;
                for (let key in times) {
                    var dataTime = times[key]['time'];
                    if (parseInt(times[key]['interval']) > 0) {
                        dataTime = times[key]['time']+'#'+times[key]['interval'];
                    }
                    var dataInterval = times[key]['interval'];
                    var dataObjects = times[key]['objects'];
                    var dataName = times[key]['name'];

                    timeList[iterator] = dataTime;
                    intervalList[iterator] = dataInterval;
                    objectList[iterator] = dataObjects;
                    nameList[iterator] = dataName;
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
                        var formRadioGroup = radioGroups[i];
                        for (j = 0; j < formRadioGroup.children.length; j++) {
                            var formCheck = formRadioGroup.children[j];
                            if (formCheck.style && formCheck.style == "display:none") {
                                continue;
                            }

                            for (k = 0; k < formCheck.children.length; k++) {
                                var formCheckInput = formCheck.children[k];
                                var value = formCheckInput.value;

                                if (value) {
                                    namefield = formCheckInput.getAttribute('name').substr(1);
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
                                            for (var m = 0; m < selectField.length; m++) {
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

                                        formCheckInput.setAttribute('data-object', objstr);
                                        formCheckInput.disabled = false;
                                        formCheckInput.style.display = "block";

                                        if (nameList[arrindex]) {
                                            var id = formCheckInput.getAttribute('id');
                                            if (id) {
                                                var formCheckLabel = formCheckInput.parentNode.getElementsByTagName('label')[0];
                                                formCheckLabel.innerHTML = nameList[arrindex];
                                            }
                                        }

                                        if (percent > 0) {
                                            formCheckInput.classList.add("radio_object_hurry_up");
                                        }

                                        if (!val || (val == -1)) {
                                            val = objArr[0]; //first valid option
                                        }

                                        if (!objectId) {
                                            hideOptions(additionalId, objstr, showDateTime);
                                        } else if (objCaptions && objCaptions[objectId]) {
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
                                    } else {
                                        formCheckInput.setAttribute('disabled', true);
                                        formCheckInput.setAttribute('hidden', true);
                                    }
                                }
                            }
                        }
                    }
                }

                handleBrickConditions();

                if (nameField) {
                   var valueElement = document.getElementById(nameField);
                   if (valueElement) {
                      valueElement.value = '';
                   }

                   var reservation_time_button = document.querySelectorAll('.reservation_time_button_'+additionalId+' input[type = "radio"]');
                   for (z=0; z < reservation_time_button.length; z++) {
                       reservation_time_button[z].removeAttribute("checked");
                   }
                }

                //var reservationObjects = document.getElementsByClassName("displayReservationObjects");

                if (additionalId != -1) {
                    var timeGroups = document.querySelectorAll('.reservation_time_button_'+additionalId+'.formdata input[type = "hidden"]');
                    var timeValue = false;
                    if (timeGroups) {
                        for (z = 0; z < timeGroups.length; z++) {
                            if (timeGroups[z].style.display != "none") {
                                timeValue = timeGroups[z].value;
                                break;
                            }
                        }
                    }

                    var radioButton = document.querySelectorAll('.reservation_time_button_'+additionalId+' input[type = "radio"]');
                    var visibleButtons = [];
                    if (radioButton && radioButton.length) {
                        for (z = 0; z < radioButton.length; z++) {
                            var button = radioButton[z];
                            if (button /*&& isElementReallyShowed(button)*/) {

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
                        for (z = 0; z < visibleButtons.length; z++) {
                                targetButton = visibleButtons[z];
                                break;
                        }
                    }

                    if (!objectId && selectField) {
                        setObjectId(0,additionalId,showDateTime);
                        selectField.value = -1;
                        eventFire(selectField,'change');
                        selectField.disabled = true;
                    }

                    if (!objectId) {
                        if (targetButton && !targetButton.disabled && !targetButton.classList.contains("radio_object_disabled")) {
                            targetButton.removeAttribute("checked");
                            //selectField.value = object;
                            eventFire(targetButton,'click');
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
    var selectField = document.querySelector('.reservation-event-object select');
    //handleBrickConditions();
    //ToDo test with contao events
    let eventData = document.getElementsByClassName('eventdata');
    if (eventData[0]) {
        eventData[0].style.display = 'none';

    }

    if (selectField && (document.querySelector("reservation-id:not([hidden])"))) {

        document.getElementsByClassName("reservation-id");
        for (i = 0; i < selectField.length; i++) {
            if (selectField[i]) {
                var selectField = selectField[i];
                var additional = -1;
                if (selectField[i].value) {
                    additional = typeId.toString() + "-22" + selectField[i].value.toString();
                    document.getElementsByClassName('eventdata_' + additional).style.display = "block";
                    document.getElementsByClassName('eventdata_' + additional).children[0].style.display = "block";
                }

                var dateFields = document.getElementsByClassName('begindate-event');
                if (dateFields) {
                    for (j = 0; j < dateFields.length; j++) {
                        if ((additional != -1) && dateFields[j].children[0].getElementsByClassName('c4g__form-date-container')[0].children[0].getElementsByTagName('input')[0].classList.contains('c4g_beginDateEvent_' + additional)) {
                            dateFields[j].style.display = "block";
                            dateFields[j].children[0].getElementsByTagName('label')[0].style.display = "block";
                            dateFields[j].children[0].getElementsByClassName('c4g__form-date-container')[0].style.display = "block";
                            dateFields[j].children[0].getElementsByClassName('c4g__form-date-container')[0].children[0].getElementsByTagName('input')[0].style.display = "block";
                        } else {
                            dateFields[j].style.display = "none";
                            dateFields[j].getElementsByTagName('label')[0].style.display = "none";
                            dateFields[j].children[0].getElementsByClassName('c4g__form-date-container')[0].style.display = "none";
                            dateFields[j].children[0].getElementsByClassName('c4g__form-date-container')[0].children[0].getElementsByTagName('input')[0].style.display = "none";
                        }
                    }
                }

                var timeFields = document.getElementsByClassName('reservation_time_event_button');
                if (timeFields) {
                    for (j = 0; j < timeFields.length; j++) {
                        if ((additional != -1) && timeFields[j].classList.contains('reservation_time_event_button_' + additional)) {
                            timeFields[j].style.display = "block";
                            timeFields[j].children[0].getElementsByTagName('label')[0].style.display = "block";
                            timeFields[j].parent.style.display = "block";
                            timeFields[j].parent.parent.style.display = "block";
                            timeFields[j].parent.parent.parent.style.display = "block";
                        } else {
                            timeFields[j].style.display = "none";
                            timeFields[j].children[0].getElementsByTagName('label')[0].style.display = "none";
                            timeFields[j].parent.style.display = "none";
                            timeFields[j].parent.parent.style.display = "none";
                            timeFields[j].parent.parent.parent.style.display = "none";
                        }
                    }
                }
            }
        }
    } else {
        var dateFields = document.getElementsByClassName('begindate-event');
        if (dateFields && Array.isArray(dateFields)) {
            for (i = 0; i < dateFields.length; i++) {
                dateFields[i].style.display = "none";
            }
        }

        var timeFields = document.getElementsByClassName('reservation_time_event_button');
        if (timeFields && Array.isArray(timeFields)) {
            for (i = 0; i < timeFields.length; i++) {
                timeFields[i].style.display = "none";
            }
        }
    }
}