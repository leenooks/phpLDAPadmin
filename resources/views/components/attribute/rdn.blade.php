<!-- $o=RDN::class -->
<x-attribute.layout :edit="$edit ?? FALSE" :new="$new ?? FALSE" :o="$o">
	@foreach(($o->values->count() ? $o->values : ['']) as $value)
		@if($edit)
			<div class="input-group has-validation mb-3">
				<select class="form-select @error('rdn')is-invalid @enderror" id="rdn" name="rdn">
					<option value=""></option>

					@foreach($o->attrs->map(fn($item)=>['id'=>$item,'value'=>$item]) as $option)
						@continue(! Arr::get($option,'value'))
						<option value="{{ Arr::get($option,'id') }}" @selected(Arr::get($option,'id') == old('rdn',$value ?? ''))>{{ Arr::get($option,'value') }}</option>
					@endforeach
				</select>

				<span class="input-group-text">=</span>
				<input type="text" @class(['form-control','is-invalid'=>$errors->get('rdn_value')]) id="rdn_value" name="rdn_value" value="{{ old('rdn_value') }}" placeholder="rdn">
				<label class="input-group-text" for="inputGroupSelect02">,{{ $o->base }}</label>

				<div class="invalid-feedback pb-2">
					@error('rdn')
						{{ $message }}
					@enderror
					@error('rdn_value')
						{{ $message }}
					@enderror
				</div>
			</div>
		@else
			{{ $value }}
		@endif
	@endforeach
</x-attribute.layout>

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
				// if rdn_attr is already set (and its now different), remove read only and clear value
				if (rdn_attr)
					$('#'+rdn_attr).find('input').first().attr('readonly',false).val('');

				// set RDN attribute read-only
				if (rdn_attr = $(this).val())
					$('#'+rdn_attr).find('input').first().attr('readonly',true).val('');

				set_rdn_value();
			})

			$('input#rdn_value').on('change',function() {
				rdn_value_set = $(this).val();

				set_rdn_value();
			})
		});
	</script>
@endsection