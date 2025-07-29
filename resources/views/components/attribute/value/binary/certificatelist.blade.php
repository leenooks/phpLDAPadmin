<!-- $o=CertificateList::class -->
<div class="input-group has-validation mb-3">
	<textarea class="form-control mb-1 font-size-md font-monospace overflow-hidden"
		rows="{{ count(explode("\n",$x=($o->isDirty() ? $value : $o->render_item_old($dotkey)))) }}"
		disabled>{{ $x }}</textarea>

	<x-form.invalid-feedback :errors="$errors->get($o->name_lc.'.'.$dotkey)"/>
</div>