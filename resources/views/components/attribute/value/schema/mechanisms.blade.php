<abbr class="pb-1" title="{{ $value }}"><i class="fas fa-book pe-2"></i> {{ $o->get($value,'title') }}</abbr>

@if($x=$o->get($item,'ref'))
	<abbr class="ps-2" title="{{ $x }}"><i class="fas fa-comment-dots"></i></abbr>
@endif

<p class="mb-0">{{ $o->get($value,'desc') }}</p>