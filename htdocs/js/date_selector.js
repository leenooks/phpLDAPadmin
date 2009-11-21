function dateSelector(id) {
	var el = document.getElementById('new_values_'+id);
	var format = gettype(el.id);
	var epoch;
	var parse = false;

	var cal = new Calendar(0, null, onSelect, onClose);

	if (defaults['f_time_'+id]) {
		cal.showsTime = true;
	} else {
		cal.showsTime = false;
	}

	cal.weekNumbers = true;
	cal.showsOtherMonths = true;
	cal.create();

	// convert to milliseconds (Epoch is usually expressed in seconds, but Javascript uses Milliseconds)
	switch (format) {
		case '%es' :	epoch = el.value * 86400 * 1000;
				format = '%s';
				parse = true;
			break;
		case '%s' :	epoch = el.value * 1000;
				parse = true;
			break;
	}

	// Convert the value to the date so that the calendar will display it 
	if (parse) {
		var dDate = new Date();
		dDate.setTime(epoch);
		cal.setDateFormat('%a, %d %b %Y');  // set the specified date format
		cal.parseDate(dDate.toString());    // try to parse the text in field
		cal.setDateFormat(format);  // set the specified date format
	} else {
		cal.setDateFormat(format);  // set the specified date format
		cal.parseDate(el.value);    // try to parse the text in field
	}

	cal.sel = el;                       // inform it what input field we use
	cal.showAtElement(el, 'BR');        // show the calendar
}

function onSelect(calendar,date) {
	switch (gettype(calendar.sel.id)) {
		case '%es' :	date = Math.round(date / 86400);
			break;
	}

	calendar.sel.value = date;
	if (calendar.dateClicked)
		onClose(calendar);
}

function onClose(calendar,date) {
	calendar.hide();
}

function gettype(attr) {
	if (typeof defaults == "undefined") {
		return '%s';
	}

	if (typeof defaults[attr] == "undefined") {
		if (typeof default_date_format == "undefined") {
			return '%s';
		} else {
			return default_date_format;
		}
	} else {
		return defaults[attr];
	}
}
