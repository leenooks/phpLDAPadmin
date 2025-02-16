<?php

namespace App\Classes\LDAP\Attribute\Password;

final class ExtDes extends Base
{
	public const key = 'CRYPT';
	protected const subkey = 'ext_des';
	protected const salt = 8;
	private const identifier = '_';

	public static function subid(string $password): bool
	{
		return str_starts_with(self::password($password),self::identifier);
	}

	public function compare(string $source,string $compare): bool
	{
		return hash_equals($cp=self::password($source),crypt($compare,$cp));
	}

	public function encode(string $password,?string $salt=NULL): string
	{
		if (is_null($salt))
			$salt = sprintf('%s%s',self::identifier,random_salt(self::salt));

		return sprintf('{%s}%s',self::key,crypt($password,$salt));
	}
}