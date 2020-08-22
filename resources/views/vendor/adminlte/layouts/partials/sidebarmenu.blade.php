<li class="nav-header">{{ $server ?? 'Server Name' }}</li>

@foreach (($bases ?? []) as $base)
<li class="nav-item has-treeview">
	<a href="#" class="nav-link">
		<i class="nav-icon fas fa-tree"></i>
		<p>
			{{ $base }}
			<i class="fas fa-angle-left right"></i>
		</p>
	</a>

	<ul class="nav nav-treeview">
		<div id="{{ $base }}">
	</ul>
</li>
@endforeach
