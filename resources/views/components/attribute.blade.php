<!-- $o=Attribute::class -->
<x-attribute.layout :edit="$edit" :new="$new" :o="$o">
	@foreach (old($o->name_lc,$new ? [NULL] : $o->values) as $value)
		@if ($edit && ! $o->is_rdn)
			<div class="input-group has-validation">
				<input type="text" class="form-control @if($e=$errors->get($o->name_lc.'.'.$loop->index))is-invalid @endif mb-1 @if($o->values->search($value) === FALSE) border-focus @endif" name="{{ $o->name_lc }}[]" value="{{ $value }}" placeholder="{{ ! is_null($x=Arr::get($o->values,$loop->index)) ? $x : '['.__('NEW').']' }}" @if (! $new)readonly="true" @endif">

				<div class="invalid-feedback pb-2">
					@if($e)
						{{ join('|',$e) }}
					@endif
				</div>
			</div>

		@else
			{{ $value }}
		@endif
	@endforeach
</x-attribute.layout>