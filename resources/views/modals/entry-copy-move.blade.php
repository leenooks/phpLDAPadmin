<div class="modal-header bg-dark text-white">
	<h1 class="modal-title fs-5">
		<i class="fas fa-fw fa-exclamation-triangle"></i> @lang('Rename') <strong>{{ $x=Crypt::decryptString($dn) }}</strong>
	</h1>
</div>

<div class="modal-body">
	<span>
		@lang('New') DN: <strong><span id="newdn" class="fs-4 opacity-50"><small class="fs-5">[@lang('Select Base')]</small></span></strong>
	</span>
	<br>
	<br>
	<form id="entry-rename-form" method="POST" action="{{ url('entry/copy-move') }}">
		@csrf
		<input type="hidden" name="_key" value="{{ Crypt::encryptString('*copy_move|'.$x) }}">
		<input type="hidden" name="to_dn" value="">

		<div class="row pb-3">
			<div class="col-4">
				<div class="form-check">
					<input class="form-check-input" type="checkbox" id="delete-checkbox" name="delete" value="1">
					<label class="form-check-label" for="delete-checkbox">
						<i class="fas fa-fw fa-trash"></i> @lang('Delete after Copy')
					</label>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-12">
				<div class="input-group mb-3">
					<span class="input-group-text" id="label">@lang('Select Base of Entry')</span>
					<input type="text" id="rdn" class="form-control d-none" style="width:20%;" placeholder="{{ $rdn=collect(explode(',',$x))->first() }}" value="{{ $rdn }}">
					<span class="input-group-text p-1 d-none">,</span>
					<select class="form-select w-25 d-none" id="rename-subbase" disabled style="width:30%;"></select>
					<span class="input-group-text p-1 d-none">,</span>
					<select class="form-select w-25" id="rename-base" style="width:30%;" disabled></select>
				</div>
			</div>
		</div>
	</form>
</div>

<div class="modal-footer">
	<x-modal.close/>
	<button id="entry-rename" type="button" class="btn btn-sm btn-primary">@lang('Copy')</button>
</div>

<script type="text/javascript">
	function refreshdn(value) {
		$('#newdn')
			.removeClass('opacity-50')
			.empty()
			.append(value);
	}

	$(document).ready(function() {
		var rdn = '{{ $rdn }}';
		var base = '';

		var that=$('#newdn');

		// Get our bases
		$.ajax({
			method: 'POST',
			url: '{{ url('ajax/subordinates') }}',
			dataType: 'json',
			cache: false,
			beforeSend: function() {
				that.empty().append('<span class="p-3"><i class="fas fa-xs fa-spinner fa-pulse"></i></span>');
			},
			success: function(data) {
				that.empty().html('<small class="fs-5">[@lang('Select Base')]</small>');

				$('#rename-base').children().remove();
				$('#rename-base').append('<option value=""></option>');
				data.forEach(function(item) {
					$('#rename-base').append('<option value="'+item.id+'">'+item.value+'</option>');
				});

				$('#rename-base').prop('disabled',false);
			},
			error: function(e) {
				if (e.status !== 412)
					alert('That didnt work? Please try again....');
			},
		});

		// The base DN container
		$('#rename-base').select2({
			dropdownParent: $('#page-modal'),
			theme: 'bootstrap-5',
			dropdownAutoWidth: true,
			width: 'style',
			allowClear: false,
			placeholder: 'Choose Base',
		})
			.on('change',function() {
				$(this).prev().removeClass('d-none');
				$('#rename-subbase').removeClass('d-none')
					.prev().removeClass('d-none')
					.prev().removeClass('d-none');
				$('#label').empty().append("@lang('Complete Path')");

				base = '';
				if (x=$('#rename-subbase option:selected').text())
					base += x+',';
				base += $('#rename-base option:selected').text();

				refreshdn(rdn+','+base);
				var newdn = '';

				$.ajax({
					method: 'POST',
					url:'{{ url('ajax/children') }}',
					data: {_key: $(this).val() },
					dataType: 'json',
					cache: false,
					beforeSend: function() {
						newdn = that.text();
						that.empty().append('<span class="p-3"><i class="fas fa-xs fa-spinner fa-pulse"></i></span>');
					},
					success: function(data) {
						that.empty().text(newdn);
						$('#rename-subbase').children().remove();
						$('#rename-subbase').append('<option value=""></option>');
						data.forEach(function(item) {
							$('#rename-subbase').append('<option value="'+item.item+'">'+item.title+'</option>');
						});

						$('#rename-subbase').prop('disabled',false);
					},
					error: function(e) {
						if (e.status !== 412)
							alert('That didnt work? Please try again....');
					},
				});
			});

		// Optional make a child a new branch
		$('#rename-subbase').select2({
			dropdownParent: $('#page-modal'),
			theme: 'bootstrap-5',
			dropdownAutoWidth: true,
			width: 'style',
			allowClear: true,
			placeholder: 'New Subordinate (optional)',
		})
			.on('change',function(item) {
				base = '';
				if (x=$('#rename-subbase option:selected').text())
					base += x+',';
				base += $('#rename-base option:selected').text();

				refreshdn(rdn+','+base);
			});

		// Complete the RDN
		$('#rdn').on('input',function(item) {
			rdn = $(this).val();
			refreshdn(rdn+','+base);

			$('button[id=entry-rename]').attr('disabled',! rdn.includes('='));
		})

		// The submit button text
		$('input#delete-checkbox').on('change',function() {
			$('button#entry-rename').html($(this).prop('checked') ? '{{ __('Move') }}' : '{{ __('Copy') }}');
		});

		// Submit
		$('button[id=entry-rename]').on('click',function() {
			$('input[name=to_dn]').val(rdn+','+base);
			$('form#entry-rename-form').submit();
		});
	});
</script>