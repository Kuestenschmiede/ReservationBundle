function getWeekdate(e){var t,r,i;return 0<e.indexOf(".")?e=(t=e.split("."))[2]+"/"+(r=(r=t[1])<10?"0"+r:r)+"/"+(i=(i=t[0])<10?"0"+i:i):0<e.indexOf("/")&&(e=(t=e.split("/"))[2]+"/"+(r=(r=t[1])<10?"0"+r:r)+"/"+(i=(i=t[0])<10?"0"+i:i)),new Date(e).getDay()}function isSunday(e,t){return 0===getWeekdate(e)}function isMonday(e,t){return 1===getWeekdate(e)}function isTuesday(e,t){return 2===getWeekdate(e)}function isWednesday(e,t){return 3===getWeekdate(e)}function isThursday(e,t){return 4===getWeekdate(e)}function isFriday(e,t){return 5===getWeekdate(e)}function isSaturday(e,t){return 6===getWeekdate(e)}function setObjectId(e,t,r=0){var i,n=jQuery(e).attr("data-object"),a=t,l=document.getElementById("c4g_reservation_object_"+a),s=jQuery(document.getElementsByClassName("displayReservationObjects")),t="";return l&&(jQuery(l).show(),s&&s.show(),n&&(i=n.split("-"),jQuery(e).is(":disabled")||jQuery(l).val(i[0]).change(),t=i||t)),hideOptions(s,a,t,r),!0}function hideOptions(e,t,r,n){if(e){-1==t&&(t=(e=document.getElementById("c4g_reservation_type"))?e.value:-1);var a=document.getElementById("c4g_reservation_object_"+t),l=-1,s=0;if(a){for(i=0;i<a.options.length;i++){var d=a.options[i],o=d.getAttribute("min")?parseInt(d.getAttribute("min")):1,u=d.getAttribute("max")?parseInt(d.getAttribute("max")):1,c=document.getElementById("c4g_desiredCapacity_"+t),c=c?c.value:1,y=!1;if(jQuery.isArray(r)){for(j=0;j<r.length;j++)if(r[j]==d.value){0==j&&(s=r[j]),y=!0;break}}else 0<=parseInt(r)&&r==d.value&&(s=r,y=!0);if(y||-1==d.value?-1!=d.value&&(jQuery(a).children('option[value="'+d.value+'"]').removeAttr("disabled"),o&&u&&c&&0<c&&(c<o||u<c)?jQuery(a).children('option[value="'+d.value+'"]').attr("disabled","disabled"):(jQuery(a).children('option[value="'+d.value+'"]').removeAttr("disabled"),0<=s&&d.value==s?l=s:-1==l&&-1!=d.value&&(l=d.value))):jQuery(a).children('option[value="'+d.value+'"]').attr("disabled","disabled"),n&&-1!=d.value&&y){var u=jQuery(a).children('option[value="'+d.value+'"]').text(),m="",v="",g=document.querySelectorAll(".c4g__form-date-container .c4g_beginDate_"+t);if(g)for(k=0;k<g.length;k++){var h=g[k];if(h&&h.value){m=h.value;break}}var f=jQuery(".reservation_time_button_"+t+' input[type = "radio"]:checked');if(f)for(k=0;k<f.length;k++){var p=f[k];if(p){p=jQuery('label[for="'+jQuery(p).attr("id")+'"]'),v=p?p[0].firstChild.nodeValue:"";break}}u&&""!=m&&""!=v&&(-1!=(c=u.lastIndexOf(" ("))&&(u=u.substr(0,c)),jQuery(a).children('option[value="'+d.value+'"]').text(u+" ("+m+" "+v+")"))}}0<=parseInt(l)?(jQuery(a).val(l).change(),jQuery(a).children('option[value="'+l+'"]').removeAttr("disabled"),jQuery(a).children('option[value="-1"]').attr("disabled","disabled"),jQuery(a).removeAttr("disabled")):(jQuery(a).children('option[value="-1"]').removeAttr("disabled"),jQuery(a).val("-1").change(),jQuery(a).prop("disabled",!0))}}checkEventFields()}function checkType(e,t){return t?!!jQuery(e).parent().parent().hasClass("begindate-event"):!!jQuery(e).parent().parent().hasClass("begin-date")}function setReservationForm(e,t,r,n){var a;jQuery(document.getElementsByClassName("reservation-id")).hide(),-1==t&&(t=(a=document.getElementById("c4g_reservation_type"))?a.value:-1,n||(l=a.selectedIndex,(s=a.options[l])&&(n=2==s.getAttribute("type"))));var l="c4g_beginDate_"+t;if(document.getElementById(l))setTimeset(document.getElementById(l),e,t,r);else if(n){var s=window.location.search;const c=new URLSearchParams(s);s=c.get("event");if(s){l="c4g_beginDateEvent_"+t+"-22"+s;document.getElementById(l)&&(setTimeset(document.getElementById(l),e,t,r),checkEventFields())}else{var d=document.getElementsByClassName("c4g__form-date-input");if(d)for(i=0;i<d.length;i++){var o=d[i];if(o&&checkType(o,n)&&o.value){var u=o.id;if(u&&u.indexOf("c4g_beginDateEvent_"+t+"-22")){setTimeset(o,e,t,r),checkEventFields();break}}}}}document.getElementsByClassName("c4g__spinner-wrapper")[0].style.display="none"}function checkTimelist(n,a){var l=-1;if(n&&a)for(idx=0;idx<a.length;idx++){let i=0;if(a[idx]){let e=[],t=a[idx].toString();t&&t.indexOf("#")?e=t.split("#"):e[0]=t;let r=[];if((n=n.toString()).indexOf("#")?r=n.split("#"):r[0]=n,parseInt(e[0])===parseInt(r[0])&&(l=idx,i++),e[1]&&r[1]){var s=parseInt(e[0]),d=s+parseInt(e[1]),o=parseInt(r[0]),u=o+parseInt(r[1]);if(s<=o&&o<d&&(l=idx,i++),s<u&&u<=d&&(l=idx,i++),3==i)break}}else if(1==i)break}return l}function checkMax(n,a,l,s,d,e){let o=!0;var u,c,m,v;if(n[a][l].act+parseInt(e)<=n[a][l].max){for(y=0;y<n.length;y++)if(s&&d&&y!=a){let e=[],t=d[y].toString();t&&t.indexOf("#")?e=t.split("#"):e[0]=t;let r=[];(s=s.toString()).indexOf("#")?r=s.split("#"):r[0]=s;let i=!1;if(parseInt(e[0])===parseInt(r[0])?i=!0:e[1]&&r[1]&&(c=(u=parseInt(e[0]))+parseInt(e[1]),v=(m=parseInt(r[0]))+parseInt(r[1]),(u<=m&&m<c||u<v&&v<=c)&&(i=!0)),i)for(z=0;z<n[y].length;z++)if(n[y][z].act>=n[y][z].max||n[y][z].act+n[a][l].act>=n[a][l].max)return!1;o=!0}}else o=!1;return o}function shuffle(e){let t=e.length;for(;0<t;){var r=Math.floor(Math.random()*t);t--;var i=e[t];e[t]=e[r],e[r]=i}return e}function setTimeset(e,t,W,D){var F=0,z=-1;-1==W?(jQuery(document.getElementsByClassName("reservation_time_button"))&&jQuery(document.getElementsByClassName("reservation_time_button")).hide(),jQuery(document.getElementsByClassName("displayReservationObjects"))&&jQuery(document.getElementsByClassName("displayReservationObjects")).hide()):(e=e||document.getElementById("c4g_beginDate_"+W),F=e?e.value:0);var r,e=document.getElementById("c4g_duration");e&&(r=e.value),C4GCallOnChangeMethodswitchFunction(document.getElementById("c4g_reservation_object_"+W)),C4GCallOnChange(document.getElementById("c4g_reservation_object_"+W)),F&&F.indexOf("/")&&(F=(F=F.replace("/","~")).replace("/","~")),t&&F&&W&&(r=r||-1,document.getElementsByClassName("c4g__spinner-wrapper")[0].style.display="flex",fetch("/reservation-api/currentTimeset/"+F+"/"+W+"/"+r).then(e=>e.json()).then(e=>{var t=document.getElementById("c4g_beginTime_"+W+"-00"+getWeekdate(F)),n=t?t.parentElement.getElementsByClassName("c4g__form-radio-group"):document.querySelectorAll(".reservation_time_button .c4g__form-radio-group"),a=[],s=[],d=[],r=e.times;r.length;document.getElementById("c4g_reservation_id").value=e.reservationId,jQuery(document.getElementsByClassName("reservation-id")).show(),jQuery(document.getElementsByClassName("reservation_time_button_"+W))&&jQuery(document.getElementsByClassName("reservation_time_button_"+W)).show();var o,u=0;for(o in r){var c=r[o].time;0<parseInt(r[o].interval)&&(c=r[o].time+"#"+r[o].interval);var y=r[o].interval,m=r[o].objects;a[u]=c,s[u]=y,d[u]=m,u++}var v=document.getElementById("c4g_reservation_object_"+W),g=1,h=1;if(v)for(i=0;i<v.options.length;i++){var f=v.options[i],p=f.getAttribute("min")?parseInt(f.getAttribute("min")):1;(-1==p||p<g)&&(g=p);f=f.getAttribute("max")?parseInt(f.getAttribute("max")):1;(-1==f||h<f)&&(h=f)}var e=document.getElementById("c4g_desiredCapacity_"+W),_=e?e.value:1;if(n)for(i=0;i<n.length;i++)for(j=0;j<n[i].children.length;j++)if(!n[i].children[j].style||"display:none"!=n[i].children[j].style)for(k=0;k<n[i].children[j].children.length;k++){var b=jQuery(n[i].children[j].children[k]).val();if(b){namefield=n[i].children[j].children[k].getAttribute("name").substr(1);var Q=checkTimelist(b,a),E=-1,I=0;if(-1!==Q)for(l=0;l<d[Q].length;l++)-1!=d[Q][l].id&&checkMax(d,Q,l,b,a,_)&&(E=E<d[Q][l].act?d[Q][l].act:E,I=d[Q][l].percent);if(0<=E&&E<h&&g<=_&&_<=h){let e="",t=!1,r=[];for(l=0;l<d[Q].length;l++){var B=d[Q][l];if(B.priority&&1==B.priority){t=!0;break}}for(d[Q]=shuffle(d[Q]),l=0;l<d[Q].length;l++){var C=d[Q][l];s[Q],a[Q];t&&C.priority&&1==C.priority?r.splice(0,0,C.id):r.push(C.id);for(var x=0;x<jQuery(v).length;x++)if(v[x].value==C.id){if(t&&C.priority&&1==C.priority){z=C.id;break}if(!t){z=C.id;break}}}for(l=0;l<r.length;l++)0==l?e+=r[l]:e=e+"-"+r[l];jQuery(n[i].children[j].children[k]).attr("data-object",e),jQuery(n[i].children[j].children[k]).attr("disabled",!1),0<I&&jQuery(n[i].children[j].children[k]).addClass("radio_object_hurry_up"),z&&-1!=z||(z=r[0]),hideOptions(w,W,e,D)}else jQuery(n[i].children[j].children[k]).attr("disabled",!0)}}var w=document.getElementsByClassName("displayReservationObjects");if(-1!=W){var O=jQuery(".reservation_time_button_"+W+'.formdata input[type = "hidden"]'),A=!1;if(O)for(i=0;i<O.length;i++)if("none"!=O[i].style.display){A=O[i].value;break}var N=!1,T=jQuery(".reservation_time_button_"+W+' input[type = "radio"]');if(A){if(T)for(i=0;i<T.length;i++){var S=T[i];if(S&&jQuery(S).is(":visible")&&S.value===A){N=S;break}}}else T&&1===T.length&&(N=T[0]);!N||jQuery(N).is(":disabled")||jQuery(N).hasClass("radio_object_disabled")||(T.prop("checked",!0),e=N.getAttribute("name").substr(1),(e=document.getElementById(e))&&(e.value=N.value),setObjectId(N,W,D))}}).catch(function(){}).finally(function(){document.getElementsByClassName("c4g__spinner-wrapper")[0].style.display="none"})),!W||(r=jQuery(".reservation_time_button_"+W+' input[type = "radio"]'))&&1===r.length&&r.click()}function checkEventFields(){var e=document.getElementById("c4g_reservation_type"),t=e?e.value:-1,r=jQuery(".reservation-event-object select");if(jQuery(".eventdata").hide(),r&&r.is(":visible")){for(jQuery(document.getElementsByClassName("reservation-id")).show(),i=0;i<r.length;i++)if(r[i]){var n,a,l=-1;if(r[i].value&&(l=t.toString()+"-22"+r[i].value.toString(),jQuery(".eventdata_"+l).show(),jQuery(".eventdata_"+l).children().show()),n=document.getElementsByClassName("begindate-event"))for(j=0;j<n.length;j++)-1!=l&&jQuery(n[j]).children(".c4g__form-date-container").children("input").hasClass("c4g_beginDateEvent_"+l)?(jQuery(n[j]).show(),jQuery(n[j]).children("label").show(),jQuery(n[j]).children(".c4g__form-date-container").show(),jQuery(n[j]).children(".c4g__form-date-container").children("input").show()):(jQuery(n[j]).hide(),jQuery(n[j]).children("label").hide(),jQuery(n[j]).children(".c4g__form-date-container").hide(),jQuery(n[j]).children(".c4g__form-date-container").children("input").hide());if(a=jQuery(".reservation_time_event_button"))for(j=0;j<a.length;j++)-1!=l&&jQuery(a[j]).hasClass("reservation_time_event_button_"+l)?(jQuery(a[j]).show(),jQuery(a[j]).children("label").show(),jQuery(a[j]).parent().show(),jQuery(a[j]).parent().parent().show(),jQuery(a[j]).parent().parent().parent().show()):(jQuery(a[j]).hide(),jQuery(a[j]).children("label").hide(),jQuery(a[j]).parent().hide(),jQuery(a[j]).parent().parent().hide(),jQuery(a[j]).parent().parent().parent().hide())}}else{if(n=jQuery(".begindate-event"))for(i=0;i<n.length;i++)jQuery(n[i]).hide();if(a=jQuery(".reservation_time_event_button"))for(i=0;i<a.length;i++)jQuery(a[i]).hide()}}