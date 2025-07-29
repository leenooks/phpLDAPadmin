<!-- $o=KrbPrincipleKey::class -->
<div class="input-group has-validation mb-3">
	<input type="password"
		{{ $attributes->class(['is-invalid'=>($e=$errors->get($o->name_lc.'.'.$dotkey))]) }}
		name="{{ $o->name_lc }}[{{ $attrtag }}][]"
		value="{{ Arr::get(old($o->name_lc),$dotkey,$value ? md5($value) : '') }}"
		@readonly(! $edit)>

	<x-form.invalid-feedback :errors="$e"/>
</div>