<div class="app-header header-shadow bg-dark header-text-light">
	<div class="app-header__logo">
		<div class="logo-src"></div>
		<div class="header__pane ms-auto">
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
			<button type="button" class="btn-icon btn-icon-only btn btn-sm btn-dark mobile-toggle-header-nav">
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
					<input type="text" class="search-input" id="search" placeholder="Type to search">
					<button class="search-icon">
						<span></span>
						<div id="searching" class="d-none"><i class="fas fa-fw fa-spinner fa-pulse text-light"></i></div>
					</button>
					<div id="search_results"></div>
				</div>
				<button class="btn-close"></button>
			</div>

			<ul class="header-menu nav server-icon">
				<li>
					<button id="link-info" class="btn btn-light p-1 m-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('Server Info')" data-link="{{ url('server/info') }}">
						<i class="fas fa-fw fa-info fs-5"></i>
					</button>
				</li>
				<li>
					<button id="link-schema" class="btn btn-light p-1 m-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('Schema Viewer')" data-link="{{ url('server/schema') }}">
						<i class="fas fa-fw fa-fingerprint fs-5"></i>
					</button>
				</li>
				<li>
					<button id="link-import" class="btn btn-light p-1 m-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('Import')" data-link="{{ url('entry/import') }}">
						<i class="fas fa-fw fa-upload fs-5"></i>
					</button>
				</li>
				<li>
					<button id="link-debug" class="btn btn-light p-1 m-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('Debug')" data-link="{{ url('debug') }}">
						<i class="fas fa-fw fa-toolbox fs-5"></i>
					</button>
				</li>
			</ul>
		</div>

		<div class="app-header-right">
			<ul class="header-menu nav">
				@if(! request()->isSecure())
					<li>
						<button class="btn btn-danger p-1 m-1" data-bs-custom-class="custom-tooltip-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="WARNING - SESSION NOT SECURE">
							<i class="fas fa-fw fa-unlock-keyhole fs-5"></i>
						</button>
					</li>
				@endif

				@if(($x=Config::get('update_available')) && $x->action !== 'current')
					<li>
						@switch($x->action)
							@case('unable')
								<button class="btn btn-light opacity-2 p-1 m-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Upstream Version Unavailable">
									<i class="fas fa-fw fa-bolt fs-5"></i>
								</button>
								@break
							@case('upgrade')
								<button class="btn btn-warning p-1 m-1" data-bs-custom-class="custom-tooltip-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-html="true" title="Update Available:<br>{{ $x->version }}">
									<i class="fas fa-fw fa-wrench fs-5"></i>
								</button>
								@break
							@case('mismatch')
								<button class="btn btn-light opacity-2 p-1 m-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-html="true" title="Version Issue - Upstream<br>{{ $x->version }}">
									<i class="fas fa-fw fa-exclamation fs-5"></i>
								</button>
								@break
							@case('unknown')
								<button class="btn btn-light opacity-2 p-1 m-1" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-html="true" title="Version Issue - Unknown<br>{{ $x->version }}">
									<i class="fas fa-fw fa-question fs-5"></i>
								</button>
								@break
						@endswitch
					</li>
				@endif
			</ul>

			<div class="header-btn-lg pe-0">
				<div class="widget-content p-0">
					<div class="widget-content-wrapper">
						{{--
						<div class="widget-content-left">
							<div class="btn-group">
								<a data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="p-0 btn">
									<img width="42" class="rounded-circle" src="assets/images/avatars/1.jpg" alt="">
									<i class="fa fa-angle-down ms-2 opacity-8"></i>
								</a>
								<div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right">
									<button type="button" tabindex="0" class="dropdown-item">User Account</button>
									<button type="button" tabindex="0" class="dropdown-item">Settings</button>
									<h6 tabindex="-1" class="dropdown-header">Header</h6>
									<button type="button" tabindex="0" class="dropdown-item">Actions</button>
									<div tabindex="-1" class="dropdown-divider"></div>
									<button type="button" tabindex="0" class="dropdown-item">Dividers</button>
								</div>
							</div>
						</div>
						--}}
						<div class="widget-content-left header-user-info ms-3">
							<div class="widget-heading">
								{{ $user->exists ? Arr::get($user->getAttribute('cn'),0,Arr::get($user->getAttribute('entryuuid'),0,'Secret Person')) : 'Anonymous' }}
							</div>
							<div class="widget-subheading">
								{{ $user->exists ? Arr::get($user->getAttribute('mail'),0,'') : '' }}
							</div>
						</div>

						<div class="widget-content-left">
							<div class="btn-group">
								<a data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="p-0 btn">
									<i class="fas fa-angle-down ms-2 opacity-8"></i>
									<img width="35" height="35" class="rounded-circle p-1 bg-light" src="{{ url('user/image') }}" alt="">
								</a>
								<div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right">
									@if($user->exists)
										<h6 tabindex="-1" class="dropdown-header text-center">User Menu</h6>
										<div tabindex="-1" class="dropdown-divider"></div>
										<a href="{{ url('logout') }}" tabindex="0" class="dropdown-item">
											<i class="fas fa-fw fa-sign-out-alt me-2"></i> Sign Out
										</a>
									@else
										<a href="{{ url('login') }}" tabindex="0" class="dropdown-item">
											<i class="fas fa-fw fa-sign-in-alt me-2"></i> Sign In
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

