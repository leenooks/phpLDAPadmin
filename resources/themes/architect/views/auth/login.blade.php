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
					<div class="mx-auto col-12 col-sm-8">
						<x-file-note file="login-note.html"/>

						<div class="modal-dialog modal-lg">
							<div class="modal-content">
								<form class="form-control needs-validation p-0" novalidate method="post">
									@csrf

									<div class="modal-body p-3">
										<div class="h5 modal-title text-center">
											<h4 class="mt-2">
												<div class="app-logo mx-auto mb-3"><img class="w-75" src="{{ url('images/logo-h-lg.png') }}"></div>
												<small>@lang('Sign in to') <strong>{{ $server->name }}</strong></small>
											</h4>
										</div>

										<div class="row">
											<div class="col-md-12 mt-3">
												<label class="mb-1">{{ login_attr_description() }}</label>
												<input name="{{ login_attr_name() }}" id="user" placeholder="" type="@if(in_array(login_attr_name(),['mail','email'])) email @else text @endif" class="form-control" required="">
												<div class="invalid-feedback">
													@lang('Please enter your '.strtolower(login_attr_description()))
												</div>
											</div>
										</div>

										<div class="row">
											<div class="col-md-12 mt-2">
												<label class="mb-1">@lang('Password')</label>
												<input name="password" id="password" placeholder="" type="password" class="form-control" required>
												<div class="invalid-feedback">
													@lang('Please enter your password')
												</div>
											</div>
										</div>

										@if(count($errors) > 0)
											<div class="row">
												<div class="col">
													<div class="alert alert-danger	 m-3">
														<strong>Whoops!</strong> Something went wrong?<br><br>
														<ul>
															@foreach($errors->all() as $error)
																<li>{{ $error }}</li>
															@endforeach
														</ul>
													</div>
												</div>
											</div>
										@endif
									</div>

									<div class="modal-footer p-2 border-top">
											<div class="row">
											<div class="col float-end">
												<button class="btn btn-lg btn-primary">Login</button>
											</div>
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
