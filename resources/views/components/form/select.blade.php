<x-form.base {{ $attributes }}>
	@isset($name)
		<input type="hidden" id="{{ $id ?? $name }}_disabled" name="{{ $name }}" value="" disabled>
	@endisset
	<select class="form-select @isset($name)@error((! empty($old)) ? $old : $name) is-invalid @enderror @endisset" id="{{ $id ?? $name }}" @isset($name)name="{{ $name }}"@endisset @required(isset($required) && $required) @disabled(isset($disabled) && $disabled)>
		@if((empty($value) && ! empty($options)) || isset($addnew) || isset($choose))
			<option value=""></option>
			@isset($addnew)
				<option value="new">{{ $addnew ?: 'Add New' }}</option>
			@endisset
		@endif

		@isset($options)
			@empty($groupby)
				@foreach($options as $option)
					@continue(! Arr::get($option,'value'))
					<option value="{{ Arr::get($option,'id') }}" @selected(isset($name) && (Arr::get($option,'id') == old($old ?? $name,$value ?? '')))>{{ Arr::get($option,'value') }}</option>
				@endforeach

			@else
				@foreach($options->groupBy($groupby) as $group)
					<optgroup label="{{ $groupby == 'active' ? (Arr::get($group->first(),$groupby) ? 'Active' : 'Not Active') : Arr::get($group->first(),$groupby) }}">
						@foreach($group as $option)
							@continue(! Arr::get($option,'value'))
							<option value="{{ Arr::get($option,'id') }}" @selected(isset($name) && (Arr::get($option,'id') == old($old ?? $name,$value ?? '')))>{{ Arr::get($option,'value') }}</option>
						@endforeach
					</optgroup>
				@endforeach
			@endempty
		@endisset
	</select>
</x-form.base>

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
				dropdownAutoWidth: true,
				width: 'style',
				allowClear: {{ $allowclear ?? 'false' }},
				placeholder: '{{ $placeholder ?? '' }}',
				@isset($addvalues)
					tags: true,
				@endisset
			});
		});
	</script>
@append