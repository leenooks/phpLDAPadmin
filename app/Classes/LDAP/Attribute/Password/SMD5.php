<?php

namespace App\Classes\LDAP\Attribute\Password;

final class SMD5 extends Base
{
	public const key = 'SMD5';
	protected const salt = 8;

	public function compare(string $source,string $compare): bool
	{
		return $source === $this->encode($compare,$this->salted_salt($source));
	}

	public function encode(string $password,string $salt=NULL): string
	{
		if (is_null($salt))
			$salt = hex2bin(random_salt(self::salt));

		return sprintf('{%s}%s',self::key,$this->salted_hash($password,'md5',self::salt,$salt));
	}
}