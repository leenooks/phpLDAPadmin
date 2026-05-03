@use(App\Ldap\Entry)

<!-- $o=Boolean::class -->
<div class="input-group has-validation mb-3">
	<span @class(['btn-group btn-group-sm','is-invalid'=>($e=$errors->get($o->name_lc.'.'.$dotkey))]) role="group">
		<input type="radio" class="btn-check" id="{{ $o->name_lc }}_true" name="{{ $o->name_lc }}[{{ $attrtag }}][{{ $index }}]" value="TRUE" @checked(($x=(strtolower(old($o->name_lc.'.'.$attrtag.'.'.$index,$value)) === 'true')))>
		<label @class(['btn','btn-outline-dark'=>(! $x),'btn-dark'=>$x,'readonly-checkbox'=>(! $edit) || ($template && $o->dn)]) for="{{ $o->name_lc }}_true" data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('TRUE')">T</label>

		<input type="radio" class="btn-check" id="{{ $o->name_lc }}_false" name="{{ $o->name_lc }}[{{ $attrtag }}][{{ $index }}]" value="FALSE" @checked(! $x)>
		<label @class(['btn','btn-outline-dark'=>$x,'btn-dark'=>(! $x),'readonly-checkbox'=>(! $edit) || ($template && $o->dn)]) for="{{ $o->name_lc }}_false" data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('FALSE')">F</label>
	</span>

	<x-form.invalid-feedback :errors="$e"/>
</div>

@section($o->name_lc.'-scripts')
<!-- components.attribute.value.equality.boolean -->
<script type="text/javascript">
	if (typeof boolean !== 'function') {
		function boolean() {
			function boolToggleSelect(label) {
				// Initial rendering of selected elements
				if ({{ $updated ? 'true' : 'false' }} || ((! label) && {{ (old($o->name_lc.'.'.$attrtag.'.'.$index,$o->render_item_old($dotkey)) !== $o->render_item_old($dotkey)) ? 'true' : 'false' }})) {
					$('attribute#{{ $o->name_lc }} input').each(function(key,item) {
						var label = $(this).next('label');

						if ({{ $updated ? 'true' : 'false' }} && label.hasClass('btn-dark') && item.checked)
							label.toggleClass('btn-success').toggleClass('btn-dark');

						else if (label.hasClass('btn-outline-dark') && (! item.checked))
							label.toggleClass('btn-outline-success').toggleClass('btn-outline-dark')

						else if (label.hasClass('btn-dark') && item.checked)
							label.toggleClass('btn-success').toggleClass('btn-dark');
					});

					return;
				}

				if (! label)
					return;

				var input;
				var changed = false;
				['true','false'].forEach((item) => {
					input = label.find('input#{{ $o->name_lc }}_'+item).next('label:not(.readonly-checkbox)');

					if (! input.length)
						return;

					changed = true;

					if (input.hasClass('btn-outline-dark') || input.hasClass('btn-success'))
						input.toggleClass('btn-success').toggleClass('btn-outline-dark');
					else
						input.toggleClass('btn-outline-success').toggleClass('btn-dark');
				});

				if (changed)
					label.find('input:not(:checked)').prop('checked',true);
			}

			$(document).ready(function() {
				$('form[id^="dn-"] attribute#{{ $o->name_lc }} span.btn-group:not(.readonly-checkbox)')
					.on('click',function(item) {
						item.preventDefault();
						boolToggleSelect($(this));
					});

				boolToggleSelect();
			});
		}

		// When returning to a Entry after an update, jquery hasnt loaded yet, so make sure we defer this to after the page has run
		if (window.$ === undefined) {
			document.addEventListener('DOMContentLoaded',() => boolean());

		} else {
			boolean();
		}
	}
</script>
@endsection