<!-- $o=KrbPrincipleKey::class -->
<x-attribute.layout :edit="$edit" :new="$new" :o="$o">
	@foreach(($o->tagValues($langtag)->count() ? $o->tagValues($langtag) : [$langtag => NULL]) as $key => $value)
		@if($edit)
			<div class="input-group has-validation mb-3">
				<input type="password" @class(['form-control','is-invalid'=>($e=$errors->get($o->name_lc.'.'.$langtag.'.'.$loop->index)),'mb-1','border-focus'=>! $o->tagValuesOld($langtag)->contains($value),'bg-success-subtle'=>$updated]) name="{{ $o->name_lc }}[{{ $langtag }}][]" value="{{ Arr::get(old($o->name_lc),$langtag.'.'.$loop->index,$value ? md5($value) : '') }}" @readonly(! $new)>

				<div class="invalid-feedback pb-2">
					@if($e)
						{{ join('|',$e) }}
					@endif
				</div>
			</div>
		@else
			{{ $o->render_item_old($langtag.'.'.$key) }}
		@endif
	@endforeach
</x-attribute.layout>