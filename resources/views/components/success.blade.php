@if(session()->has('success'))
	<div class="alert alert-success">
		<h4 class="alert-heading"><i class="fas fa-fw fa-thumbs-up"></i> Success!</h4>
		<hr>
		<p>{{ session()->pull('success') }}</p>
		<ul style="list-style-type: square;">
			@foreach (session()->pull('updated') as $key => $values)
				<li>{{ $key }}: {{ join(',',$values) }}</li>
			@endforeach
		</ul>
	</div>
@endif