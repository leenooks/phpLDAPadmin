<?php

namespace App\Classes\LDAP\Attribute\Password;

final class Argon2i extends Base
{
	public const key = 'ARGON2';
	protected const subkey = 'argon2i';
	protected const identifier = '$argon2i';

	public static function subid(string $password): bool
	{
		return str_starts_with(self::password($password),self::identifier.'$');
	}

	public function compare(string $source,string $compare): bool
	{
		return password_verify($compare,$this->password($source));
	}

	public function encode(string $password): string
	{
		return sprintf('{%s}%s',self::key,password_hash($password,PASSWORD_ARGON2I));
	}
}