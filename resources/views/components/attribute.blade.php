<!-- $o=Attribute::class -->
<x-attribute.layout :edit="$edit=($edit ?? FALSE)" :new="$new=($new ?? FALSE)" :o="$o">
	<div class="col-12">
		<div class="tab-content">
			@foreach($o->langtags as $langtag)
				<span @class(['tab-pane','active'=>$loop->index === 0]) id="langtag-{{ $o->name_lc }}-{{ $langtag }}" role="tabpanel">
					@foreach(Arr::get(old($o->name_lc,[$langtag=>$new ? [NULL] : $o->tagValues($langtag)]),$langtag,[]) as $key => $value)
						<!-- AutoValue Lock -->
						@if($new && $template && ($av=$template->attributeValue($o->name_lc)))
							<input type="hidden" name="_auto_value[{{ $o->name_lc }}]" value="{{ $av }}">
						@endif

						<div class="input-group has-validation">
							<input type="text"
								@class([
									'form-control',
									'noedit'=>(! $edit) || ($o->is_rdn),
									'is-invalid'=>($e=$errors->get($o->name_lc.'.'.$langtag.'.'.$loop->index)) || ($e=$errors->get('_auto_value.'.$o->name_lc)),
									'mb-1',
									'border-focus'=>! ($tv=$o->tagValuesOld($langtag))->contains($value),
									'bg-success-subtle'=>$updated])
								name="{{ $o->name_lc }}[{{ $langtag }}][]"
								value="{{ $value ?: ($av ?? '') }}"
								placeholder="{{ ! is_null($x=$tv->get($loop->index)) ? $x : '['.__('NEW').']' }}"
								readonly
								@disabled($o->isDynamic())>

							<div class="invalid-feedback pb-2">
								@if($e)
									{{ join('|',$e) }}
								@endif
							</div>
						</div>
					@endforeach
				</span>
			@endforeach

			@if($edit && (! $o->is_rdn))
				<span @class(['tab-pane']) id="langtag-{{ $o->name_lc }}-+" role="tabpanel">
					<span class="d-flex font-size-sm alert alert-warning p-2">
						It is not possible to create new language tags at the moment. This functionality should come soon.<br>
						You can create them with an LDIF import though.
					</span>
				</span>
			@endif
		</div>
	</div>
</x-attribute.layout>

@if($new && ($x=$template?->onChange($o->name))?->count())
	@section('page-scripts')
		<!-- START: ONCHANGE PROCESSING {{ $o->name }} -->
		<script type="text/javascript">
			$('#{{ $o->name_lc }}').on('change',function() {
				{!! $x->join('') !!}
			});
		</script>
		<!-- END: ONCHANGE PROCESSING {{ $o->name }} -->
	@append
@endif