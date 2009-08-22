var nodeLayer = null;
var sonLayer = null;
var oldstyle = '';
var newstyle = '';
var oldimg = '';
var newimg = '';

function readCollapsedNodes() {
	collapsedNodes = new Array();
	cn = document.cookie.split('collapsedNodes=');
	if (cn.length < 2) return;
	vl = cn[1];
	if (vl.indexOf(';') != -1) {
		vl = vl.split(';');
		vl = vl[0];
	}
	if (vl) {
		collapsed = vl.split('|');
		for (i = 0; i < collapsed.length; i++) {
			collapsedNodes[i] = collapsed[i];
		}
	}
}

function writeCollapsedNodes() {
	document.cookie = 'collapsedNodes=' + collapsedNodes.join('|') + ';path=/';
}

function addCollapsedNode(nodeId) {
	for (i = 0; i < collapsedNodes.length; i++) {
		if (collapsedNodes[i] == nodeId) return;
	}
	collapsedNodes[collapsedNodes.length] = nodeId;
	writeCollapsedNodes();
}

function delCollapsedNode(nodeId) {
	newCollapsedNodes = new Array();
	j = 0;
	for (i = 0; i < collapsedNodes.length; i++) {
		if (collapsedNodes[i] != nodeId) {
			newCollapsedNodes[j++] = collapsedNodes[i];
		}
	}
	collapsedNodes = newCollapsedNodes;
	writeCollapsedNodes();
}

function updateNewStyle() {
	nodeLayer.src = newimg;
	sonLayer.style.display = newstyle;
}

function cancelNewStyle() {
	nodeLayer.src = oldimg;
	sonLayer.style.display = oldstyle;
}

function alertTreeNodeContents(html) {
	if (html.replace(/(^\s*)|(\s*$)/g, '')) {
		includeHTML(sonLayer, html);
	}
	updateNewStyle();
}

function opencloseTreeNode(nodeid,params,imgdir) {
	cancelHttpRequest(); // cancel last request

	// get the node element
	if ((!DOM || Opera56 || Konqueror22) && !IE4) return;
	if (!IE4) {
		sonLayer = document.getElementById('jt' + nodeid + 'son');
		nodeLayer = document.getElementById('jt' + nodeid + 'node');
	} else {
		sonLayer = document.all('jt' + nodeid + 'son');
		nodeLayer = document.all('jt' + nodeid + 'node');
	}
	if (!sonLayer || !nodeLayer) return false;

	// update global variables
	oldstyle = sonLayer.style.display;
	oldimg = nodeLayer.src;
	var action = 0; // (action = 1) => expand ; (action = 2) => collapse
	if (oldimg.indexOf(imgdir+'/tree_expand.png') > -1) {
		newimg = imgdir+'/tree_collapse.png';
		action = 1;
	} else if (oldimg.indexOf(imgdir+'/tree_expand_first.png') > -1) {
		newimg = imgdir+'/tree_collapse_first.png';
		action = 1;
	} else if (oldimg.indexOf(imgdir+'/tree_expand_corner.png') > -1) {
		newimg = imgdir+'/tree_collapse_corner.png';
		action = 1;
	} else if (oldimg.indexOf(imgdir+'/tree_expand_corner_first.png') > -1) {
		newimg = imgdir+'/tree_collapse_corner_first.png';
		action = 1;
	} else if (oldimg.indexOf(imgdir+'/tree_collapse.png') > -1) {
		newimg = imgdir+'/tree_expand.png';
		action = 2;
	} else if (oldimg.indexOf(imgdir+'/tree_collapse_first.png') > -1) {
		newimg = imgdir+'/tree_expand_first.png';
		action = 2;
	} else if (oldimg.indexOf(imgdir+'/tree_collapse_corner.png') > -1) {
		newimg = imgdir+'/tree_expand_corner.png';
		action = 2;
	} else if (oldimg.indexOf(imgdir+'/tree_collapse_corner_first.png') > -1) {
		newimg = imgdir+'/tree_expand_corner_first.png';
		action = 2;
	}
	nodeLayer.src = 'images/ajax-spinner.gif';

	// perform action
	if (action == 2) {
		newstyle = 'none';
		updateNewStyle();
		addCollapsedNode(nodeid);
	} else if (action == 1) {
		newstyle = 'block';
		if (sonLayer.innerHTML == '') {
			makeHttpRequest('cmd.php',params+'&cmd=draw_tree_node&action=1','GET','alertTreeNodeContents','cancelNewStyle');
		} else {
			updateNewStyle();
		}
		delCollapsedNode(nodeid);
	}
	return false;
}

function getDiv(div) {
	if (!IE4) {
		return document.getElementById('aj'+div);
	} else {
		return document.all('aj'+div);
	}
}

function alertAJ(html,div) {
	var pageDiv = getDiv(div);
	if (pageDiv) includeHTML(pageDiv, html);
}

function cancelAJ(div) {
	var pageDiv = getDiv(div);
	if (pageDiv) includeHTML(pageDiv, '');
}

// close initial collapsed nodes
// Cant figure out why, but this runs twice in AJAX mode when using "refresh".
// Causing the collapsedNodes to be incorrect.
readCollapsedNodes();
for (k = 0; k < collapsedNodes.length; k++) {
	opencloseTreeNode(collapsedNodes[k],'#','images/default');
}

var current;

function tree_unhide(whichLayer,old) {
	if (current == null) current = old;

	var oldtree = document.getElementById('ajSID_'+current).style;
	oldtree.display = 'none';

	if (document.getElementById) {
		// this is the way the standards work
		var newtree = document.getElementById(whichLayer).value;
		var newtree_div = document.getElementById('ajSID_'+newtree).style;
		newtree_div.display = 'block';
	}

	current = newtree;
}
