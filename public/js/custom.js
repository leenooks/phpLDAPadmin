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
		url: 'dn',
		method: 'POST',
		data: { key: item },
		dataType: 'html',
		beforeSend: function() {
			content = $('.main-content').contents();
			$('.main-content').empty().append('<div class="fa-3x"><i class="fas fa-spinner fa-pulse"></i></div>');
		}

	}).done(function(html) {
		$('.main-content').empty().append(html);

	}).fail(function(item) {
		switch(item.status) {
			case 404:
				$('.main-content').empty().append(item.responseText);
				break;
			case 419:
				alert('Session has expired, reloading the page and try again...');
				location.reload();
				break;
			case 500:
				$('.main-content').empty().append(item.responseText);
				break;
			default:
				alert(item.status+': Well that didnt work?');
		}
	});
}

$(document).ready(function() {
	// If our bases have been set, we'll render them directly
	if (typeof basedn !== 'undefined') {
		sources = basedn;
	} else {
		sources = { url: 'api/bases' };
	}

	// Attach the fancytree widget to an existing <div id="tree"> element
	// and pass the tree options as an argument to the fancytree() function:
	$('#tree').fancytree({
		clickFolderMode: 3,
		extensions: ['glyph','persist'],
		autoCollapse: true, // Automatically collapse all siblings, when a node is expanded.
		autoScroll: true, // Automatically scroll nodes into visible area.
		focusOnSelect: true, // Set focus when node is checked by a mouse click
		glyph: {
			preset: 'bootstrap3',	// @todo look at changing this to awesome5
			map: {}
		},
		persist: {
			// Available options with their default:
			cookieDelimiter: '~',    // character used to join key strings
			cookiePrefix: undefined, // 'fancytree-<treeId>-' by default
			cookie: { // settings passed to jquery.cookie plugin
				raw: false,
				expires: '',
				path: '',
				domain: '',
				secure: false
			},
			expandLazy: true, // true: recursively expand and load lazy nodes
			expandOpts: undefined, // optional `opts` argument passed to setExpanded()
			overrideSource: true,  // true: cookie takes precedence over `source` data attributes.
			store: 'auto',     // 'cookie': use cookie, 'local': use localStore, 'session': use sessionStore
			types: 'active expanded focus selected'  // which status types to store
		},
		click: function(event,data) {
			if (data.targetType == 'title') {
				getNode(data.node.data.item);
			}
		},
		source: sources,
		lazyLoad: function(event,data) {
			data.result = {
				url: '/api/children',
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