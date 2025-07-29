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
		$(document).ready(function() {
			$('#newattr').on('change',function(item) {
				var oc = $('attribute#objectclass input[type=text]')
					.map((key,item)=>{return $(item).val()}).toArray();

				$.ajax({
					type: 'POST',
					url: '{{ url('entry/attr/add') }}/'+item.target.value,
					data: {
						objectclasses: oc,
					},
					cache: false,
					beforeSend: function() {},
					success: function(data) {
						$('#newattrs').append(data);
					},
					error: function(e) {
						if (e.status !== 412)
							alert('That didnt work? Please try again....');
					}
				});

				// Remove the option from the list
				$(this).find('[value="'+item.target.value+'"]').remove()

				// If there are no more options
				if ($(this).find("option").length === 1)
					$('#newattr-select').remove();
			});
		});
	</script>
@append