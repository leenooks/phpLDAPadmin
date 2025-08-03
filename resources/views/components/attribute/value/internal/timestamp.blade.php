<!-- $o=Internal\Timestamp::class -->
<!-- apacheDS, the timestamp is: 20250803033900.291Z -->
<!-- openldap, the timestamp is: 20250803032604Z -->
{{ \Carbon\Carbon::createFromFormat('YmdHis.uZ',$value)->format(config('pla.datetime_format','Y-m-d H:i:s')) }}