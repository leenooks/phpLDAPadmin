<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta http-equiv="Content-Language" content="en">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no" />
	<meta name="description" content="phpLDAPadmin - A web interface into LDAP data management">
	<meta name="msapplication-tap-highlight" content="no">

	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>{{ config('app.name') }} - @yield('htmlheader_title','🥇 An LDAP Administration Tool')</title>
	<link rel="shortcut icon" href="/{{ config('app.favicon','favicon.ico') }}" />

	<!-- App CSS -->
	<link rel="stylesheet" href="{{ asset('/css/app.css') }}">

	<!-- Google Font: Source Sans Pro -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family={{ str_replace(' ','+',config('app.font') ?: 'IBM Plex Sans') }}:wght@300&display=swap">

	<!-- Country Flags -->
	<link rel="stylesheet" href="{{ asset('/css/flags/flags16-both.css') }}">
	<link rel="stylesheet" href="{{ asset('/css/flags/flags32-both.css') }}">

	@if(file_exists('css/fixes.css'))
		<!-- CSS Fixes -->
		<link rel="stylesheet" href="{{ asset('/css/fixes.css') }}">
	@endif

	@if(file_exists('css/custom.css'))
		<!-- Custom CSS -->
		<link rel="stylesheet" href="{{ asset('/css/custom.css') }}">
	@endif

	<!-- Page Styles -->
	@yield('page-styles')
	{{--
	@if(file_exists('css/print.css'))
		<!-- Printing Modifications -->
		<link rel="stylesheet" href="{{ asset('/css/print.css') }}">
	@endif
	--}}
</head>