@section('page-scripts')
	<script type="text/javascript">
		$(document).ready(function() {
			$('button[id^="link-"]').on('click',function(item) {
				var content;

				// Remove our fancy-tree highlight, since we are rendering the frame
				$('.fancytree-node.fancytree-active').removeClass('fancytree-active');

				$.ajax({
					url: $(this).data('link'),
					method: 'GET',
					dataType: 'html',
					statusCode: {
						404: function() {
							$('.main-content').empty().append(content);
						}
					},
					beforeSend: function() {
						content = $('.main-content').contents();
						$('.main-content').empty().append('<div class="fa-3x"><i class="fas fa-spinner fa-pulse"></i></div>');
					}

				}).done(function(html) {
					$('.main-content').empty().append(html);

				}).fail(function() {
					alert('Well that didnt work?');
				});

				item.stopPropagation();

				return false;
			});

			$('.search-wrapper input[id="search"]').typeahead({
				autoSelect: false,
				scrollHeight: 10,
				theme: 'bootstrap5',
				delay: 500,
				minLength: 2,
				items: {{ $search_limit ?? 100 }},
				selectOnBlur: false,
				appendTo: "#search_results",
				source: function(query,process) {
					search('{{ url('search') }}',query,process);
				},
				// Disable sorting and just return the items (items should be sorted by the ajax method)
				sorter: function(items) {
					return items;
				},
				matcher: function() { return true; },
				// Disable sorting and just return the items (items should by the ajax method)
				updater: function(item) {
					// If item has a data value, then we'll use that
					if (item.data && item.data.length)
						return item.data;

					if (! item.value)
						return item.name+'=';

					location.replace('/#'+item.value);
					location.reload();
					return '';
				},
			})
				.on('keyup keypress',function(event) {
					var key = event.keyCode || event.which;
					if (key === 13) {
						event.preventDefault();
						return false;
					}
				});
		});

		var search = _.debounce(function(url,query,process){
			$.ajax({
				url : url,
				type : 'POST',
				data : 'term=' + query,
				dataType : 'JSON',
				async : true,
				cache : false,
				beforeSend : function() {
					$('.search-wrapper div#searching').removeClass('d-none');
					$('.search-wrapper .search-icon span').addClass('d-none');
				},
				success : function(data) {
					// if json is null, means no match, won't do again.
					if(data==null || (data.length===0)) return;

					process(data);
				},
				complete : function() {
					$('.search-wrapper div#searching').addClass('d-none');
					$('.search-wrapper .search-icon span').removeClass('d-none');
				}
			})
		}, 500);
	</script>
@append