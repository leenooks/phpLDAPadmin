@extends('layouts.dn')

@section('page_title')
	@include('fragment.dn.header',['o'=>($o ?? $o=config('server')->fetch($dn))])
@endsection

@section('main-content')
	<x-note/>
	<x-updated/>
	<x-error/>

	<div class="main-card mb-3 card">
		<div class="card-body">
			<div class="card-header-tabs">
				<ul class="nav nav-tabs">
					<li class="nav-item"><a data-bs-toggle="tab" href="#attributes" class="nav-link active">@lang('Attributes')</a></li>
					<li class="nav-item"><a data-bs-toggle="tab" href="#internal" class="nav-link">@lang('Internal')</a></li>
					@env(['local'])
						<li class="nav-item"><a data-bs-toggle="tab" href="#debug" class="nav-link">@lang('Debug')</a></li>
					@endenv
				</ul>

				<div class="tab-content">
					<!-- All Attributes -->
					<div class="tab-pane active" id="attributes" role="tabpanel">
						<form id="dn-edit" method="POST" class="needs-validation" action="{{ url('entry/update/pending') }}" novalidate>
							@csrf

							<input type="hidden" name="dn" value="">

							@foreach ($o->getVisibleAttributes() as $ao)
								<x-attribute-type :edit="true" :o="$ao"/>
							@endforeach

							@include('fragment.dn.add_attr')
						</form>

						<div class="row d-none pt-3">
							<div class="col-12 offset-sm-2 col-sm-4 col-lg-2">
								<x-form.reset form="dn-edit"/>
								<x-form.submit action="Update" form="dn-edit"/>
							</div>
						</div>
					</div>

					<!-- Internal Attributes -->
					<div class="tab-pane" id="internal" role="tabpanel">
						@foreach ($o->getInternalAttributes() as $ao)
							<x-attribute-type :o="$ao"/>
						@endforeach
					</div>

					<!-- Debug -->
					<div class="tab-pane" id="debug" role="tabpanel">
						<div class="row">
							<div class="col-4">
								@dump($o)
							</div>
							<div class="col-4">
								@dump($o->getAttributes())
							</div>
							<div class="col-4">
								@dump(['available'=>$o->getAvailableAttributes()->pluck('name'),'missing'=>$o->getMissingAttributes()->pluck('name')])
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('page-modals')
	<!-- Frame Modals -->
	<div class="modal fade" id="page-modal" tabindex="-1" aria-labelledby="label" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-fullscreen-lg-down">
			<div class="modal-content"></div>
		</div>
	</div>

	<!-- EXPORT -->
	<div class="modal fade" id="entry_export-modal" tabindex="-1" aria-labelledby="entry_export-label" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-fullscreen-lg-down">
			<div class="modal-content">
				<div class="modal-header">
					<h1 class="modal-title fs-5" id="entry_export-label">LDIF for {{ $dn }}</h1>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>

				<div class="modal-body">
					<div id="entry_export"><div class="fa-3x"><i class="fas fa-spinner fa-pulse fa-sm"></i></div></div>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
					<button type="button" class="btn btn-sm btn-primary" id="entry_export-download">Download</button>
				</div>
			</div>
		</div>
	</div>

	@if($up=$o->getObject('userpassword'))
		<!-- CHECK USERPASSWORD -->
		<div class="modal fade" id="userpassword_check-modal" tabindex="-1" aria-labelledby="userpassword_check-label" aria-hidden="true">
			<div class="modal-dialog modal-lg modal-fullscreen-lg-down">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="modal-title fs-5" id="userpassword_check-label">Check Passwords for {{ $dn }}</h1>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>

					<div class="modal-body">
						<table class="table table-bordered p-1">
							@foreach($up->values as $key => $value)
								<tr>
									<th>Check</th>
									<td>{{ $up->render_item_old($key) }}</td>
									<td>
										<input type="password" style="width: 90%" name="password[{{$key}}]"> <i class="fas fa-fw fa-lock"></i>
										<div class="invalid-feedback pb-2">
											Invalid Password
										</div>
									</td>
								</tr>
							@endforeach
						</table>
					</div>

					<div class="modal-footer">
						<button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
						<button type="button" class="btn btn-sm btn-primary" id="userpassword_check-submit"><i class="fas fa-fw fa-spinner fa-spin d-none"></i> Check</button>
					</div>
				</div>
			</div>
		</div>
	@endif
