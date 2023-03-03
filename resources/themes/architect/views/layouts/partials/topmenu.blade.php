<div class="app-header header-shadow bg-dark header-text-light">
	<div class="app-header__logo">
		<div class="logo-src"></div>
		<div class="header__pane ml-auto">
			<div>
				<button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
					<span class="hamburger-box">
						<span class="hamburger-inner"></span>
					</span>
				</button>
			</div>
		</div>
	</div>
	<div class="app-header__mobile-menu">
		<div>
			<button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
				<span class="hamburger-box">
					<span class="hamburger-inner"></span>
				</span>
			</button>
		</div>
	</div>
	<div class="app-header__menu">
		<span>
			<button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
				<span class="btn-icon-wrapper">
					<i class="fas fa-ellipsis-v fa-w-6"></i>
				</span>
			</button>
		</span>
	</div>
	<div class="app-header__content">
		<div class="app-header-left">
			<div class="search-wrapper">
				<div class="input-holder">
					<input type="text" class="search-input" placeholder="Type to search">
					<button class="search-icon"><span></span></button>
				</div>
				<button class="close"></button>
			</div>

			<ul class="header-menu nav">
				{{--
				<li class="nav-item">
					<a href="javascript:void(0);" class="nav-link">
						<i class="nav-link-icon fas fa-database"></i> Link
					</a>
				</li>
				--}}
			</ul>
		</div>

		<div class="app-header-right">
			@if(! request()->isSecure())
				<span class="badge badge-danger">WARNING - SESSION NOT SECURE</span>
			@endif

			<div class="header-btn-lg pr-0">
				<div class="widget-content p-0">
					<div class="widget-content-wrapper">
						<div class="widget-content-left ml-3 header-user-info">
							<div class="widget-heading">
								{{ $user->exists ? Arr::get($user->getAttribute('cn'),0,'Anonymous') : 'Anonymous' }}
							</div>
							<div class="widget-subheading">
								{{ $user->exists ? Arr::get($user->getAttribute('mail'),0,'') : '' }}
							</div>
						</div>
						<div class="widget-content-right">
							<div class="btn-group">
								<a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="p-0 btn">
									<i class="fas fa-angle-down ml-2 opacity-8"></i>
									<img width="35" height="35" class="rounded" src="{{ url('user/image') }}" alt="" style="background-color: #eee;padding: 2px;">
								</a>
								<div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right">
									@if ($user->exists)
										<h6 tabindex="-1" class="dropdown-header text-center">User Menu</h6>
										<div tabindex="-1" class="dropdown-divider"></div>
										<a href="{{ url('logout') }}" tabindex="0" class="dropdown-item">
											<i class="fas fa-fw fa-sign-out-alt mr-2"></i> Sign Out
										</a>
									@else
										<a href="{{ url('login') }}" tabindex="0" class="dropdown-item">
											<i class="fas fa-fw fa-sign-in-alt mr-2"></i> Sign In
										</a>
									@endif
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
