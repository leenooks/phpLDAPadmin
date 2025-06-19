@extends('architect::layouts.error')

@section('error')
	501: @lang('LDAP Authentication Error')
@endsection

@section('content')
	<table class="table table-sm table-borderless table-condensed">
		<tr>
			<th>@lang('Error')</th>
		</tr>

		<tr>
			<td colspan="2">{{ $exception->getMessage() }}</td>
		</tr>

		<tr>
			<th>@lang('Possible Causes')</th>
		</tr>
		<tr>
			<td>
				<ul class="ps-3">
					<li>The DN you used to login actually doesnt exist in the server (DN's must exist in order to login)</li>
					<li>You are attempting to use the <strong>rootdn</strong> to login (not supported)</li>
				</ul>
			</td>
		</tr>
	</table>

	<p>To suppress this message, set <strong>LDAP_ALERT_ROOTDN</strong> to <strong>FALSE</strong> before starting PLA.</p>
	<p>Back to <a href="{{ url('login') }}">login</a>?</p>

@endsection