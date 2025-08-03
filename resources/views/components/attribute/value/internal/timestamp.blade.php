<!-- $o=Internal\Timestamp::class -->
<!-- apacheDS, the timestamp is: 20250803033900.291Z -->
<!-- openldap, the timestamp is: 20250803032604Z -->
<!-- 389ds, the timestamp is: 20250430215448Z -->
@if(preg_match('/[0-9]+\.[0-9]+Z/',$value))
	{{ \Carbon\Carbon::createFromFormat('YmdHis.uZ',$value)->format(config('pla.datetime_format','Y-m-d H:i:s')) }}
@else
	{{ \Carbon\Carbon::createFromFormat('YmdHisZ',$value)->format(config('pla.datetime_format','Y-m-d H:i:s')) }}
@endif