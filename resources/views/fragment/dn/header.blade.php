<table class="table table-borderless">
	<tr>
		<td class="{{ ($x=$o->getObject('jpegphoto')) ? 'border' : '' }}" rowspan="2">
			{!! $x ? $x->render(FALSE,TRUE) : sprintf('<div class="page-title-icon f32"><i class="%s"></i></div>',$o->icon() ?? "fas fa-info") !!}
		</td>
		<td class="text-end align-text-top p-0 pt-2 pe-3 {{ $x ? 'ps-5' : '' }}"><strong>{{ $o->getDn() }}</strong></td>
	</tr>
	<tr>
		<td class="line-height-1" style="font-size: 55%;vertical-align: bottom;" colspan="2">
			<table>
				<tr>
					<td class="p-1 m-1">Created</td>
					<th class="p-1 m-1">
						<x-attribute :o="$o->getObject('createtimestamp')" :na="__('Unknown')"/> [<x-attribute :o="$o->getObject('creatorsname')" :na="__('Unknown')"/>]
					</th>
				</tr>
				<tr>
					<td class="p-1 m-1">Modified</td>
					<th class="p-1 m-1">
						<x-attribute :o="$o->getObject('modifytimestamp')" :na="__('Unknown')"/> [<x-attribute :o="$o->getObject('modifiersname')" :na="__('Unknown')"/>]
					</th>
				</tr>
				<tr>
					<td class="p-1 m-1">UUID</td>
					<th class="p-1 m-1">
						<x-attribute :o="$o->getObject('entryuuid')" :na="__('Unknown')"/>
					</th>
				</tr>
			</table>
		</td>
	</tr>
</table>