<!-- @todo We are not handling redirect backs yet with updated photos -->
<!-- $o=Binary\JpegPhoto::class -->
<x-attribute.layout :edit="$edit" :new="false" :o="$o">
	<table class="table table-borderless p-0 m-0">
		@foreach (($old ? $o->old_values : $o->values) as $value)
			<tr>
				@switch ($x=$f->buffer($value,FILEINFO_MIME_TYPE))
					@case('image/jpeg')
					@default
						<td>
							<input type="hidden" name="{{ $o->name_lc }}[]" value="{{ md5($value) }}">
							<img @class(['border','rounded','p-2','m-0','is-invalid'=>($e=$errors->get($o->name_lc.'.'.$loop->index))]) src="data:{{ $x }};base64, {{ base64_encode($value) }}" />

							@if ($edit)
								<br>
								<!-- @todo TO IMPLEMENT -->
								<span class="btn btn-sm btn-danger deletable d-none mt-3"><i class="fas fa-trash-alt"></i> @lang('Delete')</span>

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