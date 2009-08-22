/**
 * This function will provide a enable/disable toggle for a HTML form field
 */
function form_field_toggle_enable(field,disable,focus,rev) {
	var i;
	var result;
	var element;

	switch (field.type) {
		case 'checkbox':
			if (field.checked)
				result = rev ? false : true;
			else
				result = rev ? true : false;

			for (i=0;i<disable.length;i++) { 
				element = disable[i];
				field.form[element].disabled = result;

				if (focus && (disable[i] == focus))
					field.form[element].focus();
			}
				
		break;

		default:
			alert('field: '+field.type+' disable: '+disable.length+' focus: '+focus);
	}
}

/**
 * Used by the copy form to enable/disable the recursive copy and delete
 */
function copy_field_toggle(field) {
	var disable = ['remove','filter'];
	var clear = 'remove';

	form_field_toggle_enable(field,disable,'filter',true);
	field.form[clear].checked = field.form[clear].checked && field.checked ? true : false;
}

/**
 * Used by the export form to enable/disable compression
 */
function export_field_toggle(field) {
	var disable = ['compress'];
	var clear = 'compress';

	form_field_toggle_enable(field,disable,'compress',true);
	field.form[clear].checked = field.form[clear].checked && field.checked ? true : false;
}
