@isset($name)
	<input type="hidden" id="{{ $id ?? $name }}_disabled" name="{{ $name }}" value="" disabled>
@endisset

@if(isset($label))
	<label {{ $attributes->only(['class'])->merge(['class'=>'form-label']) }} for="{{ $id ?? $name }}">{!! html_entity_decode($label) !!}</label>
@endisset

<div class="input-group">
	<x-select :id="$id ?? NULL" :name="$name ?? NULL" :old="$old ?? NULL" :options="$options ?? []" :value="$value ?? NULL" :class="$class ?? NULL"/>

	@isset($name)
		<span class="invalid-feedback">
			@error((! empty($old)) ? $old : ($id ?? $name))
				{{ $message }}
			@elseif(isset($feedback))
				{{ $feedback }}
			@enderror
		</span>
	@endisset

	@isset($helper)
		<span class="input-helper">{!! html_entity_decode($helper) !!}</span>
	@endif
</div>