@if(session()->has('updated'))
	<div class="alert alert-success">
		<h4 class="alert-heading"><i class="fas fa-fw fa-thumbs-up"></i> Success!</h4>
		<hr>
		<p>{{ __('Entry updated') }}</p>
		<ul style="list-style-type: square;">
			@foreach (session()->pull('updated') as $key => $o)
				<li><abbr title="{{ $o->description }}">{{ $o->name }}</abbr>: {{ $o->values->map(fn($item,$key)=>$o->render_item_new($key))->join(',') }}</li>
			@endforeach
		</ul>
	</div>
@endif