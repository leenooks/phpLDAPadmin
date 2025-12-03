<!-- $o=Binary\JpegPhoto::class -->
@use(App\Ldap\Entry)

<tr>
	@switch($x=$f->buffer($o->isDirty() ? $value : $o->render_item_old($dotkey),FILEINFO_MIME_TYPE))
		@case('image/jpeg')
		@default
			<td>
				<input type="hidden" name="{{ $o->name_lc }}[{{ $attrtag }}{{ $o->isDirty() ? '' : Entry::TAG_MD5 }}][]" value="{{ $o->isDirty() ? $o->render_item_new($dotkey) : md5($o->values->dot()->get($dotkey)) }}">
				<img alt="{{ $o->dn }}"
					@class([
						'border',
						'rounded',
						'p-2',
						'm-0',
						'is-invalid'=>($e=$errors->get($o->name_lc.'.'.$dotkey)),'bg-success-subtle'=>$updated])
					src="data:{{ $x }};base64, {{ $o->render_item_new($dotkey) }}"/>

				@if($edit||$editable)
					<br>
					<!-- @todo TO IMPLEMENT -->
					<button class="btn btn-sm btn-danger deletable d-none mt-3" disabled><i class="fas fa-trash-alt"></i> @lang('Delete')</button>

					<x-form.invalid-feedback :errors="$e"/>
				@endif
			</td>
	@endswitch
</tr>