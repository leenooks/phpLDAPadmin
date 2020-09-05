@extends('architect::layouts.auth')

@section('htmlheader_title')
	Log in
@endsection

@section('content')
	@if(isset($login_note) AND $login_note)
		<div class="alert alert-info alert-dismissible m-auto">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			<h5><i class="icon fas fa-info"></i> NOTE!</h5>
			{!! $login_note !!}
		</div>
		<br>
	@endisset

	@if (count($errors) > 0)
		<div class="alert alert-danger">
			<strong>Whoops!</strong> {{ trans('adminlte_lang::message.someproblems') }}<br><br>
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	@if (Session::has('error'))
		<div class="alert alert-danger">
			<strong>Whoops!</strong> {{ trans('adminlte_lang::message.someproblems') }}<br><br>
			<ul>
				<li>{{ Session::get('error') }}</li>
			</ul>
		</div>
	@endif

	<!-- /.login-logo -->
	<div class="app-container app-theme-white body-tabs-shadow">
		<div class="app-container">
			<div class="h-100 bg-animation">
				<div class="d-flex h-100 justify-content-center align-items-center">
					<div class="mx-auto app-login-box col-md-8">
						<div class="modal-dialog w-100 mx-auto">
							<div class="modal-content">
									<form method="post">
										{{ csrf_field() }}
								<div class="modal-body">
									<div class="h5 modal-title text-center">
										<h4 class="mt-2">
											<div class="app-logo mx-auto mb-3"><img class="w-75" src="{{ url('img/logo-h-lg.png') }}"></div>
											<small>Please sign in to your account below.</small>
										</h4>
									</div>

										<div class="form-row">
											<div class="col-md-12">
												<div class="position-relative form-group">
													<input name="{{ config('ldap_auth.identifiers.ldap.locate_users_by') }}" id="user" placeholder="Email..." type="email" class="form-control">
												</div>
											</div>
											<div class="col-md-12">
												<div class="position-relative form-group">
													<input name="password" id="password" placeholder="Password..." type="password" class="form-control">
												</div>
											</div>
										</div>
									{{--
									<div class="divider"></div>
									<h6 class="mb-0">No account? <a href="javascript:void(0);" class="text-primary">Sign up now</a></h6>
									--}}
								</div>
								<div class="modal-footer">
									{{--
									<div class="float-left">
										<a href="javascript:void(0);" class="btn-lg btn btn-link">Recover Password</a>
									</div>
									--}}
									<div class="float-right">
										<button class="btn btn-primary btn-lg">Login</button>
									</div>
								</div>
									</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
