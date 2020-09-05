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
	// Attach the fancytree widget to an existing <div id="tree"> element
	// and pass the tree options as an argument to the fancytree() function:
	$('#tree').fancytree({
		clickFolderMode: 3,
		extensions: ['glyph'],
		autoCollapse: true, // Automatically collapse all siblings, when a node is expanded.
		autoScroll: true, // Automatically scroll nodes into visible area.
		focusOnSelect: true, // Set focus when node is checked by a mouse click
		click: function(event, data) {
			if (data.targetType == 'title')
				return false;
		},
		init: function(event, data) {
			expandChildren(data.tree.rootNode);
		},
		source: {
			url: "api/bases"
		},
		lazyLoad: function(event,data) {
			data.result = {
				url: "api/query",
				data: {key: data.node.data.item,depth: 1}
			};

			expandChildren(data.tree.rootNode);
		},
		keydown: function(event, data){
			switch( $.ui.fancytree.eventToString(data.originalEvent) ) {
				case "return":
				case "space":
					data.node.toggleExpanded();
					break;
			}
		}
	});
});
