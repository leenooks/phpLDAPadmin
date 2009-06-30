var current;

function tree_unhide(whichLayer,old) {
	if (current == null) current = old;
	var oldtree = document.getElementById('SID_'+current).style;
	oldtree.display = 'none';

	if (document.getElementById) {
		// this is the way the standards work
		var newtree = document.getElementById(whichLayer).value;
		var newtree_div = document.getElementById('SID_'+newtree).style;
		newtree_div.display = 'block';

/*
	} else if (document.all) {
		// this is the way old msie versions work
		var style2 = document.all[whichLayer].style;
		style2.display = style2.display ? '':'block';

	} else if (document.layers) {
		// this is the way nn4 works
		var style2 = document.layers[whichLayer].style;
		style2.display = style2.display ? '':'block';
*/
	}
	current = newtree;
}
