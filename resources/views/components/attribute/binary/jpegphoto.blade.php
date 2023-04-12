<!-- $o=Binary\JpegPhoto::class -->
@if($edit)
	<div class="input-group has-validation @if($e=$errors->get($o->name_lc))is-invalid @endif">
@endif

	<table class="table table-borderless p-0 m-0">
		<tr>
			@foreach ($o->values as $value)
				@switch ($x=$f->buffer($value,FILEINFO_MIME_TYPE))
					@case('image/jpeg')
					@default
						<td>
							<input type="hidden" name="{{ $o->name_lc }}[]" value="{{ md5($value) }}">
							<img class="jpegphoto" src="data:{{ $x }};base64, {{ base64_encode($value) }}" />

							@if($edit)
								<br><span class="btn btn-sm btn-danger deletable d-none"><i class="fas fa-trash-alt"></i> @lang('Delete')</span>
							@endif
						</td>
				@endswitch
			@endforeach
		</tr>
	</table>

	@if($edit)
		<div class="invalid-feedback pb-2">
			@if($e)
				{{ join('|',$e) }}
			@endif
		</div>

	</div>
	@endif