<!-- $o=Attribute::class -->
<x-attribute.layout :edit="$edit" :new="$new" :o="$o">
	@foreach (old($o->name_lc,$o->values) as $value)
		@if ($edit && ($value === NULL || (! $o->isStructural($value))))
			<div class="input-group has-validation">
				<input type="text" @class(['form-control','is-invalid'=>($e=$errors->get($o->name_lc.'.'.$loop->index)),'mb-1','border-focus'=>$o->values->contains($value)]) name="{{ $o->name_lc }}[]" value="{{ $value }}" placeholder="{{ ! is_null($x=Arr::get($o->values,$loop->index)) ? $x : '['.__('NEW').']' }}" @readonly(true)>
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
				<span class="float-end">@lang('structural')</span>
			@endif
			<br>
		@endif
	@endforeach
</x-attribute.layout>