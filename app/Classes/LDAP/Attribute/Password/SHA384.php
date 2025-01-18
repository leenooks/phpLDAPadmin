<?php

namespace App\Classes\LDAP\Attribute\Password;

final class SHA384 extends Base
{
	public const key = 'SHA384';

	public function encode(string $password): string
	{
		return sprintf('{%s}%s',self::key,base64_encode(hash('sha384',$password,true)));
	}
}