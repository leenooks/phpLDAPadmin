<!-- $template=Template -->
<form id="template-edit" method="POST" class="needs-validation" action="{{ url('entry/update/pending') }}" novalidate readonly>
	@csrf

	<input type="hidden" name="dn" value="">

	<div class="card-body">
		<div class="tab-content">
			@php($up=(session()->get('updated') ?: collect()))

			@foreach($o->getVisibleAttributes()->filter(fn($item)=>$template->attributes->keys()->map('strtolower')->contains($item->name_lc)) as $ao)
				<x-attribute-type :o="$ao" :edit="true" :new="false" :template="$template" :updated="$up->contains($ao->name)"/>
			@endforeach
		</div>
	</div>
</form>