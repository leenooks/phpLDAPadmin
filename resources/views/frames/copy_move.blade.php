@use(App\Classes\LDAP\Attribute\ObjectClass)
@use(App\Ldap\Entry)

@extends('layouts.frame')

@section('page_title')
	@include('fragment.dn.header')
@endsection

@section('page_status')
	<x-error/>
	<x-failed/>
@endsection

@section('main-content')
	<div class="main-card mb-3 card">
		<div class="card-body">
			<div class="card-header-tabs">
				<ul class="nav nav-tabs mb-0">
					<li class="nav-item"><a data-bs-toggle="tab" href="#attributes" class="nav-link active">@lang('Attributes')</a></li>
				</ul>

				<div class="tab-content">
					<!-- All Attributes -->
					<div class="tab-pane active" id="attributes" role="tabpanel">
						<div class="row pt-3">
							<div class="col-12">
								<form id="dn-copy" method="POST" class="needs-validation" action="{{ url('entry/create') }}" novalidate readonly>
									@csrf

									<input type="hidden" name="_key" value="{{ $o->getDNContainerSecure('create') }}">
									<input type="hidden" name="_step" value="2">

									<div class="card-body">
										<div class="tab-content">
											<x-attribute :o="$o->rdn" :edit="true" :template="$template"/>

											@foreach($o->getVisibleAttributes() as $ao)
												<x-attribute :o="$ao" :edit="true" :new="false" :template="null"/>
											@endforeach
										</div>
									</div>
								</form>

								<div class="row pt-3">
									<div class="col-11 text-end">
										<x-form.reset form="dn-copy"/>
										<x-form.submit :action="__('Create')" form="dn-copy"/>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection