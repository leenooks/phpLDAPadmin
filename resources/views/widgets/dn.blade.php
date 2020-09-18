@extends('architect::layouts.dn')

@section('htmlheader_title')
	Home
@endsection

@section('page_title')
	{{ $dn }}
@endsection
@section('page_subtitle')
	{{ $leaf->entryuuid[0] ?? '' }}
@endsection
@section('page_icon')
	fas fa-cog
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
