@use(App\Ldap\Entry)

@extends('layouts.dn')

@section('page_title')
	@include('fragment.dn.header',[
		'o'=>($oo=$server->fetch(old('container',$container))),
		'langtags'=>collect(),
	])
@endsection

@section('page_status')
	<x-error/>
@endsection

@section('main-content')
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
											name="objectclass[{{ Entry::TAG_NOTAG }}][]"
											old="objectclass.{{ Entry::TAG_NOTAG }}"
											:label="__('Select a Structural ObjectClass...')"
											:options="($oc=$server->schema('objectclasses'))
												->filter(fn($item)=>$item->isStructural())
												->sortBy(fn($item)=>$item->name_lc)
												->map(fn($item)=>['id'=>$item->name,'value'=>$item->name])"
											multiple="false"
										/>
									</div>
								</div>
								@break

							@case(2)
								<x-attribute-type :o="$o->getObject('rdn')" :edit="TRUE" :new="FALSE" :langtag="Entry::TAG_NOTAG" :updated="FALSE"/>

								@foreach ($o->getVisibleAttributes() as $ao)
									<x-attribute-type :o="$ao" :edit="TRUE" :new="FALSE" :langtag="Entry::TAG_NOTAG" :updated="FALSE"/>
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
			$('span.addable.d-none').removeClass('d-none');
			$('span.deletable.d-none').removeClass('d-none');
			$('#newattr-select.d-none').removeClass('d-none');
		}

		$(document).ready(function() {
			@if($step === 2)
				$('#newattr').on('change',function(item) {
					var oc = $('attribute#objectClass input[type=text]')
						.map((key,item)=>{return $(item).val()}).toArray();

					$.ajax({
						type: 'POST',
						url: '{{ url('entry/attr/add') }}/'+item.target.value,
						data: {
							objectclasses: oc,
						},
						cache: false,
						beforeSend: function() {},
						success: function(data) {
							$('#newattrs').append(data);
						},
						error: function(e) {
							if (e.status != 412)
								alert('That didnt work? Please try again....');
						},
					});

					// Remove the option from the list
					$(this).find('[value="'+item.target.value+'"]').remove()

					// If there are no more options
					if ($(this).find("option").length === 1)
						$('#newattr-select').remove();
				});
			@endif

			editmode();
		});
	</script>
@append