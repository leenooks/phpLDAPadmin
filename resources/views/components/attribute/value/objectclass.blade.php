<!-- $o=Attribute/ObjectClass::class -->
<span id="objectclass_{{$value}}">
	<div class="input-group has-validation">
		<input type="text"
			{{ $attributes->class(['is-invalid'=>($e=$errors->get($o->name_lc.'.'.$dotkey))]) }}
			name="{{ $o->name_lc }}[{{ $attrtag }}][]"
			value="{{ $value }}"
			readonly>

		@if($o->isStructural($value))
			<span class="input-group-end text-black-50">@lang('structural')</span>
		@else
			<!-- @todo Have an "x" to remove the entry, we need an event to process the removal, removing any attribute values along the way -->
			<span class="input-group-end"><i class="fas fa-fw fa-xmark d-none"></i></span>
		@endif

		<x-form.invalid-feedback :errors="$e"/>
	</div>
</span>