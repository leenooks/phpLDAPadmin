// PHP Layers Menu 3.2.0-rc (C) 2001-2004 Marco Pratesi - http://www.marcopratesi.it/
// PHPLM v. 4.0.0 (C) 2007 Andreas Kasenides andreas@kasenides.org
// PHPLM v. 4.0.4 (C) 2008 Andreas Kasenides andreas@kasenides.org, Brett Zamir
/**
 * @version 4.0.4
 * @author PHPLM v. 4.0.4 (C) 2008 Andreas Kasenides andreas@kasenides.org
 * @author PHP Layers Menu 3.2.0-rc (C) 2001-2004 Marco Pratesi - http://www.marcopratesi.it/
* @author Brett Zamir
 */

var DOM = (document.getElementById) ? 1 : 0;
var NS4 = (document.layers) ? 1 : 0;

// We need to explicitly detect Konqueror
// because Konqueror 3 sets IE = 1 ... AAAAAAAAAARGHHH!!!
var Konqueror = (navigator.userAgent.indexOf('Konqueror') > -1) ? 1 : 0;
// We need to detect Konqueror 2.2 as it does not handle the window.onresize event
var Konqueror22 = (navigator.userAgent.indexOf('Konqueror 2.2') > -1 || navigator.userAgent.indexOf('Konqueror/2.2') > -1) ? 1 : 0;
var Konqueror30 =
	(
		navigator.userAgent.indexOf('Konqueror 3.0') > -1
		|| navigator.userAgent.indexOf('Konqueror/3.0') > -1
		|| navigator.userAgent.indexOf('Konqueror 3;') > -1
		|| navigator.userAgent.indexOf('Konqueror/3;') > -1
		|| navigator.userAgent.indexOf('Konqueror 3)') > -1
		|| navigator.userAgent.indexOf('Konqueror/3)') > -1
	)
	? 1 : 0;
var Konqueror31 = (navigator.userAgent.indexOf('Konqueror 3.1') > -1 || navigator.userAgent.indexOf('Konqueror/3.1') > -1) ? 1 : 0;
// We need to detect Konqueror 3.2 and 3.3 as they are affected by the see-through effect only for 2 form elements
var Konqueror32 = (navigator.userAgent.indexOf('Konqueror 3.2') > -1 || navigator.userAgent.indexOf('Konqueror/3.2') > -1) ? 1 : 0;
var Konqueror33 = (navigator.userAgent.indexOf('Konqueror 3.3') > -1 || navigator.userAgent.indexOf('Konqueror/3.3') > -1) ? 1 : 0;

var Opera = (navigator.userAgent.indexOf('Opera') > -1) ? 1 : 0;
var Opera5 = (navigator.userAgent.indexOf('Opera 5') > -1 || navigator.userAgent.indexOf('Opera/5') > -1) ? 1 : 0;
var Opera6 = (navigator.userAgent.indexOf('Opera 6') > -1 || navigator.userAgent.indexOf('Opera/6') > -1) ? 1 : 0;
var Opera56 = Opera5 || Opera6;
var Opera7 = (navigator.userAgent.indexOf('Opera 7') > -1 || navigator.userAgent.indexOf('Opera/7') > -1) ? 1 : 0;
var Opera8 = (navigator.userAgent.indexOf('Opera 8') > -1 || navigator.userAgent.indexOf('Opera/8') > -1) ? 1 : 0;
var Opera9 = (navigator.userAgent.indexOf('Opera 9') > -1 || navigator.userAgent.indexOf('Opera/9') > -1) ? 1 : 0;

var IE = (navigator.userAgent.indexOf('MSIE') > -1) ? 1 : 0;
IE = IE && !Opera;
var IE5 = IE && DOM;
var IE4 = (document.all) ? 1 : 0;
IE4 = IE4 && IE && !DOM;
