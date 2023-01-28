@extends('architect::layouts.error')

@section('error')
	@lang('LDAP Server Unavailable')
@endsection

@section('content')
	{{ $exception->getMessage() }}
@endsection