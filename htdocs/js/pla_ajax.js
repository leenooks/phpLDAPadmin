// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/js/pla_ajax.js,v 1.2 2007/12/15 07:50:31 wurley Exp $

/**
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 * @author Xavier Bruyet
 */

// current request
var http_request = null;
var http_request_success_callback = '';
var http_request_error_callback = '';

// include html into a component
function includeHTML(component, html) {
	if (typeof(component) != 'object' || typeof(html) != 'string') return;
	component.innerHTML = html;

	var scripts = component.getElementsByTagName('script');
	if (!scripts) return;

	// load scripts
	for (var i = 0; i < scripts.length; i++) {
		var scriptclone = document.createElement('script');
		if (scripts[i].attributes.length > 0) {
			for (var j in scripts[i].attributes) {
				if (typeof(scripts[i].attributes[j]) != 'undefined'
				    && typeof(scripts[i].attributes[j].nodeName) != 'undefined'
				    && scripts[i].attributes[j].nodeValue != null
				    && scripts[i].attributes[j].nodeValue != '') {
					    scriptclone.setAttribute(scripts[i].attributes[j].nodeName, scripts[i].attributes[j].nodeValue);
				}
			}
		}
		scriptclone.text = scripts[i].text;
		scripts[i].parentNode.replaceChild(scriptclone, scripts[i]);
		eval(scripts[i].innerHTML);
	}
}

// callback function
function alertHttpRequest() {
	if (http_request && (http_request.readyState == 4)) {
		if (http_request.status == 200) {
			response = http_request.responseText;
			http_request = null;
			//alert(response);
			if (http_request_success_callback) {
				eval(http_request_success_callback + '(response)');
			}
		} else {
			alert('There was a problem with the request.');
			cancelHttpRequest();
		}
	}
}

function cancelHttpRequest() {
	if (http_request) {
		http_request = null;
		if (http_request_error_callback) {
			eval(http_request_error_callback + '()');
		}
	}
}

// resquest
function makeGETRequest(url, parameters, successCallbackFunctionName, errorCallbackFunctionName) {
	makeHttpRequest(url, parameters, 'GET', successCallbackFunctionName, errorCallbackFunctionName);
}

function makePOSTRequest(url, parameters, successCallbackFunctionName, errorCallbackFunctionName) {
	makeHttpRequest(url, parameters, 'POST', successCallbackFunctionName, errorCallbackFunctionName);
}

function makeHttpRequest(url, parameters, meth, successCallbackFunctionName, errorCallbackFunctionName) {
	cancelHttpRequest();

	http_request_success_callback = successCallbackFunctionName;
	http_request_error_callback = errorCallbackFunctionName;

	if (window.XMLHttpRequest) { // Mozilla, Safari,...
		http_request = new XMLHttpRequest();
		if (http_request.overrideMimeType) {
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
		alert('Cannot create XMLHTTP instance.');
		return false;
	}

	http_request.onreadystatechange = window['alertHttpRequest'];
	if (meth == 'GET') url = url + '?' + parameters;
	http_request.open(meth, url, true);

	http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http_request.setRequestHeader("Content-length", parameters.length);
	http_request.setRequestHeader("Connection", "close");

	if (meth == 'GET') parameters = null;
	http_request.send(parameters);
}

