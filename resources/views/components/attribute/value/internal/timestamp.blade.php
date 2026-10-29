@use(App\Classes\LDAP\Attribute\Syntax\Timestamp)

<!-- $o=Internal\Timestamp::class -->
<!-- apacheDS, the timestamp is: 20250803033900.291Z -->
<!-- openldap, the timestamp is: 20250803032604Z -->
<!-- openldap ppolicy (permanent lock), the timestamp is: 000001010000Z -->
<!-- 389ds, the timestamp is: 20250430215448Z -->
<!-- lldap, the timestamp is: 2026-01-30T23:05:17.578672322+00:00 -->
{{ ($x=Timestamp::parse($value)) ? $x->format(config('pla.datetime_format','Y-m-d H:i:s')) : $value }}