@extends('architect::layouts.error')

@section('title')
	599: @lang('Untrapped Error')
@endsection

@section('content')
	{{ $exception->getMessage() }}
@endsection