@extends('layouts.dn')

@section('page_title')
	<table class="table table-borderless">
		<tr>
			<td style="border-radius: 5px;"><div class="page-title-icon f32"><i class="fas fa-info"></i></div></td>
			<td class="top text-right align-text-top p-0 pt-2 }}"><strong>{{ __('Server Info') }}</strong><br><small>{{ $s->rootDSE()->entryuuid[0] ?? '' }}</small></td>
		</tr>
	</table>
@endsection

@section('main-content')
	<div class="bg-white p-3">
		<table class="table">
			@foreach ($s->rootDSE()->getAttributes() as $attribute => $value)
				<tr>
					<th class="w-25">
						{!! ($x=$s->schema('attributetypes',$attribute))
							? sprintf('<a class="attributetype" id="strtolower(%s)" href="%s">%s</a>',$x->name_lc,url('schema/attributetypes',$x->name_lc),$x->name)
							: $attribute !!}
					</th>
					<td>{!! $value !!}</td>
				</tr>
			@endforeach
		</table>
	</div>
@endsection