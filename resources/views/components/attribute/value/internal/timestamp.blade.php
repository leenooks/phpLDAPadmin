@use(Carbon\Carbon)

<!-- $o=Internal\Timestamp::class -->
<!-- apacheDS, the timestamp is: 20250803033900.291Z -->
<!-- openldap, the timestamp is: 20250803032604Z -->
<!-- 389ds, the timestamp is: 20250430215448Z -->
<!-- lldap, the timestamp is: 2026-01-30T23:05:17.578672322+00:00 -->
@if(preg_match('/[0-9]+\.[0-9]+Z/',$value))
	{{ Carbon::createFromFormat('YmdHis.uZ',$value)->format(config('pla.datetime_format','Y-m-d H:i:s')) }}
@elseif(preg_match('/[0-9]+Z/',$value))
	{{ Carbon::createFromFormat('YmdHisZ',$value)->format(config('pla.datetime_format','Y-m-d H:i:s')) }}
@else
	{{ Carbon::parse($value) }}
@endif