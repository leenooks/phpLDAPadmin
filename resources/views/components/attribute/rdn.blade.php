<!-- $o=RDN::class -->
@if($edit)
	<div class="input-group has-validation mb-3">
		<select @class(['form-select','is-invalid'=>$errors->get('_rdn')]) id="rdn" name="_rdn">
			<option value=""></option>

			@foreach($o->attrs->map(fn($item)=>['id'=>$item,'value'=>$item]) as $option)
				@continue(! Arr::get($option,'value'))
				<option
					value="{{ strtolower(Arr::get($option,'id')) }}"
					@selected(Arr::get($option,'id') == old('_rdn',$o->rdn_attr ?: $template?->rdn))>
					{{ Arr::get($option,'value') }}
				</option>
			@endforeach
		</select>

		<span class="input-group-text">=</span>
		<input type="text"
			@class(['form-control','is-invalid'=>$errors->get('_rdn_value')])
			id="rdn_value"
			name="_rdn_value"
			value="{{ old('_rdn_value',$o->rdn_value) }}"
			placeholder="rdn">
		<label class="input-group-text">,{{ $o->base }}</label>

		<div class="invalid-feedback pb-2">
			@error('_rdn')
				{{ $message }}
			@enderror
			@error('_rdn_value')
				{{ $message }}
			@enderror
		</div>
	</div>

@else
	{{ $o->rdn_value }}
@endif

@section('page-scripts')
	<script type="text/javascript">
		$(document).ready(function() {
			var rdn_value_set = $('input#rdn_value').val();
			rdn_attr = $('select#rdn').val();

			if (rdn_attr) {
				$('#'+rdn_attr).find('input').first().attr('readonly',true);
				set_rdn_value();
			}

			function set_rdn_value() {
				if (rdn_attr && rdn_value_set)
					$('#'+rdn_attr).find('input').first().val($('input#rdn_value').val());
			}

			$('select#rdn').on('change',function() {
				// If the selected attr has a value, use it
				var rdn_value = $(this).val() ? $('#'+$(this).val()).find('input').first().val() : null;

				if (rdn_value)
					$('input#rdn_value').val(rdn_value);

				// if rdn_attr is already set (and its now different), remove read only and clear value
				if (rdn_attr)
					$('#'+rdn_attr).find('input').first().attr('readonly',false).val('');

				// set RDN attribute read-only
				if ((rdn_attr=$(this).val()) && rdn_attr)
					$('#'+rdn_attr).find('input').first().attr('readonly',true);

				set_rdn_value();
			})

			$('input#rdn_value').on('change',function() {
				rdn_value_set = $(this).val();

				set_rdn_value();
			})
		});
	</script>
@endsection