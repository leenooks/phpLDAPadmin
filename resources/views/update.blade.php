@extends('home')

@section('page_title')
	@include('fragment.dn.header',[
		'langtags'=>($langtags=$o->getLangTags()
			->flatMap(fn($item)=>$item->values())
			->unique()
			->sort())
	])
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
								<th>Tag</th>
								<th>OLD</th>
								<th>NEW</th>
							</tr>
							</thead>

							<tbody>
							@foreach ($o->getObjects()->filter(fn($item)=>$item->isDirty()) as $key => $oo)
								<tr>
									<th rowspan="{{ $x=max($oo->values->dot()->keys()->count(),$oo->values_old->dot()->keys()->count())+1}}">
										<abbr title="{{ $oo->description }}">{{ $oo->name }}</abbr>
									</th>

									@foreach($oo->values->dot()->keys()->merge($oo->values_old->dot()->keys())->unique() as $dotkey)
										@if($loop->index)
											</tr><tr>
										@endif

										<th>
											{{ $dotkey }}
										</th>

										@if((! Arr::get($oo->values_old->dot(),$dotkey)) && (! Arr::get($oo->values->dot(),$dotkey)))
											<td colspan="2" class="text-center">@lang('Ignoring blank value')</td>
										@else
											<td>{{ (($r=$oo->render_item_old($dotkey)) !== NULL) ? $r : '['.strtoupper(__('New Value')).']' }}</td>
											<td>{{ (($r=$oo->render_item_new($dotkey)) !== NULL) ? $r : '['.strtoupper(__('Deleted')).']' }}<input type="hidden" name="{{ $key }}[{{ collect(explode('.',$dotkey))->first() }}][]" value="{{ Arr::get($oo,$dotkey) }}"></td>
										@endif
									@endforeach
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