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

function isSunday(date, fieldName) {
    if (getWeekdate(date) === 0) {
        return true;
    } else {
        return false;
    }
}

function isMonday(date, fieldName) {
    if (getWeekdate(date) === 1) {
        return true;
    } else {
        return false;
    }
}

function isTuesday(date, fieldName) {
    if (getWeekdate(date) === 2) {
        return true;
    } else {
        return false;
    }
}

function isWednesday(date, fieldName) {
    if (getWeekdate(date) === 3) {
        return true;
    } else {
        return false;
    }
}

function isThursday(date, fieldName) {
    if (getWeekdate(date) === 4) {
        return true;
    } else {
        return false;
    }
}

function isFriday(date, fieldName) {
    if (getWeekdate(date) === 5) {
        return true;
    } else {
        return false;
    }
}

function isSaturday(date, fieldName) {
    if (getWeekdate(date) === 6) {
        return true;
    } else {
        return false;
    }
}

function setObjectId(object, typeid) {
    var className = object.className;
    var typeId = typeid;
    var selectField = document.getElementById("c4g_reservation_object_"+typeId);
    var reservationObjects = jQuery(document.getElementsByClassName("displayReservationObjects"));
    var emptyOption = null;
    var val = -1;

    if (selectField) {
        jQuery(selectField).show();
        reservationObjects ? reservationObjects.show() : false;
        if (className) {
            val = className.split("_")[2];
            //if (jQuery(selectField.options[value=val]).style == "display:block") {
                jQuery(selectField).val(val).change();
            //}
        }
    }
    hideOptions(reservationObjects,typeId, val);
    return true;
}

function hideOptions(reservationObjects,typeId,firstValue) {
    if (reservationObjects) {
        var selectField = document.getElementById("c4g_reservation_object_"+typeId);
        var first = firstValue;
        if (selectField) {
            for (i = 0; i < selectField.options.length; i++) {
                var option = selectField.options[i];
                var min = option.getAttribute('min') ? parseInt(option.getAttribute('min')) : 0;
                var max = option.getAttribute('max') ? parseInt(option.getAttribute('max')) : 0;
                var desiredCapacity = jQuery(document.getElementById("c4g_desiredCapacity_"+typeId));
                var capacity = desiredCapacity ? desiredCapacity.value || desiredCapacity.val() : 0;

                if (min && max && capacity && capacity > 0) {
                    if ((capacity < min) || (capacity > max)) {
                        option.style = "display:none";
                    } else {
                        option.style = "display:block";
                        if ((first == -1) && (option.value != -1)) {
                            first = option.value;
                        }
                    }
                }
            }

            if (parseInt(first) >= 0) {
                jQuery(selectField).value ? jQuery(selectField).value = first : jQuery(selectField).val(first).change();
                jQuery(selectField).val(first);
                jQuery(selectField).children('option[value="-1"]').attr('disabled','disabled');

                jQuery(selectField).removeAttr('disabled');
            } else {
                jQuery(selectField).value ? jQuery(selectField).value = "-1" : jQuery(selectField).val("-1").change();
                jQuery(selectField).prop("disabled", true);
            }
        }
    }
}

function setTimeset(object, id, additionalId, callFunction) {
    var brick_api = apiBaseUrl+"/c4g_brick_ajax";
    var elementId = 0;
    var date = 0;
    var val = -1;

    if (additionalId == -1) {
        jQuery(document.getElementsByClassName('reservation_time_button')) ? jQuery(document.getElementsByClassName('reservation_time_button')).hide() : false;
        jQuery(document.getElementsByClassName('displayReservationObjects')) ? jQuery(document.getElementsByClassName('displayReservationObjects')).hide() : false;
    } else {
        elementId = "c4g_beginDate_"+additionalId;
        date = document.getElementById(elementId).value;
    }
    var durationNode = document.getElementById("c4g_duration");
    if (durationNode) {
        var duration = durationNode.value;
    }

    C4GCallOnChangeMethodswitchFunction(object);
    C4GCallOnChange(object);

    //hotfix dates with slashes
    if (date && date.indexOf("/")) {
        date = date.replace("/", "~");
        date = date.replace("/", "~");
    }

    if (id && callFunction && date && additionalId) {
        jQuery.ajax({
            dataType: "json",
            url: brick_api + "/"+id+"/" + "buttonclick:" + callFunction + ":"+ date +":"+additionalId+ ":"+ duration +  "?id=0",
            success: function (data) {
                var timeGroup = document.getElementById("c4g_beginTime_"+additionalId+"00"+getWeekdate(date));
                var radioGroups = timeGroup.parentElement.getElementsByClassName("c4g_brick_radio_group");
                var timeList = [];
                var objectList = [];

                var times = data['times'];

                var size = times.length;

                jQuery(document.getElementsByClassName('reservation_time_button_'+additionalId)) ? jQuery(document.getElementsByClassName('reservation_time_button_'+additionalId)).show() : false;
                for (var i = 0; i < size; i++) {
                    var dataTime = times[i].id;
                    timeList[i] = dataTime;

                    var dataObjects = times[i].objects;
                    objectList[i] = dataObjects;
                }


                //if (radioGroups.style && radioGroups.style != "display:none") {
                for (i = 0; i < radioGroups.length; i++) {
                    try {
                        for (j = 0; j < jQuery(radioGroups[i].children).length; j++) {
                            if (radioGroups[i].children.style && radioGroups[i].children.style == "display:none") {
                                continue;
                            }

                            for (k = 0; k < jQuery(radioGroups[i].children[j].children).length; k++) {
                                jQuery(radioGroups[i].children[j].children[k]).removeAttr("checked");

                                var value = jQuery(radioGroups[i].children[j].children[k]).val();

                                if (value && parseInt(value)) {
                                    var arrIndex = jQuery.inArray(parseInt(value), timeList);
                                    var disabled = (
                                        (arrIndex === -1) || (objectList[arrIndex] == -1)
                                    );

                                    jQuery(radioGroups[i].children[j].children[k]).attr('disabled', disabled);
                                    //jQuery(radioGroups[i].children[j].children[k]).attr("onchange", "setObjectId(this,"+objectList[arrIndex][0]+");");

                                    if ((!disabled)) {
                                        jQuery(radioGroups[i].children[j].children[k]).removeClass().addClass("radio_object_" + objectList[arrIndex][0]);
                                        val = objectList[arrIndex][0];
                                    } else {
                                        jQuery(radioGroups[i].children[j].children[k]).removeClass().addClass("radio_object_disabled");
                                    }
                                }
                            }
                        }
                    } catch (err) {
                        //ToDo
                    }
                }
            }
            // }
        })
    }

    var reservationObjects = document.getElementsByClassName("displayReservationObjects");
    hideOptions(reservationObjects,additionalId, val);
}