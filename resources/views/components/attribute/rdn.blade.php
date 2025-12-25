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
		<!-- component.attribute.rdn -->
		var rdn_value;
		var rdn_attr;
		var rdn_options_sort = false;

		// Set the RDN attribute's value
		function rdn_value_set() {
			if (rdn_attr && rdn_value)
				$('attribute#'+rdn_attr+' input:first')
					.val($('input#rdn_value').val());
		}

		function rdn_options() {
			return $('select#rdn option')
				.map((key,item)=>item.value)
				.toArray();
		}

		function sort_rdn_options() {
			// Sort the RDN attributes
			if (rdn_options_sort) {
				$('select#rdn')
					.append($('select#rdn option')
						.remove()
						.sort(sort_text))
					.val('');

				rdn_options_sort = false;
			}
		}

		$(document).ready(function() {
			rdn_attr = $('select#rdn').val();
			rdn_value = $('input#rdn_value').val();

			if (rdn_attr) {
				$('#'+rdn_attr+' input:first')
					.attr('readonly',true);

				rdn_value_set();
			}

			$('select#rdn').on('change',function() {
				// Reset the RDN value to the new attribute, or blank
				rdn_value = $(this).val()
					? $('attribute#'+$(this).val()+' input:first').val()
					: '';

				$('input#rdn_value').val(rdn_value);

				// if rdn_attr is already set (and its now different), remove read only and clear value
				if (rdn_attr) {
					var x = $('attribute#'+rdn_attr+' input:first');

					x.val('');

					if (! x.hasClass('no-edit'))
						x.attr('readonly',false);
				}

				// set RDN attribute read-only
				if ((rdn_attr=$(this).val()) && rdn_attr)
					$('attribute#'+rdn_attr+' input:first')
						.attr('readonly',true);

				rdn_value_set();
			})

			// If the value is changed, update the attribute
			$('input#rdn_value').on('change',function() {
				rdn_value = $(this).val();

				rdn_value_set();
			})
		});
	</script>
@endsection