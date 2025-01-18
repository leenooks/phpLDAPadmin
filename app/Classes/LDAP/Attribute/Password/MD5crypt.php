<?php

namespace App\Classes\LDAP\Attribute\Password;

final class MD5crypt extends Base
{
	public const key = 'CRYPT';
	protected const subkey = 'md5crypt';
	protected const salt = 9;
	private const identifier = '$1$';

	public static function subid(string $password): bool
	{
		return str_starts_with(self::password($password),self::identifier);
	}

	public function compare(string $source,string $compare): bool
	{
		return hash_equals($cp=self::password($source),crypt($compare,$cp));
	}

	public function encode(string $password,string $salt=NULL): string
	{
		if (is_null($salt))
			$salt = sprintf('%s$%s',self::identifier,random_salt(self::salt));

		return sprintf('{%s}%s',self::key,crypt($password,$salt));
	}
}