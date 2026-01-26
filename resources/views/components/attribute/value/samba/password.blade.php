<!-- $o=Samba\[LM|NT]Password::class -->
@use(App\Classes\LDAP\Attribute\Password)
@use(App\Ldap\Entry)

@if($value && (! $o->isDirty()))
	<input type="hidden" name="{{ $o->name_lc }}[{{ $attrtag }}{{ Entry::TAG_MD5 }}][]" value="{{ md5($value) }}">
	<input type="hidden" name="{{ $o->name_lc }}[{{ $attrtag }}{{ Entry::TAG_HELPER }}][]" value="{{ $o->encoding }}">
@endif

<div class="input-group has-validation">
	<input type="password"
		{{ $attributes->only('class')->class([
			'is-invalid'=>$e=$errors->get($o->name_lc.'.'.$dotkey)
		]) }}
		name="{{ $o->name_lc }}[{{ $attrtag }}][]"
		value="{{ Arr::get(old($o->name_lc),$dotkey,md5($value)) }}"
		@readonly(! $edit)>

	<x-form.invalid-feedback :errors="$e"/>
</div>

@if(($edit || $editable) && $o->tagValuesOld($attrtag)->dot()->filter()->count())
	<span class="p-0 m-0">
		<button name="entry-sambapassword-check" type="button" class="btn btn-sm btn-outline-dark mt-3 disabled" data-attr="{{ $o->name_lc }}" data-bs-toggle="modal" data-bs-target="#page-modal"><i class="fas fa-user-check"></i> @lang('Check Password')</button>
	</span>
@endif