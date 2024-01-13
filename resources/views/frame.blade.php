@extends('architect::layouts.app')

@section('main-content')
	@include('frames.'.$subframe)
@endsection

@section('page-scripts')
	<script type="text/javascript">
		var basedn = {!! $bases->toJson() !!};
	</script>
@append