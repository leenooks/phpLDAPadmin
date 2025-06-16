<!-- $o=Password::class -->
<x-attribute.layout :edit="$edit" :new="$new" :o="$o">
	@foreach($o->langtags as $langtag)
		@foreach(($o->tagValues($langtag)->count() ? $o->tagValues($langtag) : [$langtag => NULL]) as $key => $value)
			@if($edit)
				<div class="input-group has-validation">
					<x-form.select id="userpassword_hash_{{$loop->index}}{{$template ?? ''}}" name="userpassword_hash[{{ $langtag }}][]" :value="$o->hash($new ? '' : ($value ?? ''))->id()" :options="$helpers" allowclear="false" :disabled="! $new"/>
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
	@endforeach
</x-attribute.layout>

@if($edit && $o->tagValuesOld($langtag)->dot()->filter()->count())
	<div class="row">
		<div class="offset-1 col-4">
			<span class="p-0 m-0">
				<button id="entry-userpassword-check" type="button" class="btn btn-sm btn-outline-dark mt-3" data-bs-toggle="modal" data-bs-target="#page-modal"><i class="fas fa-user-check"></i> @lang('Check Password')</button>
			</span>
		</div>
	</div>
@endif