<div class="modal-header bg-dark text-white">
	<h1 class="modal-title fs-5">
		<strong>@lang('Rename') <strong>{{ $x=Crypt::decryptString($dn) }}</strong>
	</h1>
</div>

<form id="entry-rename-form" method="POST" action="{{ url('entry/rename') }}">
	<div class="modal-body">
		@csrf
		<input type="hidden" name="dn" value="{{ $dn }}">

		<div class="row">
			<div class="col-12">
				<label for="rdn" class="form-label">@lang('New RDN')</label>
				<div class="input-group mb-3">
					<input type="text" id="rdn" name="_rdn_new" class="form-control w-25" placeholder="{{ $rdn=collect(explode(',',$x))->first() }}" value="{{ $rdn }}">
					<span class="input-group-text" id="label">{{ collect(explode(',',$x))->skip(1)->join(',') }}</span>
				</div>
			</div>
		</div>
	</div>

	<div class="modal-footer">
		<x-modal.close/>
		<button id="entry-rename" type="submit" class="btn btn-sm btn-primary" disabled>@lang('Rename')</button>
	</div>
</form>

<script type="text/javascript">
	$(document).ready(function() {
		var rdn = '{{ $rdn }}';

		// Complete the RDN
		$('#rdn').on('input',function(item) {
			rdn = $(this).val();

			$('button[id=entry-rename]').attr('disabled',! rdn.includes('='));
		})
	});
</script>