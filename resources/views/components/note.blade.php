@if(session()->has('note'))
	<div class="alert alert-info">
		<h4 class="alert-heading"><i class="fas fa-fw fa-note-sticky"></i> Note:</h4>
		<hr>
		<p>{{ session()->pull('note') }}</p>
	</div>
@endif