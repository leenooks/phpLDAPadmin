<!-- $o=Password::class -->
<x-attribute.layout :edit="$edit ?? FALSE" :new="$new ?? FALSE" :o="$o">
	@foreach($o->values as $value)
		@if($edit)
			<div class="input-group has-validation mb-3">
				<x-form.select id="userpassword_hash_{{$loop->index}}" name="userpassword_hash[]" :value="$o->hash($value)->id()" :options="$helpers" allowclear="false" :disabled="true"/>
				<input type="password" @class(['form-control','is-invalid'=>($e=$errors->get($o->name_lc.'.'.$loop->index)),'mb-1','border-focus'=>$o->values->contains($value)]) name="{{ $o->name_lc }}[]" value="{{ md5($value) }}" @readonly(true)>

				<div class="invalid-feedback pb-2">
					@if($e)
						{{ join('|',$e) }}
					@endif
				</div>
			</div>
		@else
			{{ (($x=$o->hash($value)) && ($x::id() !== '*clear*')) ? sprintf('{%s}',$x::shortid()) : '' }}{{ str_repeat('*',16) }}
		@endif
	@endforeach
</x-attribute.layout>

@if($edit)
	<div class="row">
		<div class="offset-1 col-4 p-2">
			<span class="p-0 m-0">
				<button type="button" class="btn btn-sm btn-outline-dark mt-3" data-bs-toggle="modal" data-bs-target="#userpassword_check-modal"><i class="fas fa-user-check"></i> @lang('Check Password')</button>
			</span>
		</div>
	</div>
@endif