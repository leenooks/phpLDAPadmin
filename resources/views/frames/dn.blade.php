@extends('layouts.dn')

@section('page_title')
	@include('fragment.dn.header',['o'=>($o ?? $o=$server->fetch($dn))])
@endsection

@section('page_actions')
	<div class="row">
		<div class="col">
			<div class="action-buttons float-end">
				<ul class="nav">
					@if(isset($page_actions) && $page_actions->get('create'))
						<li>
							<button class="btn btn-outline-dark p-1 m-1" id="entry-create" data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('Create Child Entry')"><i class="fas fa-fw fa-diagram-project fs-5"></i></button>
						</li>
					@endif
					@if(isset($page_actions) && $page_actions->get('export'))
						<li>
							<span id="entry-export" data-bs-toggle="modal" data-bs-target="#page-modal">
								<button class="btn btn-outline-dark p-1 m-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('Export')"><i class="fas fa-fw fa-download fs-5"></i></button>
							</span>
						</li>
					@endif
					@if(isset($page_actions) && $page_actions->get('copy'))
						<li>
							<button class="btn btn-outline-dark p-1 m-1" id="entry-copy-move" data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('Copy/Move')" disabled><i class="fas fa-fw fa-copy fs-5"></i></button>
						</li>
					@endif
					@if(isset($page_actions) && $page_actions->get('edit'))
						<li>
							<button class="btn btn-outline-dark p-1 m-1" id="entry-edit" data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('Edit Entry')"><i class="fas fa-fw fa-edit fs-5"></i></button>
						</li>
					@endif
					<!-- @todo Dont offer the delete button for an entry with children -->
					@if(isset($page_actions) && $page_actions->get('delete'))
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
	@elseif(($x=$o->getLangMultiTags())->count())
		<div class="alert alert-danger p-2">
			This entry has multi-language tags used by [<strong>{!! $x->keys()->join('</strong>, <strong>') !!}</strong>] that cant be managed by PLA. You can though manage those lang tags with an LDIF import.
		</div>
	@endif

	<x-note/>
	<x-error/>
	<x-updated/>
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
									<div role="group" class="btn-group btn-group-sm nav pb-3">
										<!-- It is assumed that the entry has atleast 1 template "default" -->
										@if($o->templates->count() > 1)
											@foreach($o->templates as $template => $name)
												<span data-bs-toggle="tab" href="#template-{{$template}}" @class(['btn','btn-outline-focus','active'=>$loop->index === 0])>{{ $name }}</span>
											@endforeach
										@endif
									</div>
								</div>

								<div class="tab-content">
									@foreach($o->templates as $template => $name)
										@switch($template)
											@case('default')
												<div @class(['tab-pane','active'=>$loop->index === 0]) id="template-{{$template}}" role="tabpanel">
													<form id="dn-edit" method="POST" class="needs-validation" action="{{ url('entry/update/pending') }}" novalidate readonly>
														@csrf

														<input type="hidden" name="dn" value="">

														<div class="card-body">
															<div class="tab-content">
																@php($up=(session()->pull('updated') ?: collect()))
																@foreach($o->getVisibleAttributes() as $ao)
																	<x-attribute-type :o="$ao" :edit="TRUE" :new="FALSE" :updated="$up->contains($ao->name_lc)"/>
																@endforeach

																@include('fragment.dn.add_attr')
															</div>
														</div>
													</form>
												</div>
												@break

											@default
												<div @class(['tab-pane','active'=>$loop->index === 0]) id="template-{{$template}}" role="tabpanel">
													<p>{{$name}}</p>
												</div>
										@endswitch
									@endforeach
								</div>
							</div>
						</div>

						<div class="row d-none pt-3">
							<div class="col-12 offset-sm-2 col-sm-4 col-lg-2">
								<x-form.reset form="dn-edit"/>
								<x-form.submit action="Update" form="dn-edit"/>
							</div>
						</div>
					</div>

					<!-- Internal Attributes -->
					<div class="tab-pane mt-3" id="internal" role="tabpanel">
						@foreach($o->getInternalAttributes() as $ao)
							<x-attribute-type :o="$ao" :edit="FALSE" :new="FALSE" :updated="FALSE"/>
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
				// Except for objectClass - @todo show an "X" instead
				if ($(this)[0].name.match(/^objectclass/))
					return;

				$(this).attr('readonly',false);
			});

			// Our password type
			$('attribute#userpassword .form-select').each(function() {
				$(this).prop('disabled',false);
			})

			$('.row.d-none').removeClass('d-none');
			$('span.addable.d-none').removeClass('d-none');
			$('span.deletable.d-none').removeClass('d-none');

			@if($o->getMissingAttributes()->count())
				$('#newattr-select.d-none').removeClass('d-none');
			@endif
		}

		$(document).ready(function() {
			$('button[id=entry-create]').on('click',function(item) {
				location.replace('/#{{ Crypt::encryptString(sprintf('*%s|%s','create',$dn)) }}');
				location.reload();
			});

			$('button[id=entry-edit]').on('click',function(item) {
				item.preventDefault();

				if ($(this).hasClass('btn-dark'))
					return;

				editmode();
			});

			$('#newattr').on('change',function(item) {
				var oc = $('attribute#objectclass input[type=text]')
					.map((key,item)=>{return $(item).val()}).toArray();

				$.ajax({
					type: 'POST',
					beforeSend: function() {},
					success: function(data) {
						$('#newattrs').append(data);
					},
					error: function(e) {
						if (e.status !== 412)
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