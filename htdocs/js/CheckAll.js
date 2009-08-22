function CheckAll(setbgcolor,form,index) {
	htmlform = document.getElementById(form+index);

	for (var i=0;i<htmlform.elements.length;i++) {
		var e = htmlform.elements[i];

		if (e.type == 'checkbox' && e.name != 'allbox') {
			e.checked = htmlform.allbox.checked;

			if (!document.layers && setbgcolor) {
				var tr = document.getElementById('tr_'+e.id);

				if (e.checked) {
					tr.style.backgroundColor='#DDDDFF';
				} else {
					var id = e.id.substr(3);
					tr.style.backgroundColor= id%2 ? '#E0E0E0' : '#F0F0F0';
				}
			}
		}
	}
}
