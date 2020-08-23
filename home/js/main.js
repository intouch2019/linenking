function makeSecureRequest(url, parameters, callback) {
	_makeSecureRequest('POST', url, parameters, callback);
}

function _makeSecureRequest(getORpost, url, parameters, callback) {
	http_request = false;
	
	// First create an object of XMLHttpRequest.
	if (window.XMLHttpRequest) { // Mozilla, Safari,...
		http_request = new XMLHttpRequest();
		if (http_request.overrideMimeType) {
			// set type accordingly to anticipated content type
			// http_request.overrideMimeType('text/xml');
			http_request.overrideMimeType('text/html');
		}
	} else if (window.ActiveXObject) { // IE
		try {
			http_request = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				http_request = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {
			}
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
				alert('There was a problem with the request:' + url
						+ '?' + parameters + '-http_request.status='
						+ http_request.status);
			}
		}
	};
	
	http_request.open(getORpost, url, true);
	http_request.setRequestHeader("Content-type",
			"application/x-www-form-urlencoded");
	http_request.setRequestHeader("Content-length", parameters.length);
	http_request.setRequestHeader("Connection", "close");
	http_request.setRequestHeader("SM_REQUEST_TYPE", "XMLHttpRequest");
	http_request.send(parameters);
}

Array.prototype.contains = function(obj) {
  var i = this.length;
  while (i--) {
    if (this[i] === obj) {
      return true;
    }
  }
  return false;
}

// Return new array with duplicate values removed
Array.prototype.unique =
	function() {
		var a = [];
		var l = this.length;
		for(var i=0; i<l; i++) {
		for(var j=i+1; j<l; j++) {
		// If this[i] is found later in the array
		if (this[i] === this[j])
			j = ++i;
		}
		a.push(this[i]);
		}
		return a;
	};
