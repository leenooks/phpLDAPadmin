<span id="objectclass_{{$value}}">
	<div class="input-group has-validation">
		<!-- @todo Have an "x" to remove the entry, we need an event to process the removal, removing any attribute values along the way -->
		<input type="text" @class(['form-control','input-group-end','is-invalid'=>($e=$errors->get($o->name_lc.'.'.$langtag.'.'.$loop->index)),'mb-1','border-focus'=>! $o->tagValuesOld($langtag)->contains($value),'bg-success-subtle'=>$updated]) name="{{ $o->name_lc }}[{{ $langtag }}][]" value="{{ $value }}" placeholder="{{ Arr::get($o->values,$loop->index,'['.__('NEW').']') }}" @readonly(true)>
		@if ($o->isStructural($value))
			<span class="input-group-end text-black-50">@lang('structural')</span>
		@else
			<span class="input-group-end"><i class="fas fa-fw fa-xmark"></i></span>
		@endif
		<div class="invalid-feedback pb-2">
			@if($e)
				{{ join('|',$e) }}
			@endif
		</div>
	</div>
</span>