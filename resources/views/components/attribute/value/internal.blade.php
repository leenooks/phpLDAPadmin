<!-- $o=Attribute|Internal::class -->
<input type="hidden"
	name="{{ $o->name_lc }}[{{ $attrtag }}][]"
	value="{{ $value }}"
	disabled>
{{ $value }}