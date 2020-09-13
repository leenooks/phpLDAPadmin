@extends('architect::layouts.app')

@section('htmlheader_title')
	Home
@endsection

@section('page_title')
@endsection
@section('page_subtitle')
	Content Header - Description
@endsection
@section('page_icon')
@endsection

@section('main-content')
	<div class="card card-solid">
		<div class="card-body">
			<div class="row">
				<div class="col-12 col-sm-4">
					<h3 class="d-inline-block d-sm-none">phpLDAPadmin</h3>
					<img src="img/logo.png" class="logo-image col-12" alt="PLA Logo">
				</div>

				<div class="col-12 col-sm-8">
					<h3 class="mb-1">Welcome to phpLDAPadmin</h3>
					<h4 class="mb-3"><small>{{ config('app.version') }}</small></h4>
					<p>phpLDAPadmin (or PLA for short) is an LDAP data management tool for administrators.</p>
					<p>PLA aims to adhere to the LDAP standards so that it can interact with any LDAP server that implements those standards.</p>
				</div>
			</div>

			<div class="row">
				<div class="col-12">
					<hr>
					<p>Version 2 is a complete re-write of PLA, leveraging the advancements and modernisation of web tools and methods, libraries since version 1 was released.</p>
					<p>You can support this application by letting us know which LDAP server you use (including version and platform).</p>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('page-scripts')
	<script>
		var basedn = {!! $bases->toJson() !!};
	</script>
@append
