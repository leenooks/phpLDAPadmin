<!-- $o=Attribute::class -->
<x-attribute.layout :edit="false" :new="false" :detail="true" :o="$o">
	@foreach(old($o->name_lc,($new ?? FALSE) ? [NULL] : $o->values) as $value)
		<div class="input-group">
			<input type="text" @class(['form-control','mb-1']) name="{{ $o->name_lc }}[]" value="{{ \Carbon\Carbon::createFromTimestamp(strtotime($value))->format(config('pla.datetime_format','Y-m-d H:i:s')) }}" @disabled(true)>
		</div>
	@endforeach
</x-attribute.layout>