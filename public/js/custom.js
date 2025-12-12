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

$(document).ready(function() {
	// If our bases have been set, we'll render them directly
	if (typeof basedn !== 'undefined') {
		sources = basedn;
	} else {
		sources = { method: 'POST', url: web_base+'/ajax/bases' };
	}

	// Attach the fancytree widget to an existing <div id="tree"> element
	// and pass the tree options as an argument to the fancytree() function:
	$('#tree').fancytree({
		clickFolderMode: 3,	// 1:activate, 2:expand, 3:activate and expand, 4:activate (dblclick expands)
		extensions: ['persist'],
		autoCollapse: true, // Automatically collapse all siblings, when a node is expanded.
		autoScroll: true, // Automatically scroll nodes into visible area.
		focusOnSelect: true, // Set focus when node is checked by a mouse click
		persist: {
			// Available options with their default:
			cookieDelimiter: '~',    // character used to join key strings
			cookiePrefix: 'pla-<treeId>-', // 'fancytree-<treeId>-' by default
			cookie: { // settings passed to jquery.cookie plugin
				raw: false,
				expires: '',
				path: '',
				domain: '',
				secure: false
			},
			expandLazy: true, // true: recursively expand and load lazy nodes
			expandOpts: undefined, // optional `opts` argument passed to setExpanded()
			fireActivate: false, //
			overrideSource: true,  // true: cookie takes precedence over `source` data attributes.
			store: 'auto',     // 'cookie': use cookie, 'local': use localStore, 'session': use sessionStore
			types: 'active expanded focus selected'  // which status types to store
		},
		click: function(event,data) {
			if (data.targetType === 'title' && data.node.data.item)
				get_frame(data.node.data.item);
		},
		source: sources,
		lazyLoad: function(event,data) {
			data.result = {
				method: 'POST',
				url: web_base+'/ajax/children',
				data: {_key: data.node.data.item,create: true},
				error: function(e) {
					if (e.status === 419) {	// Session Expired
						window.location.reload();
					}
				}
			};

			expandChildren(data.tree.rootNode);
		},
		keydown: function(event,data){
			switch( $.ui.fancytree.eventToString(data.originalEvent) ) {
				case 'return':
				case 'space':
					data.node.toggleExpanded();
					break;
			}
		}
	});
});

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
		.toArray()
}

// This function will update values that are altered from a modal
function update_from_modal(attr,modal_data) {
	// Existing Values
	var existing = attribute_values(attr);
	var addition = [];

	// Add New Values
	modal_data.forEach(function (item) {
		if (existing.indexOf(item) === -1) {
			// Add attribute to the page
			var active = $('form#dn-edit attribute#'+attr)
				.find('.tab-content .tab-pane.active');

			var clone = active.find('div.input-group:last')
				.clone()
				.appendTo(active);

			clone.find('input')
				.attr('value',item)
				.addClass('border-focus')

			addition.push(item);
		}
	});

	// Remove Values
	existing.forEach(function(item) {
		if (modal_data.indexOf(item) === -1) {
			$('form#dn-edit attribute#'+attr+' input[value="'+item+'"]')
				.closest('div.input-group')
				.empty();
		}
	});

	// For new entries, there is a blank input box, we'll clear that too
	$('form#dn-edit attribute#'+attr+' input[value=""]')
		.closest('div.input-group')
		.empty();

	return addition;
}

/* Sidebar resize */
var aside = $('aside.app-sidebar');

$('aside .draghandle').on('mousedown',function(event) {
	// Ignore if closed
	if ($('.close-sidebar-btn').hasClass('is-active'))
		return;

	event.preventDefault();

	window.addEventListener('mousemove',Resize,false);
	window.addEventListener('mouseup',stopResize,false);
})

$('.close-sidebar-btn:not(is-active)').on('click',function(event) {
	aside.css('width','');
	$('.app-header').css('margin-left','');
	$('main.app-main__outer').css('padding-left','');
})

function Resize(e) {
	var mouseX = e.clientX - aside.offset().left;

	if (mouseX < 250) {
		aside.css('width','');
		$('.app-header').css('margin-left','');
		$('main.app-main__outer').css('padding-left','');

	} else {
		aside.css('width',mouseX+'px');
		$('.app-header').css('margin-left',mouseX+'px');
		$('main.app-main__outer').css('padding-left',mouseX+'px');
	}
}

function stopResize() {
	window.removeEventListener('mousemove',Resize,false);
	window.removeEventListener('mouseup',stopResize,false);
}