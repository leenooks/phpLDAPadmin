<div class="app-page-title">
	<div class="page-title-wrapper">
		<div class="page-title-heading">
			@if (trim($__env->yieldContent('page_icon')))
				<div class="page-title-icon">
					<i class="@yield('page_icon','')"></i>
				</div>
			@endif
			<div>
				@yield('page_title','Page Title')
				<div class="page-title-subheading">
					@yield('page_subtitle','')
				</div>
			</div>
		</div>

		@isset($page_actions)
			<div class="page-title-actions">
				{{--
				<button type="button" data-toggle="tooltip" title="Example Tooltip" data-placement="bottom" class="btn-shadow mr-3 btn btn-dark">
					<i class="fa fa-star"></i>
				</button>
				--}}
				<div class="d-inline-block dropdown">
					<button type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn-shadow dropdown-toggle btn btn-info">
						<span class="btn-icon-wrapper pr-2 opacity-7">
							<i class="fa fa-business-time fa-w-20"></i>
						</span>
						Item Menu
					</button>

					<div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right">
						<ul class="nav flex-column">
							{{--
							<li class="nav-item">
								<a href="javascript:void(0);" class="nav-link">
									<i class="nav-link-icon lnr-inbox"></i>
									<span>Inbox</span>
									<div class="ml-auto badge badge-pill badge-secondary">86</div>
								</a>
							</li>
							--}}
						</ul>
					</div>
				</div>
			</div>
		@endif
	</div>
</div>
