<!-- @todo We are not handling redirect backs yet with updated passwords -->
<!-- $o=KrbPrincipleKey::class -->
<x-attribute.layout :edit="$edit ?? FALSE" :new="$new ?? FALSE" :o="$o">
	@foreach($o->values_old as $value)
		@if($edit)
			<div class="input-group has-validation mb-3">
				<input type="password" @class(['form-control','is-invalid'=>($e=$errors->get($o->name_lc.'.'.$loop->index)),'mb-1','border-focus'=>$o->values->contains($value)]) name="{{ $o->name_lc }}[]" value="{{ md5($value) }}" @readonly(true)>

				<div class="invalid-feedback pb-2">
					@if($e)
						{{ join('|',$e) }}
					@endif
				</div>
			</div>
		@else
			{{ str_repeat('*',16) }}
		@endif
	@endforeach
</x-attribute.layout>