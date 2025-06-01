@section('page_title')
	<table class="table table-borderless">
		<tr>
			<td><div class="page-title-icon f32"><i class="fas fa-upload"></i></div></td>
			<td class="top text-start align-text-top p-0 pt-2"><strong>@lang('LDIF Import Result')</strong><br><small>@lang('To Server') <strong>{{ $server->name }}</strong></small></td>
		</tr>
	</table>
@endsection

@section('main-content')
	<div class="row">
		<div class="offset col-12">
			<div class="main-card mb-3 card">
				<div class="card-body">
					<div class="card-header-tabs">
						<ul class="nav nav-tabs">
							<li class="nav-item"><a data-bs-toggle="tab" href="#result" class="nav-link active">@lang('Import Result')</a></li>
							<li class="nav-item"><a data-bs-toggle="tab" href="#ldif" class="nav-link">@lang('LDIF')</a></li>
						</ul>

						<div class="tab-content">
							<div class="tab-pane active" id="result" role="tabpanel">
								<table class="table table-borderless">
									<thead>
									<tr>
										<th>@lang('DN')</th>
										<th>@lang('Result')</th>
										<th class="text-end">@lang('Line')</th>
									</tr>
									</thead>
									@foreach($result as $item)
										<tr>
											<td>{{ $item->get('dn') }}</td>
											<td>{{ $item->get('result') }}</td>
											<td class="text-end">{{ $item->get('line') }}</td>
										</tr>
									@endforeach
								</table>
							</div>

							<div class="tab-pane" id="ldif" role="tabpanel">
								<pre class="code"><code>{{ $ldif }}</code></pre>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('page-scripts')
	<script type="text/javascript">
		$(document).ready(function() {
			$('pre code').html(function(index, html) {
				return html.trim().replace(/^(.*)$/mg, "<span class=\"line\">$1</span>");
			});
		});
	</script>
@append