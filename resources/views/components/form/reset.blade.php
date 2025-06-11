<button id="form-reset" class="btn btn-sm btn-outline-danger">@lang('Reset')</button>

@section('page-scripts')
	<script>
		$(document).ready(function() {
			$('#form-reset').on('click',()=>$('#{{$form}}')[0].reset());
		});
	</script>
@append