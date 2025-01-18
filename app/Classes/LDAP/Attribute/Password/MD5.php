<?php

namespace App\Classes\LDAP\Attribute\Password;

final class MD5 extends Base
{
	public const key = 'MD5';

	public function encode(string $password): string
	{
		return sprintf('{%s}%s',self::key,base64_encode(hash('md5',$password,true)));
	}
}