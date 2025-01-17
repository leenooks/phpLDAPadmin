<div class="row pt-2">
	<div @class(['col-1','d-none'=>(! $edit)])></div>
	<div class="col-10 p-2">
		<div id="{{ $o->name_lc }}">
			{{ $slot }}
		</div>

		<x-attribute.widget.options :o="$o" :edit="$edit" :new="$new"/>
	</div>
</div>
