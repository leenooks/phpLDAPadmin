@extends('architect::layouts.error')

@section('title')
	@lang('Not Implemented') <small>(555)</small>
@endsection

@section('content')
	{{ $exception->getMessage() }}
@endsection