@endsection

@section('page-scripts')
	<script type="text/javascript">
		var dn = '{{ $o->getDNSecure() }}';
		var oc = {!! $o->getObject('objectclass')->values !!};

		function download(filename,text) {
			var element = document.createElement('a');

			element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
			element.setAttribute('download', filename);
			element.style.display = 'none';
			document.body.appendChild(element);

			element.click();
			document.body.removeChild(element);
		}

		function editmode() {
			$('#dn-edit input[name="dn"]').val(dn);

			$('button[id=entry-edit]')
				.removeClass('btn-outline-dark')
				.addClass('btn-dark');

			// Find all input items and turn off readonly
			$('input.form-control').each(function() {
				// Except for objectClass - @todo show an "X" instead
				if ($(this)[0].name.match(/^objectclass/))
					return;

				$(this).attr('readonly',false);
			});

			// Our password type
			$('attribute#userPassword .form-select').each(function() {
				$(this).prop('disabled',false);
			})

			$('.row.d-none').removeClass('d-none');
			$('.addable.d-none').removeClass('d-none');
			$('.deletable.d-none').removeClass('d-none');

			@if($o->getMissingAttributes()->count())
				$('#newattr-select.d-none').removeClass('d-none');
			@endif
		}

		$(document).ready(function() {
			$('#newattr').on('change',function(item) {
				$.ajax({
					type: 'POST',
					beforeSend: function() {},
					success: function(data) {
						$('#newattrs').append(data);
					},
					error: function(e) {
						if (e.status != 412)
							alert('That didnt work? Please try again....');
					},
					url: '{{ url('entry/attr/add') }}/'+item.target.value,
					data: {
						objectclasses: oc,
					},
					cache: false
				});

				// Remove the option from the list
				$(this).find('[value="'+item.target.value+'"]').remove()

				// If there are no more options
				if ($(this).find("option").length === 1)
					$('#newattr-select').remove();
			});

			$('#entry_export-download').on('click',function(item) {
				item.preventDefault();

				let ldif = $('#entry_export').find('pre:first'); // update this selector in your local version
				download('ldap-export.ldif',ldif.html());
			});

			$('#page-modal').on('shown.bs.modal',function(item) {
				var that = $(this).find('.modal-content');

				switch ($(item.relatedTarget).attr('id')) {
					case 'entry-delete':
						$.ajax({
							method: 'GET',
							url: '{{ url('modal/delete') }}/'+dn,
							dataType: 'html',
							cache: false,
							beforeSend: function() {
								that.empty().append('<span class="p-3"><i class="fas fa-3x fa-spinner fa-pulse"></i></span>');
							},
							success: function(data) {
								that.empty().html(data);
							},
							error: function(e) {
								if (e.status != 412)
									alert('That didnt work? Please try again....');
							},
						})
						break;

					default:
						console.log('No action for button:'+$(item.relatedTarget).attr('id'));
				}
			});

			$('#entry_export-modal').on('shown.bs.modal',function() {
				$.ajax({
					type: 'GET',
					success: function(data) {
						$('#entry_export').empty().append(data);
					},
					error: function(e) {
						if (e.status != 412)
							alert('That didnt work? Please try again....');
					},
					url: '{{ url('entry/export') }}/'+dn,
					cache: false
				})
			})

			@if($up)
				$('button[id=userpassword_check-submit]').on('click',function(item) {
					var that = $(this);

					var passwords = $('#userpassword_check-modal')
						.find('input[name^="password["')
						.map((key,item)=>item.value);

					if (passwords.length === 0) return false;

					$.ajax({
						type: 'POST',
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
								var i = $('#userpassword_check-modal')
									.find('input[name="password['+key+']')
									.siblings('i');

								var feedback = $('#userpassword_check-modal')
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
							if (e.status != 412)
								alert('That didnt work? Please try again....');
						},
						url: '{{ url('entry/password/check') }}',
						data: {
							dn: dn,
							password: Array.from(passwords),
						},
						dataType: 'json',
						cache: false
					})
				});
			@endif

			@if(old())
				editmode();
			@endif
		});
	</script>
@append