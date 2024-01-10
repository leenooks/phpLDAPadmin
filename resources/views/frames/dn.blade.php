@extends('layouts.dn')

@section('page_title')
	<table class="table table-borderless">
		<tr>
			<td class="{{ ($x=$o->getObject('jpegphoto')) ? 'border' : '' }}" rowspan="2">{!! $x ? $x->render() : sprintf('<div class="page-title-icon f32"><i class="%s"></i></div>',$o->icon() ?? "fas fa-info") !!}</td>
			<td class="text-end align-text-top p-0 {{ $x ? 'ps-5' : 'pt-2' }}"><strong>{{ $dn }}</strong></td>
		</tr>
		<tr>
			<td class="line-height-1" style="font-size: 55%;vertical-align: bottom;" colspan="2">
				<table>
					<tr>
						<td class="p-1 m-1">Created</td>
						<th class="p-1 m-1">{{ ($x=$o->getObject('createtimestamp')) ? $x->render() : __('Unknown') }} [{{ ($x=$o->getObject('creatorsname')) ? $x->render() : __('Unknown') }}]</th>
					</tr>
					<tr>
						<td class="p-1 m-1">Modified</td>
						<th class="p-1 m-1">{{ ($x=$o->getObject('modifytimestamp')) ? $x->render() : __('Unknown') }} [{{ ($x=$o->getObject('modifiersname')) ? $x->render() : __('Unknown') }}]</th>
					</tr>
					<tr>
						<td class="p-1 m-1">UUID</td>
						<th class="p-1 m-1">{{ $o->entryuuid[0] ?? '' }}</th>
					</tr>
				</table>
			</td>
		</tr>
	</table>
@endsection

@section('main-content')
	@if(session()->has('note'))
		<div class="alert alert-info">
			<h4 class="alert-heading"><i class="fas fa-fw fa-note-sticky"></i> Note:</h4>
			<hr>
			<p>{{ session()->pull('note') }}</p>
		</div>
	@endif

	@if(session()->has('success'))
		<div class="alert alert-success">
			<h4 class="alert-heading"><i class="fas fa-fw fa-thumbs-up"></i> Success!</h4>
			<hr>
			<p>{{ session()->pull('success') }}</p>
			<ul style="list-style-type: square;">
				@foreach (session()->pull('updated') as $key => $values)
					<li>{{ $key }}: {{ join(',',$values) }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<!-- @todo If we are redirected here, check old() and add back any attributes that were in the original submission -->
	@if($errors->any())
		<div class="alert alert-danger">
			<h4 class="alert-heading"><i class="fas fa-fw fa-thumbs-down"></i> Error?</h4>
			<hr>
			<ul style="list-style-type: square;">
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

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
												<label for="newattr" class="form-label">Select from...</label>
												<select class="form-select" id="newattr">
													<option value="">&nbsp;</option>
													@foreach ($o->getMissingAttributes() as $ao)
														<option value="{{ $ao->name_lc }}">{{ $ao->name }}</option>
													@endforeach
												</select>
											</div>
										</div>
									@endif
									</div>
								</div>
								<div class="col-2"></div>
							</div>

							<div class="row d-none pt-3">
								<div class="col-12 offset-sm-2 col-sm-4 col-lg-2">
									<span id="form-reset" class="btn btn-outline-danger">@lang('Reset')</span>
									<span id="form-submit" class="btn btn-success">@lang('Update')</span>
								</div>
							</div>
						</form>
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
	<!-- Modal -->
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
					<button id="entry-export-download" type="button" class="btn btn-primary btn-sm">Download</button>
				</div>
			</div>
		</div>
	</div>
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

			$('.row.d-none').removeClass('d-none');
			$('.addable.d-none').removeClass('d-none');
			$('.deletable.d-none').removeClass('d-none');

			@if($o->getMissingAttributes()->count())
				$('#newattr-select.d-none').removeClass('d-none');
			@endif
		}

		$(document).ready(function() {
			$('#form-reset').click(function() {
				$('#dn-edit')[0].reset();
			});

			$('#form-submit').click(function() {
				$('#dn-edit')[0].submit();
			});

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

			$('#entry-export-modal').on('shown.bs.modal', function () {
				$.ajax({
					type: 'GET',
					beforeSend: function() {},
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

			@if(old())
				editmode();
			@endif
		});
	</script>
@append