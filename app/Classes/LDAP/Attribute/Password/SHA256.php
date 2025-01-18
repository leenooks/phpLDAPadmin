<?php

namespace App\Classes\LDAP\Attribute\Password;

final class SHA256 extends Base
{
	public const key = 'SHA256';

	public function encode(string $password): string
	{
		return sprintf('{%s}%s',self::key,base64_encode(hash('sha256',$password,true)));
	}
}