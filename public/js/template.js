/* JavaScript template engine abstraction layer */
/* Currently implemented for jquery */

// Get a value from an attribute
function get_attribute(attribute,start,end) {
	var val = $('#'+attribute).find('input').val();

	return ((start !== undefined) && (end !== undefined))
		? val.substring(start,end)
		: val;
}

// Put a value to an attribute
function put_attribute(attribute,result) {
	// Get the value, if the value hasnt changed, then we dont need to do anything
	if (get_attribute(attribute) === result)
		return;

	$('#'+attribute)
		.find('input')
		.val(result)
		.trigger('change');
}