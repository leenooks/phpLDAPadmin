@if (trim($__env->yieldContent('page_title')))
	@include('architect::layouts.partials.contentheader')
@endif

<!-- Main content -->
<div class="row">
	<div class="col-12">
		<!-- Your Page Content Here -->
		@yield('main-content')
	</div>
</div>

@yield('page-modals')
@yield('page-scripts')