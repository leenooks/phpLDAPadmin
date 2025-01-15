@extends('home')

@section('page_title')
	@include('fragment.dn.header')
@endsection

@section('main-content')
	<x-note/>
	<x-success/>
	<x-error/>

	<div class="main-card mb-3 card">
		<form id="dn-update" method="POST" class="needs-validation" action="{{ url('entry/update/commit') }}" novalidate>
			@csrf
			<input type="hidden" name="dn" value="{{ $o->getDNSecure() }}">

			<div class="card-body">
				<div class="row">
					<div class="col-12 col-lg-6 col-xl-4 mx-auto pt-3">

						<div class="card-title"><h3>@lang('Do you want to make the following changes?')</h3></div>
						<table class="table table-bordered table-striped">
							<thead>
							<tr>
								<th>Attribute</th>
								<th>OLD</th>
								<th>NEW</th>
							</tr>
							</thead>

							<tbody>
							@foreach ($o->getDirty() as $key => $value)
								<tr>
									<th rowspan="{{ $x=max(count($value),count(Arr::get($o->getOriginal(),$key,[])))}}">{{ $key }}</th>
									@for($xx=0;$xx<$x;$xx++)
										@if($xx)
											</tr><tr>
										@endif

										<td>{{ Arr::get(Arr::get($o->getOriginal(),$key),$xx,'['.strtoupper(__('New Value')).']') }}</td>
										<td>{{ ($y=Arr::get($value,$xx)) ?: '['.strtoupper(__('Deleted')).']' }}<input type="hidden" name="{{ $key }}[]" value="{{ $y }}"></td>
									@endfor
								</tr>
							@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</form>

		<div class="row p-3">
			<div class="col-12 offset-sm-2 col-sm-4 col-lg-2 mx-auto">
				<x-form.cancel/>
				<x-form.submit action="Update" form="dn-update"/>
			</div>
		</div>

	</div>
@endsection