<div class="row pt-2">
	<div class="col-10 offset-1">
		<attribute id="{{ $o->name_lc }}">
			{{ $slot }}

			<x-attribute.widget.options :o="$o" :edit="$edit" :editable="$editable" :new="$new" :template="$template ?? FALSE"/>
		</attribute>
	</div>
</div>

<!-- START {{ $o->name_lc }}-scripts -->
@yield($o->name_lc.'-scripts')
<!-- END {{ $o->name_lc }}-scripts -->