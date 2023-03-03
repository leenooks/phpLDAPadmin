<script src="{{ asset('/js/app.js') }}" type="text/javascript"></script>
<!-- bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.2.1/dist/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
<!-- Architect -->
<script src="{{ asset('/js/architect.min.js') }}"></script>
<!-- Select2 -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script type="text/javascript">
	// Our CSRF token to each interaction
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$.ArchitectSidebarTweak = {};
	$.ArchitectSidebarTweak.Open = true;
	$.ArchitectSidebarTweak.ResizeDelay = 1000;

	$.ArchitectSidebarTweak.options = {
		EnableRemember: true,
		//Removes the transition after page reload.
		NoTransitionAfterReload: false
	};

	// Work out our timezone.
	const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;

	/**
	 * Open and close the sidebar
	 * @param state
	 * @param addclass
	 */
	function sidebaropenclose(state,addclass) {
		// Dont do anything if the state hasnt changed.
		if (state == $.ArchitectSidebarTweak.Open) {
			return;
		}

		$.ArchitectSidebarTweak.Open = state;

		if (addclass !== 'undefined')
			if (state)
				$(".app-container")
					.removeClass('closed-sidebar');
			else
				$(".app-container")
					.addClass('closed-sidebar');

		$(this).delay($.ArchitectSidebarTweak.ResizeDelay).queue(function() {
			window.dispatchEvent(new Event('resize'));
			$(this).dequeue();
		});
	}

	$(function () {
		"use strict";

		// @todo This event were for AdminLTE.
		$("body").on("collapsed.lte.pushmenu", function(){
			if($.ArchitectSidebarTweak.options.EnableRemember) {
				document.cookie = "toggleState=closed;path=/";

				$("body").queue(sidebaropenclose(false));
			}
		});

		$("body").on("shown.lte.pushmenu", function(){
			if($.ArchitectSidebarTweak.options.EnableRemember){
				document.cookie = "toggleState=opened;path=/";

				$("body").queue(sidebaropenclose(true));
			}
		});

		if($.ArchitectSidebarTweak.options.EnableRemember){
			var re = new RegExp('toggleState' + "=([^;]+)");
			var value = re.exec(document.cookie);
			var toggleState = (value != null) ? unescape(value[1]) : null;
			if(toggleState == 'closed'){
				if($.ArchitectSidebarTweak.options.NoTransitionAfterReload){
					$("body").addClass('sidebar-collapse hold-transition').delay(100).queue(function(){
						$(this).removeClass('hold-transition');
					});
				}else{
					$("body").queue(sidebaropenclose(false,true));
				}
			}
		}
	});
</script>

<!-- Fancy Tree -->
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.fancytree/2.36.1/jquery.fancytree-all.min.js"></script>

@if(file_exists('js/custom.js'))
	<!-- Any Custom JS -->
	<script src="{{ asset('js/custom.js') }}"></script>
@endif
