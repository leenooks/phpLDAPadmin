<div class="input-group has-validation">
	<input type="text"
		{{ $attributes->class(['is-invalid'=>($e=$errors->get($o->name_lc.'.'.$dotkey))]) }}
		name="{{ $o->name_lc }}[{{ $attrtag }}][]"
		value="{{ strlen($value) ? $value : ($av ?? '') }}"
		placeholder="{{ $value ?: '['.__('NEW').']' }}"
		@readonly(! $edit)
		@disabled($o->isDynamic())>

	<x-form.invalid-feedback :errors="$e"/>
</div>