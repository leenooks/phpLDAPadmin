@use(App\Classes\LDAP\Attribute\Binary\{Certificate,CertificateList,JpegPhoto})
@use(App\Classes\LDAP\Attribute\{Member,ObjectClass})

<span class="p-0 m-0">
	@if($o->is_rdn && $editable)
		<button type="button" class="btn btn-sm btn-outline-focus mt-3" name="entry-rename" data-bs-toggle="modal" data-bs-target="#page-modal"><i class="fas fa-fw fa-exchange"></i> @lang('Rename')</button>

	@elseif(($edit || $editable) && $o->can_addvalues && (! $o->isDynamic()))
		@switch(get_class($o))
			@case(Certificate::class)
			@case(CertificateList::class)
				@break

			@case(Member::class)
				<button type="button" name="member-manage" @class(['btn','btn-sm','btn-outline-primary','mt-3','addable','d-none'=>$editable]) data-attr={{ $o->name_lc }} data-bs-toggle="modal" data-bs-target="#page-modal"><i class="fas fa-fw fa-plus"></i> @lang('Add Member')</button>

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
								modal_attr = $(item.relatedTarget).data('attr');

								$.ajax({
									method: 'GET',
									url: '{{ url('modal/member-manage') }}/'+dn,
									dataType: 'html',
									cache: false,
									beforeSend: before_send_spinner(that),

								}).done(function(html) {
									that.empty().html(html);

								}).fail(ajax_error);
							});

							$('#page-modal').on('hide.bs.modal',function() {
								var updates = attribute_values('destination','select','option');

								if (updates.length)
									// Go through the updated items and ensure the input-group-end reflects that the entry exists
									update_from_modal(modal_attr,updates).forEach(function(item) {
										$('attribute#'+modal_attr+' [value="'+item+'"]')
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
				<button type="button" @class(['btn','btn-sm','btn-outline-primary','mt-3','addable','d-none'=>$editable]) data-bs-toggle="modal" data-bs-target="#new_objectclass-modal"><i class="fas fa-fw fa-plus"></i> @lang('Add Objectclass')</button>

				<!-- NEW OBJECT CLASS -->
				<div class="modal fade" id="new_objectclass-modal" tabindex="-1" aria-labelledby="new_objectclass-label" aria-hidden="true" data-bs-backdrop="static">
					<div class="modal-dialog modal-lg modal-fullscreen-lg-down">
						<div class="modal-content">
							<div class="modal-header">
								<h1 class="modal-title fs-5" id="new_objectclass-label">New Object Class</h1>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>

							<div class="modal-body">
								<x-form.select id="newoc" :label="__('Select from').'...'" :edit="true"/>
							</div>

							<div class="modal-footer">
								<button type="button" class="btn btn-sm btn-primary" data-bs-dismiss="modal">Next</button>
							</div>
						</div>
					</div>
				</div>

				@section('page-scripts')
					<script type="text/javascript">
						<!-- attribte.widget.options/objectclass -->
						// Sort select lists
						var sort_text = function(a,b) {
							let at = $(a).text(),
								bt = $(b).text();
							return (at > bt) ? 1 : ((at < bt) ? -1 : 0);
						}

						// Rendered OC values
						function oc_rendered() {
							return $('attribute#objectclass input[type=text]')
								.map((key,item)=>item.value)
								.toArray();
						}

						function newattr_options() {
							return $('select#newattr option')
								.map((key,item)=>item.value)
								.toArray();
						}

						$(document).ready(function() {
							var modal_rendered = false;
							var oc_added = [];		// Object classes being added to this entry
							var oc_from_modal = [];
							var attr_dependancy = [];

							// Determine which attrs are dependant on objectclasses
							$('attribute').map(function(key,item) {
								attr_dependancy[item.id] = 1;
							});

							newattr_options().forEach(function(item) {
								if (item.length)
									attr_dependancy[item] = 1;
							})

							function process_oc() {
								// Find out what was selected, and add them
								oc_from_modal.forEach(function(item) {
									if (oc_added.indexOf(item) !== -1)
										return;

									// Add objectclass value to the page
									$.ajax({
										method: 'POST',
										url: '{{ url('entry/attr/add',[$o->name_lc]) }}',
										data: {
											noheader: true,
											value: item,
											objectclasses: oc_rendered(),
										},
										dataType: 'html',
										cache: false,

									}).done(function(html) {
										$('attribute#{{ $o->name_lc }} .tab-content .tab-pane.active').append(html);

										// Get a list of attributes for this new objectclass, taking into account what is on the page
										$.ajax({
											method: 'POST',
											url: '{{ url('ajax/schema/objectclass/attrs') }}/'+item,
											dataType: 'json',
											cache: false,

										}).done(function(data) {
											// Render any must attributes
											if (data.must.length) {
												data.must.forEach(function(item) {
													if (attr_dependancy[item] === undefined)
														attr_dependancy[item] = 0;

													attr_dependancy[item]++;

													// If the item is already rendered, we dont need to do anything
													if ($('attribute#'+item.toLowerCase()).length)
														return;

													// Add attribute to the page
													$.ajax({
														method: 'POST',
														url: '{{ url('entry/attr/add') }}/'+item.toLowerCase(),
														data: {
															value: item,
															objectclasses: oc_rendered(),
														},
														dataType: 'html',
														cache: false,

													}).done(function(html) {
														$('#newattrs').append(html);

													}).fail(ajax_error);

													// If this is a new entry, add the required attributes to the RDN
													if (! rdn_options().includes(item)) {
														$('select#rdn').append(new Option(item,item));
														rdn_options_sort = true;
													}

													// Remove the new attributes from the newattr select list.
													$('#newattr').find('[value="'+item+'"]').remove()
												});

												sort_rdn_options();
											}

											// Add attributes to "Add new Attribute" that are now available
											if (data.may.length) {
												data.may.forEach(function(item) {
													if (attr_dependancy[item] === undefined)
														attr_dependancy[item] = 0;

													attr_dependancy[item]++;

													if (! newattr_options().includes(item))
														$('select#newattr').append(new Option(item,item));
												});

												// Sort the attributes
												$('select#newattr')
													.append($('select#newattr option')
														.remove()
														.sort(sort_text))
													.val('');
											}

										}).fail(ajax_error);

									}).fail(ajax_error);
								});

								// Loop through oc_added, and remove anything not in oc_from_modal
								oc_added.forEach(function(item) {
									if (oc_from_modal.indexOf(item) === -1) {
										$('span#objectclass_'+item).remove();

										$.ajax({
											method: 'POST',
											url: '{{ url('ajax/schema/objectclass/attrs') }}/'+item,
											cache: false,
											dataType: 'json',

										}).done(function(data) {
											var attrs = [];

											// Remove attributes from "Add new Attribute" that are no longer available
											if (data.may.length) {
												data.may.forEach(function(item) {
													var x = $("select#newattr option[value='"+item+"']");

													if (x.length) {
														x.remove();

													// Add this to the must attrs list, because its been rendered
													} else {
														attrs.push(item);
													}
												});
											}

											data.must.concat(attrs).forEach(function(item) {
												attr_dependancy[item]--;

												if (attr_dependancy[item] === 0) {
													// Remove the rendered attribute
													$('attribute#'+item.toLowerCase())
														.parentsUntil('div#newattrs')
														.last()
														.remove();

													// Remove the option from the RDN
													$('select#rdn').find('[value="'+item+'"]').remove();
												}
											});

										}).fail(ajax_error);
									}
								});

								oc_added = oc_from_modal;
							}

							// Show our ObjectClass modal so that we can add more objectclasses
							$('#new_objectclass-modal').on('shown.bs.modal',function() {
								if (! modal_rendered)
									$.ajax({
										method: 'POST',
										url: '{{ url('entry/objectclass/add') }}',
										data: {
											oc: oc_rendered(),
										},
										dataType: 'json',
										cache: false,

									}).done(function(data) {
										$('select#newoc').select2({
											dropdownParent: $('#new_objectclass-modal'),
											theme: 'bootstrap-5',
											multiple: true,
											data: data,
										});

									}).fail(ajax_error);

								modal_rendered = true;
							})

							// When the ObjectClass modal is closed, process what was selected
							$('#new_objectclass-modal').on('hide.bs.modal',function() {
								oc_from_modal = $('select#newoc').val();

								// If nothing selected, we dont have anything to do, otherwise reprocess the objectclasses
								if (oc_added.sort().join('|') !== oc_from_modal.sort().join('|'))
									process_oc();
							});
						});
					</script>
				@append
				@break

			@case(JpegPhoto::class)
				<button type="button" @class(['btn','btn-sm','btn-outline-primary','mt-3','addable','d-none'=>$editable]) id="{{ $o->name_lc }}-upload" disabled><i class="fas fa-fw fa-file-arrow-up"></i> @lang('Upload JpegPhoto')</button>
				@section('page-scripts')
					<script type="text/javascript">
							$(document).ready(function() {
								$('#{{ $o->name_lc }}-upload.addable').on('click',function(e) {
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
					<button type="button" @class(['btn','btn-sm','btn-outline-primary','mt-3','addable','d-none'=>$editable]) id="{{ $o->name_lc }}-addnew"><i class="fas fa-fw fa-plus"></i> @lang('Add Value')</button>
				@endif

				@section('page-scripts')
					@if($o->can_addvalues && (! $template) && ($edit || $editable))
						<script type="text/javascript">
							$(document).ready(function() {
								// Create a new entry when Add Value clicked
								$('form#dn-edit #{{ $o->name_lc }}-addnew.addable').on('click',function() {
									var attribute = $(this).closest('attribute');
									var active = attribute.find('.tab-content .tab-pane.active');

									active.find('input:last')
										.clone()
										.val('')
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