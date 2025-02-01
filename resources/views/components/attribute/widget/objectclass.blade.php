<div class="input-group has-validation">
	<!-- @todo Have an "x" to remove the entry, we need an event to process the removal, removing any attribute values along the way -->
	<input type="text" @class(['form-control','is-invalid'=>($e=$errors->get($o->name_lc.'.'.$loop->index)),'mb-1','border-focus'=>$o->values->contains($value)]) name="{{ $o->name_lc }}[]" value="{{ $value }}" placeholder="{{ Arr::get($o->values,$loop->index,'['.__('NEW').']') }}" @readonly(true)>
	<div class="invalid-feedback pb-2">
		@if($e)
			{{ join('|',$e) }}
		@endif
	</div>
</div>
<span class="input-group-delete"><i class="fas fa-fw fa-xmark"></i></span>

<style>
	.input-group-delete {
		float: right;
		position: relative;
		top: -32px;
		right: 10px;
	}
</style>