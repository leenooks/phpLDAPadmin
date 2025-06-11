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

function getNode(item) {
	$.ajax({
		url: '/frame',
		method: 'POST',
		data: { key: item },
		dataType: 'html',
		beforeSend: function() {
			content = $('.main-content')
				.contents();

			$('.main-content')
				.empty()
				.append('<div class="fa-3x"><i class="fas fa-spinner fa-pulse"></i></div>');
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
				location.replace('/#'+item);
				break;
			case 419:	// Session Expired
				location.replace('/#'+item);
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
		sources = { url: '/ajax/bases' };
	}

	// Attach the fancytree widget to an existing <div id="tree"> element
	// and pass the tree options as an argument to the fancytree() function:
	$('#tree').fancytree({
		clickFolderMode: 3,
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
			if (data.targetType === 'title')
				getNode(data.node.data.item);
		},
		source: sources,
		lazyLoad: function(event,data) {
			data.result = {
				url: '/ajax/children',
				data: {key: data.node.data.item,depth: 1}
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
		},
		restore: function(event,data) {
			//getNode(data.tree.getActiveNode().data.item);
		}
	});
});