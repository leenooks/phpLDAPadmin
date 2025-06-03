<!-- $o=CertificateList::class -->
<x-attribute.layout :edit="$edit" :new="$new" :o="$o">
	@foreach($o->tagValuesOld('binary') as $key => $value)
		<div class="input-group has-validation mb-3">
			<textarea class="form-control mb-1 font-size-md font-monospace overflow-hidden" rows="{{ count(explode("\n",$x=$o->render_item_old('binary.'.$key))) }}" disabled>{{ $x }}</textarea>

			<div class="invalid-feedback pb-2">
				@if($e=$errors->get($o->name_lc.'.binary.'.$loop->index))
					{{ join('|',$e) }}
				@endif
			</div>
		</div>
	@endforeach
</x-attribute.layout>