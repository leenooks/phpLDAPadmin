<!-- $template=Template -->
<form id="template-edit" method="POST" class="needs-validation" action="{{ url('entry/update/pending') }}" novalidate readonly>
	@csrf

	<input type="hidden" name="dn" value="">

	<div class="card-body">
		<div class="tab-content">
			@php($up=(session()->pull('updated') ?: collect()))
			@php($attributes=$o->template($template)?->attributes)

			@foreach($o->getVisibleAttributes()->filter(fn($item)=>in_array($item,$attributes)) as $ao)
				<x-attribute-type :o="$ao" :edit="TRUE" :new="FALSE" :updated="$up->contains($ao->name_lc)"/>
			@endforeach
		</div>
	</div>
</form>