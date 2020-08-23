function formSubmit(theForm, action, successCB, param, failCB, failParam) {
	if(!theForm) {
		alert("Form not found");
		return;
	}
	var params="";
	var num = theForm.elements.length;
	var formId=null;
	var elemArr = new Array();
	var elemCount = 0;
	for (var i=0; i<num; ++i) {
		var elem = theForm.elements[i];
		if (elem.type == "checkbox") {
			if (elem.checked) { elem.value = "1"; }
			else { elem.value = "0"; }
		}
		if (elem.getAttribute("post")) {
			if (params != "") { params = params + '&'; }
			params = params + elem.name + '=' + escape(encodeURI(elem.value));
			if (elem.name == 'formId') { formId = elem.value; }
			elemArr[elemCount]=elem.name;
			elemCount++;
		}
	}
	for (var i=0; i<elemCount; ++i) { // clear the error messages
		var idname = formId+"_error_"+elemArr[i];
		var elem = document.getElementById(idname);
		if (elem) { elem.innerHTML=""; }
/* this will happen only for checkboxes that are not posted - ignore
		else { alert(idname+" not found"); return false; }
*/
	}

//alert("action="+action+":"+params+":"+formId);
	document.getElementById(formId+'_status').innerHTML='<img src="images/loading.gif" />';
	makeSecureRequest(action, params, function(data, responseCode) {
		if (http_request.readyState == 4) {
			if (http_request.status == 200) {
				processFormResponse(http_request.responseText, successCB, param, failCB, failParam);
			} else {
				alert('There was a problem with the request:'+http_request.status+':');
			}
		}
	});
}

function processFormResponse(data, successCB, param, failCB, failParam) {
//alert("data="+data);
	var lines = data.split("\n");
	var formId, formStatus="", errorCode;
	var errors = new Object();
	for (i=0; i<lines.length; ++i) {
		var idx=lines[i].indexOf("=");
		var name = lines[i].substr(0,idx);
		var value = lines[i].substr(idx+1);
		if (name == "formId") { formId = value; }
		if (name == "status") { formStatus = value; }
		if (name == "errorCode") { errorCode = value; }
		if (name.indexOf("error.") == 0) {
			var fieldName = name.substr(name.indexOf(".")+1);
			errors[fieldName] = value;
		}
	}
	if (!formId) { postLog(2, "Error processing form Response:"+data); alert("An error has occurred and has been reported to our technical team. Please try again later."); }
	else
	if (!errorCode) { alert("errorCode is missing"); }
	else {
		if (formStatus.startsWith("http")) {
			document.getElementById(formId+'_status').innerHTML="Redirecting. Please wait...";
			document.location.href = formStatus;
			return;
		}
		document.getElementById(formId+'_status').innerHTML=formStatus;
		for (fieldName in errors) {
			var elem = document.getElementById(formId+"_error_"+fieldName);
			if (!elem) { alert("Missing "+formId+"_error_"+fieldName+". Please refresh your browser and try again."); }
			else { document.getElementById(formId+"_error_"+fieldName).innerHTML=errors[fieldName]; }
		}
	}

	if (errorCode == 0) {
		if (successCB != null) {
			if (param == null) { successCB(); }
			else { successCB(param); }
		}
	} else {
		if (failCB != null) {
			if (failParam == null) { failCB(); }
			else { failCB(failParam); }
		}
	}
}

function postLog(type, msg) {
return; // todo Unmesh
params = "log="+type;
params = params + '&msg=' + escape(encodeURI(msg));
makeSecureRequest("/postLog.php", params, function(data, responseCode) { });
}

function openWin(url) {
    window.open(url, "_blank", "status=no,top=50,left=200,resizable=yes,scrollbars=1",false);
}

function isEmpty(val) { return (val == undefined || val == ""); }

String.prototype.startsWith = function(str)
{return (this.match("^"+str)==str)}

String.prototype.endsWith = function(str)
{return (this.match(str+"$")==str)}


   function makeSecureRequest(url, parameters, callback) {
   	_makeSecureRequest('POST', url, parameters, callback);
   }

   function _makeSecureRequest(getORpost, url, parameters, callback) {
      http_request = false;
      if (window.XMLHttpRequest) { // Mozilla, Safari,...
         http_request = new XMLHttpRequest();
         if (http_request.overrideMimeType) {
         	// set type accordingly to anticipated content type
            //http_request.overrideMimeType('text/xml');
            http_request.overrideMimeType('text/html');
         }
      } else if (window.ActiveXObject) { // IE
         try {
            http_request = new ActiveXObject("Msxml2.XMLHTTP");
         } catch (e) {
            try {
               http_request = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {}
         }
      }
      if (!http_request) {
         alert('Cannot create XMLHTTP instance');
         return false;
      }
      
	http_request.onreadystatechange = function(data, responseCode) {
		if (http_request.readyState == 4) {
			if (http_request.status == 200) {
				callback(http_request.responseText);
			} else {
				postLog("2", 'There was a problem with the request:'+url+'?'+parameters+'-http_request.status='+http_request.status);
			}
		}
	};
      http_request.open(getORpost, url, true);
      http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      http_request.setRequestHeader("Content-length", parameters.length);
      http_request.setRequestHeader("Connection", "close");
      http_request.setRequestHeader("SM_REQUEST_TYPE", "XMLHttpRequest");
      http_request.send(parameters);
   }

