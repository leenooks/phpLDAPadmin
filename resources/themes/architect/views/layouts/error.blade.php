<!DOCTYPE html>
<html>
@section('htmlheader')
	@include('architect::layouts.partials.htmlheader')
@show

<body class="hold-transition error-page">
<div id="app">
	<!-- /.login-logo -->
	<div class="app-container app-theme-white body-tabs-shadow">
		<div class="app-container">
			<div class="h-100 bg-animation">
				<div class="d-flex h-100 justify-content-center align-items-center">
					<div class="mx-auto app-login-box col-md-8">

						<div class="modal-dialog w-100 mx-auto">
							<div class="modal-content">
								<div class="modal-header">
									<img class="w-25" src="{{ url('images/logo-h-lg.png') }}">
									<span class="card-header-title text-danger ms-auto fs-4">@yield('title')</span>
								</div>

								<div class="modal-body">
									<div class="text-center">
										<span class="badge text-danger fsize-2 mb-3">@yield('error')</span>
									</div>
									<table class="table">
										<tr>
											<th>Configuration</th>
											<td>{{ $x=config('ldap.default') }}</td>
										</tr>
										<tr>
											<th>Host</th>
											<td>{{ ($y=collect(config('ldap.connections.'.$x.'.hosts')))->join(',') }} (IP: <strong>{!! $y->transform(function($item) { return collect(dns_get_record($item))->transform(function($item) { return Arr::get($item,'ip',Arr::get($item,'ipv6')); })->filter()->join('</strong>,<strong>'); })->join(',') !!}</strong>)</td>
										</tr>
										<tr>
											<th>Port</th>
											<td>{{ config('ldap.connections.'.$x.'.port') }}</td>
										</tr>
										<tr>
											<th>Message</th>
											<td>@yield('content')</td>
										</tr>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

</body>
</html>