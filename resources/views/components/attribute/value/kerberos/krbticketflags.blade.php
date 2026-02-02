<!-- $o=KrbTicketFlags::class -->
@use(App\Ldap\Entry)

<input type="hidden" name="{{ $o->name_lc }}[{{ $attrtag }}][]" value="{{ $value }}" readonly>

<div class="input-group has-validation mb-1">
	<span @class(['btn-group btn-group-sm','is-invalid'=>($e=$errors->get($o->name_lc.'.'.$dotkey))]) role="group">
		@for($bit=31;$bit>=16;$bit--)
			<input type="checkbox" class="btn-check" id="kerb_tf_{{ $bit }}" name="{{ $o->name_lc }}[{{ Entry::TAG_INTERNAL }}][0][{{ $bit }}]" value="1" @checked(($updated && $o->isset($bit)) || old('krbticketflags.'.Entry::TAG_INTERNAL.'.0.'.$bit,$o->isset($bit) && (! count(old()))))>
			<label @class(['btn','btn-outline-dark'=>(! ($x=$o->isset($bit))),'btn-dark'=>$x,'readonly-checkbox'=>$template && $o->dn]) for="kerb_tf_{{ $bit }}" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ $helper->get($bit,__('Bit').' '.$bit) }}">{{ $x ? 1 : 0 }}</label>
		@endfor
	</span>
</div>

<div class="input-group has-validation mb-3">
	<span @class(['btn-group btn-group-sm','is-invalid'=>($e=$errors->get($o->name_lc.'.'.$dotkey))]) role="group">
		@for($bit=15;$bit>=0;$bit--)
			<input type="checkbox" class="btn-check" id="kerb_tf_{{ $bit }}" name="{{ $o->name_lc }}[{{ Entry::TAG_INTERNAL }}][0][{{ $bit }}]" value="1" @checked(($updated && $o->isset($bit)) || old('krbticketflags.'.Entry::TAG_INTERNAL.'.0.'.$bit,$o->isset($bit) && (! count(old()))))>
			<label @class(['btn','btn-outline-dark'=>(! ($x=$o->isset($bit))),'btn-dark'=>$x,'readonly-checkbox'=>(! $edit) || ($template && $o->dn)]) for="kerb_tf_{{ $bit }}" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ $helper->get($bit,__('Bit').' '.$bit) }}" readonly>{{ $x ? 1 : 0 }}</label>
		@endfor
	</span>

	<x-form.invalid-feedback :errors="$errors->get($o->name_lc.'.'.$dotkey)"/>
</div>

@section($o->name_lc.'-scripts')
	<!-- components.attribute.value.kerberos.krbticketflags -->
	<script type="text/javascript">
		var value = {{ $value ?: 0 }};

		function krbticketflags() {
			function toggleSelect(label) {
				// Initial rendering of selected elements
				if (! label) {
					$('attribute#{{ $o->name_lc }} input').each(function(key,item) {
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
				label.text(input.prop('checked') ? 1 : 0);

				var bit = parseInt(input.prop('id').replace(/^kerb_tf_/,''));
				if (input.prop('checked'))
					value |= (1<<bit);
				else
					value &= ~(1<<bit);

				$('attribute#{{ $o->name_lc }} input').val(value);
			}

			$(document).ready(function() {
				$('form[id^="dn-"] attribute#{{ $o->name_lc }} label')
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
			document.addEventListener('DOMContentLoaded',() => krbticketflags());

		} else {
			krbticketflags();
		}
	</script>
@endsection