@use(App\Classes\LDAP\Attribute\Binary\{Certificate,CertificateList,JpegPhoto})
@use(App\Classes\LDAP\Attribute\{Member,ObjectClass})

<span class="p-0 m-0">
	@if($o->is_rdn && $editable)
		<span id="entry-rename" class="btn btn-sm btn-outline-focus mt-3" data-bs-toggle="modal" data-bs-target="#page-modal"><i class="fas fa-fw fa-exchange"></i> @lang('Rename')</span>

	@elseif(($edit || $editable) && $o->can_addvalues && (! $o->isDynamic()))
		@switch(get_class($o))
			@case(Certificate::class)
			@case(CertificateList::class)
				@break

			@case(Member::class)
				<button type="button" name="member-manage" @class(['btn','btn-sm','btn-outline-primary','mt-3','addable','d-none'=>$editable]) data-bs-toggle="modal" data-bs-target="#page-modal"><i class="fas fa-fw fa-plus"></i> @lang('Add Member')</button>

				@section('page-scripts')
					<script type="text/javascript">
						$(document).ready(function() {
							// Show our ObjectClass modal so that we can add more objectclasses
							$('#page-modal').on('shown.bs.modal',function(item) {
								pagemodal_eventhandled = ($(item.relatedTarget).attr('name') === 'member-manage');

								// Make sure the event is for us
								if (! pagemodal_eventhandled)
									return;

								var that = $(this).find('.modal-content');

								$.ajax({
									method: 'GET',
									url: '{{ url('modal/member-manage') }}/'+dn,
									dataType: 'html',
									cache: false,
									beforeSend: before_send_spinner(that),

								}).done(function(html) {
									that.empty().html(html);

								}).fail(ajax_error)
							});

							$('#page-modal').on('hide.bs.modal',function() {
								var updates = attribute_values('destination','select','option');

								if (updates.length)
									// Go through the updated items and ensure the input-group-end reflects that the entry exists
									update_from_modal('member',updates).forEach(function(item) {
										$('attribute#member [value="'+item+'"]')
											.next('.input-group-end')
											.removeClass('text-danger')
											.removeClass('text-black-50')
											.addClass('text-success')
											.empty()
											.append('<i class="fas fa-fw fa-plus"></i>')
									});
							});
						});
					</script>
				@append
				@break

			@case(ObjectClass::class)
				<span @class(['btn','btn-sm','btn-outline-primary','mt-3','addable','d-none'=>$editable]) data-bs-toggle="modal" data-bs-target="#new_objectclass-modal"><i class="fas fa-fw fa-plus"></i> @lang('Add Objectclass')</span>

				<!-- NEW OBJECT CLASS -->
				<div class="modal fade" id="new_objectclass-modal" tabindex="-1" aria-labelledby="new_objectclass-label" aria-hidden="true" data-bs-backdrop="static">
					<div class="modal-dialog modal-lg modal-fullscreen-lg-down">
						<div class="modal-content">
							<div class="modal-header">
								<h1 class="modal-title fs-5" id="new_objectclass-label">New Object Class</h1>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>

							<div class="modal-body">
								<x-form.select id="newoc" :label="__('Select from').'...'"/>
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

							var oc = $('attribute#objectclass input[type=text]')
								.map((key,item)=>$(item).val())
								.toArray();

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
										dataType: 'html',
										cache: false,
										success: function(data) {
											$('attribute#{{ $o->name_lc }} .tab-content .tab-pane.active').append(data);
										},
										error: function(e) {
											if (e.status !== 412)
												alert('That didnt work? Please try again....');
										},
									});

									// Get a list of attributes already on the page, so we dont double up
									$.ajax({
										method: 'POST',
										url: '{{ url('ajax/schema/objectclass/attrs') }}/'+item,
										data: {
											attrs: $('attribute').map(function () { return $(this).attr('id'); }).toArray()
										},
										cache: false,
										success: function(data) {
											// Render any must attributes
											if (data.must.length) {
												var newattr = $('select#rdn');
												var oldoptions = $('select#rdn option').map((i,o)=>o.value).get();

												data.must.forEach(function(item) {
													if ($('attribute#'+item.toLowerCase()).length)
														return;

													// Add attribute to the page
													$.ajax({
														method: 'POST',
														url: '{{ url('entry/attr/add') }}/'+item.toLowerCase(),
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

													// If this is a new entry, add the required attributes to the RDN
													if (! oldoptions.includes(item))
														newattr.append(new Option(item,item,false,false));

													// Sort the attributes
													newattr
														.append($('select#rdn option')
															.remove()
															.sort(function (a,b) {
																let at = $(a).text(),
																	bt = $(b).text();
																return (at > bt) ? 1 : ((at < bt) ? -1 : 0);
															}))
														.val('');
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
											url: '{{ url('ajax/schema/objectclass/attrs') }}/'+item,
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
													var x = $('#'+attr.toLowerCase()).find('input');

													x.css('background-color','#f0c0c0')
														.attr('readonly',true)
														.attr('placeholder',x.val())
														.val('');
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
				<span @class(['btn','btn-sm','btn-outline-primary','mt-3','addable','d-none'=>$editable]) id="{{ $o->name_lc }}-upload" disabled><i class="fas fa-fw fa-file-arrow-up"></i> @lang('Upload JpegPhoto')</span>
				@section('page-scripts')
					<script type="text/javascript">
							$(document).ready(function() {
								$('#{{ $o->name_lc }}-upload.addable').click(function(e) {
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
				@if($o->values_old->count() && (! $template) && $new)
					<span @class(['btn','btn-sm','btn-outline-primary','mt-3','addable','d-none'=>$editable]) id="{{ $o->name_lc }}-addnew"><i class="fas fa-fw fa-plus"></i> @lang('Add Value')</span>
				@endif

				@section('page-scripts')
					@if($o->can_addvalues && (! $template) && ($edit || $editable))
						<script type="text/javascript">
							$(document).ready(function() {
								// Create a new entry when Add Value clicked
								$('form#dn-edit #{{ $o->name_lc }}-addnew.addable').click(function(item) {
									var attribute = $(this).closest('attribute');
									var active = attribute.find('.tab-content .tab-pane.active');

									active.find('input:last')
										.clone()
										.attr('value','')
										.attr('placeholder','[@lang('NEW')]')
										.addClass('border-focus')
										.appendTo(active);
								});
							});
						</script>
					@endif
				@append
		@endswitch
	@endif
</span>