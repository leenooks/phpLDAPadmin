@php
	$o = $server->fetch(Crypt::decryptString($dn))
@endphp
<div class="modal-header bg-dark text-white">
	<h1 class="modal-title fs-5">Check Passwords for {{ $o->getDN() }}</h1>
</div>

<div class="modal-body">
	<table class="table table-bordered p-1">
		@foreach(($up=$o->getObject('userpassword'))->values as $key => $value)
			<tr>
				<th>Check</th>
				<td>{{ $up->render_item_old($key) }}</td>
				<td>
					<input type="password" style="width: 90%" name="password[{{$key}}]"> <i class="fas fa-fw fa-lock"></i>
					<div class="invalid-feedback pb-2">
						@lang('Invalid Password')
					</div>
				</td>
			</tr>
		@endforeach
	</table>
</div>

<div class="modal-footer">
	<x-modal.close/>
	<button id="userpassword_check-submit" type="button" class="btn btn-sm btn-primary"><i class="fas fa-fw fa-spinner fa-spin d-none"></i> @lang('Check')</button>
</div>

<script type="text/javascript">
	$('button[id=userpassword_check-submit]').on('click',function(item) {
		var that = $(this);

		var passwords = $('#page-modal')
			.find('input[name^="password["')
			.map((key,item)=>item.value);

		if (passwords.length === 0) return false;

		$.ajax({
			method: 'POST',
			url: '{{ url('entry/password/check') }}',
			data: {
				dn: dn,
				password: Array.from(passwords),
			},
			dataType: 'json',
			cache: false,
			beforeSend: function() {
				// Disable submit, add spinning icon
				that.prop('disabled',true);
				that.find('i').removeClass('d-none');
			},
			complete: function() {
				that.prop('disabled',false);
				that.find('i').addClass('d-none');
			},
			success: function(data) {
				data.forEach(function(item,key) {
					var i = $('#page-modal')
						.find('input[name="password['+key+']')
						.siblings('i');

					var feedback = $('#page-modal')
						.find('input[name="password['+key+']')
						.siblings('div.invalid-feedback');

					if (item === 'OK') {
						i.removeClass('text-danger').addClass('text-success').removeClass('fa-lock').addClass('fa-lock-open');
						if (feedback.is(':visible'))
							feedback.hide();

					} else {
						i.removeClass('text-success').addClass('text-danger').removeClass('fa-lock-open').addClass('fa-lock');
						if (! feedback.is(':visible'))
							feedback.show();
					}
				})
			},
			error: function(e) {
				if (e.status !== 412)
					alert('That didnt work? Please try again....');
			},
		})
	});
</script>