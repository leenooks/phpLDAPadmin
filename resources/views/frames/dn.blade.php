@extends('layouts.dn')

@section('page_title')
	@include('fragment.dn.header')
@endsection

@section('page_actions')
	<div class="row">
		<div class="col">
			<div class="action-buttons float-end">
				<ul class="nav">
					@if($page_actions->get('create'))
						<li>
							<button class="btn btn-outline-dark p-1 m-1" id="entry-create" data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('Create Child Entry')"><i class="fas fa-fw fa-diagram-project fs-5"></i></button>
						</li>
					@endif
					@if($page_actions->get('export'))
						<li>
							<span id="entry-export" data-bs-toggle="modal" data-bs-target="#page-modal">
								<button class="btn btn-outline-dark p-1 m-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('Export')"><i class="fas fa-fw fa-download fs-5"></i></button>
							</span>
						</li>
					@endif
					@if($page_actions->get('copy'))
						<li>
							<span id="entry-copy-move" data-bs-toggle="modal" data-bs-target="#page-modal">
								<button class="btn btn-outline-dark p-1 m-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('Copy/Move')"><i class="fas fa-fw fa-copy fs-5"></i></button>
							</span>
						</li>
					@endif
					@if($page_actions->get('edit'))
						<li>
							<button class="btn btn-outline-dark p-1 m-1" id="entry-edit" data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('Edit Entry')"><i class="fas fa-fw fa-edit fs-5"></i></button>
						</li>
					@endif
					@if($page_actions->get('delete'))
						<li>
							<span id="entry-delete" data-bs-toggle="modal" data-bs-target="#page-modal">
								<button class="btn btn-outline-danger p-1 m-1" data-bs-custom-class="custom-tooltip-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('Delete Entry')"><i class="fas fa-fw fa-trash-can fs-5"></i></button>
							</span>
						</li>
					@endif
				</ul>
			</div>
		</div>
	</div>
@endsection

@section('page_status')
	@if(($x=$o->getOtherTags()->filter(fn($item)=>$item->diff(['binary'])->count()))->count())
		<div class="alert alert-danger p-2">
			This entry has [<strong>{!! $x->flatten()->join('</strong>, <strong>') !!}</strong>] tags used by [<strong>{!! $x->keys()->join('</strong>, <strong>') !!}</strong>] that cant be managed by PLA. You can though manage those tags with an LDIF import.
		</div>
	@endif

	<x-success/>
	<x-updated :updated="$updated"/>
	<x-note/>
	<x-error/>
	<x-failed/>
@endsection

@section('main-content')
	<div class="main-card mb-3 card">
		<div class="card-body">
			<div class="card-header-tabs">
				<ul class="nav nav-tabs mb-0">
					<li class="nav-item"><a data-bs-toggle="tab" href="#attributes" class="nav-link active">@lang('Attributes')</a></li>
					<li class="nav-item"><a data-bs-toggle="tab" href="#internal" class="nav-link">@lang('Internal')</a></li>
				</ul>

				<div class="tab-content">
					<!-- All Attributes -->
					<div class="tab-pane active" id="attributes" role="tabpanel">
						<div class="row pt-3">
							<div class="col-12">
								<div class="d-flex justify-content-center">
									<div class="btn-group btn-group-sm nav pb-3" role="group">
										<!-- If we have templates that cover this entry -->
										@foreach($o->templates as $template)
											<span data-bs-toggle="tab" href="#template-{{ $template->name }}" @class(['btn','btn-outline-focus','active'=>$loop->index === 0])><i class="fa fa-fw pe-2 {{ $template->icon }}"></i> {{ $template->title }}</span>
										@endforeach

										@if($o->templates->count())
											<span data-bs-toggle="tab" href="#template-default" @class(['btn','btn-outline-focus','p-1','active'=>(! $o->templates->count())])><i class="fa fa-fw fa-list pe-2"></i> {{ __('LDAP Entry') }}</span>
										@endif
									</div>
								</div>

								<div class="tab-content">
									@foreach($o->templates as $template)
										<div @class(['tab-pane','active'=>$loop->index === 0]) id="template-{{ $template->name }}" role="tabpanel">
											@include('fragment.template.dn',['template'=>$template,'updated'=>$updated])
										</div>
									@endforeach

									<div @class(['tab-pane','active'=>(! $o->templates->count())]) id="template-default" role="tabpanel">
										<form id="dn-edit" method="POST" class="needs-validation" action="{{ url('entry/update/pending') }}" novalidate readonly>
											@csrf

											<input type="hidden" name="dn" value="">

											<div class="card-body">
												<div class="tab-content">
													@foreach($o->getVisibleAttributes() as $ao)
														<x-attribute :o="$ao" :edit="false" :editable="true" :new="true" :template="null" :updated="$updated->contains($ao->name_lc)"/>
													@endforeach

													@include('fragment.dn.add_attr')
												</div>
											</div>
										</form>

										<div class="row d-none pt-3">
											<div class="col-11 text-end">
												<x-form.reset form="dn-edit"/>
												<x-form.submit :action="__('Update')" form="dn-edit"/>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Internal Attributes -->
					<div class="tab-pane mt-3" id="internal" role="tabpanel">
						@foreach($o->getInternalAttributes() as $ao)
							<x-attribute :o="$ao" :edit="false" :new="false" :template="null" :updated="false"/>
						@endforeach
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
@endsection

