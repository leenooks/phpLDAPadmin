@extends('layouts.dn')

@section('page_title')
	<table class="table table-borderless">
		<tr>
			<td class="{{ ($x=Arr::get($o->getAttributes(),'jpegphoto')) ? 'border' : '' }}" style="border-radius: 5px;">{!! $x ?: sprintf('<div class="page-title-icon f32"><i class="%s"></i></div>',$o->icon() ?? "fas fa-info") !!}</td>
			<td class="top text-right align-text-top p-0 {{ $x ? 'pl-5' : 'pt-2' }}"><strong>{{ $dn }}</strong><br><small>{{ $o->entryuuid[0] ?? '' }}</small></td>
		</tr>
	</table>
@endsection

@section('main-content')
	<div class="main-card mb-3 card">
		<div class="card-body">
			<div class="card-header-tabs">
				<ul class="nav nav-tabs">
					<li class="nav-item"><a data-toggle="tab" href="#attributes" class="nav-link active">{{ __('Attributes') }}</a></li>
					<li class="nav-item"><a data-toggle="tab" href="#placeholder" class="nav-link">placeholder</a></li>
					<li class="nav-item"><a data-toggle="tab" href="#internal" class="nav-link">{{ __('Internal') }}</a></li>
					<li class="nav-item"><a data-toggle="tab" href="#addtemplate" class="nav-link">{{ __('Add Template') }}</a></li>
				</ul>

				<div class="tab-content">
					<!-- All Attributes -->
					<div class="tab-pane active" id="attributes" role="tabpanel">
						<div class="row">
							<div class="offset-2 col-8">
								<table class="table">
									@foreach ($o->getVisibleAttributes() as $ao)
										<tr class="bg-light text-dark small">
											<th class="w-25" colspan="2">
												{{ $ao->name }}
												<!-- Attribute Hints -->
												<span class="float-right"></span>
											</th>
										</tr>
										<tr>
											<td class="pl-5">
												{!! $ao->deletable() !!}<br>
												<span class="p-0 m-0" id="add{{ $ao->name_lc }}"></span>
												<span class="btn btn-sm btn-outline-primary mt-3 mb-3"><i class="fas fa-plus"></i> {{ __('Add Value') }}</span>
											</td>
											{{--
											<td>@dump($ao)</td>
											--}}
										</tr>
									@endforeach
								</table>
							</div>
						</div>
					</div>

					<!-- Templates -->
					<div class="tab-pane" id="placeholder" role="tabpanel">
						<div><i class="fas fa-fw fa-spinner fa-pulse"></i></div>
					</div>

					<!-- Internal Attributes -->
					<div class="tab-pane" id="internal" role="tabpanel">
						<div><i class="fas fa-fw fa-spinner fa-pulse"></i></div>
					</div>

					<!-- Add Template -->
					<div class="tab-pane" id="addtemplate" role="tabpanel">
						<div><i class="fas fa-fw fa-spinner fa-pulse"></i></div>
					</div>
				</div>
			</div>
		</div>
	</div>


@endsection
