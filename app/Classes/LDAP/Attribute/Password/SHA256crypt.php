<?php

namespace App\Classes\LDAP\Attribute\Password;

final class SHA256crypt extends Base
{
	public const key = 'CRYPT';
	protected const subkey = 'sha256crypt';
	protected const salt = 5;
	private const identifier = '$5$';

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