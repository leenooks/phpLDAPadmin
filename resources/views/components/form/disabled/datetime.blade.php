<!-- $o=Attribute::class -->
<x-attribute.layout :edit="false" :new="false" :detail="true" :o="$o">
	@foreach(Arr::get(old($o->name_lc,[$langtag=>$o->tagValues($langtag)]),$langtag,[]) as $value)
		<div class="input-group">
			<input type="text" class="form-control mb-1" value="{{ \Carbon\Carbon::createFromTimestamp(strtotime($value))->format(config('pla.datetime_format','Y-m-d H:i:s')) }}" disabled>
		</div>
	@endforeach
</x-attribute.layout>