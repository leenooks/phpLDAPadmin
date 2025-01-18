<?php

namespace App\Classes\LDAP\Attribute\Password;

final class SSHA512 extends Base
{
	public const key = 'SSHA512';
	protected const salt = 8;

	public function compare(string $source,string $compare): bool
	{
		return $source === $this->encode($compare,$this->salted_salt($source));
	}

	public function encode(string $password,string $salt=NULL): string
	{
		return sprintf('{%s}%s',self::key,$this->salted_hash($password,'sha512',self::salt,$salt));
	}
}