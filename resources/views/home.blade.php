@extends('adminlte::layouts.app')

@section('htmlheader_title')
    Home
@endsection
@section('page_title')
    {{ $user }}
@endsection

@section('contentheader_title')
@endsection
@section('contentheader_description')
    Client Home
@endsection

@section('main-content')
    @dump($user)
@endsection
