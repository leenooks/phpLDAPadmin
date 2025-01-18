<?php

namespace App\Classes\LDAP\Attribute\Password;

final class SHA512 extends Base
{
	public const key = 'SHA512';

	public function encode(string $password): string
	{
		return sprintf('{%s}%s',self::key,base64_encode(hash('sha512',$password,true)));
	}
}