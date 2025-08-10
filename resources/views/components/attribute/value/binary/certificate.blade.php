<!-- $o=Certificate::class -->
@use(App\Ldap\Entry)

<input type="hidden" name="{{ $o->name_lc }}[{{ $attrtag }}{{ Entry::TAG_MD5 }}][]" value="{{ md5($o->isDirty() ? $value : $o->render_item_old($dotkey)) }}">

<div class="input-group has-validation mb-3">
	<textarea {{ $attributes->class([
		'font-size-md font-monospace overflow-hidden',
		'is-invalid'=>($e=$errors->get($o->name_lc.'.'.$dotkey))]) }}
		name="{{ $o->name_lc }}[{{ $attrtag }}][]"
		rows="{{ max(count(explode("\n",$value)),5) }}"
		@readonly(! $edit)>{{ $value }}</textarea>

	<x-form.invalid-feedback :errors="$errors->get($o->name_lc.'.'.$dotkey)"/>
</div>

@if($o->subject($dotkey))
	<div class="input-helper small">
		<table class="table table-borderless w-100">
			<tr >
				<td class="p-0">@lang('Certificate Subject')</td>
				<th class="p-0">{{ $o->subject($dotkey) }}</th>
			</tr>
			<tr >
				<td class="p-0">@lang('Certificate Issuer')</td>
				<th class="p-0">{{ $o->issuer($dotkey) }}</th>
			</tr>
			@if($expire=$o->expires($dotkey))
			<tr>
				<td class="p-0">{{ $expire->isPast() ? __('Expired') : __('Expires') }}</td>
				<th class="p-0">{{ $expire->format(config('pla.datetime_format','Y-m-d H:i:s')) }}</th>
			</tr>
			@endif
			<tr>
				<td class="p-0">@lang('Serial Number')</td>
				<th class="p-0">{{ $o->cert_info($dotkey)->get('serialNumberHex') }}</th>
			</tr>
			<tr>
				<td class="p-0">@lang('Subject Key Identifier')</td>
				<th class="p-0">{{ $o->subject_key_identifier($dotkey) }}</th>
			</tr>
			<tr>
				<td class="p-0">@lang('Authority Key Identifier')</td>
				<th class="p-0">{{ $o->authority_key_identifier($dotkey) }}</th>
			</tr>
		</table>
	</div>
@endif