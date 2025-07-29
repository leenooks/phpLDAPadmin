@if(preg_match('/[0-9]+\.[0-9]+\.[0-9]+/',$value))
	<abbr class="pb-1" title="{{ $value }}"><i class="fas fa-list-ol pe-2"></i>{{ $o->get($value,'title') }}</abbr>

	@if($x=$o->get($value,'ref'))
		<abbr class="ps-2" title="{{ $x }}"><i class="fas fa-comment-dots"></i></abbr>
	@endif

	<p class="mb-2">{{ $o->get($value,'desc') }}</p>
@else
	{{ $value }}
@endif