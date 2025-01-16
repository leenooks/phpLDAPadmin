<!-- @todo We are not handling redirect backs with updated values -->
<!-- $o=Password::class -->
<x-attribute.layout :edit="$edit" :new="$new" :o="$o">
	@foreach($o->values as $value)
		@if($edit)
			<div class="input-group has-validation">
				<input type="password" @class(['form-control','is-invalid'=>($e=$errors->get($o->name_lc.'.'.$loop->index)),'mb-1','border-focus'=>$o->values->contains($value)]) name="{{ $o->name_lc }}[]" value="{{ md5($value) }}" @readonly(true)>

				<div class="invalid-feedback pb-2">
					@if($e)
						{{ join('|',$e) }}
					@endif
				</div>
			</div>
		@else
			{{ str_repeat('x',8) }}
		@endif
	@endforeach

	@if($edit)
		<span class="p-0 m-0">
			<span class="btn btn-sm btn-outline-dark mt-3"><i class="fas fa-user-check"></i> @lang('Check Password')</span>
		</span>
	@endif
</x-attribute.layout>