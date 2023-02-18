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
	<title>{{ config('app.name') }} - @yield('htmlheader_title','🥇 The BEST ldap admin tool!')</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no" />

	<meta name="description" content="phpLDAPadmin - A web interface into LDAP data management">
	<meta name="msapplication-tap-highlight" content="no">
	<link rel="shortcut icon" href="{{ config('app.favicon','favicon.ico') }}" />

	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<!-- Google Font: Source Sans Pro -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family={{ str_replace(' ','+',config('app.font') ?: 'IBM Plex Sans') }}:wght@300&display=swap">

	<!-- Bootstrap -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.2.1/dist/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">

	@if(file_exists('css/print.css'))
		<!-- Printing Modifications -->
		<link rel="stylesheet" href="{{ asset('/css/print.css') }}">
	@endif

	<!-- Fancy Tree -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.fancytree/2.36.1/skin-xp/ui.fancytree.min.css">

	<!-- Country Flags -->
	<link rel="stylesheet" href="{{ url('/css/flags16-both.css') }}">
	<link rel="stylesheet" href="{{ url('/css/flags32-both.css') }}">

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
