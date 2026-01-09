<!-- $o=Entry -->
@use(App\Ldap\Entry)

<table class="table table-borderless">
	<tr class="border-bottom line-height-2">
		<td class="p-1 pt-0" rowspan="2">
			@if($x=$o->getObject('jpegphoto'))
				<x-attribute.values :o="$x" :new="false"/>
			@else
				<div class="page-title-icon f32 m-2"><i class="{{ $o->icon() ?? 'fas fa-info' }}"></i></div>
			@endif
		</td>
		<td class="text-end align-bottom pb-0 mb-0 pt-2 pe-3 {{ $x ? 'ps-3' : '' }}"><strong class="user-select-all">{{ $o->getDn() ?: $container }}</strong></td>
	</tr>
	<tr>
		<td class="align-bottom font-size-xs" colspan="2">
			<table class="table table-condensed table-borderless w-100">
				@if($x=$o->getObject('createtimestamp'))
					<tr class="mt-1">
						<td class="p-0 pe-2">@lang('Created')</td>
						<th class="p-0">
							<x-attribute.values :o="$x" :new="false"/> [<x-attribute.values :o="$o->getObject('creatorsname')" :new="false"/>]
						</th>
					</tr>
				@endif
				@if($x=$o->getObject('modifytimestamp'))
					<tr class="mt-1">
						<td class="p-0 pe-2">@lang('Modified')</td>
						<th class="p-0">
							<x-attribute.values :o="$x" :new="false"/> [<x-attribute.values :o="$o->getObject('modifiersname')" :new="false"/>]
						</th>
					</tr>
				@endif
				@if($x=$o->getObject($o->getGuidKey()))
					<tr class="mt-1">
						<td class="p-0 pe-2">UUID</td>
						<th class="p-0">
							<x-attribute.values :o="$x" :new="false"/>
						</th>
					</tr>
				@endif
				<!-- It is assumed that langtags contains at least Entry::TAG_NOTAG -->
				@if(($x=$o->getLangTags()
					->flatMap(fn($item)=>$item->values())
					->unique()
					->sort()
					->filter(fn($item)=>($item !== Entry::TAG_NOTAG))
					->map(fn($item)=>preg_replace('/'.Entry::LANG_TAG_PREFIX.'/','',$item)))
					->count())
					<tr class="mt-1">
						<td class="p-0 pe-2">Tags</td>
						<th class="p-0">{{ $x->join(', ') }}</th>
					</tr>
				@endif
			</table>
		</td>
	</tr>
</table>