@section('page-scripts')
	<script type="text/javascript">
		var dn = '{{ $o->getDNSecure() }}';

		function editmode() {
			$('#dn-edit input[name="dn"]').val(dn);

			$('form#dn-edit').attr('readonly',false);
			$('button[id=entry-edit]')
				.removeClass('btn-outline-dark')
				.addClass('btn-dark')
				.addClass('opacity-100')
				.attr('disabled',true);

			// Find all input items and turn off readonly
			$('input.form-control').each(function() {
				if ($(this)[0].name.match(/^objectclass/))
					return;

				$(this).attr('readonly',false);
			});

			// Find all input items and turn off readonly
			$('textarea.form-control').each(function() {
				$(this).attr('readonly',false);
			});

			// Our password type
			$('attribute#userpassword .form-select').each(function() {
				$(this).prop('disabled',false);
			})

			// Objectclasses that can be removed
			$('.input-group-end i.d-none').removeClass('d-none');

			$('.row.d-none').removeClass('d-none');
			$('span.addable.d-none').removeClass('d-none');
			$('span.deletable.d-none').removeClass('d-none');

			@if($o->getMissingAttributes()->count())
				$('#newattr-select.d-none').removeClass('d-none');
			@endif
		}

		$(document).ready(function() {
			$('button[id=entry-create]').on('click',function(item) {
				location.replace(web_base+'/#{{ Crypt::encryptString(sprintf('*%s|%s','create',$dn)) }}');
				if (web_base_path === '/')
					location.reload();
			});

			$('button[id=entry-edit]').on('click',function(item) {
				item.preventDefault();

				if ($(this).hasClass('btn-dark'))
					return;

				editmode();
			});

			$('#page-modal').on('shown.bs.modal',function(item) {
				var that = $(this).find('.modal-content');

				switch ($(item.relatedTarget).attr('id')) {
					case 'entry-copy-move':
						$.ajax({
							method: 'GET',
							url: '{{ url('modal/copy-move') }}/'+dn,
							dataType: 'html',
							cache: false,
							beforeSend: function() {
								that.empty().append('<span class="p-3"><i class="fas fa-3x fa-spinner fa-pulse"></i></span>');
							},
							success: function(data) {
								that.empty().html(data);
							},
							error: function(e) {
								if (e.status !== 412)
									alert('That didnt work? Please try again....');
							},
						});
						break;

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
								if (e.status !== 412)
									alert('That didnt work? Please try again....');
							},
						});
						break;

					case 'entry-export':
						$.ajax({
							method: 'GET',
							url: '{{ url('modal/export') }}/'+dn,
							dataType: 'html',
							cache: false,
							beforeSend: function() {
								that.empty().append('<span class="p-3"><i class="fas fa-3x fa-spinner fa-pulse"></i></span>');
							},
							success: function(data) {
								that.empty().html(data);

								that = $('#entry_export');

								$.ajax({
									method: 'GET',
									url: '{{ url('entry/export') }}/'+dn,
									cache: false,
									beforeSend: function() {
										that.empty().append('<span class="p-3"><i class="fas fa-3x fa-spinner fa-pulse"></i></span>');
									},
									success: function(data) {
										that.empty().append(data);
									},
									error: function(e) {
										if (e.status !== 412)
											alert('That didnt work? Please try again....');
									},
								})
							},
							error: function(e) {
								if (e.status !== 412)
									alert('That didnt work? Please try again....');
							},
						})
						break;

					case 'entry-rename':
						$.ajax({
							method: 'GET',
							url: '{{ url('modal/rename') }}/'+dn,
							dataType: 'html',
							cache: false,
							beforeSend: function() {
								that.empty().append('<span class="p-3"><i class="fas fa-3x fa-spinner fa-pulse"></i></span>');
							},
							success: function(data) {
								that.empty().html(data);
							},
							error: function(e) {
								if (e.status !== 412)
									alert('That didnt work? Please try again....');
							},
						});
						break;

					default:
						switch ($(item.relatedTarget).attr('name')) {
							case 'entry-userpassword-check':
								$.ajax({
									method: 'GET',
									url: '{{ url('modal/userpassword-check') }}/'+dn,
									dataType: 'html',
									cache: false,
									beforeSend: function() {
										that.empty().append('<span class="p-3"><i class="fas fa-3x fa-spinner fa-pulse"></i></span>');
									},
									success: function(data) {
										that.empty().html(data);
									},
									error: function(e) {
										if (e.status !== 412)
											alert('That didnt work? Please try again....');
									},
								})
								break;

							default:
								console.log('No action for button:'+$(item.relatedTarget).attr('id'));
						}
				}
			});

			$('#page-modal').on('hide.bs.modal',function() {
				// Clear any select ranges that occurred while the modal was open
				document.getSelection().removeAllRanges();
			});

			@if(old())
				editmode();
			@endif
		});
	</script>
@append