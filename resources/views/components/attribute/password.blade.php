<!-- $o=Password::class -->
<div class="row">
	<div class="col-12">
		<div id="{{ $o->name_lc }}">
			@foreach (old($o->name_lc,$o->values) as $value)
				@if ($edit)
					<input type="password" class="form-control mb-1 @if($x=($o->values->search($value) === FALSE)) border-focus @endif" type="text" name="{{ $o->name_lc }}[]" value="{{ str_repeat('*',10) }}" readonly="true">
				@else
					{{ $value }}<br>
				@endif
			@endforeach
		</div>
	</div>

	<div class="col-12 col-sm-6 col-lg-4">
		<span class="btn btn-sm btn-outline-dark mt-3 mb-3"><i class="fas fa-user-check"></i> @lang('Check Password')</span>
	</div>
</div>