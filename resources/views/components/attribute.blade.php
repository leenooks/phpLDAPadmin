<!-- $o=Attribute::class -->
<x-attribute.layout :edit="$edit ?? FALSE" :new="$new ?? FALSE" :o="$o">
	@foreach(old($o->name_lc,($new ?? FALSE) ? [NULL] : $o->tagValues($langtag)) as $values)
		<div class="col-12">
			@foreach($values as $value)
				@if(($edit ?? FALSE) && ! $o->is_rdn)
					<div class="input-group has-validation">
						<input type="text" @class(['form-control','is-invalid'=>($e=$errors->get($o->name_lc.'.'.$loop->index)),'mb-1','border-focus'=>($o->values->contains($value))]) name="{{ $o->name_lc }}[]" value="{{ $value }}" placeholder="{{ ! is_null($x=Arr::get($o->values,$loop->index)) ? $x : '['.__('NEW').']' }}" @readonly(! ($new ?? FALSE))>

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
		</div>
	@endforeach
</x-attribute.layout>