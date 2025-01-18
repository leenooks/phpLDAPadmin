<?php

namespace App\Classes\LDAP\Attribute\Password;

final class SHA extends Base
{
	public const key = 'SHA';

	public function encode(string $password): string
	{
		return sprintf('{%s}%s',self::key,base64_encode(hash('sha1',$password,true)));
	}
}