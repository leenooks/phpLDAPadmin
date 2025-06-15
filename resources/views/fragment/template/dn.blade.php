<!-- $template=Template -->
<form id="template-edit" method="POST" class="needs-validation" action="{{ url('entry/update/pending') }}" novalidate readonly>
	@csrf

	<input type="hidden" name="dn" value="">

	<div class="card-body">
		<div class="tab-content">
			@php($up=(session()->pull('updated') ?: collect()))

			@foreach($o->getVisibleAttributes()->filter(fn($item)=>$template->attributes->contains($item->name_lc)) as $ao)
				<x-attribute-type :o="$ao" :edit="TRUE" :new="FALSE" :template="$template->name" :updated="$up->contains($ao->name_lc)"/>
			@endforeach
		</div>
	</div>
</form>