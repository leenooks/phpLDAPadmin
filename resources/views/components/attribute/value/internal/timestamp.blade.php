<!-- $o=Internal\Timestamp::class -->
{{ \Carbon\Carbon::createFromTimestamp(strtotime($value))->format(config('pla.datetime_format','Y-m-d H:i:s')) }}