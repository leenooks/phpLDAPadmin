<!DOCTYPE html>
<html translate="no">
	@section('htmlheader')
		@include('architect::layouts.partials.htmlheader')
	@show

	<body>
		<div class="app-container app-theme-white body-tabs-shadow fixed-sidebar">
			@include('architect::layouts.partials.topmenu')

			@includeIf('architect::layouts.partials.controlsidebar')

			<div class="app-main">
				@include('architect::layouts.partials.sidebarmenu')

				<div class="app-main__outer">
					<div class="app-main__inner">
						<div class="main-content">
							@if(trim($__env->yieldContent('page_title')))
								@include('architect::layouts.partials.contentheader')
							@endif

							<!-- Main content -->
							<div class="row">
								<div class="col-12">
									<!-- Your Page Content Here -->
									@yield('main-content')
								</div>
							</div>
						</div>
					</div>

					@include('architect::layouts.partials.contentfooter')
				</div>
			</div>
		</div>

		@yield('page-modals')

		@section('scripts')
			@include('architect::layouts.partials.scripts')

			@yield('page-scripts')
		@show
	</body>
</html>