@extends('architect::layouts.error')

@section('title')
	@lang('Error') <small>(598)</small>
@endsection

@section('content')
	{{ $exception->getMessage() }}
@endsection