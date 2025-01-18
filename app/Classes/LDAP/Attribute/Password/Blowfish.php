<?php

namespace App\Classes\LDAP\Attribute\Password;

final class Blowfish extends Base
{
	public const key = 'CRYPT';
	protected const subkey = 'blowfish';
	private const cost = 12;
	protected const salt = 22;
	private const identifier = '$2a$';

	public static function subid(string $password): bool
	{
		return preg_match('/^\\$2.\\$/',self::password($password));
	}

	public function compare(string $source,string $compare): bool
	{
		return hash_equals($cp=self::password($source),crypt($compare,$cp));
	}

	public function encode(string $password,string $salt=NULL): string
	{
		if (is_null($salt))
			$salt = sprintf('%s%d$%s',self::identifier,self::cost,random_salt(self::salt));

		return sprintf('{%s}%s',self::key,crypt($password,$salt));
	}
}