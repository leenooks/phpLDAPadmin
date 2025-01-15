@extends('home')

@section('page_title')
	<table class="table table-borderless">
		<tr>
			<td class="{{ ($x=$o->getObject('jpegphoto')) ? 'border' : '' }}" rowspan="2">
				{!! $x ? $x->render(FALSE,TRUE) : sprintf('<div class="page-title-icon f32"><i class="%s"></i></div>',$o->icon() ?? "fas fa-info") !!}
			</td>
			<td class="text-end align-text-top p-0 {{ $x ? 'ps-5' : 'pt-2' }}"><strong>{{ $dn }}</strong></td>
		</tr>
		<tr>
			<td class="line-height-1" style="font-size: 55%;vertical-align: bottom;" colspan="2">
				<table>
					<tr>
						<td class="p-1 m-1">Created</td>
						<th class="p-1 m-1">
							{{ ($x=$o->getObject('createtimestamp')) ? $x->render() : __('Unknown') }} [{{ ($x=$o->getObject('creatorsname')) ? $x->render() : __('Unknown') }}]
						</th>
					</tr>
					<tr>
						<td class="p-1 m-1">Modified</td>
						<th class="p-1 m-1">
							{{ ($x=$o->getObject('modifytimestamp')) ? $x->render() : __('Unknown') }} [{{ ($x=$o->getObject('modifiersname')) ? $x->render() : __('Unknown') }}]
						</th>
					</tr>
					<tr>
						<td class="p-1 m-1">UUID</td>
						<th class="p-1 m-1">{{ $o->entryuuid[0] ?? '' }}</th>
					</tr>
				</table>
			</td>
		</tr>
	</table>
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