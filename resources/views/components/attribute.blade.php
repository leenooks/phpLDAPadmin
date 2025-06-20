<!-- $o=Attribute::class -->
<x-attribute.layout :edit="$edit=($edit ?? FALSE)" :new="$new=($new ?? FALSE)" :o="$o">
	<div class="col-12">
		<div class="tab-content">
			@foreach($o->langtags as $langtag)
				<span @class(['tab-pane','active'=>$loop->index === 0]) id="langtag-{{ $o->name_lc }}-{{ $langtag }}" role="tabpanel">
					@foreach(Arr::get(old($o->name_lc,[$langtag=>$new ? [NULL] : $o->tagValues($langtag)]),$langtag,[]) as $key => $value)
						@if($edit && (! $o->is_rdn))
							<div class="input-group has-validation">
								<input type="text" @class(['form-control','is-invalid'=>($e=$errors->get($o->name_lc.'.'.$langtag.'.'.$loop->index)),'mb-1','border-focus'=>! ($tv=$o->tagValuesOld($langtag))->contains($value),'bg-success-subtle'=>$updated]) name="{{ $o->name_lc }}[{{ $langtag }}][]" value="{{ $value }}" placeholder="{{ ! is_null($x=$tv->get($loop->index)) ? $x : '['.__('NEW').']' }}" @readonly(! $new) @disabled($o->isDynamic())>

								<div class="invalid-feedback pb-2">
									@if($e)
										{{ join('|',$e) }}
									@endif
								</div>
							</div>

						@else
							<input type="text" @class(['form-control','mb-1','bg-success-subtle'=>$updated]) value="{{ $value }}" disabled>
						@endif
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