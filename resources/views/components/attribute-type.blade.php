<!-- $o=AttributeType::class -->
<div class="row pb-3">
	<div class="col-12 col-sm-1 col-md-2"></div>
	<div class="col-12 col-sm-10 col-md-8">
		<div class="row">
			<div class="col-12 bg-light text-dark p-2">
				<strong><abbr title="{{ $o->description }}">{{ $o->name }}</abbr></strong>
				<!-- Attribute Hints -->
				<span class="float-end small">
					@foreach($o->hints as $name => $description)
						@if ($loop->index),@endif
						<abbr title="{{ $description }}">{{ $name }}</abbr>
					@endforeach
				</span>
			</div>
		</div>

		<x-attribute :o="$o" :edit="true" :new="$new ?? FALSE"/>
	</div>
</div>