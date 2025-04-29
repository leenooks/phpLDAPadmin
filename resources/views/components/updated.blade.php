@if(session()->has('updated'))
	<div class="alert alert-success p-2">
		<p class="m-0"><i class="fas fa-fw fa-pen-to-square"></i> @lang('Entry updated') [{{ session()->get('updated')->count() }} @lang('attributes(s)')]</p>
	</div>
@endif