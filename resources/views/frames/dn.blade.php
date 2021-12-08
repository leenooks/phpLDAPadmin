@extends('layouts.dn')

@section('page_title')
	<table class="table table-borderless">
		<tr>
			<td class="{{ ($x=Arr::get($o->getAttributes(),'jpegphoto')) ? 'border' : '' }}" style="border-radius: 5px;">{!! $x ?: sprintf('<div class="page-title-icon f32"><i class="%s"></i></div>',$o->icon() ?? "fas fa-info") !!}</td>
			<td class="top text-right align-text-top p-0 {{ $x ? 'pl-5' : 'pt-2' }}"><strong>{{ $dn }}</strong><br><small>{{ $o->entryuuid[0] ?? '' }}</small></td>
		</tr>
	</table>
@endsection

@section('main-content')
	<div class="bg-white p-3">
		<table class="table">
			@foreach ($o->getAttributes() as $attribute => $value)
				<tr>
					<th>{{ $attribute }}</th>
					<td>{!! $value !!}</td>
				</tr>
			@endforeach
		</table>
	</div>
@endsection
