<!-- $o=Internal\Timestamp::class -->
@foreach (old($o->name_lc,$o->values) as $value)
	@if($loop->index)<br>@endif
	{{ \Carbon\Carbon::createFromTimestamp(strtotime($value))->format(config('ldap.datetime_format','Y-m-d H:i:s')) }}
@endforeach