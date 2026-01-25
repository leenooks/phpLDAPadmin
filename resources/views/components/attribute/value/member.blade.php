<div class="input-group has-validation">
	<input type="text"
		{{ $attributes->only('class')->class([
			'is-invalid'=>($e=$errors->get($o->name_lc.'.'.$dotkey))
		]) }}
		name="{{ $o->name_lc }}[{{ $attrtag }}][]"
		value="{{ $value }}"
		readonly>

	@if($value)
		@if($o->dn_exists($value))
			<span class="input-group-end text-black-50"><a href="#{{ Crypt::encryptString($value) }}"><i class="fas fa-fw fa-external-link-alt"></i></a></span>
		@else
			<span class="input-group-end text-danger"><i class="fas fa-fw fa-ban" data-bs-toggle="tooltip" title="@lang('DN doesnt exist')"></i></span>
		@endif
	@else
		<span class="input-group-end"></span>
	@endif

	<x-form.invalid-feedback :errors="$e"/>
</div>

@section('page-scripts')
	<script type="text/javascript">
		$(document).ready(function() {
			$('attribute#{{ $o->name_lc }} a').on('click',function() {
				location.replace(web_base+$(this).attr('href'));
				location.reload();
				return false;
			})
		});
	</script>
@endsection