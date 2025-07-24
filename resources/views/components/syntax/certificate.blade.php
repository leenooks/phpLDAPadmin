@use(App\Classes\LDAP\Attribute\Certificate)

<!-- $o=Certificate::class -->
<x-attribute.layout :edit="$edit" :new="$new" :o="$o">
	@foreach($o->tagValuesOld('binary') as $key => $value)
		<!-- If this attribute is not handled, it'll be an Attribute::class, we'll just render it normally -->
		@if(($o instanceof Certificate) && $edit)
			<input type="hidden" name="name={{ $o->name_lc }}[binary][]" value="{{ md5($value) }}">

			<div class="input-group has-validation mb-3">
				<textarea class="form-control mb-1 font-size-md font-monospace overflow-hidden" rows="{{ count(explode("\n",$x=$o->certificate())) }}" disabled>{{ $x }}</textarea>

				<div class="invalid-feedback pb-2">
					@if($e=$errors->get($o->name_lc.'.binary.'.$loop->index))
						{{ join('|',$e) }}
					@endif
				</div>
			</div>
			<div class="input-helper small">
				<table class="table table-borderless w-75">
					<tr >
						<td class="p-0">@lang('Certificate Subject')</td>
						<th class="p-0">{{ $o->field('subject',$loop->index) }}</th>
					</tr>
					<tr >
						<td class="p-0">@lang('Certificate Issuer')</td>
						<th class="p-0">{{ $o->field('issuer',$loop->index) }}</th>
					</tr>
					<tr>
						<td class="p-0">{{ ($expire=$o->expires($loop->index))->isPast() ? __('Expired') : __('Expires') }}</td>
						<th class="p-0">{{ $expire->format(config('pla.datetime_format','Y-m-d H:i:s')) }}</th>
					</tr>
					<tr>
						<td class="p-0">@lang('Serial Number')</td>
						<th class="p-0">{{ $o->cert_info('serialNumberHex',$loop->index) }}</th>
					</tr>
					<tr>
						<td class="p-0">@lang('Subject Key Identifier')</td>
						<th class="p-0">{{ $o->subject_key_identifier($loop->index) }}</th>
					</tr>
					<tr>
						<td class="p-0">@lang('Authority Key Identifier')</td>
						<th class="p-0">{{ $o->authority_key_identifier($loop->index) }}</th>
					</tr>
				</table>
			</div>

		@else
			<span class="form-control mb-1"><pre class="m-0">{{ $o->render_item_old('binary.'.$key) }}</pre></span>
		@endif
	@endforeach
</x-attribute.layout>