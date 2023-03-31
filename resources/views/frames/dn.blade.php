@extends('layouts.dn')

@section('page_title')
	<table class="table table-borderless">
		<tr>
			<td class="{{ ($x=Arr::get($o->getAttributes(),'jpegphoto')) ? 'border' : '' }}" rowspan="2">{!! $x ?: sprintf('<div class="page-title-icon f32"><i class="%s"></i></div>',$o->icon() ?? "fas fa-info") !!}</td>
			<td class="text-end align-text-top p-0 {{ $x ? 'ps-5' : 'pt-2' }}"><strong>{{ $dn }}</strong></td>
		</tr>
		<tr>
			<td class="line-height-1" style="font-size: 55%;vertical-align: bottom;" colspan="2">
				<table>
					<tr>
						<td class="p-1 m-1">Created</td>
						<th class="p-1 m-1">{{ ($x=Arr::get($o->getAttributes(),'createtimestamp')) ? $x : __('Unknown') }} [{{ ($x=Arr::get($o->getAttributes(),'creatorsname')) ? $x : __('Unknown') }}]</th>
					</tr>
					<tr>
						<td class="p-1 m-1">Modified</td>
						<th class="p-1 m-1">{{ ($x=Arr::get($o->getAttributes(),'modifytimestamp')) ? $x : __('Unknown') }} [{{ ($x=Arr::get($o->getAttributes(),'modifiersname')) ? $x : __('Unknown') }}]</th>
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
		<div class="card-body">
			<div class="card-header-tabs">
				<ul class="nav nav-tabs">
					<li class="nav-item"><a data-bs-toggle="tab" href="#attributes" class="nav-link active">{{ __('Attributes') }}</a></li>
					<li class="nav-item"><a data-bs-toggle="tab" href="#internal" class="nav-link">{{ __('Internal') }}</a></li>
					@env(['local'])
						<li class="nav-item"><a data-bs-toggle="tab" href="#debug" class="nav-link">{{ __('Debug') }}</a></li>
					@endenv
				</ul>

				<div class="tab-content">
					<!-- All Attributes -->
					<div class="tab-pane active" id="attributes" role="tabpanel">
						<form id="form-entry" method="POST" action="{{ url('entry/update') }}">
							@csrf

							<input type="hidden" name="dn" value="{{ $o->getDNSecure() }}">

							<div class="row">
								<div class="offset-2 col-8">
									<table class="table">
										@foreach ($o->getVisibleAttributes() as $ao)
											<tr class="bg-light text-dark small">
												<th class="w-25">
													<abbr title="{{ $ao->description }}">{{ $ao->name }}</abbr>
													<!-- Attribute Hints -->
													<span class="float-end">
														@foreach($ao->hints as $name => $description)
															@if ($loop->index),@endif
															<abbr title="{{ $description }}">{{ $name }}</abbr>
														@endforeach
													</span>
												</th>
											</tr>
											<tr>
												<td class="ps-5">
													<x-attribute :edit="true" :o="$ao"/>
												</td>
											</tr>
										@endforeach
									</table>
								</div>
							</div>

							<div class="row">
								<div class="col-12 offset-sm-2 col-sm-4 col-lg-2">
									<span id="form-reset" class="btn btn-outline-danger">{{ __('Reset') }}</span>
									<span id="form-submit" class="btn btn-success">{{ __('Update') }}</span>
								</div>
							</div>
						</form>
					</div>

					<!-- Internal Attributes -->
					<div class="tab-pane" id="internal" role="tabpanel">
						<div class="row">
							<div class="offset-2 col-8">
								<table class="table">
									@foreach ($o->getInternalAttributes() as $ao)
										<tr class="bg-light text-dark small">
											<th class="w-25">
												<abbr title="{{ $ao->description }}">{{ $ao->name }}</abbr>
											</th>
										</tr>
										<tr>
											<td class="ps-5">
												<x-attribute :edit="false" :o="$ao"/>
											</td>
										</tr>
									@endforeach
								</table>
							</div>
						</div>
					</div>

					<!-- Debug -->
					<div class="tab-pane" id="debug" role="tabpanel">
						<div class="row">
							<div class="col-6">
								@dump($o)
							</div>
							<div class="col-6">
								@dump($o->getAttributes())
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('page-scripts')
	<script>
		$(document).ready(function() {
			$('#reset').click(function() {
				$('#form-entry')[0].reset();
			})

			$('#form-submit').click(function() {
				$('#form-entry')[0].submit();
			})

			// Create a new entry when Add Value clicked
			$('.addable').click(function(item) {
				var cln = $(this).parent().parent().find('input:last').clone();
				cln.val('').attr('placeholder',undefined);
				cln.appendTo('#'+item.currentTarget.id)
			})
		});
	</script>
@append