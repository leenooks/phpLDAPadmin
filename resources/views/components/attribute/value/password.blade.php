<!-- $o=Password::class -->
<div class="input-group has-validation">
	<x-select class="mb-1"
		id="userpassword_hash_{{$index}}_{{ $template?->name }}"
		name="_userpassword_hash[{{ $attrtag }}][]"
		:value="old('_userpassword_hash.'.$dotkey,$o->hash($o->values->dot()->get($dotkey) ?: '')->id())"
		:options="$helpers"
		allowclear="false"
		:disabled="! $edit"/>
	<input type="password"
		{{ $attributes->class(['is-invalid'=>($e=$errors->get($o->name_lc.'.'.$dotkey))]) }}
		name="{{ $o->name_lc }}[{{ $attrtag }}][]"
		value="{{ Arr::get(old($o->name_lc),$dotkey,$value) }}"
		@readonly(! $edit)>

	<x-form.invalid-feedback :errors="$e"/>
</div>

@if(($edit || $editable) && $o->tagValuesOld($attrtag)->dot()->filter()->count())
	<span class="p-0 m-0">
		<button name="entry-userpassword-check" type="button" class="btn btn-sm btn-outline-dark mt-3" data-bs-toggle="modal" data-bs-target="#page-modal"><i class="fas fa-user-check"></i> @lang('Check Password')</button>
	</span>
@endif