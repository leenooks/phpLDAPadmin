@extends('adminlte::layouts.auth')

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

	<div class="login-box m-auto">
		<div class="login-logo">
			<a>{!! config('app.name_html_long') !!}</a>
		</div>

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
		<div class="card">
			<div class="card-body login-card-body">
				<p class="login-box-msg">{{ trans('adminlte_lang::message.siginsession') }}</p>

				<form method="post">
					{{ csrf_field() }}

					<div class="row">
						<div class="col-12">
							<div class="input-group mb-3">
								<input type="email" name="{{ config('ldap_auth.identifiers.ldap.locate_users_by') }}" class="form-control" placeholder="Email">
								<div class="input-group-append">
									<span class="input-group-text"><i class="fas fa-envelope fa-fw"></i></span>
								</div>
							</div>
						</div>

						<div class="col-12">
							<div class="input-group mb-3">
								<input type="password" name="password" class="form-control" placeholder="Password">
								<div class="input-group-append">
									<span class="input-group-text"><i class="fas fa-key fa-fw"></i></span>
								</div>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-8">
							<div class="checkbox icheck">
								<label>
									<input type="checkbox" name="remember"> Remember Me
								</label>
							</div>
						</div>

						<!-- /.col -->
						<div class="col-4">
							<button type="submit" name="submit" class="btn btn-primary mr-0 float-right">Sign In</button>
						</div>
						<!-- /.col -->
					</div>
				</form>

				@if(count(config('auth.social',[])))
					@include('adminlte::auth.partials.social_login')
				@endif

				<p class="mb-1">
					<a name="reset" href="{{ url('password/reset') }}">{{ trans('adminlte_lang::message.forgotpassword') }}</a>
				</p>

				@isset($register)
					<p class="mb-0">
						<a href="{{ url('register') }}" class="text-center">{{ trans('adminlte_lang::message.register') }}</a>
					</p>
				@endisset
			</div>
			<!-- /.login-card-body -->
		</div>
	</div>
	<!-- /.login-box -->
@endsection
