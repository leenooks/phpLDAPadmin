<div class="input-group has-validation">
	<input type="text"
		   {{ $attributes->class(['is-invalid'=>($e=$errors->get($o->name_lc.'.'.$dotkey))]) }}
		   name="{{ $o->name_lc }}[{{ $attrtag }}][]"
		   value="{{ $value }}"
		   readonly>

	@if($o->dn_exists($value))
		<span class="input-group-end text-black-50"><a href="/?#{{ Crypt::encryptString($value) }}"><i class="fas fa-fw fa-external-link-alt"></i></a></span>
	@else
		<span class="input-group-end text-danger"><i class="fas fa-fw fa-ban" data-bs-toggle="tooltip" title="@lang('DN doesnt exist')"></i></span>
	@endif

	<x-form.invalid-feedback :errors="$e"/>
</div>