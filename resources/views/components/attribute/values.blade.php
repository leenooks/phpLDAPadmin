@foreach($o->langtags->merge($o->binarytags) as $langtag)
	<span @class(['tab-pane','active'=>$loop->index === 0]) id="langtag-{{ $o->name_lc }}-{{ $langtag }}" role="tabpanel">
		@if($o->render_tables && (! $loop->index))
			<table class="table table-borderless p-0 m-0">
		@endif

		<!-- AutoValue Lock -->
		@if($new && $template && ($av=$template->attributeValue($o->name_lc)))
			<input type="hidden" name="_auto_value[{{ $o->name_lc }}]" value="{{ $av }}">
		@endif

		<!-- At this point $_values is the original/updated values, however old() might have md5 values -->
		@foreach(Arr::get(old($o->name_lc,$o->_values ?: [$langtag=>[]]),$langtag,[]) as $key => $value)
			<x-attribute.value
				@class([
					'form-control',
					'mb-1',
					'border-focus'=>$o->isDirty() || (! strlen($value)),
					'bg-success-subtle'=>$updated ?? FALSE])
				:o="$o"
				:attrtag="$langtag"
				:index="$key"
				:edit="$edit ?? FALSE"
				:editable="$editable ?? FALSE"
				:new="$new ?? FALSE"
				:template="$template ?? NULL"/>
		@endforeach

		@if($o->render_tables && (! $loop->index))
			</table>
		@endif
	</span>
@endforeach
