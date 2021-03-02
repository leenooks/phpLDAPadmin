@extends('architect::layouts.dn')

@section('page_title')
	{{ $dn }}
@endsection
@section('page_subtitle')
	{{ $leaf->entryuuid[0] ?? '' }}
@endsection
@section('page_icon')
	{{ $leaf->icon() ?? 'fas fa-info' }}
@endsection

@section('main-content')
	<div class="bg-white p-3">
		<table class="table">
			@foreach ($attributes as $attribute => $value)
				<tr>
					<th>{{ $attribute }}</th>
					<td>{!! is_array($value) ? join('<br>',$value) : $value  !!}</td>
				</tr>
			@endforeach
		</table>
	</div>
@endsection
