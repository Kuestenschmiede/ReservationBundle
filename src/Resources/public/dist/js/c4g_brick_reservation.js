function getWeekdate(e){var t,r,i;return 0<e.indexOf(".")?e=(t=e.split("."))[2]+"/"+(r=(r=t[1])<10?"0"+r:r)+"/"+(i=(i=t[0])<10?"0"+i:i):0<e.indexOf("/")&&(e=(t=e.split("/"))[2]+"/"+(r=(r=t[1])<10?"0"+r:r)+"/"+(i=(i=t[0])<10?"0"+i:i)),new Date(e).getDay()}function isWeekday(e,t){e=e.split("--");return!!(e&&e[0]&&e[1]&&getWeekdate(e[0])==e[1])}function setObjectId(e,t,r=0){var i,n=!!e&&jQuery(e).attr("data-object"),a=t,l=document.getElementById("c4g_reservation_object_"+a),d=jQuery(document.getElementsByClassName("displayReservationObjects")),t="";return l&&(jQuery(l).show(),d&&d.show(),n&&(i=n.split("-"),jQuery(e).is(":disabled")||(!jQuery(l).val()||jQuery(l).val()<=0)&&(jQuery(l).val(i[0]),jQuery(l).attr("value",i[0])),jQuery(l).change(),t=i||t)),hideOptions(d,a,t,r),!0}function hideOptions(e,t,r,n){if(e){-1==t&&(t=(e=document.getElementById("c4g_reservation_type"))?e.value:-1);var a=document.getElementById("c4g_reservation_object_"+t),l=-1,d=0;if(a){var s=0;for(i=0;i<a.options.length;i++){var o=a.options[i],u=o.getAttribute("min")?parseInt(o.getAttribute("min")):1,c=o.getAttribute("max")?parseInt(o.getAttribute("max")):0,y=document.getElementById("c4g_desiredCapacity_"+t),v=y?y.value:0,h=v-u,y=c-v,m=y<h?h:y,g=!1;if(jQuery.isArray(r)){if(u&&v&&0<v&&u<=v&&v<=c)for(j=0;j<r.length;j++)r[j]==o.value&&(!s||0==s||m&&0<m&&m<s)&&(s=m||s,d=r[j],g=!0);else for(j=0;j<r.length;j++)if(r[j]==o.value){0==j&&(d=r[j]),g=!0;break}}else 0<=parseInt(r)&&r==o.value&&(d=r,g=!0);if(g||-1==o.value?-1!=o.value&&(jQuery(a).children('option[value="'+o.value+'"]').removeAttr("disabled"),jQuery(a).children('option[value="'+o.value+'"]').removeAttr("hidden"),u&&v&&0<v&&(v<u||c&&c<v)?(jQuery(a).children('option[value="'+o.value+'"]').attr("disabled","disabled"),jQuery(a).children('option[value="'+o.value+'"]').attr("hidden","hidden")):(jQuery(a).children('option[value="'+o.value+'"]').removeAttr("disabled"),jQuery(a).children('option[value="'+o.value+'"]').removeAttr("hidden"),0<=d&&o.value==d?l=d:-1==l&&-1!=o.value&&(l=o.value))):(jQuery(a).children('option[value="'+o.value+'"]').attr("disabled","disabled"),jQuery(a).children('option[value="'+o.value+'"]').attr("hidden","hidden")),n&&-1!=o.value&&g){var c=jQuery(a).children('option[value="'+o.value+'"]').text(),p="",f="",_=document.querySelectorAll(".c4g__form-date-container .c4g_beginDate_"+t);if(_)for(k=0;k<_.length;k++){var Q=_[k];if(Q&&Q.value){p=Q.value;break}}var b=jQuery(".reservation_time_button_"+t+' input[type = "radio"]:checked');if(b)for(k=0;k<b.length;k++){var I=b[k];if(I){I=jQuery('label[for="'+jQuery(I).attr("id")+'"]'),f=I?I[0].firstChild.nodeValue:"";break}}c&&""!=p&&""!=f&&(-1!=(v=c.lastIndexOf(" ("))&&(c=c.substr(0,v)),jQuery(a).children('option[value="'+o.value+'"]').text(c+" ("+p+" "+f+")"))}}!jQuery(a).is(":disabled")&&jQuery(a).val()&&0<=jQuery(a).val()&&(l=jQuery(a).val()),0<=parseInt(l)?(jQuery(a).val(l).change(),jQuery(a).children('option[value="'+l+'"]').removeAttr("disabled"),jQuery(a).children('option[value="'+l+'"]').removeAttr("hidden"),jQuery(a).children('option[value="-1"]').attr("disabled","disabled"),jQuery(a).children('option[value="-1"]').attr("hidden","hidden"),jQuery(a).removeAttr("disabled")):(jQuery(a).children('option[value="-1"]').removeAttr("disabled"),jQuery(a).children('option[value="-1"]').removeAttr("hidden"),jQuery(a).val("-1").change(),jQuery(a).prop("disabled",!0))}}checkEventFields()}function checkType(e,t){return t?!!jQuery(e).parent().parent().hasClass("begindate-event"):!!jQuery(e).parent().parent().hasClass("begin-date")}function setReservationForm(e,t){jQuery(".reservation-id").hide();var r=!1,n=!1,a=document.getElementById("c4g_reservation_type");e=a?a.value:-1;var l=a.selectedIndex,l=a.options[l];if(l&&(r=2==l.getAttribute("type"),n=3==l.getAttribute("type")),0<e){l=jQuery("#c4g_desiredCapacity_"+e);l&&(d=l.val(),l.attr("max")&&d>parseInt(l.attr("max"))&&l.val(l.attr("max")),l.attr("min")&&d<parseInt(l.attr("min"))&&l.val(l.attr("min")));l=jQuery("#c4g_duration_"+e);l&&(d=l.val(),l.attr("max")&&d>parseInt(l.attr("max"))&&l.val(l.attr("max")),l.attr("min")&&d<parseInt(l.attr("min"))&&l.val(l.attr("min")));var d="c4g_beginDate_"+e;if(document.getElementById(d))setTimeset(document.getElementById(d),e,t);else if(r){l=window.location.search;const c=new URLSearchParams(l);l=c.get("event");if(l){d="c4g_beginDateEvent_"+e+"-22"+l;document.getElementById(d)&&(setTimeset(document.getElementById(d),e,t),checkEventFields())}else{var s=document.getElementsByClassName("c4g__form-date-input");if(s)for(i=0;i<s.length;i++){var o=s[i];if(o&&checkType(o,r)&&o.value){var u=o.id;if(u&&u.indexOf("c4g_beginDateEvent_"+e+"-22")){setTimeset(o,e,t),checkEventFields();break}}}}}else!n||(n=document.getElementById("c4g_reservation_object_"+e))&&(d=d+"-33"+n.value,setTimeset(document.getElementById(d),e,t))}handleBrickConditions(),document.getElementsByClassName("c4g__spinner-wrapper")[0].style.display="none"}function checkTimelist(n,a){var l=-1;if(n&&a)for(idx=0;idx<a.length;idx++){let i=0;if(a[idx]){let e=[],t=a[idx].toString();t&&t.indexOf("#")?e=t.split("#"):e[0]=t;let r=[];if((n=n.toString()).indexOf("#")?r=n.split("#"):r[0]=n,parseInt(e[0])===parseInt(r[0])&&(l=idx,i++),e[1]&&r[1]){var d=parseInt(e[0]),s=d+parseInt(e[1]),o=parseInt(r[0]),u=o+parseInt(r[1]);if(d<=o&&o<s&&(l=idx,i++),d<u&&u<=s&&(l=idx,i++),3==i)break}}else if(1==i)break}return l}function checkMax(n,a,l,d,s,e){let o=!0;var u,c,v,h,e=n[a][l].act+parseInt(e);if(n[a][l].max&&e<=n[a][l].max){for(y=0;y<n.length;y++)if(d&&s&&y!=a){let e=[],t=s[y].toString();t&&t.indexOf("#")?e=t.split("#"):e[0]=t;let r=[];(d=d.toString()).indexOf("#")?r=d.split("#"):r[0]=d;let i=!1;if(parseInt(e[0])===parseInt(r[0])?i=!0:e[1]&&r[1]&&(c=(u=parseInt(e[0]))+parseInt(e[1]),h=(v=parseInt(r[0]))+parseInt(r[1]),(u<=v&&v<c||u<h&&h<=c)&&(i=!0)),i)for(z=0;z<n[y].length;z++)if(n[y][z].max&&n[y][z].act>=n[y][z].max||n[y][z].act+n[a][l].act>=n[a][l].max)return!1;o=!0}}else o=!n[a][l].max;return o}function shuffle(e){let t=e.length;for(;0<t;){var r=Math.floor(Math.random()*t);t--;var i=e[t];e[t]=e[r],e[r]=i}return e}function isElementReallyShowed(e){var t=!(jQuery(e).is(":disabled")||jQuery(e).is(":hidden")||"hidden"==jQuery(e).css("visibility"));return jQuery(e).parents().each(function(){t=t&&!jQuery(e).is(":disabled")&&!jQuery(this).is(":hidden")&&!("hidden"==jQuery(this).css("visibility"))}),t}function setTimeset(e,z,F){var M=0,W=-1,q=0;-1==z?(jQuery(document.getElementsByClassName("reservation_time_button"))&&jQuery(document.getElementsByClassName("reservation_time_button")).hide(),jQuery(document.getElementsByClassName("displayReservationObjects"))&&jQuery(document.getElementsByClassName("displayReservationObjects")).hide()):(e?e.id&&e.id.indexOf("-33")&&(q=e.id.substr(e.id.indexOf("-33")+3)):e=document.getElementById("c4g_beginDate_"+z),M=e?e.value:0);var t,L,e=document.getElementById("c4g_duration_"+z);e&&(t=e.value),(M=M&&M.indexOf("/")?(M=M.replace("/","~")).replace("/","~"):M)&&z&&(t=t||-1,L=!(document.getElementsByClassName("c4g__spinner-wrapper")[0].style.display="flex"),fetch("/reservation-api/currentTimeset/"+M+"/"+z+"/"+t+"/"+q).then(e=>e.json()).then(e=>{var t=document.getElementById("c4g_beginTime_"+z+"-00"+getWeekdate(M)),n=t?t.parentElement.getElementsByClassName("c4g__form-radio-group"):document.querySelectorAll(".reservation_time_button .c4g__form-radio-group"),a=[],d=[],s=[],o=[],r=e.times;r.length;document.getElementById("c4g_reservation_id").value&&document.getElementById("c4g_reservation_id").value==e.reservationId||(document.getElementById("c4g_reservation_id").value=e.reservationId),jQuery(document.getElementsByClassName("reservation-id")).show(),jQuery(document.getElementsByClassName("reservation_time_button_"+z))&&jQuery(document.getElementsByClassName("reservation_time_button_"+z)).show();var u,c=0;for(u in r){var y=r[u].time;0<parseInt(r[u].interval)&&(y=r[u].time+"#"+r[u].interval);var v=r[u].interval,h=r[u].objects,m=r[u].name;a[c]=y,d[c]=v,s[c]=h,o[c]=m,c++}var g=document.getElementById("c4g_reservation_object_"+z),p=1,f=0;if(g)for(i=0;i<g.options.length;i++){var _=g.options[i],Q=_.getAttribute("min")?parseInt(_.getAttribute("min")):1;(-1==Q||Q<p)&&(p=Q);_=_.getAttribute("max")?parseInt(_.getAttribute("max")):0;(-1==_||f<_)&&(f=_)}var e=document.getElementById("c4g_desiredCapacity_"+z),b=e?e.value:0;if(n)for(i=0;i<n.length;i++)for(j=0;j<n[i].children.length;j++)if(!n[i].children[j].style||"display:none"!=n[i].children[j].style)for(k=0;k<n[i].children[j].children.length;k++){var I=jQuery(n[i].children[j].children[k]).val();if(I){namefield=n[i].children[j].children[k].getAttribute("name").substr(1);var E,x=checkTimelist(I,a),B=-1,C=0;if(-1!==x)for(l=0;l<s[x].length;l++)-1!=s[x][l].id&&checkMax(s,x,l,I,a,b)&&(C=f?(B=B<s[x][l].act?s[x][l].act:B,s[x][l].percent):B=0);if(0<=B&&(!f||B<f)&&(!b||p<=b&&(!f||b<=f))){let e="",t=!1,r=[];for(l=0;l<s[x].length;l++){var w=s[x][l];if(w.priority&&1==w.priority){t=!0;break}}for(s[x]=shuffle(s[x]),l=0;l<s[x].length;l++){var A=s[x][l];d[x],a[x];t&&A.priority&&1==A.priority?r.splice(0,0,A.id):r.push(A.id);for(var O=0;O<jQuery(g).length;O++)if(g[O].value==A.id){if(t&&A.priority&&1==A.priority){W=A.id;break}if(!t){W=A.id;break}}}for(l=0;l<r.length;l++)0==l?e+=r[l]:e=e+"-"+r[l];jQuery(n[i].children[j].children[k]).attr("data-object",e),jQuery(n[i].children[j].children[k]).attr("disabled",!1),jQuery(n[i].children[j].children[k]).attr("hidden",!1),!o[x]||(E=jQuery(n[i].children[j].children[k]).attr("id"))&&jQuery('label[for="'+E+'"]').text(o[x]),0<C&&jQuery(n[i].children[j].children[k]).addClass("radio_object_hurry_up"),W&&-1!=W||(W=r[0]),q||hideOptions(document.getElementsByClassName("displayReservationObjects"),z,e,F)}else jQuery(n[i].children[j].children[k]).attr("disabled",!0),jQuery(n[i].children[j].children[k]).attr("hidden",!0)}}if(handleBrickConditions(),-1!=z){var N=jQuery(".reservation_time_button_"+z+'.formdata input[type = "hidden"]'),S=!1;if(N)for(i=0;i<N.length;i++)if("none"!=N[i].style.display){S=N[i].value;break}var T=jQuery(".reservation_time_button_"+z+' input[type = "radio"]'),D=[];if(T)for(i=0;i<T.length;i++){var R=T[i];R&&isElementReallyShowed(R)&&(S&&R.value===S?L=R:R.value&&D.push(R))}if(!L&&D&&1===D.length)for(i=0;i<D.length;i++){L=D[i];break}!L||jQuery(L).is(":disabled")||jQuery(L).hasClass("radio_object_disabled")||(jQuery(L).prop("checked",!1),jQuery(L).click())}}).finally(function(){document.getElementsByClassName("c4g__spinner-wrapper")[0].style.display="none"}))}function checkEventFields(){var e=document.getElementById("c4g_reservation_type"),t=e?e.value:-1,r=jQuery(".reservation-event-object select");if(jQuery(".eventdata").hide(),r&&r.is(":visible")){for(jQuery(document.getElementsByClassName("reservation-id")).show(),i=0;i<r.length;i++)if(r[i]){var n,a,l=-1;if(r[i].value&&(l=t.toString()+"-22"+r[i].value.toString(),jQuery(".eventdata_"+l).show(),jQuery(".eventdata_"+l).children().show()),n=document.getElementsByClassName("begindate-event"))for(j=0;j<n.length;j++)-1!=l&&jQuery(n[j]).children(".c4g__form-date-container").children("input").hasClass("c4g_beginDateEvent_"+l)?(jQuery(n[j]).show(),jQuery(n[j]).children("label").show(),jQuery(n[j]).children(".c4g__form-date-container").show(),jQuery(n[j]).children(".c4g__form-date-container").children("input").show()):(jQuery(n[j]).hide(),jQuery(n[j]).children("label").hide(),jQuery(n[j]).children(".c4g__form-date-container").hide(),jQuery(n[j]).children(".c4g__form-date-container").children("input").hide());if(a=jQuery(".reservation_time_event_button"))for(j=0;j<a.length;j++)-1!=l&&jQuery(a[j]).hasClass("reservation_time_event_button_"+l)?(jQuery(a[j]).show(),jQuery(a[j]).children("label").show(),jQuery(a[j]).parent().show(),jQuery(a[j]).parent().parent().show(),jQuery(a[j]).parent().parent().parent().show()):(jQuery(a[j]).hide(),jQuery(a[j]).children("label").hide(),jQuery(a[j]).parent().hide(),jQuery(a[j]).parent().parent().hide(),jQuery(a[j]).parent().parent().parent().hide())}}else{if(n=jQuery(".begindate-event"))for(i=0;i<n.length;i++)jQuery(n[i]).hide();if(a=jQuery(".reservation_time_event_button"))for(i=0;i<a.length;i++)jQuery(a[i]).hide()}}