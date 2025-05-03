@if(session()->has('failed'))
	<div class="alert alert-danger p-2">
		<p class="m-0"><i class="fas fa-fw fa-thumbs-down"></i> {{ session()->pull('failed') }}</p>
	</div>
@endif