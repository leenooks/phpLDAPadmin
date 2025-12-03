<!-- $o=Password::class -->
@use(App\Classes\LDAP\Attribute\Password)
@use(App\Ldap\Entry)

<div class="input-group has-validation">
	@if(! $o->isDirty() && $value)
		<input type="hidden" name="{{ $o->name_lc }}[{{ $attrtag }}{{ Entry::TAG_MD5 }}][]" value="{{ md5($value) }}">
	@endif

	<x-select
		id="userpassword_hash_{{$index}}_{{ $template?->name }}"
		name="{{ $o->name_lc }}[{{ $attrtag }}{{ Entry::TAG_HELPER }}][]"
		@class(['mb-1','no-edit'=>(! $editable)])
		:value="old($o->name_lc.'.'.$attrtag.Entry::TAG_HELPER.'.'.$index,
			((! $o->values->dot()->get($dotkey)) && ($x=$template?->attribute($o->name_lc)?->get('helper')))
				? $x
				: $o->hash($o->values->dot()->get($dotkey) ?: '')
			->id())"
		:options="$helpers"
		allowclear="false"
		:disabled="! $edit"/>
	<input type="password"
		{{ $attributes->class(['is-invalid'=>($e=$errors->get($o->name_lc.'.'.$dotkey)) || $value === '{*clear*}'.Password::obfuscate]) }}
		name="{{ $o->name_lc }}[{{ $attrtag }}][]"
		value="{{ Arr::get(old($o->name_lc),$dotkey,md5($value)) }}"
		@readonly(! $edit)>

	<x-form.invalid-feedback :errors="$e" alt="{{ $value === '{*clear*}'.Password::obfuscate ? __('Please (re)enter password') : '' }}"/>
</div>

@if(($edit || $editable) && $o->tagValuesOld($attrtag)->dot()->filter()->count())
	<span class="p-0 m-0">
		<button name="entry-userpassword-check" type="button" class="btn btn-sm btn-outline-dark mt-3" data-bs-toggle="modal" data-bs-target="#page-modal"><i class="fas fa-user-check"></i> @lang('Check Password')</button>
	</span>
@endif