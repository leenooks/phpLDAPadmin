<div class="row">
	<div class="col-5 m-auto">
		<table class="schema table table-sm table-bordered table-striped">
			<thead>
			<tr>
				<th class="table-dark">{{ __('Description') }}</th>
				<th class="table-dark">OID</th>
			</tr>
			</thead>

			<tbody>
			@foreach ($ldapsyntaxes as $o)
				<tr>
					<td>
						<abbr title="{{ $o->line }}">{{ $o->description }}</abbr>
						@if ($o->binary_transfer_required)
							<span class="float-end"><i class="fas fa-fw fa-file-download"></i></span>
						@endif
						@if ($o->is_not_human_readable)
							<span class="float-end"><i class="fas fa-fw fa-tools"></i></span>
						@endif
					</td>
					<td>{{ $o->oid }}</td>
				</tr>
			@endforeach
			</tbody>
		</table>
	</div>
</div>