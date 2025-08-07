<div class="invalid-feedback pb-2">
	@if($errors)
		{{ join('|',$errors) }}
	@elseif(strlen($alt ?? ''))
		{{ $alt }}
	@endif
</div>