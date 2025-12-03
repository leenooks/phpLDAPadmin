<div class="modal-header bg-dark text-white">
	<h1 class="modal-title fs-5">
		<i class="fas fa-fw fa-users"></i> @lang('Membership Maintenance for') <strong>{{ $x=Crypt::decryptString($dn) }}</strong>
	</h1>
</div>

<div class="modal-body">
	<div class="member-box">
		<label>@lang('Group Members')</label>
		<select class="form-control" id="destination" multiple></select>
	</div>

	<div class="select-arrows text-center">
		<button type='button' id='btnAllRight' class="btn btn-default btn-outline-light m-1"><i class="fa fa-angle-double-right"></i></button>
		<button type='button' id='btnSwap' class="btn btn-default btn-outline-light m-1"><i class="fa fa-exchange"></i></button>
		<button type='button' id='btnAllLeft' class="btn btn-default btn-outline-light m-1"><i class="fa fa-angle-double-left"></i></button>
	</div>

	<div class="member-box">
		<label>@lang('Available Members')</label>
		<select class="form-control" id="source" multiple></select>
	</div>
</div>

<div class="modal-footer">
	<x-modal.close/>
</div>

<style>
	.member-box {
		float: left;
		width: 45%;
		label {
			font-weight: bold;
			padding-bottom: 5px;
		}
		select {
			height: 25em;
			padding: 0;
			option {
				padding: 4px 10px 4px 10px;
			}
			option:hover {
				background: var(--bs-light);
			}
		}
	}

	.select-arrows {
		float: left;
		width: 10%;
		padding-top: 5em;
		input {
			width: 70%;
			margin-bottom: 5px;
		}
	}
</style>

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
		$('attribute#member input[type=text]')
			.each((index,element)=>
				$('select#destination').append(new Option($(element).val(),$(element).val(),false,false)));

		// Populate the potential members
		$.ajax({
			method: 'POST',
			url: '{{ url('ajax/member/member') }}',
			data: {
				existing: attribute_values('member'),
				dn: dn,
			},
			dataType: 'json',
			cache: false,
			success: function(data) {
				data.forEach((item)=>$('select#source').append(new Option(item,item,false,false)));
			},
			error: ajax_error,
		})

		$('#btnSwap').click(function(e) {
			$('select')
				.moveToList('#destination','#source')
				.moveToList('#source','#destination');

			e.preventDefault();
		});

		$('#btnAllRight').click(function(e) {
			$('select').moveAllToList('#destination','#source');
			e.preventDefault();
		});

		$('#btnAllLeft').click(function(e) {
			$('select').moveAllToList('#source','#destination');
			e.preventDefault();
		});
	});
</script>