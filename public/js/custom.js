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
		extensions: ['glyph'],
		autoCollapse: true, // Automatically collapse all siblings, when a node is expanded.
		autoScroll: true, // Automatically scroll nodes into visible area.
		focusOnSelect: true, // Set focus when node is checked by a mouse click
		glyph: {
			preset: 'bootstrap3',	// @todo look at changing this to awesome5
			map: {}
		},
		click: function(event,data) {
			if (data.targetType == 'title') {
				$.ajax({
					url: 'dn',
					method: 'POST',
					data: { key: data.node.data.item },
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
		},
		source: sources,
		lazyLoad: function(event,data) {
			data.result = {
				url: '/api/children',
				data: {key: data.node.data.item,depth: 1}
			};

			expandChildren(data.tree.rootNode);
		},
		keydown: function(event, data){
			switch( $.ui.fancytree.eventToString(data.originalEvent) ) {
				case 'return':
				case 'space':
					data.node.toggleExpanded();
					break;
			}
		}
	});
});