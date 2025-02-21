@extends('architect::layouts.error')

@section('error')
	597: @lang('LDAP Server Unavailable')
@endsection

@section('content')
	@switch($exception->getMessage())
		@case("Can't contact LDAP server")
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
						<ul>
							<li>@lang('Your LDAP server is not connectable')</li>
							<li>@lang('Your LDAP server hostname is incorrect')</li>
							<li>@lang('Your DNS server cannot resolve that hostname')</li>
							<li>@lang('Your Resolver is not pointing to your DNS server')</li>
						</ul>
					</td>
				</tr>
			</table>
			@break

		@default
			{{ $exception->getMessage() }}
	@endswitch
@endsection