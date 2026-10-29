<script type="text/javascript">
	const web_base = '{{ request()->root() }}';
	const web_base_path = '{{ Request::header('X-Forwarded-Prefix','/') }}';
	const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
</script>

@vite([
    'resources/js/app.js',
    'resources/themes/architect/src/init.js',
])

<script type="text/javascript">
	// Runs after Vite modules have executed (DOMContentLoaded fires after all deferred/module scripts)
	document.addEventListener('DOMContentLoaded', function() {
		// Our CSRF token to each interaction
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
	});
</script>

@if(file_exists('js/custom.js'))
	<!-- Any Custom JS -->
	<script src="{{ asset('js/custom.js') }}"></script>
@endif

@if(file_exists('js/template.js'))
	<!-- Template Engine JS -->
	<script src="{{ asset('js/template.js') }}"></script>
@endif