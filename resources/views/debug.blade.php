<div class="card card-solid">
	<div class="card-body">
		<div class="row">
			<div class="col-12">
				<h3 class="d-inline-block">DEBUG Information</h3>

				<table class="table">
					<thead>
					<tr>
						<th>Setting</th>
						<th>Value</th>
					</tr>
					</thead>

					<tbody>
					<!-- User Logged In -->
					<tr>
						<td>User</td>
						<td>{{ $user }}</td>
					</tr>

					<!-- Base DNs -->
					<tr>
						<td>BaseDN(s)</td>
						<td>
							<table class="table table-sm table-borderless">
								@foreach(\App\Ldap\Entry::baseDN()->sort(function($item) { return $item->sortKey; }) as $item)
									<tr>
										<td>{{ $item->getDn() }}</td>
									</tr>
								@endforeach
							</table>
						</td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>