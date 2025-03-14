<div class="row">

	<div class="col-12 col-xl-3">
		<select id="attributetype" class="form-control">
			<option value="-all-">-all-</option>
			@foreach ($attributetypes as $o)
				<option value="{{ $o->name_lc }}">{{ $o->name }}</option>
			@endforeach
		</select>
	</div>

	<div class="col-12 col-xl-9">
		@foreach ($attributetypes as $o)
			<span id="at-{{ $o->name_lc }}">
				<table class="schema table table-sm table-bordered table-striped">
					<thead>
					<tr>
						<th class="table-dark" colspan="2">{{ $o->name }}<span class="float-end"><abbr title="{{ $o->line }}"><i class="fas fa-fw fa-file-contract"></i></abbr></span></th>
					</tr>
					</thead>

					<tbody>
					<tr>
						<td class="w-25">@lang('Description')</td><td><strong>{{ $o->description ?: __('(no description)')}}</strong></td>
					</tr>
					<tr>
						<td><abbr title="@lang('Object Identifier')">OID</abbr></td><td><strong>{{ $o->oid }}</strong></td>
					</tr>
					<tr>
						<td>@lang('Obsolete')</td><td><strong>@lang($o->is_obsolete ? 'Yes' : 'No')</strong></td>
					</tr>
					<tr>
						<td>@lang('Inherits from')</td>
						<td><strong>@if ($o->sup_attribute)<a class="attributetype" id="{{ strtolower($o->sup_attribute) }}" href="#{{ strtolower($o->sup_attribute) }}">{{ $o->sup_attribute }}</a>@else @lang('(none)')@endif</strong></td>
					</tr>
					<tr>
						<td>@lang('Parent to')</td>
						<td>
							<strong>
								@if (! $o->children->count())
									@lang('(none)')
								@else
									@foreach ($o->children->sort() as $child)
										@if($loop->index)</strong> <strong>@endif
										<a class="attributetype" id="{{ strtolower($child) }}" href="#{{ strtolower($child) }}">{{ $child }}</a>
									@endforeach
								@endif
							</strong>
						</td>
					</tr>
					<tr>
						<td>@lang('Equality')</td><td><strong>{{ $o->equality ?: __('(not specified)') }}</strong></td>
					</tr>
					<tr>
						<td>@lang('Ordering')</td><td><strong>{{ $o->ordering ?: __('(not specified)') }}</strong></td>
					</tr>
					<tr>
						<td>@lang('Substring Rule')</td><td><strong>{{ $o->sub_str_rule ?: __('(not specified)') }}</strong></td>
					</tr>
					<tr>
						<td>@lang('Syntax')</td><td><strong>{{ ($o->syntax_oid && $x=$server->schemaSyntaxName($o->syntax_oid)) ? $x->description : __('(unknown syntax)') }} @if($o->syntax_oid)({{ $o->syntax_oid }})@endif</strong></td>
					</tr>
					<tr>
						<td>@lang('Single Valued')</td><td><strong>@lang($o->is_single_value ? 'Yes' : 'No')</strong></td>
					</tr>
					<tr>
						<td>@lang('Collective')</td><td><strong>@lang($o->is_collective ? 'Yes' : 'No')</strong></td>
					</tr>
					<tr>
						<td>@lang('User Modification')</td><td><strong>@lang($o->is_no_user_modification ? 'Yes' : 'No')</strong></td>
					</tr>
					<tr>
						<td>@lang('Usage')</td><td><strong>{{ $o->usage ?: __('(not specified)') }}</strong></td>
					</tr>
					<tr>
						<td>@lang('Maximum Length')</td><td><strong>{{ is_null($o->max_length) ? __('(not applicable)') : sprintf('%s %s',number_format($o->max_length),Str::plural('character',$o->max_length)) }}</strong></td>
					</tr>
					<tr>
						<td>@lang('Aliases')</td>
						<td><strong>
							@if ($o->aliases->count())
								@foreach ($o->aliases as $alias)
									@if ($loop->index)</strong> <strong>@endif
									<a class="attributetype" id="{{ strtolower($alias) }}" href="#{{ strtolower($alias) }}">{{ $alias }}</a>
								@endforeach
							@else
								@lang('(none)')
							@endif
						</strong></td>
					</tr>
					<tr>
						<td>@lang('Used by ObjectClasses')</td>
						<td>
							@if ($o->used_in_object_classes->count())
								@foreach ($o->used_in_object_classes as $class => $structural)
									@if($structural)
										<strong>
									@endif
									<a class="objectclass" id="{{ strtolower($class) }}" href="#{{ strtolower($class) }}">{{ $class }}</a>
									@if($structural)
										</strong>
									@endif
								@endforeach
							@else
								@lang('(none)')
							@endif
						</td>
					</tr>
					<tr>
						<td>@lang('Required by ObjectClasses')</td>
						<td>
							@if ($o->required_by_object_classes->count())
								@foreach ($o->required_by_object_classes as $class => $structural)
									@if($structural)
										<strong>
									@endif
									<a class="objectclass" id="{{ strtolower($class) }}" href="#{{ strtolower($class) }}">{{ $class }}</a>
									@if($structural)
										</strong>
									@endif
								@endforeach
							@else
								@lang('(none)')
							@endif
						</td>
					</tr>
					<tr>
						<td>@lang('Force as MAY by config')</td><td><strong>@lang($o->forced_as_may ? 'Yes' : 'No')</strong></td>
					</tr>
					</tbody>
				</table>
			</span>
		@endforeach
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		<!-- Links to object class -->
		$('.objectclass')
			.on('click',function(item) {
				$('.nav-item a[href="#objectclasses"]').tab('show');

				$('#objectclass').val(item.target.id).trigger('change');
			});

		<!-- Handle our parent to/inherits from fields -->
		$('.attributetype')
			.on('click',function(item) {
				$('.nav-item a[href="#attributetypes"]').tab('show');
				$('#attributetype').val(item.target.id).trigger('change');

				return false;
			});

		<!-- Handle our select list -->
		$('#attributetype')
			.select2({width: '100%'})
			.on('change',function(item) {
				if (item.target.value === '-all-') {
					$('#attributetypes span').each(function() { $(this).show(); });

				} else {
					$('#attributetypes span').each(function() {
						if ($(this)[0].id.match(/select2/) || (! $(this)[0].id))
							return;

						if ('at-'+item.target.value === $(this)[0].id)
							$(this).show();
						else
							$(this).hide();
					});
				}
			});
	});
</script>