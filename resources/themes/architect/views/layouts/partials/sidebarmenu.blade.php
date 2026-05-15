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

		/* Sidebar resize */
		var aside = $('aside.app-sidebar');

		$(document).ready(function() {
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

			// If our bases have been set, we'll render them directly
			if (typeof basedn !== 'undefined') {
				sources = basedn;
			} else {
				sources = { method: 'POST', url: web_base+'/ajax/bases' };
			}

			// Attach the fancytree widget to an existing <div id="tree"> element
			// and pass the tree options as an argument to the fancytree() function:
			$('#tree').fancytree({
				clickFolderMode: 3,	// 1:activate, 2:expand, 3:activate and expand, 4:activate (dblclick expands)
				extensions: ['persist'],
				autoCollapse: true, // Automatically collapse all siblings, when a node is expanded.
				autoScroll: true, // Automatically scroll nodes into visible area.
				focusOnSelect: true, // Set focus when node is checked by a mouse click
				persist: {
					// Available options with their default:
					cookieDelimiter: '~',    // character used to join key strings
					cookiePrefix: 'pla-<treeId>-', // 'fancytree-<treeId>-' by default
					cookie: { // settings passed to jquery.cookie plugin
						raw: false,
						expires: '',
						path: '',
						domain: '',
						secure: false
					},
					expandLazy: true, // true: recursively expand and load lazy nodes
					expandOpts: undefined, // optional `opts` argument passed to setExpanded()
					fireActivate: false, //
					overrideSource: true,  // true: cookie takes precedence over `source` data attributes.
					store: 'auto',     // 'cookie': use cookie, 'local': use localStore, 'session': use sessionStore
					types: 'active expanded focus selected'  // which status types to store
				},
				click: function(event,data) {
					if (data.targetType === 'title' && data.node.data.item)
						get_frame(data.node.data.item);
				},
				source: sources,
				lazyLoad: function(event,data) {
					data.result = {
						method: 'POST',
						url: web_base+'/ajax/children',
						data: {_key: data.node.data.item,create: true},
						error: function(e) {
							if (e.status === 419) {	// Session Expired
								window.location.reload();
							}
						}
					};

					expandChildren(data.tree.rootNode);
				},
				keydown: function(event,data){
					switch( $.ui.fancytree.eventToString(data.originalEvent) ) {
						case 'return':
						case 'space':
							data.node.toggleExpanded();
							break;
					}
				}
			});
		});
	</script>
@append