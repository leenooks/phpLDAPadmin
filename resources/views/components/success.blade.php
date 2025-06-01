@if(session()->has('success'))
	<div class="alert alert-success">
		<h4 class="alert-heading"><i class="fas fa-fw fa-thumbs-up"></i> Success!</h4>
		<hr>
		<ul class="square">
			@foreach (session()->get('success') as $item)
				<li>{{ $item }}</li>
			@endforeach
		</ul>
	</div>
@endif