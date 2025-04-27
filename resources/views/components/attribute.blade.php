@use(App\Ldap\Entry)

<!-- $o=Attribute::class -->
<x-attribute.layout :edit="$edit=($edit ?? FALSE)" :new="$new=($new ?? FALSE)" :o="$o">
	<div class="col-12">
		@foreach(Arr::get(old($o->name_lc,[($langtag=($langtag ?? Entry::TAG_NOTAG))=>$new ? [NULL] : $o->tagValues($langtag)]),$langtag,[]) as $key => $value)
			@if($edit && (! $o->is_rdn))
				<div class="input-group has-validation">
					<input type="text" @class(['form-control','is-invalid'=>($e=$errors->get($o->name_lc.'.'.$langtag.'.'.$loop->index)),'mb-1','border-focus'=>! ($tv=$o->tagValuesOld($langtag))->contains($value)]) name="{{ $o->name_lc }}[{{ $langtag }}][]" value="{{ $value }}" placeholder="{{ ! is_null($x=$tv->get($loop->index)) ? $x : '['.__('NEW').']' }}" @readonly(! $new) @disabled($o->isDynamic())>

					<div class="invalid-feedback pb-2">
						@if($e)
							{{ join('|',$e) }}
						@endif
					</div>
				</div>

			@else
				<input type="text" class="form-control mb-1" value="{{ $value }}" disabled>
			@endif
		@endforeach
	</div>
</x-attribute.layout>