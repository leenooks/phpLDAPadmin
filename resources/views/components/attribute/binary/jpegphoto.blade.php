<!-- $o=Binary\JpegPhoto::class -->
<table class="table table-borderless p-0 m-0">
	<tr>
		@foreach ($o->values as $value)
			@switch ($x=$f->buffer($value,FILEINFO_MIME_TYPE))
				@case('image/jpeg')
				@default
					<td>
						<img class="jpegphoto" src="data:{{ $x }};base64, {{ base64_encode($value) }}" />

						@if($edit)
							<br><span class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i> @lang('Delete')</span>
						@endif
					</td>
			@endswitch
		@endforeach
	</tr>
</table>