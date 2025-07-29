<!-- $o=Certificate::class -->
<input type="hidden" name="name={{ $o->name_lc }}[{{ $attrtag }}][]" value="{{ md5($o->isDirty() ? $value : $o->render_item_old($dotkey)) }}">

<div class="input-group has-validation mb-3">
	<textarea class="form-control mb-1 font-size-md font-monospace overflow-hidden" rows="{{ count(explode("\n",$x=($o->isDirty() ? $value : $o->certificate()))) }}" disabled>{{ $x }}</textarea>

	<x-form.invalid-feedback :errors="$errors->get($o->name_lc.'.'.$dotkey)"/>
</div>

<div class="input-helper small">
	<table class="table table-borderless w-100">
		<tr >
			<td class="p-0">@lang('Certificate Subject')</td>
			<th class="p-0">{{ $o->subject($index) }}</th>
		</tr>
		<tr >
			<td class="p-0">@lang('Certificate Issuer')</td>
			<th class="p-0">{{ $o->issuer($index) }}</th>
		</tr>
		<tr>
			<td class="p-0">{{ ($expire=$o->expires($index))->isPast() ? __('Expired') : __('Expires') }}</td>
			<th class="p-0">{{ $expire->format(config('pla.datetime_format','Y-m-d H:i:s')) }}</th>
		</tr>
		<tr>
			<td class="p-0">@lang('Serial Number')</td>
			<th class="p-0">{{ $o->cert_info('serialNumberHex',$index) }}</th>
		</tr>
		<tr>
			<td class="p-0">@lang('Subject Key Identifier')</td>
			<th class="p-0">{{ $o->subject_key_identifier($index) }}</th>
		</tr>
		<tr>
			<td class="p-0">@lang('Authority Key Identifier')</td>
			<th class="p-0">{{ $o->authority_key_identifier($index) }}</th>
		</tr>
	</table>
</div>