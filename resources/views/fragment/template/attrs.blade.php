<div class="card-body">
	<div class="tab-content">
		@foreach($o->getVisibleAttributes()->filter(fn($item)=>$template->attributes->keys()->map('strtolower')->contains($item->name_lc)) as $ao)
			<x-attribute :o="$ao" :edit="false" :editable="false" :new="false" :template="$template" :updated="$updated->contains($ao->name)"/>
		@endforeach
	</div>
</div>