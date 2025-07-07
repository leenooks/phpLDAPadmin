<select class="form-select {{ $class }} @error($old ?? $id ?? $name) is-invalid @enderror" id="{{ $id ?? $name}}" @isset($name)name="{{ $name }}"@endisset @required($required ?? FALSE) @disabled($disabled ?? FALSE)>
	@if((empty($value) && ! empty($options)) || isset($addnew))
		<option value=""></option>

		@isset($addnew)
			<option value="new">{{ $addnew ?: 'Add New' }}</option>
		@endisset
	@endif
	@isset($options)
		@empty($groupby)
			@foreach($options as $option)
				@continue(! Arr::get($option,'value'))
				<option value="{{ Arr::get($option,'id') }}" @selected(Arr::get($option,'id') == collect(old())->dot()->get(isset($old) ? $old.'.0' : ($id ?? $name),$value ?? ''))>{{ Arr::get($option,'value') }}</option>
			@endforeach

		@else
			@foreach($options->groupBy($groupby) as $group)
				<optgroup label="{{ Arr::get($group->first(),$groupby) }}">
					@foreach($group as $option)
						@continue(! Arr::get($option,'value'))
						<option value="{{ Arr::get($option,'id') }}" @selected(Arr::get($option,'id') == collect(old())->dot()->get(isset($old) ? $old.'.0' : ($id ?? $name),$value ?? ''))>{{ Arr::get($option,'value') }}</option>
					@endforeach
				</optgroup>
			@endforeach
		@endempty
	@endisset
</select>

@section('page-scripts')
	<script type="text/javascript">
		// Select doesnt support read only so we'll use disable and a new field
		@isset($name)
		function {{$id ?? $name}}_readonly(on) {
			if (on) {
				$('#{{ $name }}').prop('disabled',true);
				$('#{{ $name }}_disabled').prop('disabled',false).val($('#{{ $name }}').val());

			} else {
				$('#{{ $name }}').prop('disabled',false);
				$('#{{ $name }}_disabled').prop('disabled',true);
			}
		}
		@endisset

		$(document).ready(function() {
			$('#{{ $id ?? $name }}').select2({
				theme: 'bootstrap-5',
				dropdownAutoWidth: false,
				width: 'style',
				allowClear: {{ $allowclear ?? 'false' }},
				placeholder: '{{ $placeholder ?? '' }}',
				multiple: {{ $multiple ?? 'false' }},
				@isset($addvalues)
				tags: true,
				@endisset
			});

			@if(isset($multiple) && (! $multiple))
			$('#{{ $id ?? $name }}').val(' ');
			$('#{{ $id ?? $name }}').trigger('change');
			@endif

			@isset($options)
			@if(($autoselect ?? FALSE) && $options->count() === 1)
			$('#{{ $id ?? $name }}')
				.val('{{ $options->first()['id'] }}')
				.trigger("change")
			@endif
			@endisset
		});
	</script>
@append