@extends('layouts.dn')

@section('page_title')
	<table class="table table-borderless">
		<tr>
			<td style="border-radius: 5px;"><div class="page-title-icon f32"><i class="fas fa-info"></i></div></td>
			<td class="top text-end align-text-top p-0 pt-2"><strong>@lang('Server Info')</strong><br><small>{{ $s->rootDSE()->entryuuid[0] ?? '' }}</small></td>
		</tr>
	</table>
@endsection

@section('main-content')
	<div class="main-card mb-3 card">
		<div class="card-body">
			<table class="table">
				@foreach ($s->rootDSE()->getObjects() as $attribute => $ao)
					<tr>
						<th class="w-25">
							{!! ($x=$s->schema('attributetypes',$attribute))
								? sprintf('<a class="attributetype" id="strtolower(%s)" href="%s">%s</a>',$x->name_lc,url('schema/attributetypes',$x->name_lc),$x->name)
								: $attribute !!}
						</th>
						<td>
							<x-attribute :edit="false" :o="$ao"/>
						</td>
					</tr>
				@endforeach
			</table>
		</div>
	</div>
@endsection