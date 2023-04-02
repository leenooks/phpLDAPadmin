<!-- $o=Attribute::class -->
<div class="row">
	<div class="col-12">
		<div id="{{ $o->name_lc }}">
			@foreach (old($o->name_lc,$o->values) as $value)
				@if ($edit && ! $o->is_structural)
					<input class="form-control mb-1 @if($x=($o->values->search($value) === FALSE)) border-danger @endif" type="text" name="{{ $o->name_lc }}[]" value="{{ $value }}" @if($x)placeholder="{{ Arr::get($o->values,$loop->index) }}"@endif>
				@else
					{{ $value }}@if ($o->is_structural)@lang('structural')@endif<br>
				@endif
			@endforeach
		</div>
	</div>

	<div class="col-12 col-sm-6 col-lg-4">
		@if($o->is_rdn)
			<span class="btn btn-sm btn-outline-focus mt-3 mb-3"><i class="fas fa-fw fa-exchange"></i> {{ __('Rename') }}</span>
		@elseif($edit && $o->can_addvalues)
			<div class="p-0 m-0 addable" id="{{ $o->name_lc }}">
				<span class="btn btn-sm btn-outline-primary mt-3 mb-3"><i class="fas fa-fw fa-plus"></i> {{ __('Add Value') }}</span>
			</div>
		@endif
	</div>
</div>