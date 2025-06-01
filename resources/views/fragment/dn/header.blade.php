@use(App\Ldap\Entry)

<table class="table table-borderless">
	<tr class="border-bottom line-height-2">
		<td class="p-1 pt-0" rowspan="2">
			{!! ($x=$o->getObject('jpegphoto')) ? $x->render(FALSE,TRUE) : sprintf('<div class="page-title-icon f32 m-2"><i class="%s"></i></div>',$o->icon() ?? "fas fa-info") !!}
		</td>
		<td class="text-end align-bottom pb-0 mb-0 pt-2 pe-3 {{ $x ? 'ps-3' : '' }}"><strong class="user-select-all">{{ $o->getDn() }}</strong></td>
	</tr>
	<tr>
		<td class="align-bottom font-size-xs" colspan="2">
			<table class="table table-condensed table-borderless w-100">
				<tr class="mt-1">
					<td class="p-0 pe-2">Created</td>
					<th class="p-0">
						<x-attribute :o="$o->getObject('createtimestamp')"/> [<x-attribute :o="$o->getObject('creatorsname')"/>]
					</th>
				</tr>
				<tr class="mt-1">
					<td class="p-0 pe-2">Modified</td>
					<th class="p-0">
						<x-attribute :o="$o->getObject('modifytimestamp')"/> [<x-attribute :o="$o->getObject('modifiersname')"/>]
					</th>
				</tr>
				<tr class="mt-1">
					<td class="p-0 pe-2">UUID</td>
					<th class="p-0">
						<x-attribute :o="$o->getObject('entryuuid')"/>
					</th>
				</tr>
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