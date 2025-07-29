<!-- $o=Attribute::class -->
@foreach($o->langtags as $langtag)
	@foreach(($o->tagValues($langtag)->count() ? $o->tagValues($langtag) : [$langtag => NULL]) as $key => $value)
		<x-form.select
			@class([
				'is-invalid'=>($e=$errors->get($o->name_lc.'.'.$langtag.'.'.$loop->index)),
				'mb-1',
				'border-focus'=>! $o->tagValuesOld($langtag)->contains($value)])
			id="{{ $o->name_lc }}_{{$loop->index}}{{$template?->name ?: ''}}"
			name="{{ $o->name_lc }}[{{ $langtag }}][]"
			:value="$value"
			:options="$template->attributeOptions($o->name_lc)"
			allowclear="true"
			:disabled="! $edit"
			:readonly="false"/>

		<x-form.invalid-feedback :errors="$e"/>
	@endforeach
@endforeach