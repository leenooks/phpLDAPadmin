@if(session()->has('note'))
	<div class="alert alert-info p-2">
		<p class="m-0"><i class="fas fa-fw fa-info"></i> {{ session()->pull('note') }}</p>
	</div>
@endif