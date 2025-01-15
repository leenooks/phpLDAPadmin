<div class="form-group">
	@if(isset($label))
		<label {{ $attributes->only(['class'])->merge(['class'=>'form-label']) }} for="{{ $id ?? $name }}">{!! html_entity_decode($label) !!}</label>
	@endisset
	<div class="input-group has-validation">
		@if(isset($icon) || isset($prepend))
			{{-- // messes with the icon box, we have rounded corners on the right side
			<div class="input-group-prepend">
			--}}
				<span class="input-group-text">@isset($icon)<i class="bi {{ $icon }}"></i>@endisset @isset($prepend){!! $prepend !!}@endisset</span>
			{{--
			</div>
			--}}
		@endif
		{{ $slot }}
		@isset($name)
			<span class="invalid-feedback">
				@error((! empty($old)) ? $old : $name)
					{{ $message }}
				@elseif(isset($feedback))
					{{ $feedback }}
				@enderror
			</span>
		@endisset
	</div>
	@isset($helper)
		<span class="input-helper">{!! html_entity_decode($helper) !!}</span>
	@endif
</div>