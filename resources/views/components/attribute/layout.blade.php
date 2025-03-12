<div class="row pt-2">
	<div @class(['col-1','d-none'=>(! $edit) && (! ($detail ?? true))])></div>
	<div class="col-10 p-2">
		<attribute id="{{ $o->name }}">
			{{ $slot }}
		</attribute>

		<x-attribute.widget.options :o="$o" :edit="$edit" :new="$new"/>
	</div>
</div>

@yield($o->name_lc.'-scripts')