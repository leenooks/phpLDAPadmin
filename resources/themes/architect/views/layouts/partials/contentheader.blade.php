<div class="app-page-title">
	<div class="page-title-wrapper">
		<div class="page-title-heading">
			@if (trim($__env->yieldContent('page_icon')))
				<div class="page-title-icon f32">
					<i class="@yield('page_icon','')"></i>
				</div>
			@endif

			@yield('page_title','Page Title')
			<div class="page-title-subheading">
				@yield('page_subtitle','')
			</div>
		</div>

		<div class="page-title-actions">
			@yield('page_actions')
		</div>
	</div>
</div>