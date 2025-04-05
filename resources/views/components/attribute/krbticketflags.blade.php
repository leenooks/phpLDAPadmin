<!-- $o=KrbTicketFlags::class -->
<x-attribute.layout :edit="$edit ?? FALSE" :new="$new ?? FALSE" :o="$o">
	@foreach(Arr::get(old($o->name_lc,[$langtag=>$o->tagValues($langtag)]),$langtag,[]) as $key => $value)
		@if($edit)
			<div id="32"></div>
			<div id="16"></div>

			<div class="input-group has-validation mb-3">
				<input type="hidden" name="{{ $o->name_lc }}[{{ $langtag }}][]" value="{{ $value }}" @readonly(true)>

				<div class="invalid-feedback pb-2">
					@if($e=$errors->get($o->name_lc.'.'.$loop->index))
						{{ join('|',$e) }}
					@endif
				</div>
			</div>
		@else
			{{ $o->render_item_old($langtag.'.'.$key) }}
		@endif
	@endforeach
</x-attribute.layout>

@section($o->name_lc.'-scripts')
	<script type="text/javascript">
		var value = {{ $value ?? 0 }};
		var label = {!! $helper !!};

		function tooltip(bit) {
			if (bit === undefined)
				return;

			return label[bit] ? label[bit] : 'Bit '+bit;
		}

		function binary(s=31,e=0) {
			var result = '';

			for (let x=s;x>=e;x--) {
				var bit = (value&Math.pow(2,x));

				result += '<i id="b'+x+'" style="margin-left:-1px;" class="fs-4 bi bi-'+(bit ? '1' : '0')+'-square'+(bit ? '-fill' : '')+'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="'+tooltip(x)+'"></i>';
			}

			return result;
		}

		function krbticketflags() {
			$('div#32').append(binary(31,16));
			$('div#16').append(binary(15,0));

			$('attribute#krbTicketFlags').find('i')
				.on('click',function() {
					var item = $(this);
					if ($('form#dn-edit').attr('readonly'))
						return;

					var key = Number(item.attr('id').substring(1));

					if (item.data('old') === undefined)
						item.data('old',null);

					item.toggleClass('text-success');

					// has the item changed?
					if (item.data('old') === null) {
						// It was set to 1
						if (item.hasClass('bi-1-square-fill')) {
							item.data('old',1);
							item.removeClass('bi-1-square-fill').addClass('bi-0-square-fill');

							value -= Math.pow(2,key);

						// It was set to 0
						} else if (item.hasClass('bi-0-square')) {
							item.data('old',0);
							item.removeClass('bi-0-square').addClass('bi-1-square-fill');

							value += Math.pow(2,key);
						}

					} else {
						if (item.data('old') === 0) {
							item.removeClass('bi-1-square-fill').addClass('bi-0-square');
							value -= Math.pow(2,key);

						} else {
							item.removeClass('bi-0-square-fill').addClass('bi-1-square-fill');
							value += Math.pow(2,key);
						}

						item.data('old',null);
					}

					$('attribute#krbTicketFlags').find('input').val(value);
				});
		}

		// When returning to a Entry after an update, jquery hasnt loaded yet, so make sure we defer this to after the page has run
		if (window.$ === undefined) {
			document.addEventListener('DOMContentLoaded',() => krbticketflags());

		} else {
			krbticketflags();

			$('attribute#krbTicketFlags').find('i')
				.tooltip();
		}
	</script>
@endsection