/**
 * @package phpLDAPadmin
 * @author The phpLDAPadmin development team
 * @author Xavier Bruyet
 */

// current request
var http_div = '';
var http_request = null;
var http_request_success_callback = '';
var http_request_error_callback = '';

function ajSUBMIT(div,obj,display) {
	var pageDiv = getDiv(div);

	window.scrollTo(0,95);

	makeHttpRequest('cmd.php',getParameters(obj.parentNode)+'meth=ajax','POST','alertAJ','cancelAJ',div);

	if (pageDiv)
		includeHTML(pageDiv,'<img src="images/ajax-progress.gif"><br><small>'+display+'...</small>');
	else
		return true;

	return false;
}

function ajDISPLAY(div,urlParameters,display,ns) {
	var pageDiv = getDiv(div);

	if (! ns)
		window.scrollTo(0,95);

	makeHttpRequest('cmd.php',urlParameters+'&meth=ajax','GET','alertAJ','cancelAJ',div);

	if (pageDiv)
		includeHTML(pageDiv,'<img src="images/ajax-progress.gif"><br><small>'+display+'...</small>');
	else
		return true;

	return false;
}

function ajJUMP(url,title,index,prefix) {
	var attr = prefix ? document.getElementById(prefix+index).value : index;

	if (attr)
		url += '&viewvalue='+attr;

	return ajDISPLAY('BODY',url,'Loading '+title);
}

function ajSHOWTHIS(key,except,ctl) {
	select = document.getElementById(key+except);

	if (select.style.display == '')
		return false;

	hideall(key,except,ctl);

	return false;
};

function ajSHOWSCHEMA(type,key,value) {
	select = document.getElementById(type);

	if (value != null) {
		except = value;
		select.value = value;
	} else {
		except = select.value;
	}

	if (! except) {
		showall(key);
	} else {
		objectclass = document.getElementById(key+except);
		objectclass.style.display = '';
		hideall(key,except);
	};

	return false;
};

function hideall(key,except,ctl) {
	items = items();

	for (x in items) {
		if (! isNaN(x) && except != items[x]) {
			item = document.getElementById(key+items[x]);
			item.style.display = 'none';

			if (ctl && (item = document.getElementById(ctl+items[x]))) {
				item.style.background = '#E0E0E0';
			}

		} else if (! isNaN(x) && except == items[x]) {
			item = document.getElementById(key+items[x]);
			item.style.display = '';

			if (ctl && (item = document.getElementById(ctl+items[x]))) {
				item.style.background = '#F0F0F0';
			}
		}
	}
}

function showall(key) {
	items = items();

	for (x in items) {
		if (! isNaN(x)) {
			item = document.getElementById(key+items[x]);
			item.style.display = '';
		}
	}
}

// include html into a component
function includeHTML(component,html) {
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
					scriptclone.setAttribute(scripts[i].attributes[j].nodeName,scripts[i].attributes[j].nodeValue);
				}
			}
		}
		scriptclone.text = scripts[i].text;
		scripts[i].parentNode.replaceChild(scriptclone,scripts[i]);
		eval(scripts[i].innerHTML);
	}
}

// callback function
function alertHttpRequest() {
	if (http_request && (http_request.readyState == 4)) {
		if (http_request.status == 200 || http_request.status == 401) {
			response = http_request.responseText;
			http_request = null;
			//alert(response);
			if (http_request_success_callback) {
				eval(http_request_success_callback + '(response,http_div)');
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
			eval(http_request_error_callback + '(http_div)');
		}
	}
}

function makeHttpRequest(url,parameters,meth,successCallbackFunctionName,errorCallbackFunctionName,div) {
	cancelHttpRequest(div);

	http_request_success_callback = successCallbackFunctionName;
	http_request_error_callback = errorCallbackFunctionName;
	http_div = div;

	if (window.XMLHttpRequest) { // Mozilla, Safari,...
		http_request = new XMLHttpRequest();
		if (http_request.overrideMimeType) {
			http_request.overrideMimeType('text/html');
		}

	} else if (window.ActiveXObject) { // IE
		try {
			http_request = new ActiveXObject('Msxml2.XMLHTTP');
		} catch (e) {
			try {
				http_request = new ActiveXObject('Microsoft.XMLHTTP');
			} catch (e) {}
		}
	}

	if (!http_request) {
		alert('Cannot create XMLHTTP instance.');
		return false;
	}

	http_request.onreadystatechange = window['alertHttpRequest'];
	if (meth == 'GET') url = url + '?' + parameters;
	http_request.open(meth,url,true);

	http_request.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	http_request.setRequestHeader('Content-length',parameters.length);
	http_request.setRequestHeader('Connection','close');

	if (meth == 'GET') parameters = null;
	http_request.send(parameters);
}

function getParameters(obj) {
	var elements = ['input','select','textarea'];
	var getstr = '';

	for (var j in elements) {
		for (i=0; i<obj.getElementsByTagName(elements[j]).length; i++) {
			// Ignore submit variables
			if (obj.getElementsByTagName(elements[j])[i].type == 'submit') {

			} else if (obj.getElementsByTagName(elements[j])[i].type == 'text') {
				getstr += obj.getElementsByTagName(elements[j])[i].name + '=' + encodeURIComponent(obj.getElementsByTagName(elements[j])[i].value) + '&';

			} else if (obj.getElementsByTagName(elements[j])[i].type == 'checkbox') {
				if (obj.getElementsByTagName(elements[j])[i].checked) {
					getstr += obj.getElementsByTagName(elements[j])[i].name + '=' + encodeURIComponent(obj.getElementsByTagName(elements[j])[i].value) + '&';
				} else {
					getstr += obj.getElementsByTagName(elements[j])[i].name + '=&';
				}

			} else if (obj.getElementsByTagName(elements[j])[i].type == 'radio') {
				if (obj.getElementsByTagName(elements[j])[i].checked) {
					getstr += obj.getElementsByTagName(elements[j])[i].name + '=' + encodeURIComponent(obj.getElementsByTagName(elements[j])[i].value) + '&';
				}

			} else if (obj.getElementsByTagName(elements[j])[i].tagName == 'SELECT') {
				var sel = obj.getElementsByTagName(elements[j])[i];
				getstr += sel.name + '=' + encodeURIComponent(sel.options[sel.selectedIndex].value) + '&';

			} else if (obj.getElementsByTagName(elements[j])[i].tagName == 'INPUT') {
				getstr += obj.getElementsByTagName(elements[j])[i].name + '=' + encodeURIComponent(obj.getElementsByTagName(elements[j])[i].value) + '&';

			} else if (obj.getElementsByTagName(elements[j])[i].tagName == 'TEXTAREA') {
				getstr += obj.getElementsByTagName(elements[j])[i].name + '=' + encodeURIComponent(obj.getElementsByTagName(elements[j])[i].value) + '&';

			} else {
				alert('UNTRAPPED FORM tag:'+elements[j]+', n: '+obj.getElementsByTagName(elements[j])[i].tagName+', t:'+obj.getElementsByTagName(elements[j])[i].type);
			}
		}
	}

	return getstr;
}
