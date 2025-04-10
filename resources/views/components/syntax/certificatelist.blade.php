<!-- $o=CertificateList::class -->
<x-attribute.layout :edit="$edit" :new="$new" :o="$o" langtag="binary">
	@foreach($o->tagValuesOld('binary') as $key => $value)
		<!-- If this attribute is not handle, it'll be an Attribute::class, we'll just render it normally -->
		<span class="form-control mb-1"><pre class="m-0">{{ $o->render_item_old('binary.'.$key) }}</pre></span>
	@endforeach
</x-attribute.layout>