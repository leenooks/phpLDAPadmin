@extends('architect::layouts.error')

@section('error')
	401: @lang('LDAP Authentication Error')
@endsection

@section('content')
	{{ $exception->getMessage() }}
@endsection