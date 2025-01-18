@extends('layouts.dn')

@section('page_title')
	@include('fragment.dn.header')
@endsection

@section('main-content')
	<x-note/>
	<x-updated/>
	<x-error/>

	<!-- @todo If we are redirected here, check old() and add back any attributes that were in the original submission -->

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

							<input type="hidden" name="dn" value="{{ $o->getDNSecure() }}">

							@foreach ($o->getVisibleAttributes() as $ao)
								<x-attribute-type :edit="true" :o="$ao"/>
							@endforeach

							<div id="newattrs"></div>

							<!-- Add new attributes -->
							<div class="row">
								<div class="col-12 col-sm-1 col-md-2"></div>
								<div class="col-12 col-sm-10 col-md-8">
									<div class="d-none" id="newattr-select">

									@if($o->getMissingAttributes()->count())
										<div class="row">
											<div class="col-12 bg-dark text-light p-2">
												<i class="fas fa-plus-circle"></i> Add New Attribute
											</div>
										</div>

										<div class="row">
											<div class="col-12 pt-2">
												<x-form.select id="newattr" label="Select from..." :options="$o->getMissingAttributes()->sortBy('name')->map(fn($item)=>['id'=>$item->name,'value'=>$item->name_lc])"/>
											</div>
										</div>
									@endif
									</div>
								</div>
								<div class="col-2"></div>
							</div>
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
						<div class="row">
							<div class="col-12 offset-lg-2 col-lg-8">
								<table class="table">
									@foreach ($o->getInternalAttributes() as $ao)
										<tr class="bg-light text-dark small">
											<th class="w-25">
												<abbr title="{{ $ao->description }}">{{ $ao->name }}</abbr>
											</th>
										</tr>
										<tr>
											<td class="ps-5">
												<x-attribute :edit="false" :o="$ao"/>
											</td>
										</tr>
									@endforeach
								</table>
							</div>
						</div>
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
	<!-- EXPORT -->
	<div class="modal fade" id="entry-export-modal" tabindex="-1" aria-labelledby="entry-export-label" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-fullscreen-xl-down">
			<div class="modal-content">
				<div class="modal-header">
					<h1 class="modal-title fs-5" id="entry-export-label">LDIF for {{ $dn }}</h1>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>

				<div class="modal-body">
					<div id="entry-export"><div class="fa-3x"><i class="fas fa-spinner fa-pulse fa-sm"></i></div></div>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary btn-sm" id="entry-export-download">Download</button>
				</div>
			</div>
		</div>
	</div>

	@if($up=$o->getObject('userpassword'))
		<!-- CHECK USERPASSWORD -->
		<div class="modal fade" id="userpassword-check-modal" tabindex="-1" aria-labelledby="userpassword-check-label" aria-hidden="true">
			<div class="modal-dialog modal-lg modal-fullscreen-lg-down">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="modal-title fs-5" id="userpassword-check-label">Check Passwords for {{ $dn }}</h1>
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
						<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
						<button type="button" class="btn btn-primary btn-sm" id="userpassword_check_submit"><i class="fas fa-fw fa-spinner fa-spin d-none"></i> Check</button>
					</div>
				</div>
			</div>
		</div>
	@endif
@endsection

@section('page-scripts')
	<script type="text/javascript">
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
			$('button[id=entry-edit]').addClass('active').removeClass('btn-outline-dark').addClass('btn-outline-light');

			// Find all input items and turn off readonly
			$('input.form-control').each(function() {
				$(this).attr('readonly',false);
			});

			// Our password type
			$('div#userpassword .form-select').each(function() {
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
					type: 'GET',
					beforeSend: function() {},
					success: function(data) {
						$('#newattrs').append(data);
					},
					error: function(e) {
						if (e.status != 412)
							alert('That didnt work? Please try again....');
					},
					url: '{{ url('entry/newattr') }}/'+item.target.value,
					cache: false
				});

				// Remove the option from the list
				$(this).find('[value="'+item.target.value+'"]').remove()

				// If there are no more options
				if ($(this).find("option").length === 1)
					$('#newattr-select').remove();
			});

			$('button[id=entry-edit]').on('click',function(item) {
				item.preventDefault();

				if ($(this).hasClass('active'))
					return;

				editmode();
			});

			$('#entry-export-download').on('click',function(item) {
				item.preventDefault();

				let ldif = $('#entry-export').find('pre:first'); // update this selector in your local version
				download('ldap-export.ldif',ldif.html());
			});

			$('#entry-export-modal').on('shown.bs.modal',function() {
				$.ajax({
					type: 'GET',
					success: function(data) {
						$('#entry-export').empty().append(data);
					},
					error: function(e) {
						if (e.status != 412)
							alert('That didnt work? Please try again....');
					},
					url: '{{ url('entry/export',$o->getDNSecure()) }}/',
					cache: false
				})
			})

			@if($up)
				$('button[id=userpassword_check_submit]').on('click',function(item) {
					var that = $(this);

					var passwords = $('#userpassword-check-modal')
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
								var i = $('#userpassword-check-modal')
									.find('input[name="password['+key+']')
									.siblings('i');

								var feedback = $('#userpassword-check-modal')
									.find('input[name="password['+key+']')
									.siblings('div.invalid-feedback');

								console.log(feedback.attr('display'));

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
							dn: '{{ $o->getDNSecure() }}',
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