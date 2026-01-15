@foreach($o->langtags->merge($o->binarytags) as $langtag)
	<span @class(['tab-pane','active'=>$loop->index === 0]) id="langtag-{{ $o->name_lc }}-{{ $langtag }}" data-attrtag="{{ $langtag }}" role="tabpanel">
		@if($o->render_tables && (! $loop->index))
			<table class="table table-borderless p-0 m-0">
		@endif

		@if((old() || ($edit ?? FALSE)) && ($template ?? NULL) && ($av=$template->attributeValue($o->name_lc)) && $template->isAttributeCalculated($o->name_lc))
			<!-- AutoValue Lock -->
			<input type="hidden" name="_auto_value[{{ $o->name_lc }}]" value="{{ $av }}">
		@endif

		{{-- At this point $values is the original/updated values, however old() might have md5 values --}}
		@foreach(($values=collect(Arr::get(old($o->name_lc,$o->values ?: [$langtag=>['']]),$langtag,$o->values->get($langtag,['']))))->take(config('pla.limit.values')) as $key => $value)
			<x-attribute.value
				@class([
					'form-control',
					'mb-1',
					'no-edit'=>(! ($editable ?? FALSE) && ($o->dn)),
					'modal-edit'=>$o->modal_editable,
					'border-focus'=>$o->isDirty() || (! strlen($value)),
					'bg-success-subtle'=>$updated ?? FALSE])
				:o="$o"
				:value="$av ?? $value"
				:attrtag="$langtag"
				:index="$key"
				:edit="$edit ?? FALSE"
				:editable="$editable ?? FALSE"
				:new="$new ?? FALSE"
				:template="$template ?? NULL"/>
		@endforeach

		@foreach(($x=$values->skip(config('pla.limit.values'))->filter()) as $key => $value)
			<input type="text" class="d-none" name="{{ $o->name_lc }}[{{ $langtag }}][]" value="{{ $value }}">
		@endforeach

		@if($x->count())
			<button type="button" class="btn btn-sm btn-outline-light mt-2" id="extra-{{ $o->name_lc }}" name="values-show" data-attr="{{ $o->name_lc }}" data-tag="{{ $langtag }}" data-bs-toggle="modal" data-bs-target="#page-modal">
				@lang(':count more values ',['count'=>$x->count()]) &hellip;
			</button>
		@endif

		@if($o->render_tables && (! $loop->index))
			</table>
		@endif
	</span>
@endforeach

@if((($edit ?? FALSE) || ($editable ?? FALSE)) && (! $o->is_rdn))
	<span @class(['tab-pane']) id="langtag-{{ $o->name_lc }}-+" role="tabpanel">
		<span class="d-flex font-size-sm alert alert-warning p-2">
			It is not possible to create new language tags at the moment. This functionality should come soon.<br>
			You can create them with an LDIF import though.
		</span>
	</span>
@endif