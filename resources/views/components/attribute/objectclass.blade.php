<!-- $o=Attribute::class -->
<x-attribute.layout :edit="$edit" :new="$new" :o="$o">
	@foreach(old($o->name_lc,$o->values) as $value)
		@if($edit)
			<x-attribute.widget.objectclass :o="$o" :edit="$edit" :new="$new" :loop="$loop" :value="$value"/>
		@else
			{{ $value }}
			@if ($o->isStructural($value))
				<input type="hidden" name="{{ $o->name_lc }}[]" value="{{ $value }}">
				<span class="float-end">@lang('structural')</span>
			@endif
			<br>
		@endif
	@endforeach
</x-attribute.layout>