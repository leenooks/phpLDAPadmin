<?php

namespace App\Classes\LDAP\Attribute\Password;

final class SASL extends Base
{
	public const key = 'SASL';

	public function encode(string $password): string
	{
		if (! str_contains($password,'@'))
			return '';

		// Ensure our id is lowercase, and realm is uppercase
		list($id,$realm) = explode('@',$password);

		return sprintf('{%s}%s@%s',self::key,strtolower($id),strtoupper($realm));
	}
}