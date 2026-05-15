<aside class="app-sidebar sidebar-shadow">
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
	<div class="scrollbar-sidebar">
		<div class="app-sidebar__inner">
			<ul class="vertical-nav-menu">
				<li class="app-sidebar__heading">{{ $server->name }}</li>
				<li>
					<i id="treeicon" class="metismenu-icon fa-fw fas fa-sitemap"></i>
					<span class="f16" id="tree"></span>
				</li>
			</ul>
		</div>
	</div>
	<div class="draghandle"></div>
</aside>

@section('page-scripts')
	<script type="text/javascript">
		/* Sidebar resize */
		var aside = $('aside.app-sidebar');

		$('aside .draghandle').on('mousedown',function(event) {
			// Ignore if closed
			if ($('.close-sidebar-btn').hasClass('is-active'))
				return;

			event.preventDefault();

			window.addEventListener('mousemove',resize,false);
			window.addEventListener('mouseup',stopResize,false);
		})

		$('.close-sidebar-btn:not(is-active)').on('click',function(event) {
			aside.css('width','');
			$('.app-header').css('margin-left','');
			$('main.app-main__outer').css('padding-left','');
		})

		function resize(e) {
			var mouseX = e.clientX - aside.offset().left;

			if (mouseX < 250) {
				aside.css('width','');
				$('.app-header').css('margin-left','');
				$('main.app-main__outer').css('padding-left','');

			} else {
				aside.css('width',mouseX+'px');
				$('.app-header').css('margin-left',mouseX+'px');
				$('main.app-main__outer').css('padding-left',mouseX+'px');
			}
		}

		function stopResize() {
			window.removeEventListener('mousemove',resize,false);
			window.removeEventListener('mouseup',stopResize,false);
		}
	</script>
@append