<div class="modal-header bg-dark text-white">
	<h1 class="modal-title fs-5">
		<i class="fas fa-fw fa-users"></i> @isset($dn)@lang('Attribute values for') <strong>{{ $x=Crypt::decryptString($dn) }}</strong>@else @lang('Add to New Entry') @endisset
	</h1>
</div>

<div class="modal-body">
	<div class="member-box w-100">
		<label id="attr"></label>
		<select class="form-control" id="source" multiple></select>
	</div>

	<div class="input-group">
		<input type="text" class="form-control mt-3" id="member-filter" placeholder="@lang('Filter Members')">
	</div>
</div>

<div class="modal-footer">
	<x-modal.close/>
</div>

<!-- JS:member-manage -->
<script type="text/javascript">
	$(document).ready(function() {
		$('label#attr').append(modal_attr)
		console.log('modal_tag:',modal_tag);

		// Populate the existing members
		$('div#template-default attribute#'+modal_attr+' input[type=text][name^="'+modal_attr+'['+modal_tag+']"]:not(.no-edit)')
			.filter((index,element)=>$(element).val())
			.each((index,element)=>
				$('select#source').append(new Option($(element).val(),$(element).val())));

		$('input#member-filter').on('keyup',function(e) {
			filter($(this).val().toLowerCase());
		});

	});

	var filter = _.debounce(function(filter) {
		$('select#source option').each(function() {
			var option = $(this).text().toLowerCase();

			$(this).toggle(option.indexOf(filter) > -1);
		});
	}, 500);
</script>