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
	<div class="row">
		<div class="offset-1 col-10">
			<div class="main-card mb-3 card">

				<div class="card-header">
					@lang('Create New Entry') - @lang('Step') {{ $step }} @if($template) <span class="ms-auto"><i class="fa fa-fw {{ $template->icon }}"></i> {{ $template->title }}</span>@endif
				</div>

				<div class="card-body">
					<form id="dn-create" method="POST" class="needs-validation" action="{{ url((int)$step === 2 ? 'entry/create' : 'entry/add') }}" enctype="multipart/form-data" novalidate>
						@csrf

						<input type="hidden" name="_key" value="{{ Crypt::encryptString('*create|'.$container) }}">
						<input type="hidden" name="_step" value="{{ $step }}">

						@switch($step)
							@case(1)
								<div class="row">
									<div class="col-12 col-md-5">
										<x-form.select
											id="objectclass"
											name="objectclass[{{ Entry::TAG_NOTAG }}][]"
											old="objectclass.{{ Entry::TAG_NOTAG }}"
											:label="__('Select a Structural ObjectClass').'...'"
											:options="($oc=$server->schema('objectclasses'))
												->filter(fn($item)=>$item->isStructural())
												->sortBy(fn($item)=>$item->name_lc)
												->map(fn($item)=>['id'=>$item->name,'value'=>$item->name])"
											allowclear="true"
											:edit="true"
										/>
									</div>

									@if($o->templates->count())
										<div class="col-md-1">
											<strong>@lang('OR')</strong>
										</div>

										<div class="col-12 col-md-5">
											<x-form.select
												id="template"
												name="template"
												:label="__('Select a Template').'...'"
												:options="$o->templates
													->map(fn($item,$key)=>['id'=>$key,'value'=>$item->title])"
												allowclear="true"
												:edit="true"
											/>
										</div>
									@endif
								</div>
								@break

							@case(2)
								<input type="hidden" name="_template" value="{{ $template?->file }}">
								<x-attribute :o="$o->rdn" :edit="true" :template="$template"/>

								@foreach($o->getVisibleAttributes() as $ao)
									<x-attribute :o="$ao" :edit="true" :template="$template"/>
								@endforeach

								@if(! $template)
									<!-- @todo When we come back from validation the javascript to append a new attribute is not loaded -->
									<!-- @todo When we render attributes with javascript, the javascript is not loaded -->
									@include('fragment.dn.add_attr')
								@endif
								@break;
						@endswitch
					</form>

					<div class="row pt-3">
						<div class="col-11 {{ $step > 1 ? 'text-end' : '' }} pe-0">
							<x-form.reset form="dn-create"/>
							<x-form.submit :action="__('Next')" form="dn-create"/>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('page-scripts')
	<script type="text/javascript">
		var rdn_attr;

		$(document).ready(function() {
			@if($step === 1)
				$('#objectclass').on('select2:open',function(){
					$('#template').val(null).trigger('change');
				});

				$('#template').on('select2:open',function(){
					$('#objectclass').val(null).trigger('change');
				})
			@endif
		});
	</script>
@append