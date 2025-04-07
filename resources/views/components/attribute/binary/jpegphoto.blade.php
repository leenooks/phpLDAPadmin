<!-- @todo We are not handling redirect backs yet with updated photos -->
<!-- $o=Binary\JpegPhoto::class -->
<x-attribute.layout :edit="$edit" :new="$new" :o="$o" :langtag="$langtag">
	<table class="table table-borderless p-0 m-0">
		@foreach($o->tagValuesOld() as $key => $value)
			<tr>
				@switch($x=$f->buffer($value,FILEINFO_MIME_TYPE))
					@case('image/jpeg')
					@default
						<td>
							<input type="hidden" name="{{ $o->name_lc }}[{{ $langtag }}][]" value="{{ md5($value) }}">
							<img alt="{{ $o->dn }}" @class(['border','rounded','p-2','m-0','is-invalid'=>($e=$errors->get($o->name_lc.'.'.$langtag.'.'.$loop->index))]) src="data:{{ $x }};base64, {{ base64_encode($value) }}" />

							@if($edit)
								<br>
								<!-- @todo TO IMPLEMENT -->
								<button class="btn btn-sm btn-danger deletable d-none mt-3" disabled><i class="fas fa-trash-alt"></i> @lang('Delete')</button>

								<div class="invalid-feedback pb-2">
									@if($e)
										{{ join('|',$e) }}
									@endif
								</div>
							@endif
						</td>
				@endswitch
			</tr>
		@endforeach
	</table>
</x-attribute.layout>