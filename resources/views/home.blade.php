@extends('architect::layouts.app')

@section('main-content')
	<x-success/>
	<x-failed/>

	<div class="card card-solid mb-3">
		<div class="card-body">
			<div class="row">
				<div class="col-12 col-sm-4">
					<h3 class="d-inline-block d-sm-none">phpLDAPadmin</h3>
					<img src="{{ url('images/logo.png') }}" class="logo-image col-12" alt="PLA Logo">
				</div>

				<div class="col-12 col-sm-8">
					<h1 class="mb-2">Welcome to phpLDAPadmin</h1>
					<p>phpLDAPadmin (or PLA for short) is an LDAP (Lightweight Directory Access Protocol) data management tool for administrators.</p>
					<p>PLA provides an easy-to-use interface for browsing, searching, and modifying data in an LDAP directory. Essentially, it's a user-friendly alternative to using command-line tools for LDAP management.</p>
				</div>
			</div>

			<div class="row">
				<div class="col-12">
					<hr>
				</div>
			</div>

			<div class="row">
				<div class="col-12 col-sm-4">
					<span class="text-dark">
						<a class="link-opacity-50 link-opacity-100-hover link-dark" href="https://phpldapadmin.org"><i class="fas fa-fw fa-2x fa-globe me-1"></i></a>
						<a class="link-opacity-50 link-opacity-100-hover link-dark" href="https://github.com/leenooks/phpldapadmin"><i class="fab fa-fw fa-2x fa-github me-1"></i></a>
						<a class="link-opacity-50 link-opacity-100-hover link-dark" href="https://github.com/leenooks/phpLDAPadmin/discussions"><i class="fas fa-fw fa-2x fa-hand me-1"></i></a>
						<a class="link-opacity-50 link-opacity-100-hover link-dark" href="https://github.com/leenooks/phpLDAPadmin/issues"><i class="fas fa-fw fa-2x fa-bug me-1"></i></a>
						<a class="link-opacity-50 link-opacity-100-hover link-dark" href="https://hub.docker.com/r/phpldapadmin/phpldapadmin"><i class="fab fa-fw fa-2x fa-docker me-1"></i></a>
					</span>
				</div>

				<div class="col-12 col-sm-8 col-xl-4">
					<h5>Key Features and Functionality</h5>
					<ul class="list-unstyled">
						<li class="ps-0 p-1">
							<i class="fas fa-fw fa-globe me-2"></i> Easy To Use Web Interface
						</li>
						<li class="ps-0 p-1">
							<i class="fas fa-fw fa-sitemap me-2"></i> Hierarchical Tree View
						</li>
						<li class="ps-0 p-1">
							<i class="fas fa-fw fa-clone me-2"></i> Creation and Modification Templates
						</li>
						<li class="ps-0 p-1">
							<i class="fas fa-fw fa-magnifying-glass-chart me-2"></i> Data Rich Attribute Values
						</li>
						<li class="ps-0 p-1">
							<i class="fas fa-fw fa-language me-2"></i> Multi-language Support
						</li>
						<li class="ps-0 p-1">
							<i class="fas fa-fw fa-file-export me-2"></i> LDIF Import/Export
						</li>
						<li class="ps-0 p-1">
							<i class="fas fa-fw fa-clipboard-list me-2"></i> Built using RFC Standards
						</li>
						<li class="ps-0 p-1">
							<i class="fas fa-fw fa-pen-to-square me-2"></i> Open Source
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>

	<x-file-note file="home-note.html"/>
@endsection

@section('page-scripts')
	<script type="text/javascript">
		var subpage = window.location.hash;

		$(document).ready(function() {
			// Enable navigating to a page via a URL fragment, and that fragment is defined with a server-icon
			if (subpage) {
				// Clear the hash
				history.replaceState(null,null,' ');
				get_frame(subpage.substring(1));
			}
		});
	</script>
@append