<!-- $o=Attribute/ObjectClass::class -->
<x-attribute.layout :edit="$edit" :new="$new" :o="$o" :langtag="$langtag">
	@foreach(Arr::get(old($o->name_lc,[$langtag=>$new ? [NULL] : $o->tagValues($langtag)]),$langtag,[]) as $key => $value)
		@if($edit)
			<x-attribute.widget.objectclass :o="$o" :edit="$edit" :new="$new" :loop="$loop" :value="$value" :langtag="$langtag"/>
		@else
			{{ $o->render_item_old($key) }}
			@if ($o->isStructural($value))
				<input type="hidden" name="{{ $o->name_lc }}[{{ $langtag }}][]" value="{{ $value }}">
				<span class="float-end">@lang('structural')</span>
			@endif
			<br>
		@endif
	@endforeach
</x-attribute.layout>