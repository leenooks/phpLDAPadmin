<?php

namespace App\Classes\LDAP\Attribute\Internal;

use Carbon\Carbon;

use App\Classes\LDAP\Attribute\Internal;

/**
 * Represents an attribute whose values are timestamps
 */
final class Timestamp extends Internal
{
	public function __toString(): string
	{
		return Carbon::createFromTimestamp(strtotime($this->values[0]))->format(config('ldap.datetime_format','Y-m-d H:i:s'));
	}
}