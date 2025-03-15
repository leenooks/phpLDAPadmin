<?php

namespace App\Classes\LDAP\Attribute\Password;

final class SSHA extends Base
{
	public const key = 'SSHA';
	protected const salt = 8;

	public function compare(string $source,string $compare): bool
	{
		return $source === $this->encode($compare,$this->salted_salt($source));
	}

	public function encode(string $password,?string $salt=NULL): string
	{
		return sprintf('{%s}%s',self::key,$this->salted_hash($password,'sha1',self::salt,$salt));
	}

	public static function subid(string $password): bool
	{
		return preg_match('/^{'.static::key.'}/',$password);
	}
}