<button id="form-submit" class="btn btn-sm btn-success">@lang($action)</button>

@section('page-scripts')
	<script type="text/javascript">
		$(document).ready(function() {
			$('#form-submit').on('click',()=>$('#{{$form}}')[0].submit());
		});
	</script>
@append