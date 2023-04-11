@extends('architect::layouts.auth')

@section('htmlheader_title')
	Log in
@endsection

@section('content')
	<!-- /.login-logo -->
	<div class="app-container app-theme-white body-tabs-shadow">
		<div class="app-container">
			<div class="h-100 bg-animation">
				<div class="d-flex h-100 justify-content-center align-items-center">
					<div class="mx-auto app-login-box col-md-8">
						@if(file_exists('login-note.html'))
							<div class="mx-auto card text-white card-body bg-primary w-50">
								<h5 class="text-white card-title"><i class="icon fa-2x fas fa-info pe-3"></i><span class="font-size-xlg">NOTE</span></h5>
								<span class="w-100 pb-0">
									{!! file_get_contents('login-note.html') !!}
								</span>
							</div>
						@endif

						<div class="modal-dialog w-100 mx-auto">
							<div class="modal-content">
								<form class="needs-validation" novalidate method="post">
									{{ csrf_field() }}

									<div class="modal-body">
										<div class="h5 modal-title text-center">
											<h4 class="mt-2">
												<div class="app-logo mx-auto mb-3"><img class="w-75" src="{{ url('images/logo-h-lg.png') }}"></div>
												<small>@lang('Sign in to <strong>:server</strong>',['server'=>config('ldap.connections.default.name')])</small>
											</h4>
										</div>

										<div class="form-row">
											<div class="col-md-12 mt-3">
												<label class="mb-1">Email</label>
												<input name="email" id="user" placeholder="" type="email" class="form-control" required="">
												<div class="invalid-feedback">
													@lang('Please enter your email')
												</div>
											</div>

											<div class="col-md-12 mt-2">
												<label class="mb-1">@lang('Password')</label>
												<input name="password" id="password" placeholder="" type="password" class="form-control" required>
												<div class="invalid-feedback">
													@lang('Please enter your password')
												</div>
											</div>
										</div>
									</div>

									<div class="modal-footer">
										@if (count($errors) > 0)
											<div class="alert alert-danger w-100">
												<strong>Whoops!</strong> Something went wrong?<br><br>
												<ul>
													@foreach ($errors->all() as $error)
														<li>{{ $error }}</li>
													@endforeach
												</ul>
											</div>
										@endif
										<div class="float-end">
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

@section('page-scripts')
	<style>
		label {
			text-transform: uppercase;
			letter-spacing: 0.05em;
			font-size: 85%;
			font-weight: bold;
		}

		table.table tr:last-child {
			border-bottom: 1px solid #e9ecef;
		}
	</style>

	<script type="text/javascript">
		// Example starter JavaScript for disabling form submissions if there are invalid fields
		(function () {
			'use strict';
			window.addEventListener('load',function () {
				// Fetch all the forms we want to apply custom Bootstrap validation styles to
				var forms = document.getElementsByClassName('needs-validation');
				// Loop over them and prevent submission
				var validation = Array.prototype.filter.call(forms, function (form) {
					form.addEventListener('submit', function (event) {
						if (form.checkValidity() === false) {
							event.preventDefault();
							event.stopPropagation();
						}
						form.classList.add('was-validated');
					}, false);
				});
			}, false);
		})();
	</script>
@append
