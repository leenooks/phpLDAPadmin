<div class="row">
	<div class="col-12 col-xl-3">
		<select id="objectclass" class="form-control">
			<option value="-all-">-all-</option>
			@foreach($objectclasses->groupBy(fn($item)=>$item->isStructural()) as $oo)
				<optgroup label="{{ __($oo->first()->isStructural() ? 'Structural' : 'Auxillary') }} Object Class"></optgroup>
				@foreach($oo as $o)
					<option value="{{ $o->name_lc }}">{{ $o->name }}</option>
				@endforeach
			@endforeach
		</select>
	</div>

	<div class="col-12 col-xl-9">
		@foreach($objectclasses as $o)
			<span id="oc-{{ $o->name_lc }}">
				<table class="schema table table-sm table-bordered table-striped">
					<thead>
					<tr>
						<th class="table-dark" colspan="4">{{ $o->name }}<span class="float-end"><abbr title="{{ $o->line }}"><i class="fas fa-fw fa-file-contract"></i></abbr></span></th>
					</tr>
					</thead>

					<tbody>
					<tr>
						<td class="w-25">@lang('Description')</td><td colspan="3"><strong>{{ $o->description ?: __('(no description)') }}</strong></td>
					</tr>
					<tr>
						<td class="w-25"><abbr title="@lang('Object Identifier')">OID</abbr></td><td colspan="3"><strong>{{ $o->oid }}</strong></td>
					</tr>
					<tr>
						<td>@lang('Type')</td><td colspan="3"><strong>{{ $o->type_name }}</strong></td>
					</tr>
					<tr>
						<td>@lang('Inherits from')</td>
						<td colspan="3">
							<strong>
								@if($o->sup->count() === 0)
									@lang('(none)')
								@else
									@foreach($o->sup as $sup)
										@if($loop->index)</strong> <strong>@endif
										<a class="objectclass" id="{{ strtolower($sup) }}" href="#{{ strtolower($sup) }}">{{ $sup }}</a>
									@endforeach
								@endif
							</strong>
						</td>
					</tr>

					<tr>
						<td>@lang('Parent to')</td>
						<td colspan="3">
							<strong>
								@if(strtolower($o->name) === 'top')
									<a class="objectclass" id="-all-">(all)</a>
								@elseif(! $o->getChildObjectClasses()->count())
									@lang('(none)')
								@else
									@foreach($o->getChildObjectClasses() as $childoc)
										@if($loop->index)</strong> <strong>@endif
										<a class="objectclass" id="{{ strtolower($childoc) }}" href="#{{ strtolower($childoc) }}">{{ $childoc }}</a>
									@endforeach
								@endif
							</strong>
						</td>
					</tr>

					<tr>
						<td class="align-top w-50" colspan="2">
							<table class="clearfix table table-sm table-borderless">
								<thead>
								<tr>
									<th class="table-primary">@lang('Required Attributes')</th>
								</tr>
								</thead>

								<tbody>
								<tr>
									<td>
										<ul class="ps-3" style="list-style-type: square;">
											@foreach($o->getMustAttrs(TRUE) as $oo)
												<li>{{ $oo->name }} @if($oo->source !== $o->name)[<strong><a class="objectclass" id="{{ strtolower($oo->source) }}" href="#{{ strtolower($oo->source) }}">{{ $oo->source }}</a></strong>]@endif</li>
											@endforeach
										</ul>
									</td>
								</tr>
								</tbody>
							</table>
						</td>

						<td class="align-top w-50" colspan="2">
							<table class="clearfix table table-sm table-borderless">
								<thead>
								<tr>
									<th class="table-primary">@lang('Optional Attributes')</th>
								</tr>
								</thead>

								<tbody>
								<tr>
									<td>
										<ul class="ps-3" style="list-style-type: square;">
											@foreach($o->getMayAttrs(TRUE) as $oo)
												<li>{{ $oo->name }} @if($oo->source !== $o->name)[<strong><a class="objectclass" id="{{ strtolower($oo->source) }}" href="#{{ strtolower($oo->source) }}">{{ $oo->source }}</a></strong>]@endif</li>
											@endforeach
										</ul>
									</td>
								</tr>
								</tbody>
							</table>
						</td>
					</tr>
					</tbody>
				</table>
			</span>
		@endforeach
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		<!-- Handle our parent to/inherits from fields -->
		$('.objectclass')
			.on('click',function(item) {
				$('#objectclass').val(item.target.id).trigger('change');

				return false;
			});

		<!-- Handle our select list -->
		$('#objectclass')
			.select2({width: '100%'})
			.on('change',function(item) {
				if (item.target.value === '-all-') {
					$('#objectclasses span').each(function() { $(this).show(); });

				} else {
					$('#objectclasses span').each(function() {
						if ($(this)[0].id.match(/select2/) || (! $(this)[0].id))
							return;

						if ('oc-'+item.target.value === $(this)[0].id)
							$(this).show();
						else
							$(this).hide();
					});
				}
			});
	});
</script>