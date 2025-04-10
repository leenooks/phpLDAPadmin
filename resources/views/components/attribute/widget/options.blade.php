@use(App\Classes\LDAP\Attribute\Certificate)
@use(App\Classes\LDAP\Attribute\CertificateList)
@use(App\Classes\LDAP\Attribute\Binary\JpegPhoto)
@use(App\Classes\LDAP\Attribute\ObjectClass)
@php($clone=FALSE)
<span class="p-0 m-0">
	@if($o->is_rdn)
		<button class="btn btn-sm btn-outline-focus mt-3" disabled><i class="fas fa-fw fa-exchange"></i> @lang('Rename')</button>
	@elseif($edit && $o->can_addvalues)
		@switch(get_class($o))
			@case(Certificate::class)
			@case(CertificateList::class)
				<span @class(['btn','btn-sm','btn-outline-primary','mt-3','addable','d-none'=>(! $new)]) id="{{ $o->name }}-replace" disabled><i class="fas fa-fw fa-certificate"></i> @lang('Replace')</span>
				@section('page-scripts')
					<script type="text/javascript">
							$(document).ready(function() {
								$('#{{ $o->name }}-replace.addable').click(function(e) {
									alert('Sorry, not implemented yet');
									e.preventDefault();
									return false;
								});
							});
					</script>
				@append
				@break

			@case(ObjectClass::class)
				<span type="button" @class(['btn','btn-sm','btn-outline-primary','mt-3','addable','d-none'=>(! $new)]) data-bs-toggle="modal" data-bs-target="#new_objectclass-modal"><i class="fas fa-fw fa-plus"></i> @lang('Add Objectclass')</span>

				<!-- NEW OBJECT CLASS -->
				<div class="modal fade" id="new_objectclass-modal" tabindex="-1" aria-labelledby="new_objectclass-label" aria-hidden="true" data-bs-backdrop="static">
					<div class="modal-dialog modal-lg modal-fullscreen-lg-down">
						<div class="modal-content">
							<div class="modal-header">
								<h1 class="modal-title fs-5" id="new_objectclass-label">New Object Class</h1>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>

							<div class="modal-body">
								<x-form.select id="newoc" label="Select from..."/>
							</div>

							<div class="modal-footer">
								<button type="button" class="btn btn-sm btn-primary" data-bs-dismiss="modal">Next</button>
							</div>
						</div>
					</div>
				</div>

				@section('page-scripts')
					<script type="text/javascript">
						$(document).ready(function() {
							var added_oc = [];	// Object classes being added to this entry
							var rendered = false;
							var newadded = [];

							if (newadded.length)
								process_oc();

							function process_oc() {
								// Find out what was selected, and add them
								newadded.forEach(function (item) {
									if (added_oc.indexOf(item) !== -1)
										return;

									// Add our new OC to the list of OCs
									oc.push(item);

									// Add attribute to the page
									$.ajax({
										method: 'POST',
										url: '{{ url('entry/attr/add',[$o->name_lc]) }}',
										data: {
											noheader: true,
											value: item,
											objectclasses: oc,
										},
										cache: false,
										success: function(data) {
											$('#{{ $o->name }}').append(data);
										},
										error: function(e) {
											if (e.status !== 412)
												alert('That didnt work? Please try again....');
										},
									});

									// Get a list of attributes already on the page, so we dont double up
									$.ajax({
										method: 'POST',
										url: '{{ url('api/schema/objectclass/attrs') }}/'+item,
										cache: false,
										success: function(data) {
											// Render any must attributes
											if (data.must.length) {
												data.must.forEach(function(item) {
													if ($('attribute#'+item).length)
														return;

													// Add attribute to the page
													$.ajax({
														method: 'POST',
														url: '{{ url('entry/attr/add') }}/'+item,
														data: {
															value: item,
															objectclasses: oc,
														},
														cache: false,
														success: function(data) {
															$('#newattrs').append(data);
														},
														error: function(e) {
															if (e.status !== 412)
																alert('That didnt work? Please try again....');
														},
													});
												})
											}

											// Add attributes to "Add new Attribute" that are now available
											if (data.may.length) {
												var newattr = $('select#newattr');
												var oldoptions = $('select#newattr option').map((i,o)=>o.value).get();

												data.may.forEach(function(item) {
													if (! oldoptions.includes(item))
														newattr.append(new Option(item,item,false,false));
												});

												// Sort the attributes
												newattr
													.append($('select#newattr option')
														.remove()
														.sort(function (a,b) {
															let at = $(a).text(),
																bt = $(b).text();
															return (at > bt) ? 1 : ((at < bt) ? -1 : 0);
														}))
													.val('');
											}
										},
										error: function(e) {
											if (e.status !== 412)
												alert('That didnt work? Please try again....');
										},
									});
								});

								// Loop through added_oc, and remove anything not in newadded
								added_oc.forEach(function(item) {
									if (newadded.indexOf(item) === -1) {
										$('span#objectclass_'+item).empty();

										$.ajax({
											method: 'POST',
											url: '{{ url('api/schema/objectclass/attrs') }}/'+item,
											cache: false,
											success: function(data) {
												var attrs = [];

												// Remove attributes from "Add new Attribute" that are no longer available
												if (data.may.length) {
													data.may.forEach(function(mayitem) {
														var x = $("select#newattr option[value='"+mayitem+"']");

														if (x.length) {
															x.remove();

														// Add this to the must attrs list, because its been rendered
														} else {
															attrs.push(mayitem);
														}
													});
												}

												data.must.concat(attrs).forEach(function(attr) {
													var x = $('#'+attr).find('input');

													x.css('background-color','#f0c0c0').attr('readonly',true).attr('placeholder',x.val()).val('');
												});
											},
											error: function(e) {
												if (e.status !== 412)
													alert('That didnt work? Please try again....');
											},
										});
									}
								});

								added_oc = newadded;
							}

							// Show our ObjectClass modal so that we can add more objectclasses
							$('#new_objectclass-modal').on('shown.bs.modal',function() {
								if (! rendered)
									$.ajax({
										method: 'POST',
										url: '{{ url('entry/objectclass/add') }}',
										data: {
											oc: oc,
										},
										cache: false,
										success: function(data) {
											$('select#newoc').select2({
												dropdownParent: $('#new_objectclass-modal'),
												theme: 'bootstrap-5',
												multiple: true,
												data: data,
											});
										},
										error: function(e) {
											if (e.status !== 412)
												alert('That didnt work? Please try again....');
										},
									});

								rendered = true;
							})

							// When the ObjectClass modal is closed, process what was selected
							$('#new_objectclass-modal').on('hide.bs.modal',function() {
								newadded = $('select#newoc').val();

								// If nothing selected, we dont have anything to do
								if (added_oc.sort().join('|') === newadded.sort().join('|'))
									return;

								process_oc();
							});
						});
					</script>
				@append
				@break

			@case(JpegPhoto::class)
				<span @class(['btn','btn-sm','btn-outline-primary','mt-3','addable','d-none'=>(! $new)]) id="{{ $o->name }}-upload" disabled><i class="fas fa-fw fa-file-arrow-up"></i> @lang('Upload JpegPhoto')</span>
				@section('page-scripts')
					<script type="text/javascript">
							$(document).ready(function() {
								$('#{{ $o->name }}-upload.addable').click(function(e) {
									alert('Sorry, not implemented yet');
									e.preventDefault();
									return false;
								});
							});
					</script>
				@append
				@break

			<!-- All other attributes -->
			@default
				@if($o->isDynamic()) @break @endif
				@php($clone=TRUE)
				<span @class(['btn','btn-sm','btn-outline-primary','mt-3','addable','d-none'=>(! $new)]) id="{{ $o->name }}-addnew"><i class="fas fa-fw fa-plus"></i> @lang('Add Value')</span>

				@section('page-scripts')
					@if($clone && $edit && $o->can_addvalues)
						<script type="text/javascript">
							$(document).ready(function() {
								// Create a new entry when Add Value clicked
								$('#{{ $o->name }}-addnew.addable').click(function (item) {
									var cln = $(this).parent().parent().find('input:last').parent().clone();
									cln.find('input:last')
										.attr('value','')
										.attr('placeholder', '[@lang('NEW')]')
										.addClass('border-focus')
										.appendTo('#'+item.currentTarget.id.replace('-addnew',''));
								});
							});
						</script>
					@endif
				@append
		@endswitch
	@endif
</span>