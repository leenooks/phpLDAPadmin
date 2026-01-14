<div class="modal-header bg-dark text-white">
	<h1 class="modal-title fs-5">
		<i class="fas fa-fw fa-users"></i> @isset($dn)@lang('Membership Maintenance for') <strong>{{ $x=Crypt::decryptString($dn) }}</strong>@else @lang('Add to New Entry') @endisset
	</h1>
</div>

<div class="modal-body">
	<div class="member-box">
		<label>@lang('Available Members')</label>
		<select class="form-control" id="source" multiple></select>
	</div>

	<div class="select-arrows text-center">
		<button type='button' id='btnAllRight' class="btn btn-default btn-outline-light m-1"><i class="fa fa-angle-double-right"></i></button>
		<button type='button' id='btnSwap' class="btn btn-default btn-outline-light m-1"><i class="fa fa-exchange"></i></button>
		<button type='button' id='btnAllLeft' class="btn btn-default btn-outline-light m-1"><i class="fa fa-angle-double-left"></i></button>
	</div>

	<div class="member-box">
		<label>@lang('Group Members')</label>
		<select class="form-control" id="destination" multiple></select>
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
	// Moves selected item(s) from sourceList to destinationList
	$.fn.moveToList = function(sourceList,destinationList) {
		var opts = $(sourceList + ' option:selected');
		if (opts.length == 0) {
			return $(this);
		}

		$(opts).remove();
		$(destinationList).append($(opts).clone());
		return $(this);
	};

	// Moves all items from sourceList to destinationList
	$.fn.moveAllToList = function(sourceList,destinationList) {
		var opts = $(sourceList+' option');
		if (opts.length) {
			$(opts).remove();
			$(destinationList).append($(opts).clone());
		}
	};

	$(document).ready(function() {
		// Populate the existing members
		$('attribute#'+modal_attr+' input[type=text]:not(.no-edit)')
			.filter((index,element)=>$(element).val())
			.each((index,element)=>
				$('select#destination').append(new Option($(element).val(),$(element).val(),false,false)));

		// Populate the potential members
		$.ajax({
			method: 'POST',
			url: '{{ url('ajax/member/member') }}',
			data: {
				existing: attribute_values(modal_attr),
				dn: dn,
			},
			dataType: 'json',
			cache: false,

		}).done(function(data) {
			data.forEach((item)=>$('select#source').append(new Option(item,item,false,false)));

		}).fail(ajax_error);

		$('select#source').on('dblclick',function(item) {
			$('select')
				.moveToList('#source','#destination');
		})

		$('select#destination').on('dblclick',function(item) {
			$('select')
				.moveToList('#destination','#source');
		})

		$('button#btnSwap').on('click',function(e) {
			$('select')
				.moveToList('#destination','#source')
				.moveToList('#source','#destination');

			e.preventDefault();
		});

		$('button#btnAllRight').on('click',function(e) {
			$('select').moveAllToList('#source','#destination');
			e.preventDefault();
		});

		$('button#btnAllLeft').on('click',function(e) {
			$('select').moveAllToList('#destination','#source');
			e.preventDefault();
		});

		$('input#member-filter').on('keyup',function(e) {
			filter($(this).val().toLowerCase());
		});

		var filter = _.debounce(function(filter) {
			$('select#source option').each(function() {
				var option = $(this).text().toLowerCase();

				$(this).toggle(option.indexOf(filter) > -1);
			});

			$('select#destination option').each(function() {
				var option = $(this).text().toLowerCase();

				$(this).toggle(option.indexOf(filter) > -1);
			});
		}, 500);
	});
</script>