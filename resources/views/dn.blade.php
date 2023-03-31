@extends('architect::layouts.app')

{{--
@section('htmlheader_title')
	@lang('Home')
@endsection

@section('page_title')
@endsection
@section('page_icon')
@endsection
--}}

@section('main-content')
	@include('frames.dn')
@endsection

@section('page-scripts')
	<script type="text/javascript">
		var basedn = {!! $bases->toJson() !!};
	</script>
@append