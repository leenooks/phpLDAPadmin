@use(App\Ldap\Entry)

<div class="row pb-3">
	<div class="col-12 offset-lg-1 col-lg-10">
		<div class="row">
			<div class="col-12 bg-light text-dark p-2 rounded-2">
				<span class="d-flex justify-content-between">
					<span style="width: 20em;">
						<strong class="align-middle"><abbr title="{{ (($x=$template?->attributeTitle($o->name)) ? $o->name.': ' : '').$o->description }}">{{ $x ?: $o->name }}</abbr></strong>
						@if($new)
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

						<!-- Attribute Hints -->
						@if($updated)
							<span class=" small text-success ms-2" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip-success" title="@lang('Updated')"><i class="fas fa-fw fa-marker"></i> </span>
						@endif
					</span>

					<div role="group" class="btn-group-sm nav btn-group">
						@if((! $o->no_attr_tags) && ($has_default=$o->langtags->contains(Entry::TAG_NOTAG)))
							<span data-bs-toggle="tab" href="#langtag-{{ $o->name_lc }}-{{ Entry::TAG_NOTAG }}" @class(['btn','btn-outline-light','border-dark-subtle','active','addable d-none'=>$o->langtags->count() === 1])>
								<i class="fas fa-fw fa-border-none" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" aria-label="No Lang Tag" data-bs-original-title="No Lang Tag"></i>
							</span>
						@endif

						@if((! $o->no_attr_tags) && (! $o->is_rdn) && (! $template))
							<span data-bs-toggle="tab" href="#langtag-{{ $o->name_lc }}-+" class="bg-primary-subtle btn btn-outline-primary border-primary addable d-none">
								<i class="fas fa-fw fa-plus text-dark" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" aria-label="Add Lang Tag" data-bs-original-title="Add Lang Tag"></i>
							</span>
						@endif

						@foreach(($langtags=$o->langtags->filter(fn($item)=>$item !== Entry::TAG_NOTAG)) as $langtag)
							<span data-bs-toggle="tab" href="#langtag-{{ $o->name_lc }}-{{ $langtag }}" @class(['btn','btn-outline-light','border-dark-subtle','active'=>(! isset($has_default)) || (! $has_default) ])>
								<span class="f16" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" aria-label="{{ $langtag }}" data-bs-original-title="{{ ($x=preg_replace('/'.Entry::LANG_TAG_PREFIX.'/','',$langtag)) }}"><i class="flag {{ $x }}"></i></span>
							</span>
						@endforeach
					</div>
				</span>
			</div>
		</div>

		@switch($template?->attributeType($o->name))
			@case('select')
				<x-attribute.template.select :o="$o" :template="$template" :edit="(! $template?->attributeReadOnly($o->name)) && $edit" :new="$new"/>
				@break;

			@default
				<x-attribute :o="$o" :edit="(! $template?->attributeReadOnly($o->name)) && $edit" :new="$new" :updated="$updated"/>
		@endswitch
	</div>
</div>

@yield($o->name_lc.'-scripts')