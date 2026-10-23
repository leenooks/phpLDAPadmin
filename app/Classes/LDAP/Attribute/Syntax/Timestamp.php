<?php

namespace App\Classes\LDAP\Attribute\Syntax;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;

use App\Classes\LDAP\Attribute\Internal;
use App\Classes\Template;

/**
 * Represents an attribute whose values are timestamps
 */
final class Timestamp extends Internal
{
	/**
	 * Convert a timestamp value into a Carbon object
	 *
	 * GeneralizedTime (RFC4517 3.3.13) allows the minutes and seconds to be omitted, eg:
	 * + apacheDS: 20250803033900.291Z
	 * + openldap: 20250803032604Z
	 * + openldap ppolicy, permanently locked account: 000001010000Z
	 * + with a time zone offset: 20250803112604+0800
	 * Other servers (eg: lldap) use ISO8601, eg: 2026-01-30T23:05:17.578672322+00:00
	 *
	 * @param string|null $value
	 * @return Carbon|null NULL if the value cannot be parsed
	 */
	public static function parse(?string $value): ?Carbon
	{
		if (! $value)
			return NULL;

		try {
			$m = [];
			if (! preg_match('/^([0-9]{10})([0-9]{2})?([0-9]{2})?(?:[.,]([0-9]+))?(Z|[+-][0-9]{2}(?:[0-9]{2})?)$/',$value,$m))
				return Carbon::parse($value);

			$tz = ($m[5] === 'Z') ? 'UTC' : substr($m[5],0,3).':'.(substr($m[5],3,2) ?: '00');
			$result = Carbon::createFromFormat('YmdHis',$x=$m[1].($m[2] ?: '00').($m[3] ?: '00'),$tz);

			// Invalid values (eg: month 13) would otherwise overflow into the next period
			if ((! $result) || ($result->format('YmdHis') !== $x))
				return NULL;

			// A fraction only applies to the seconds, if they are present
			if ($m[3] && $m[4])
				$result->setMicrosecond((int)substr(str_pad($m[4],6,'0'),0,6));

			return $result;

		} catch (\Exception $e) {
			return NULL;
		}
	}

	public function render(string $attrtag,int $index,?View $view=NULL,bool $edit=FALSE,bool $editable=FALSE,bool $new=FALSE,bool $updated=FALSE,?Template $template=NULL): View
	{
		return parent::render(
			attrtag: $attrtag,
			index: $index,
			view: view('components.attribute.value.internal.timestamp'));
	}
}