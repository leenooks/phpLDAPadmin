@if(session()->has('success'))
	<div class="alert alert-success p-2">
		<p class="m-0"><i class="fas fa-fw fa-thumbs-up"></i> {{ session()->pull('success') }}</p>
	</div>
@endif