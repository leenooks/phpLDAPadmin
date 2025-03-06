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
			<div class="row">
				<div class="col">
					<div class="action-buttons float-end">
						<ul class="nav">
							@if(isset($page_actions) && $page_actions->contains('export'))
								<li>
									<span data-bs-toggle="modal" data-bs-target="#entry_export-modal">
										<button class="btn btn-outline-dark p-1 m-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('Export')"><i class="fas fa-fw fa-download fs-5"></i></button>
									</span>
								</li>
							@endif
							@if(isset($page_actions) && $page_actions->contains('copy'))
								<li>
									<button class="btn btn-outline-dark p-1 m-1" id="entry-copy-move" data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('Copy/Move')"><i class="fas fa-fw fa-copy fs-5"></i></button>
								</li>
							@endif
							@if((isset($page_actions) && $page_actions->contains('edit')) || old())
								<li>
									<button class="btn btn-outline-dark p-1 m-1" id="entry-edit" data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('Edit Entry')"><i class="fas fa-fw fa-edit fs-5"></i></button>
								</li>
							@endif
							<!-- @todo Dont offer the delete button for an entry with children -->
							@if(isset($page_actions) && $page_actions->contains('delete'))
								<li>
									<span id="entry-delete" data-bs-toggle="modal" data-bs-target="#page-modal">
										<button class="btn btn-outline-danger p-1 m-1" data-bs-custom-class="custom-tooltip-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('Delete Entry')"><i class="fas fa-fw fa-trash-can fs-5"></i></button>
									</span>
								</li>
							@endif
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@section('page-scripts')
	<script type="text/javascript">
		$(document).ready(function() {
			$('button[id=entry-edit]').on('click',function(item) {
				item.preventDefault();

				if ($(this).hasClass('btn-dark'))
					return;

				editmode();
			});
		});
	</script>
@append