function expandChildren(node) {
	if (node.data.autoExpand && !node.isExpanded()) {
		node.setExpanded(true);
	}

	if (node.children && node.children.length > 0) {
		try {
			node.children.forEach(expandChildren);
		} catch (error) {
		}
	}
}

// Render a sub page via an ajax method
function get_frame(item) {
	$.ajax({
		url: web_base+'/frame',
		method: 'POST',
		data: { _key: item },
		dataType: 'html',
		beforeSend: function() {
			// In case we want to redirect back to the original page
			content = $('.main-content').contents();
			before_send_spinner($('.main-content').empty());
		}

	}).done(function(html) {
		$('.main-content')
			.empty()
			.append(html);

	}).fail(function(e) {
		switch(e.status) {
			case 404:
				$('.main-content').empty().append(e.responseText);
				break;
			case 409:	// Not in root
				// There is an unusual problem, that location.replace() is not being replaced, when, for example:
				// * When running in a subpath
				// * The rendered URL has a slash on the end
				// * The rendered page is a form, etc /entry/add, and the user clicks on the tree
				workaround = location.href;
				location.assign(web_base+'/#'+item);

				if ((web_base_path === '/') && (location.pathname === '/'))
					location.reload();

				else if ((! location.href.match('/\/$/')) && (workaround !== location.href))
					location.reload();

				break;
			case 419:	// Session Expired
				workaround = location.href;
				location.replace(web_base+'/#'+item);

				if ((! location.href.match('/\/$/')) && (workaround !== location.href))
					location.reload();

				break;
			case 500:
			case 555:	// Missing Method
				$('.main-content').empty().append(e.responseText);
				break;

			default:
				alert('Well that didnt work? Code ['+e.status+']');
		}
	});
}

// Handle our error message for .ajax() calls
let ajax_error = function(e) {
	alert('That didnt work? Please try again.... ('+e.status+')');
};

// Render a spinner when doing an ajax call
function before_send_spinner(that) {
	that.append('<span class="ps-3"><i class="fas fa-2x fa-spinner fa-spin-pulse"></i></span>');
}

// Find all values of an attribute in the form
function attribute_values(attr,container='attribute',input='input') {
	return $(container+'#'+attr+' '+(input === 'input' ? 'input[type=text]:not(.no-edit)' : input))
		.map((index,element)=>$(element).val())
		.toArray();
}

// Rendered OC values
function oc_rendered() {
	return $('attribute#objectclass input')
		.map((key,item)=>item.value)
		.toArray();
}

// This function will update values that are altered from a modal, and return with any new values
function update_from_modal(attr,modal_data) {
	// Existing Values
	var existing = attribute_values(attr);
	var addition = [];

	// Add New Values
	modal_data.forEach(function (item) {
		if (existing.indexOf(item) === -1) {
			// Add attribute to the page
			var active = $('form[id^="dn-"] attribute#'+attr)
				.find('.tab-content .tab-pane.active div.input-group:last');

			var clone = active
				.clone();

			active.after(clone);

			clone.find('input')
				.attr('value',item)
				.addClass('border-focus')

			addition.push(item);
		}
	});

	// If all the entries are removed, we need to leave a single input box
	if (! modal_data.length) {
		var clear = existing.shift();

		$('form[id^="dn-"] attribute#'+attr+' input[value="'+clear+'"]')
			.attr('value',"")
			.next('.input-group-end')
			.empty();
	}

	// Remove Values
	existing.forEach(function(item) {
		if (modal_data.indexOf(item) === -1) {
			$('form[id^="dn-"] attribute#'+attr+' input[value="'+item+'"]')
				.closest('div.input-group')
				.remove();

			// For the extra values that are not shown
			$('form[id^="dn-"] attribute#'+attr+' input[value="'+item+'"]')
				.remove();
		}
	});

	// For new entries, there is a blank input box, we'll clear that too, except the first one
	if (existing.length > 1)
		$('form[id^="dn-"] attribute#'+attr+' input[value=""]')
			.closest('div.input-group')
			.empty();

	// If we have a button, update it
	var button = $('button#extra-'+attr);
	if (button.length)
		button.html(button.html().replace(/\d+/,$('form[id^="dn-"] attribute#'+attr+' input.d-none').length));

	// We return with new additions
	return addition;
}