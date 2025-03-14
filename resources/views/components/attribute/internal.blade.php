<!-- $o=Internal::class -->
@foreach (old($o->name_lc,$o->values) as $value)
	@if($loop->index)<br>@endif
	{{ $value }}
@endforeach