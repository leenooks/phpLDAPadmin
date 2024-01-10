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
	@if(session()->has('note'))
		<div class="alert alert-info">
			<h4 class="alert-heading"><i class="fas fa-fw fa-note-sticky"></i> Note:</h4>
			<hr>
			<p>{{ session()->pull('note') }}</p>
		</div>
	@endif

	@if(session()->has('success'))
		<div class="alert alert-success">
			<h4 class="alert-heading"><i class="fas fa-fw fa-thumbs-up"></i> Success!</h4>
			<hr>
			<p>{{ session()->pull('success') }}</p>
			<ul style="list-style-type: square;">
				@foreach (session()->pull('updated') as $key => $values)
					<li>{{ $key }}: {{ join(',',$values) }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	@if($errors->any())
		<div class="alert alert-danger">
			<h4 class="alert-heading"><i class="fas fa-fw fa-thumbs-down"></i> Error?</h4>
			<hr>
			<ul style="list-style-type: square;">
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

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

				<div class="row pt-3">
					<div class="col-12 offset-sm-2 col-sm-4 col-lg-2 mx-auto">
						<span id="form-reset" class="btn btn-outline-danger">@lang('Reset')</span>
						<span id="form-submit" class="btn btn-success">@lang('Update')</span>
					</div>
				</div>
			</div>
		</form>
	</div>
@endsection

@section('page-scripts')
	<script type="text/javascript">
		$(document).ready(function() {
			$('#form-reset').click(function() {
				$('#dn-update')[0].reset();
			});

			$('#form-submit').click(function() {
				$('#dn-update')[0].submit();
			});
		});
	</script>
@append