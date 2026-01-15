<div id="newattrs"></div>

<hr class="opacity-05">

<!-- Add new attributes -->
<div class="row">
	<div class="col-12 offset-lg-1 col-lg-10">
		<div class="d-none round" id="newattr-select">
			<div class="row">
				<div class="col-12 bg-dark text-light p-2 rounded-2">
					<i class="fas fa-plus-circle"></i> @lang('Add New Attribute')
				</div>
			</div>

			<div class="row">
				<div class="col-12 pt-2">
					<x-form.select id="newattr" :label="__('Select from').'...'" :options="$o->getMissingAttributes()->sortBy('name')->unique('name')->map(fn($item)=>['id'=>$item->name,'value'=>$item->name])"/>
				</div>
			</div>
		</div>
	</div>
</div>

@section('page-scripts')
	<script type="text/javascript">
		<!-- fragment.dn.add_attr -->
		// Add an attribute to a page
		function attr_render(item) {
			$.ajax({
				type: 'POST',
				url: '{{ url('entry/attr/add') }}/'+item.target.value,
				data: {
					objectclasses: oc_rendered(),
				},
				dataType: 'html',
				cache: false,

			}).done(function(html) {
				$('#newattrs').append(html);

			}).fail(ajax_error);

			// Add the attr to the RDN
			if (! rdn_options().includes(item.target.value)) {
				$('select#rdn').append(new Option(item.target.value,item.target.value));

				rdn_options_sort = true;
				sort_rdn_options();
			}

			// Remove the option from the new attr list
			$('#newattr').find('[value="'+item.target.value+'"]').remove();

			// If there are no more options
			if ($('#newattr').find('option').length === 1)
				$('#newattr-select').addClass('d-none');
		}

		$(document).ready(function() {
			$('#newattr').on('change',function(item) {
				attr_render(item);
			});

			@if($edit && $o->getMissingAttributes()->count())
				$('#newattr-select.d-none')
					.removeClass('d-none')
					.find('select')
					.attr('disabled',false);
			@endif
		});
	</script>
@append