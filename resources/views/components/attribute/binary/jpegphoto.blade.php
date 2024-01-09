<!-- $o=Binary\JpegPhoto::class -->
<!-- @todo We are not handling redirect backs with updated photos -->
<div class="row pt-2">
	<div class="col-1"></div>
	<div class="col-10 p-2">
		<div id="{{ $o->name_lc }}">
			<table class="table table-borderless p-0 m-0">
				@foreach ($o->values as $value)
					<div class="input-group has-validation">
						<tr>
							@switch ($x=$f->buffer($value,FILEINFO_MIME_TYPE))
								@case('image/jpeg')
								@default
									<td>
										<input type="hidden" name="{{ $o->name_lc }}[]" value="{{ md5($value) }}">
										<img class="jpegphoto" src="data:{{ $x }};base64, {{ base64_encode($value) }}" @if($e=$errors->get($o->name_lc.'.'.$loop->index))is-invalid @endif />

										@if ($edit)
											<br>
											<!-- @todo TO IMPLEMENT -->
											<span class="btn btn-sm btn-danger deletable d-none"><i class="fas fa-trash-alt"></i> @lang('Delete')</span>

											<div class="invalid-feedback pb-2">
												@if($e)
													{{ join('|',$e) }}
												@endif
											</div>
										@endif
									</td>
							@endswitch
						</tr>
					</div>
				@endforeach
			</table>
		</div>

		<!-- @todo TO IMPLEMENT -->
		@include('components.attribute.widget.options')
	</div>
</div>