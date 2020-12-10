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
    className = className.split(" ")[0];
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
            var objects = val.split('-');
            jQuery(selectField).val(objects[0]).change();
        }
    }
    hideOptions(reservationObjects,typeId, objects ? objects : val);
    return true;
}

function hideOptions(reservationObjects,typeId,values) {
    if (reservationObjects) {
        if (typeId == -1) {
            var typeField = document.getElementById("c4g_reservation_type");
            typeId = typeField ? typeField.value : -1;
        }
        var selectField = document.getElementById("c4g_reservation_object_"+typeId);
        var first = -1;//jQuery.isArray(values) ? values[0] : -1;//values;
        if (selectField) {
            for (i = 0; i < selectField.options.length; i++) {
                var option = selectField.options[i];
                var min = option.getAttribute('min') ? parseInt(option.getAttribute('min')) : 1;
                var max = option.getAttribute('max') ? parseInt(option.getAttribute('max')) : 1;
                var desiredCapacity = document.getElementById("c4g_desiredCapacity_"+typeId);
                var capacity = desiredCapacity ? desiredCapacity.value : 1;

                //not in values
                var foundValue = false;
                if (jQuery.isArray(values)) {
                    for (j = 0; j < values.length; j++) {
                        if (values[j] == option.value) {
                            foundValue = true;
                        }
                    }
                }

                if (!foundValue && (option.value != -1)) {
                    jQuery(selectField).children('option[value="'+option.value+'"]').attr('disabled','disabled');
                } else if (option.value != -1) {
                    jQuery(selectField).children('option[value="'+option.value+'"]').removeAttr('disabled');
                    if (min && max && capacity && capacity > 0) {
                        if ((capacity < min) || (capacity > max)) {
                            jQuery(selectField).children('option[value="'+option.value+'"]').attr('disabled','disabled');
                        } else {
                            jQuery(selectField).children('option[value="'+option.value+'"]').removeAttr('disabled');
                            if ((first == -1) && (option.value != -1)) {
                                first = option.value;
                            }
                        }
                    } else {
                        jQuery(selectField).children('option[value="'+option.value+'"]').removeAttr('disabled');
                        if ((first == -1) && (option.value != -1)) {
                            first = option.value;
                        }
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

    checkEventFields(-1);
}

function setReservationForm(object, id, additionalId, callFunction) {
    var typeField = document.getElementById("c4g_reservation_type");
    var typeId = typeField ? typeField.value : -1;

    var dateFieldVisible = false;
    var dateFields = document.getElementsByClassName('c4g_date_field_input');
    if (dateFields) {
        for (i = 0; i < dateFields.length; i++) {
            var dateField = dateFields[i];
            if (dateField && dateField.value && jQuery(dateField).is(":visible")) {
                dateFieldVisible = true;
                setTimeset(dateField, id, additionalId, callFunction);
                break;
            }
        }
    }

    if (!dateFieldVisible) {
        setTimeset(null, id, additionalId, callFunction);
    }

    var radioButton = jQuery('.reservation_time_button input[type = "radio"]:checked');
    if (radioButton) {
        for (i = 0; i < radioButton.length; i++) {
            var button = radioButton[i];

            if (button && jQuery(button).hasClass("radio_object_"+typeId)) {
                setObjectId(button, typeId);
                break;
            }
        }
    }
}

function setTimeset(dateField, id, additionalId, callFunction) {
    var brick_api = apiBaseUrl+"/c4g_brick_ajax";
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

        //elementId = "c4g_beginDate_"+additionalId;
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

    if (id && callFunction && date && additionalId) {
        jQuery.ajax({
            dataType: "json",
            url: brick_api + "/"+id+"/" + "buttonclick:" + callFunction + ":"+ date +":"+additionalId+ ":"+ duration +  "?id=0",
            success: function (data) {
                var timeGroup = document.getElementById("c4g_beginTime_"+additionalId+"-00"+getWeekdate(date));
                var radioGroups = timeGroup ? timeGroup.parentElement.getElementsByClassName("c4g_brick_radio_group") : document.getElementsByClassName("c4g_brick_radio_group");
                var timeList = [];
                var objectList = [];
                var times = data['times'];
                var size = times.length;

                jQuery(document.getElementsByClassName('reservation_time_button_'+additionalId)) ? jQuery(document.getElementsByClassName('reservation_time_button_'+additionalId)).show() : false;
                var iterator = 0;
                for (let key in times) {
                    var dataTime = times[key]['id'];
                    var dataObjects = times[key]['objects'];

                    timeList[iterator] = dataTime;
                    objectList[iterator] = dataObjects;
                    iterator++;
                }

                var selectField = document.getElementById("c4g_reservation_object_"+additionalId);
                var capMin = 1;
                var capMax = 1;
                if (selectField) {
                    for (i = 0; i < selectField.options.length; i++) {
                        var option = selectField.options[i];
                        var min = option.getAttribute('min') ? parseInt(option.getAttribute('min')) : 1;
                        if ((min == -1) || (min < capMin)) {
                            capMin = min;
                        }
                        var max = option.getAttribute('max') ? parseInt(option.getAttribute('max')) : 1;
                        if ((max == -1) || (max > capMax)) {
                            capMax = max;
                        }
                    }
                }

                var desiredCapacity = document.getElementById("c4g_desiredCapacity_"+additionalId);
                var capacity = desiredCapacity ? desiredCapacity.value : 1;

                if (radioGroups) {
                    for (i = 0; i < radioGroups.length; i++) {
                        for (j = 0; j < radioGroups[i].children.length; j++) {
                            if (radioGroups[i].children[j].style && radioGroups[i].children[j].style == "display:none") {
                                continue;
                            }

                            for (k = 0; k < radioGroups[i].children[j].children.length; k++) {
                                var value = jQuery(radioGroups[i].children[j].children[k]).val();
                                if (value && parseInt(value)) {
                                    namefield = radioGroups[i].children[j].children[k].getAttribute('name').substr(1);
                                    var arrindex = jQuery.inArray(parseInt(value), timeList);
                                    var activateTimeButton = -1
                                    var percent = 0;
                                    if (arrindex !== -1) {
                                        for (l = 0; l < objectList[arrindex].length; l++) {
                                            if (objectList[arrindex][l]['id'] != -1) {
                                                if ((objectList[arrindex][l]['act'] < objectList[arrindex][l]['max'] )) {
                                                    activateTimeButton = (activateTimeButton < objectList[arrindex][l]['act']) ? objectList[arrindex][l]['act'] : activateTimeButton;
                                                    percent = objectList[arrindex][l]['percent'];
                                                }
                                            }
                                        }
                                    }

                                    if ((activateTimeButton >= 0) && (activateTimeButton < capMax) && (capacity >= capMin) && (capacity <= capMax)) {
                                        let objstr = '';
                                        for (l = 0; l < objectList[arrindex].length; l++) {
                                            let listObj = objectList[arrindex][l];
                                            if (l == 0) {
                                                objstr = objstr + listObj['id'];
                                            } else {
                                                objstr = objstr + '-' + listObj['id'];
                                            }

                                            var optionidx = -1;
                                            for (var m = 0; m < jQuery(selectField).length; m++) {
                                                if (selectField[m].value == listObj['id']) {
                                                    optionidx = m;
                                                    break;
                                                }
                                            }

                                            //ToDo wrong position move to setObjectId
                                            // if (optionidx !== -1) {
                                            //     if (listObj['showSeats']) {
                                            //         var optionText = selectField[optionidx].text;
                                            //         var pos = optionText.lastIndexOf('[');
                                            //         if (pos != -1) {
                                            //             optionText = optionText.substr(0, pos-1);
                                            //         }
                                            //         selectField[optionidx].text = optionText + ' ['+listObj['act']+'/'+listObj['max']+']';
                                            //     }
                                            // }

                                        }

                                        if (objstr != 'undefined') {
                                            jQuery(radioGroups[i].children[j].children[k]).removeClass().addClass("radio_object_" + objstr);
                                            jQuery(radioGroups[i].children[j].children[k]).attr('disabled', false);
                                        }

                                        if (percent > 0) {
                                            jQuery(radioGroups[i].children[j].children[k]).addClass("radio_object_hurry_up");
                                        }

                                        val = objectList[arrindex][0]['id']; //first valid option
                                    } else {
                                        jQuery(radioGroups[i].children[j].children[k]).removeClass().addClass("radio_object_disabled");
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
            }
        })
    }

    var reservationObjects = document.getElementsByClassName("displayReservationObjects");
    hideOptions(reservationObjects,additionalId, val);
}

/**
 *
 * @param object
 */
function checkEventFields(object) {
    var typeField = document.getElementById("c4g_reservation_type");
    var typeId = typeField ? typeField.value : -1;
    var selectField = jQuery('.reservation-event-object select');

    jQuery('.eventdata').hide();

    if (selectField && selectField.is(":visible")) {
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
                        if ((additional != -1) && jQuery(dateFields[j]).children().children('input').hasClass('c4g_beginDateEvent_' + additional)) {
                            jQuery(dateFields[j]).show();
                            jQuery(dateFields[j]).children().show();
                        } else {
                            jQuery(dateFields[j]).hide();
                            jQuery(dateFields[j]).children().hide();
                        }
                    }
                }

                var timeFields = jQuery('.c4g_brick_radio_group_wrapper .reservation_time_event_button');
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