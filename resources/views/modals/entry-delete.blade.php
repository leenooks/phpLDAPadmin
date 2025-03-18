<div class="modal-header bg-danger text-white">
	<h1 class="modal-title fs-5">
		<i class="fas fa-fw fa-exclamation-triangle"></i> <strong>@lang('WARNING')</strong>: @lang('Delete') <strong>{{ Crypt::decryptString($dn) }}</strong>
	</h1>
	<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
	@lang('Deleting this DN will permanently delete it from your LDAP server.')
</div>

<div class="modal-footer">
	<x-modal.close/>
	<button id="entry-delete" type="button" class="btn btn-sm btn-danger">@lang('Delete')</button>
</div>

<form id="entry-delete-form" method="POST" action="{{ url('entry/delete') }}">
	@csrf

	<input type="hidden" name="dn" value="{{ $dn }}">
</form>

<script type="text/javascript">
	$(document).ready(function() {
		$('button[id=entry-delete]').on('click',function(item) {
			$('form#entry-delete-form').submit();
		});
	});
</script>