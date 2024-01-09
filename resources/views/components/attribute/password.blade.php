<!-- $o=Password::class -->
<!-- @todo We are not handling redirect backs with updated values -->
<div class="row pt-2">
	<div class="col-1"></div>
	<div class="col-10 p-2">
		<div id="{{ $o->name_lc }}">
			@foreach ($o->values as $value)
				@if ($edit)
					<div class="input-group has-validation">
						<input type="password" class="form-control @if($e=$errors->get($o->name_lc.'.'.$loop->index))is-invalid @endif mb-1 @if($o->values->search($value) === FALSE) border-focus @endif" name="{{ $o->name_lc }}[]" value="{{ md5($value) }}" readonly="true">

						<div class="invalid-feedback pb-2">
							@if($e)
								{{ join('|',$e) }}
							@endif
						</div>
					</div>
				@else
					{{ $value }}<br>
				@endif
			@endforeach
		</div>

		@include('components.attribute.widget.options')

		<span class="p-0 m-0">
			<span class="btn btn-sm btn-outline-dark mt-3"><i class="fas fa-user-check"></i> @lang('Check Password')</span>
		</span>
	</div>
</div>
