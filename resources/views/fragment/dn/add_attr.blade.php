<div id="newattrs"></div>

<hr class="opacity-05">

<!-- Add new attributes -->
<div class="row">
	<div class="col-12 offset-lg-1 col-lg-10">
		<div class="d-none round" id="newattr-select">
			<div class="row">
				<div class="col-12 bg-dark text-light p-2 rounded-2">
					<i class="fas fa-plus-circle"></i> Add New Attribute
				</div>
			</div>

			<div class="row">
				<div class="col-12 pt-2">
					<x-form.select id="newattr" label="Select from..." :options="$o->getMissingAttributes()->sortBy('name')->unique('name')->map(fn($item)=>['id'=>$item->name,'value'=>$item->name])"/>
				</div>
			</div>
		</div>
	</div>
</div>