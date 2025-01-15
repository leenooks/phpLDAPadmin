@if($errors->any())
	<div class="alert alert-danger">
		<h4 class="alert-heading"><i class="fas fa-fw fa-thumbs-down"></i> Error?</h4>
		<hr>
		<ul style="list-style-type: square;">
			@foreach ($errors->all() as $error)
				<li>{{ $error }}</li>
			@endforeach
		</ul>
	</div>
@endif