<head>
	<!--
	=========================================================
	* ArchitectUI HTML Theme Dashboard - v1.0.0
	=========================================================
	* Product Page: https://dashboardpack.com
	* Copyright 2019 DashboardPack (https://dashboardpack.com)
	* Licensed under MIT (https://github.com/DashboardPack/architectui-html-theme-free/blob/master/LICENSE)
	=========================================================
	* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
	-->
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta http-equiv="Content-Language" content="en">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>{{ config('app.name') }} - @yield('htmlheader_title','Your title here')</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no" />

	<meta name="description" content="phpLDAPadmin - A web interface into LDAP data management">
	<meta name="msapplication-tap-highlight" content="no">

	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<!-- Google Font: Source Sans Pro -->
	<link href="https://fonts.googleapis.com/css2?family={{ str_replace(' ','+',config('app.font') ?: 'IBM Plex Sans') }}:wght@300&display=swap" rel="stylesheet">

	@if(file_exists('css/print.css'))
		<!-- Printing Modifications -->
		<link rel="stylesheet" href="{{ asset('/css/print.css') }}">
	@endif

	<!-- Fancy Tree -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.fancytree/2.36.1/skin-xp/ui.fancytree.min.css">

	<!-- STYLESHEETS -->
	{!! Asset::styles() !!}

	<!-- Theme style -->
	<link rel="stylesheet" href="{{ asset('/css/architect.min.css') }}">

	@if(file_exists('css/fixes.css'))
		<!-- CSS Fixes -->
		<link rel="stylesheet" href="{{ asset('/css/fixes.css') }}">
	@endif

	@if(file_exists('css/custom.css'))
		<!-- Custom CSS -->
		<link rel="stylesheet" href="{{ asset('/css/custom.css') }}">
	@endif
</head>
