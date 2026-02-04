<!-- $o=PwdReset::class -->

<div class="input-group has-validation">
	@if($edit || ($editable ?? false))
		<div class="form-check form-switch">
			<input type="hidden"
				name="{{ $o->name_lc }}[{{ $attrtag }}][]"
				value="{{ strtoupper($value ?? '') === 'TRUE' ? 'TRUE' : 'FALSE' }}"
				id="{{ $o->name_lc }}_hidden_{{ $index ?? 0 }}">
			<input class="form-check-input pwdreset-toggle"
				type="checkbox"
				role="switch"
				id="{{ $o->name_lc }}_{{ $index ?? 0 }}"
				data-hidden-id="{{ $o->name_lc }}_hidden_{{ $index ?? 0 }}"
				@checked(strtoupper($value ?? '') === 'TRUE')>
			<label class="form-check-label" for="{{ $o->name_lc }}_{{ $index ?? 0 }}">
				@if(strtoupper($value ?? '') === 'TRUE')
					<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> @lang('Password must be changed')</span>
				@else
					<span class="text-success"><i class="fas fa-check-circle"></i> @lang('No password reset required')</span>
				@endif
			</label>
		</div>
		@if(isset($errors) && $errors->any())
			<x-form.invalid-feedback :errors="$errors->get($o->name_lc.'.'.($dotkey ?? '')) ?? []"/>
		@endif
	@else
		@if(strtoupper($value ?? '') === 'TRUE')
			<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> @lang('Yes - Password must be changed')</span>
		@else
			<span class="text-success"><i class="fas fa-check-circle"></i> @lang('No')</span>
		@endif
	@endif
</div>

@section($o->name_lc.'-scripts')
<script type="text/javascript">
	function initPwdResetToggle() {
		$('.pwdreset-toggle').off('change').on('change', function() {
			var hiddenId = $(this).data('hidden-id');
			var newValue = $(this).is(':checked') ? 'TRUE' : 'FALSE';
			$('#' + hiddenId).val(newValue);

			var label = $(this).next('label');
			if ($(this).is(':checked')) {
				label.html('<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{ __("Password must be changed") }}</span>');
			} else {
				label.html('<span class="text-success"><i class="fas fa-check-circle"></i> {{ __("No password reset required") }}</span>');
			}
		});
	}

	if (window.$ === undefined) {
		document.addEventListener('DOMContentLoaded', () => initPwdResetToggle());
	} else {
		initPwdResetToggle();
	}
</script>
@endsection