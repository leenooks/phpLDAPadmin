<!-- $o=Attribute::class -->
<x-attribute.layout :edit="$edit" :new="$new" :o="$o">
	@foreach($o->langtags as $langtag)
		@foreach(($o->tagValues($langtag)->count() ? $o->tagValues($langtag) : [$langtag => NULL]) as $key => $value)
			@if($edit)
				<x-form.select
					@class(['is-invalid'=>($e=$errors->get($o->name_lc.'.'.$langtag.'.'.$loop->index)),'mb-1','border-focus'=>! $o->tagValuesOld($langtag)->contains($value)])
					id="{{ $o->name_lc }}_{{$loop->index}}{{$template?->name ?: ''}}"
					name="{{ $o->name_lc }}[{{ $langtag }}][]"
					:value="$value"
					:options="$template->attributeOptions($o->name_lc)"
					allowclear="true"
					:disabled="! $new"
					:readonly="false"/>

				<div class="invalid-feedback pb-2">
					@if($e=$errors->get($o->name_lc.'.'.$langtag.'.'.$loop->index))
						{{ join('|',$e) }}
					@endif
				</div>
			@else
				{{ $o->render_item_old($langtag.'.'.$key) }}
			@endif
		@endforeach
	@endforeach
</x-attribute.layout>