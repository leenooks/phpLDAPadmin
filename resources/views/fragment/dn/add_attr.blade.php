<div id="newattrs"></div>

<!-- Add new attributes -->
<div class="row">
	<div class="col-12 col-sm-1 col-md-2"></div>
	<div class="col-12 col-sm-10 col-md-8">
		<div class="d-none" id="newattr-select">
			<div class="row">
				<div class="col-12 bg-dark text-light p-2">
					<i class="fas fa-plus-circle"></i> Add New Attribute
				</div>
			</div>

			<div class="row">
				<div class="col-12 pt-2">
					<x-form.select id="newattr" label="Select from..." :options="$o->getMissingAttributes()->sortBy('name')->unique('name')->map(fn($item)=>['id'=>$item->name,'value'=>$item->name_lc])"/>
				</div>
			</div>
		</div>
	</div>
</div>