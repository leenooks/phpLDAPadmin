<!DOCTYPE html>
<html>
@section('htmlheader')
	@include('architect::layouts.partials.htmlheader')
@show

<body class="hold-transition login-page">
<div id="app">
	@yield('content')
</div>

@section('scripts')
	@include('architect::auth.partials.scripts')

	@yield('page-scripts')
@show
</body>
</html>
