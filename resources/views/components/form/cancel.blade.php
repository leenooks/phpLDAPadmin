<button id="cancel" class="btn btn-sm btn-outline-dark">Cancel</button>

@section('page-scripts')
	<script type="text/javascript">
		$(document).ready(function() {
			$('#cancel').on('click',()=>history.back());
		});
	</script>
@append