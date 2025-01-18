<?php

namespace App\Classes\LDAP\Attribute\Password;

final class SSHA384 extends Base
{
	public const key = 'SSHA384';
	protected const salt = 8;

	public function compare(string $source,string $compare): bool
	{
		return $source === $this->encode($compare,$this->salted_salt($source));
	}

	public function encode(string $password,string $salt=NULL): string
	{
		return sprintf('{%s}%s',self::key,$this->salted_hash($password,'sha384',self::salt,$salt));
	}
}