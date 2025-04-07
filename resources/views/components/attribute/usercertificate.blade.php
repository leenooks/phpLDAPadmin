<!-- $o=UserCertificate::class -->
<x-attribute.layout :edit="$edit ?? FALSE" :new="$new ?? FALSE" :o="$o" langtag="binary">
	@foreach($o->tagValuesOld('binary') as $key => $value)
		@if($edit)
			<input type="hidden" name="name={{ $o->name_lc }}[binary][]" value="{{ md5($value) }}">

			<div class="input-group has-validation mb-3">
				<textarea class="form-control mb-1 font-monospace" rows="{{ count(explode("\n",$x=$o->certificate())) }}" style="overflow:hidden" disabled>{{ $x }}</textarea>

				<div class="invalid-feedback pb-2">
					@if($e=$errors->get($o->name_lc.'.'.$langtag.'.'.$loop->index))
						{{ join('|',$e) }}
					@endif
				</div>
			</div>
			<div class="input-helper">
				@lang('Certificate Subject'): <strong>{{ $o->subject($loop->index) }}</strong><br/>
				{{ ($expire=$o->expires($loop->index))->isPast() ? __('Expired') : __('Expires') }}: <strong>{{ $expire->format(config('pla.datetime_format','Y-m-d H:i:s')) }}</strong>
			</div>

		@else
			<span class="form-control mb-1"><pre class="m-0">{{ $o->render_item_old('binary.'.$key) }}</pre></span>
		@endif
	@endforeach
</x-attribute.layout>