@use(App\Ldap\Entry)

<!-- $o=SambaAcctFlags::class -->
<div class="input-group has-validation mb-3">
	<span @class(['btn-group btn-group-sm','is-invalid'=>($e=$errors->get($o->name_lc.'.'.$dotkey))]) role="group">
		@foreach($o::values as $k => $v)
			<input type="checkbox" class="btn-check" id="samba_af_{{ $k }}" name="{{ $o->name_lc }}[{{ Entry::TAG_INTERNAL }}][0][{{ $k }}]" value="1" @checked(($updated && $o->isset($k)) || old('sambaacctflags.'.Entry::TAG_INTERNAL.'.0.'.$k,$o->isset($k) && (! count(old()))))>
			<label @class(['btn','btn-outline-dark'=>(! ($x=$o->isset($k))),'btn-dark'=>$x,'readonly-checkbox'=>$template && $o->dn]) for="samba_af_{{ $k }}" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __($v) }}">{{ $k }}</label>
		@endforeach
	</span>

	<x-form.invalid-feedback :errors="$e"/>
</div>

@section($o->name_lc.'-scripts')
	<script type="text/javascript">
		function sambaaccctflags() {
			function toggleSelect(label) {
				// Initial rendering of selected elements
				if (! label) {
					$('attribute#sambaacctflags input').each(function(key,item) {
						if ({{ $updated ? 'true' : 'false' }} && $(this).next('label').hasClass('btn-dark') && item.checked)
							$(this).next('label').toggleClass('btn-success').toggleClass('btn-dark');

						else if ($(this).next('label').hasClass('btn-outline-dark') && item.checked)
							$(this).next('label').toggleClass('btn-success').toggleClass('btn-outline-dark')

						else if ($(this).next('label').hasClass('btn-dark') && (! item.checked))
							$(this).next('label').toggleClass('btn-outline-success').toggleClass('btn-dark');
					})

					return;
				}

				if (label.hasClass('btn-outline-dark') || label.hasClass('btn-success'))
					label.toggleClass('btn-success').toggleClass('btn-outline-dark');
				else
					label.toggleClass('btn-outline-success').toggleClass('btn-dark');

				var input = label.prev('input');
				input.prop('checked',! input.prop('checked'));
			}

			$(document).ready(function() {
				$('form[id^="dn-"] attribute#sambaacctflags label')
					.off('click')
					.on('click',function(item) {
						item.preventDefault();
						toggleSelect($(this));
					});

				toggleSelect();
			});
		}

		// When returning to a Entry after an update, jquery hasnt loaded yet, so make sure we defer this to after the page has run
		if (window.$ === undefined) {
			document.addEventListener('DOMContentLoaded',() => sambaaccctflags());

		} else {
			sambaaccctflags();
		}
	</script>
@endsection