<!-- $o=Attribute::class -->
<x-attribute.layout :edit="$edit" :new="$new" :o="$o">
	@foreach (old($o->name_lc,$o->values) as $value)
		@if ($edit && ($value === NULL || (! $o->isStructural($value))))
			<div class="input-group has-validation">
				<input type="text" class="form-control @if($e=$errors->get($o->name_lc.'.'.$loop->index))is-invalid @endif mb-1 @if($o->values->search($value) === FALSE) border-focus @endif" name="{{ $o->name_lc }}[]" value="{{ $value }}" placeholder="{{ ! is_null($x=Arr::get($o->values,$loop->index)) ? $x : '['.__('NEW').']' }}" readonly="true">
				<div class="invalid-feedback pb-2">
					@if($e)
						{{ join('|',$e) }}
					@endif
				</div>
			</div>
		@else
			{{ $value }}
			@if ($o->isStructural($value))
				<input type="hidden" name="{{ $o->name_lc }}[]" value="{{ $value }}">
				<span class="float-end mb-2">@lang('structural')</span>
			@endif
			<br>
		@endif
	@endforeach
</x-attribute.layout>