<div class="modal-header bg-dark text-white">
	<h1 class="modal-title fs-5">
		LDIF for {{ Crypt::decryptString($dn) }}
	</h1>
</div>

<div class="modal-body">
	<div id="entry_export" style="user-select: text;"></div>
</div>

<div class="modal-footer">
	<x-modal.close/>
	<button id="entry_export-download" type="button" class="btn btn-sm btn-primary">@lang('Download')</button>
</div>

<script type="text/javascript">
	function download(filename,text) {
		var element = document.createElement('a');

		element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
		element.setAttribute('download', filename);
		element.style.display = 'none';
		document.body.appendChild(element);

		element.click();
		document.body.removeChild(element);
	}

	$(document).ready(function() {
		$('button[id=entry_export-download]').on('click',function(item) {
			item.preventDefault();

			let ldif = $('#entry_export pre:first');
			download('ldap-export.ldif',ldif.html());
		});
	});
</script>