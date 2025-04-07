<!-- @todo We are not handling redirect backs yet with updated passwords -->
<!-- $o=Password::class -->
<x-attribute.layout :edit="$edit" :new="$new" :o="$o" :langtag="$langtag">
	@foreach($o->tagValuesOld($langtag) as $key => $value)
		@if($edit)
			<div class="input-group has-validation mb-3">
				<x-form.select id="userpassword_hash_{{$loop->index}}" name="userpassword_hash[{{ $langtag }}][]" :value="$o->hash($value)->id()" :options="$helpers" allowclear="false" :disabled="true"/>
				<input type="password" @class(['form-control','is-invalid'=>($e=$errors->get($o->name_lc.'.'.$langtag.'.'.$loop->index)),'mb-1','border-focus'=>! $o->tagValuesOld($langtag)->contains($value)]) name="{{ $o->name_lc }}[{{ $langtag }}][]" value="{{ md5($value) }}" @readonly(true)>

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

@if($edit)
	<div class="row">
		<div class="offset-1 col-4 p-2">
			<span class="p-0 m-0">
				<button id="entry-userpassword-check" type="button" class="btn btn-sm btn-outline-dark mt-3" data-bs-toggle="modal" data-bs-target="#page-modal"><i class="fas fa-user-check"></i> @lang('Check Password')</button>
			</span>
		</div>
	</div>
@endif