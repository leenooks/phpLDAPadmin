@extends('layouts.dn')

@section('page_title')
	@include('fragment.dn.header',['o'=>($oo=config('server')->fetch(old('container',$container)))])
@endsection

@section('main-content')
	<x-error/>

	<div class="row">
		<div class="offset-1 col-10">
			<div class="main-card mb-3 card">

				<div class="card-header">
					@lang('Create New Entry') - @lang('Step') {{ $step }}
				</div>

				<div class="card-body">
					<form id="dn-create" method="POST" class="needs-validation" action="{{ url((int)$step === 2 ? 'entry/create' : 'entry/add') }}" enctype="multipart/form-data" novalidate>
						@csrf

						<input type="hidden" name="key" value="{{ Crypt::encryptString('*create|'.$container) }}">
						<input type="hidden" name="step" value="{{ $step }}">

						@switch($step)
							@case(1)
								<div class="row">
									<div class="col-12 col-md-6">
										<x-form.select
											id="objectclass"
											name="objectclass[]"
											:label="__('Select a Structural ObjectClass...')"
											:options="($oc=config('server')->schema('objectclasses'))
												->filter(fn($item)=>$item->isStructural())
												->sortBy(fn($item)=>$item->name_lc)
												->map(fn($item)=>['id'=>$item->name,'value'=>$item->name])"
											multiple="false"
										/>
									</div>
								</div>
								@break

							@case(2)
								<x-attribute-type :edit="true" :o="$o->getObject('rdn')"/>

								@foreach ($o->getVisibleAttributes() as $ao)
									<x-attribute-type :edit="true" :o="$ao"/>
								@endforeach

								@include('fragment.dn.add_attr')

								@break;
						@endswitch
					</form>

					<div class="row d-none pt-3">
						<div class="col-12 {{ $step > 1 ? 'offset-sm-2' : '' }} col-lg-10">
							<x-form.reset form="dn-create"/>
							<x-form.submit action="Next" form="dn-create"/>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('page-scripts')
	<script type="text/javascript">
		var oc = {!! $oo->getObject('objectclass')->values !!};
		var rdn_attr;

		function editmode() {
			// Find all input items and turn off readonly
			$('input.form-control').each(function() {
				// Except for objectClass - @todo show an "X" instead
				if ($(this)[0].name.match(/^objectclass/))
					return;

				// Dont take of the readonly value for our RDN if it is set
				if (rdn_attr && ($(this)[0].name === rdn_attr+'[]'))
					return;

				$(this).attr('readonly',false);
			});

			// Our password type
			$('attribute#userPassword .form-select').each(function() {
				$(this).prop('disabled',false);
			})

			$('.row.d-none').removeClass('d-none');
			$('.addable.d-none').removeClass('d-none');
			$('.deletable.d-none').removeClass('d-none');
			$('#newattr-select.d-none').removeClass('d-none');
		}

		$(document).ready(function() {
			$('#newattr').on('change',function(item) {
				$.ajax({
					type: 'POST',
					beforeSend: function() {},
					success: function(data) {
						$('#newattrs').append(data);
					},
					error: function(e) {
						if (e.status != 412)
							alert('That didnt work? Please try again....');
					},
					url: '{{ url('entry/attr/add') }}/'+item.target.value,
					data: {
						objectclasses: oc,
					},
					cache: false
				});

				// Remove the option from the list
				$(this).find('[value="'+item.target.value+'"]').remove()

				// If there are no more options
				if ($(this).find("option").length === 1)
					$('#newattr-select').remove();
			});

			editmode();
		});
	</script>
@append