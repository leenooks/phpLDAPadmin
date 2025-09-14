<script type="text/javascript" src="{{ asset('js/vendor.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/manifest.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/app.js') }}"></script>

<script type="text/javascript">
	const web_base = '{{ request()->root() }}';
	const web_base_path = '{{ Request::header('X-Forwarded-Prefix','/') }}'

	// Our CSRF token to each interaction
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	// Work out our timezone.
	const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
</script>

@if(file_exists('js/custom.js'))
	<!-- Any Custom JS -->
	<script src="{{ asset('js/custom.js') }}"></script>
@endif

@if(file_exists('js/template.js'))
	<!-- Template Engine JS -->
	<script src="{{ asset('js/template.js') }}"></script>
@endif