<div class="row">
	<div class="col-12 col-xl-3">
		<select id="matchingrule" class="form-control">
			<option value="-all-">-all-</option>
			@foreach ($matchingrules as $o)
				<option value="{{ $o->name_lc }}">{{ $o->name }}</option>
			@endforeach
		</select>
	</div>

	<div class="col-12 col-xl-9">
		@foreach ($matchingrules as $o)
			<span id="me-{{ $o->name_lc }}">
				<table class="schema table table-sm table-bordered table-striped">
					<thead>
					<tr>
						<th class="table-dark" colspan="2">{{ $o->name }}<span class="float-end"><abbr title="{{ $o->line }}"><i class="fas fa-fw fa-file-contract"></i></abbr></span></th>
					</tr>
					</thead>

					<tbody>
					<tr>
						<td class="w-25">@lang('Description')</td><td><strong>{{ $o->description ?: __('(no description)') }}</strong></td>
					</tr>
					<tr>
						<td class="w-25"><abbr title="@lang('Object Identifier')">OID</abbr></td><td><strong>{{ $o->oid }}</strong></td>
					</tr>
					<tr>
						<td class="w-25">@lang('Syntax')</td><td><strong>{{ $o->syntax }}</strong></td>
					</tr>
					<tr>
						<td>@lang('Used by Attributes')</td>
						<td>
							<strong>
								@if ($o->used_by_attrs->count() === 0)
									@lang('(none)')
								@else
									@foreach ($o->used_by_attrs as $attr)
										@if($loop->index)</strong> <strong>@endif
										<a class="attributetype" id="{{ strtolower($attr) }}" href="#at-{{ strtolower($attr) }}">{{ $attr }}</a>
									@endforeach
								@endif
							</strong>
						</td>
					</tr>
					</tbody>
				</table>
			</span>
		@endforeach
	</div>
</div>

<script type="text/javascript">
	function hl_attribute(item,count) {
		if ((count < 50) && (! loaded['attributetypes'])) {
			setTimeout(hl_attribute,250,item,++count);
		} else if (count >= 50) {
			return false;
		} else {
			$('#attributetype').val(item.target.id).trigger('change');
			return true;
		}
	}

	$(document).ready(function() {
		$('.attributetype')
			.on('click',function(item) {
				$('.nav-item a[href="#attributetypes"]').tab('show');

				return hl_attribute(item,0);
			});

		<!-- Handle our parent to/inherits from fields -->
		$('.matchingrule')
			.on('click',function(item) {
				$('#matchingrule').val(item.target.id).trigger('change');

				return false;
			});

		<!-- Handle our select list -->
		$('#matchingrule')
			.select2({width: '100%'})
			.on('change',function(item) {
				if (item.target.value === '-all-') {
					$('#matchingrules span').each(function() { $(this).show(); });

				} else {
					$('#matchingrules span').each(function() {
						if ($(this)[0].id.match(/select2/) || (! $(this)[0].id))
							return;

						if ('me-'+item.target.value === $(this)[0].id)
							$(this).show();
						else
							$(this).hide();
					});
				}
			});
	});
</script>