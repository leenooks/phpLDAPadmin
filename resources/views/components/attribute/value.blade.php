<div class="input-group has-validation">
	<input type="text"
		{{ $attributes->class([
			'is-invalid'=>($e=array_merge($errors->get($o->name_lc.'.'.$dotkey),$errors->get('_auto_value.'.$o->name_lc))),
			'no-edit'=>$o->isRDN(),
		]) }}
		name="{{ $o->name_lc }}[{{ $attrtag }}][]"
		value="{{ $value }}"
		placeholder="{{ $value ?: '['.__('NEW').']' }}"
		@readonly(! $edit || ($template && $template->attributeReadOnly($o->name_lc)))
		@disabled($o->isDynamic())>

	<x-form.invalid-feedback :errors="$e"/>
</div>