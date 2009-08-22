function pla_getComponentById(id) {
	return document.getElementById(id);
}

function pla_getComponentsByName(name) {
	return document.getElementsByName(name);
}

function pla_getComponentValue(component) {
	if (component.type == "checkbox") {
		if (component.checked) return component.value;
	} else if (component.type == "select-one") {
		if (component.selectedIndex >= 0) return component.options[component.selectedIndex].value;
	} else if (component.type == "select-multiple") {
		if (component.selectedIndex >= 0) return component.options[component.selectedIndex].value;
	} else if (component.type == undefined) { // option
		if (component.selected) return component.value;
	} else {
		return component.value;
	}
	return "";
}

function pla_setComponentValue(component,value) {
	if (component.type == "checkbox") {
		if (component.value == value) component.checked = true;
		else component.checked = false;
	} else if (component.type == "select-one") {
		for (var i = 0; i < component.options.length; i++) {
			if (component.options[i].value == value) component.options[i].selected = true;
		}
	} else if (component.type == "select-multiple") {
		for (var i = 0; i < component.options.length; i++) {
			if (component.options[i].value == value) component.options[i].selected = true;
		}
	} else if (component.type == undefined) { // option
		if (component.value == value) component.selected = true;
		else component.selected = false;
	} else {
		component.value = value;
	}
}

function getAttributeComponents(prefix,name) {
	var components = new Array();
	var i = 0;
	var j = 0;
	var c = pla_getComponentsByName(prefix + "_values[" + name + "][" + j + "]");
	while (c && (c.length > 0)) {
		for (var k = 0; k < c.length; k++) {
			components[i++] = c[k];
		}
		++j;
		c = pla_getComponentsByName(prefix + "_values[" + name + "][" + j + "]");
	}
	c = pla_getComponentsByName(prefix + "_values[" + name + "][]");
	if (c && (c.length > 0)) {
		for (var k = 0; k < c.length; k++) {
			components[i++] = c[k];
		}
	}
	return components;
}

function getAttributeValues(prefix,name) {
	var components = getAttributeComponents(prefix,name);
	var values = new Array();
	for (var k = 0; k < components.length; k++) {
		var val = pla_getComponentValue(components[k]);
		if (val) values[values.length] = val;
	}
	return values;
}

function submitForm(form) {
	for (var i = 0; i < form.elements.length; i++) {
		form.elements[i].blur();
	}
	return validateForm(true);
}

function alertError(err,silence) {
	if (!silence) alert(err);
}
