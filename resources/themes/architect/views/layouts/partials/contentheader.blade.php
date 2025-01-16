<div class="app-page-title">
	<div class="page-title-wrapper">
		<div class="page-title-heading">
			@if (trim($__env->yieldContent('page_icon')))
				<div class="page-title-icon f32">
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

		@if (isset($page_actions) || old())
			<div class="page-title-actions">
				<div class="page-title-actions">
					<div class="d-inline-block dropdown">
						<button type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="dropdown-toggle btn btn-primary">
							<span class="btn-icon-wrapper pe-2 opacity-7">
								<i class="fa fa-business-time fa-w-20"></i>
							</span>
							@lang('Entry Options')
						</button>

						<div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right">
							<ul class="nav flex-column">
								@if ((isset($page_actions) && $page_actions->contains('edit')) || old())
									<li class="nav-item">
										<span class="nav-link pt-0 pb-1">
											<button id="entry-edit" class="p-2 m-0 border-0 btn-transition btn btn-outline-dark w-100 text-start">
												<i class="fas fa-fw fa-edit me-2"></i> @lang('Edit')
											</button>
										</span>
									</li>
								@endif

								@if (isset($page_actions) && $page_actions->contains('export'))
									<li class="nav-item">
										<a class="nav-link pt-0 pb-1">
											<button type="button" class="p-2 m-0 border-0 btn-transition btn btn-outline-dark w-100 text-start" data-bs-toggle="modal" data-bs-target="#entry-export-modal" {{--data-bs-whatever="ldif"--}}>
												<i class="fas fa-fw fa-file-export me-2"></i> @lang('Export')
											</button>
										</a>
									</li>
								@endif

								@if (isset($page_actions) && $page_actions->contains('copy'))
									<li class="nav-item">
										<a class="nav-link pt-0 pb-1">
											<button class="p-2 m-0 border-0 btn-transition btn btn-outline-dark w-100 text-start">
												<i class="fas fa-fw fa-truck-moving me-2"></i> @lang('Copy or Move')
											</button>
										</a>
									</li>
								@endif
							</ul>
						</div>
					</div>
				</div>
			</div>
		@endif
	</div>
</div>