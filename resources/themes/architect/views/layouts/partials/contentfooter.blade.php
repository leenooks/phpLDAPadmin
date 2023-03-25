<div class="app-wrapper-footer">
	<div class="app-footer">
		<div class="app-footer__inner">
			<div class="app-footer-left">
				<ul class="nav">
					<li class="nav-item">
						<strong>{{ config('app.version') }}</strong>
					</li>
					@if(($x=Config::get('update_available')) && $x->action !== 'current')
						<li class="nav-item ms-2">
							@switch($x->action)
								@case('unable')
									<abbr title="Upstream Version Unavailable"><i class="fas fa-exclamation text-alternate"></i></abbr>
									@break
								@case('upgrade')
									<abbr title="Update Available: {{ $x->version }}"><i class="fas fa-wrench text-danger"></i></abbr>
									@break
								@case('mismatch')
									<abbr title="Version Issue - Upstream {{ $x->version }}"><i class="fas fa-exclamation text-danger"></i></abbr>
									@break
								@case('unknown')
									<abbr title="Version Issue - Upstream {{ $x->version }}"><i class="fas fa-bolt text-alternate"></i></abbr>
									@break
							@endswitch
						</li>
					@endif
					{{--
					<li class="nav-item">
						<a href="javascript:void(0);" class="nav-link">Footer Link</a>
					</li>
					--}}
				</ul>
			</div>
			<div class="app-footer-right">
				<ul class="nav">
					{{--
					<li class="nav-item">
						<a href="javascript:void(0);" class="nav-link">
							<div class="badge badge-success me-1 ms-0">
								<small>NEW</small>
							</div>
							Footer Link
						</a>
					</li>
					--}}
				</ul>
			</div>
		</div>
	</div>
</div>