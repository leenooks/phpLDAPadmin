<!-- $o=CertificateList::class -->
<div class="input-group has-validation mb-3">
	<textarea {{ $attributes->class([
		'font-size-md font-monospace overflow-hidden',
		'is-invalid'=>($e=$errors->get($o->name_lc.'.'.$dotkey))]) }}
		name="{{ $o->name_lc }}[{{ $attrtag }}][]"
		rows="{{ max(count(explode("\n",$value)),5) }}"
		@readonly(! $edit)>{{ $value }}</textarea>

	<x-form.invalid-feedback :errors="$errors->get($o->name_lc.'.'.$dotkey)"/>
</div>