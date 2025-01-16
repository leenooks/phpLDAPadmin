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
					<div class="col-12 col-lg-6 mx-auto pt-3">

						<div class="card-title"><h3>@lang('Do you want to make the following changes?')</h3></div>
						<table class="table table-bordered table-striped w-100">
							<thead>
							<tr>
								<th>Attribute</th>
								<th>OLD</th>
								<th>NEW</th>
							</tr>
							</thead>

							<tbody>
							@foreach ($o->getAttributesAsObjects()->filter(fn($item)=>$item->isDirty()) as $key => $oo)
								<tr>
									<th rowspan="{{ $x=max($oo->values->keys()->max(),$oo->old_values->keys()->max())+1}}">
										<abbr title="{{ $oo->description }}">{{ $oo->name }}</abbr>
									</th>
									@for($xx=0;$xx<$x;$xx++)
										@if($xx)
											</tr><tr>
										@endif

										<td>{{ $oo->render_item_old($xx) ?: '['.strtoupper(__('New Value')).']' }}</td>
										<td>{{ $oo->render_item_new($xx) ?: '['.strtoupper(__('Deleted')).']' }}<input type="hidden" name="{{ $key }}[]" value="{{ Arr::get($oo->values,$xx) }}"></td>
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