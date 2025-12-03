<!-- $o=Attribute::class -->
@use(App\Classes\LDAP\Attribute\RDN)
@use(App\Ldap\Entry)

<div class="row pb-3">
	<div class="col-12 offset-lg-1 col-lg-10">
		<!-- Attribute Title -->
		<div class="row">
			<div class="col-12 bg-light text-dark p-2 rounded-2">
				<span class="d-flex justify-content-between">
					<span class="w-50">
						<strong class="align-middle"><abbr title="{{ (($x=$template?->attributeTitle($o->name)) ? $o->name.': ' : '').$o->description }}">{{ $x ?: $o->name }}</abbr></strong>

						@if(! $o->is_internal)
							@if($edit)
								@if($template?->attributeReadOnly($o->name_lc))
									<sup data-bs-toggle="tooltip" title="@lang('Input disabled')"><i class="fas fa-ban"></i></sup>
								@endif
								@if($ca=$template?->onChangeAttribute($o->name_lc))
									<sup data-bs-toggle="tooltip" title="@lang('Value triggers an update to another attribute')"><i class="fas fa-keyboard"></i></sup>
								@endif
								@if ($ct=$template?->onChangeTarget($o->name_lc))
									<sup data-bs-toggle="tooltip" title="@lang('Value calculated from another attribute')"><i class="fas fa-wand-magic-sparkles"></i></sup>
								@endif
								@if((! $ca) && (! $ct) && $template?->attribute($o->name_lc))
									<sup data-bs-toggle="tooltip" title="@lang('Value controlled by template')"><i class="fas fa-wand-magic"></i></sup>
								@endif
							@endif

							<!-- Attribute Hints -->
							@if($o->hints->count())
								<sup>
									[
									@foreach($o->hints as $name => $description)
										@if($loop->index),@endif
										<abbr title="{{ $description }}">{{ $name }}</abbr>
									@endforeach
									]
								</sup>
							@endif

							<!-- Attribute Updated -->
							@if($updated ?? FALSE)
								<span class="small text-success ms-2" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip-success" title="@lang('Updated')"><i class="fas fa-fw fa-marker"></i> </span>
							@endif
						@endif
					</span>

					@if((! $o->is_internal) && (! $template))
						<div class="btn-group-sm nav btn-group" role="group">
							@if(! $o->no_attr_tags)
								@if($has_default=$o->langtags->contains(Entry::TAG_NOTAG))
									<button type="button" data-bs-toggle="tab" href="#langtag-{{ $o->name_lc }}-{{ Entry::TAG_NOTAG }}" @class(['btn','btn-outline-light','border-dark-subtle','active','addable d-none'=>$o->langtags->count() === 1])>
										<i class="fas fa-fw fa-border-none" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" aria-label="No Lang Tag" data-bs-original-title="No Lang Tag"></i>
									</button>
								@endif

								@if((! $o->is_rdn) && (! $template))
									<button type="button" data-bs-toggle="tab" href="#langtag-{{ $o->name_lc }}-+" class="bg-primary-subtle btn btn-outline-primary border-primary addable d-none">
										<i class="fas fa-fw fa-plus text-dark" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" aria-label="Add Lang Tag" data-bs-original-title="Add Lang Tag"></i>
									</button>
								@endif
							@endif

							@foreach(($langtags=$o->langtags->filter(fn($item)=>$item !== Entry::TAG_NOTAG)) as $langtag)
								<button type="button" data-bs-toggle="tab" href="#langtag-{{ $o->name_lc }}-{{ $langtag }}" @class(['btn','btn-outline-light','border-dark-subtle','active'=>(! isset($has_default)) || (! $has_default) ])>
									<span class="f16" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" aria-label="{{ $langtag }}" data-bs-original-title="{{ ($x=preg_replace('/'.Entry::LANG_TAG_PREFIX.'/','',$langtag)) }}"><i class="flag {{ $x }}"></i></span>
								</button>
							@endforeach
						</div>
					@endif
				</span>
			</div>
		</div>

		<!-- Attribute Values -->
		<x-attribute.layout :o="$o" :edit="$edit" :editable="$editable ?? FALSE" :new="$new ?? FALSE" :template="$template ?? NULL">
			<div class="tab-content">
				@switch($template?->attributeType($o->name))
					@case('select')
						<x-attribute.template.select :o="$o" :edit="(! $template?->attributeReadOnly($o->name)) && $edit" :editable="$editable ?? FALSE" :new="$new ?? FALSE" :template="$template" />
						@break;

					@default
						@switch(get_class($o))
							@case(RDN::class)
								<x-attribute.rdn :o="$o" :edit="$edit" :template="$template"/>
								@break

							@default
								<x-attribute.values :o="$o" :edit="$edit" :editable="$editable ?? FALSE" :new="$new ?? FALSE" :template="$template" :updated="$updated ?? FALSE"/>
						@endswitch
				@endswitch
			</div>
		</x-attribute.layout>
	</div>
</div>

<!-- Template javascript -->
@if(($x=$template?->onChange($o->name))?->count())
	@section('page-scripts')
		<!-- START: ONCHANGE PROCESSING {{ $o->name }} -->
		<script type="text/javascript">
			$('#{{ $o->name_lc }}').on('change',function() {
				{!! $x->join('') !!}
			});

			$('attribute').on('change',function() {
				if (rdn_attr === $(this).attr('id')) {
					$('#rdn_value').val($(this).find('input').val());
				}
			});
		</script>
		<!-- END: ONCHANGE PROCESSING {{ $o->name }} -->
	@append
@endif